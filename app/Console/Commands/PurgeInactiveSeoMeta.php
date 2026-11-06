<?php

namespace App\Console\Commands;

use App\Models\SeoMetaData;
use App\Models\SeoOGImage;
use App\Models\SeoTwitterImage;
use Illuminate\Console\Command;

/**
 * One-time cleanup for records soft-deleted (is_active=false) before the SEO
 * Meta "Delete" action became a hard delete. A soft-deleted row still occupies
 * its market/page/category combination, so it silently blocks re-creating a
 * fresh record there even though it no longer shows up in the list.
 *
 * Safe to re-run; once destroy() hard-deletes going forward, this will find
 * nothing left to purge.
 */
class PurgeInactiveSeoMeta extends Command
{
    protected $signature = 'seo-meta:purge-inactive {--dry-run : Report what would be deleted without writing}';

    protected $description = 'Hard-delete seo_meta_data records (and their OG/Twitter/Robot meta) left behind by the old soft-delete';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - nothing will be deleted.');
        }

        $records = SeoMetaData::where('is_active', false)->get();

        if ($records->isEmpty()) {
            $this->info('Nothing to purge.');
            return self::SUCCESS;
        }

        foreach ($records as $seoMeta) {
            $this->line(sprintf(
                'id=%s page_key=%s market_id=%s market_code=%s category_id=%s meta_title=%s',
                $seoMeta->_id,
                $seoMeta->page_key ?? '(none)',
                $seoMeta->market_id ?? '(none)',
                $seoMeta->market_code ?? '(none)',
                $seoMeta->category_id ?? '(none)',
                $seoMeta->meta_title ?? '(none)',
            ));

            if ($dryRun) {
                continue;
            }

            if ($seoMeta->ogMeta) {
                SeoOGImage::where('og_meta_id', (string) $seoMeta->ogMeta->_id)->delete();
                $seoMeta->ogMeta->delete();
            }
            if ($seoMeta->twitterMeta) {
                SeoTwitterImage::where('twitter_meta_id', (string) $seoMeta->twitterMeta->_id)->delete();
                $seoMeta->twitterMeta->delete();
            }
            if ($seoMeta->robotMeta) {
                $seoMeta->robotMeta->delete();
            }
            $seoMeta->delete();
        }

        $this->info(($dryRun ? 'Would purge ' : 'Purged ') . $records->count() . ' inactive record(s).');

        return self::SUCCESS;
    }
}
