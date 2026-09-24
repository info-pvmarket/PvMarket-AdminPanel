<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Market;
use App\Models\PageSetting;
use App\Models\SeoMetaData;
use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;

/**
 * Carries the old page_settings SEO columns into seo_meta_data.
 *
 * The Static Pages screen used to save seo_title / seo_description /
 * seo_keywords onto `page_settings`, but the Go API never exposed that
 * collection, so nothing an editor typed ever reached the storefront. SEO now
 * lives in `seo_meta_data` keyed by `page_key`, which /seo-meta does serve.
 *
 * Idempotent: a page that already has an SEO record is left untouched, so this
 * is safe to re-run.
 */
class MigratePageSettingsSeo extends Command
{
    protected $signature = 'seo:migrate-page-settings {--dry-run : Report what would change without writing}';

    protected $description = 'Copy legacy page_settings SEO fields into seo_meta_data records';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - nothing will be written.');
        }

        $created = 0;
        $skipped = 0;
        $empty   = 0;

        foreach (PageSetting::all() as $setting) {
            $page = (string) $setting->page;
            if ($page === '') {
                continue;
            }

            $title       = trim((string) $setting->seo_title);
            $description = trim((string) $setting->seo_description);
            $keywords    = trim((string) $setting->seo_keywords);

            if ($title === '' && $description === '' && $keywords === '') {
                $empty++;
                continue;
            }

            $marketCode = $this->marketCodeFor($setting->location_id);
            $label = $page.' ['.($marketCode ?? 'global').']';

            if ($this->recordExists($page, $marketCode)) {
                $this->line("  skip    {$label} - already has an SEO record");
                $skipped++;
                continue;
            }

            $this->info("  migrate {$label} - \"{$title}\"");

            if (!$dryRun) {
                $data = array_filter([
                    'page_key'         => $page,
                    'market_code'      => $marketCode,
                    'meta_title'       => $title !== '' ? $title : null,
                    'meta_description' => $description !== '' ? $description : null,
                    'meta_keywords'    => $keywords !== '' ? $keywords : null,
                    'is_active'        => true,
                ], fn ($value) => $value !== null);

                $seoMeta = SeoMetaData::create($data);

                // Give the record a usable social card and explicit robots, the
                // same defaults the form applies on a first save.
                $seoMeta->ogMeta()->updateOrCreate([], [
                    'og_title'       => $title !== '' ? $title : null,
                    'og_description' => $description !== '' ? $description : null,
                    'og_type'        => 'website',
                    'og_site_name'   => 'PV Market',
                    'og_locale'      => 'en_US',
                    'is_active'      => true,
                ]);

                $seoMeta->robotMeta()->updateOrCreate([], [
                    'index'             => true,
                    'follow'            => true,
                    'max_image_preview' => 'large',
                    'is_active'         => true,
                ]);
            }

            $created++;
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d record(s); skipped %d that already existed; %d page_settings row(s) had no SEO values.',
            $dryRun ? 'Would create' : 'Created',
            $created,
            $skipped,
            $empty
        ));

        return self::SUCCESS;
    }

    /**
     * page_settings scopes by Country _id; seo_meta_data scopes by the lowercase
     * market code. Map one to the other, null meaning the global record.
     */
    private function marketCodeFor(mixed $locationId): ?string
    {
        if (empty($locationId)) {
            return null;
        }

        $country = Country::find($locationId instanceof ObjectId ? $locationId : (string) $locationId);
        if (!$country || !$country->iso2) {
            return null;
        }

        $market = Market::where('code', strtolower($country->iso2))
            ->orWhere('code', strtoupper($country->iso2))
            ->first();

        $code = $market?->code ?? $country->iso2;

        return strtolower($code);
    }

    /** Idempotency guard: does this page + market already have a record? */
    private function recordExists(string $page, ?string $marketCode): bool
    {
        $query = SeoMetaData::where('page_key', $page);

        if ($marketCode === null) {
            $query->where(function ($q) {
                $q->whereNull('market_code')->orWhere('market_code', '');
            });
        } else {
            $query->where('market_code', $marketCode);
        }

        return $query->exists();
    }
}
