<?php

namespace App\Traits;

use App\Models\SeoMetaData;
use App\Models\SeoOGImage;
use App\Models\SeoTwitterImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Writes an SEO Meta record and its Open Graph / Twitter / robots children.
 *
 * Extracted from SeoMetaController so the Static Pages screen can offer the same
 * form. The persistence shape is deliberately identical: one `seo_meta_data`
 * parent joined to `seo_o_g_meta_data`, `seo_twitter_meta_data`,
 * `seo_robot_meta_data` and their image collections, which is what the Go API's
 * /seo-meta endpoint reads.
 */
trait WritesSeoMetaRecords
{
    /**
     * Validation rules for the full SEO form.
     *
     * Mirrors SeoMetaController::store(). `meta_title` is required only when the
     * editor has started filling the block in - see seoBlockIsEmpty().
     */
    protected function seoMetaValidationRules(): array
    {
        return [
            'meta_title'         => 'nullable|string|max:255',
            'meta_description'   => 'nullable|string|max:500',
            'meta_keywords'      => 'nullable|string|max:500',
            'canonical_url'      => 'nullable|url|max:500',
            'page_header'        => 'nullable|string|max:255',
            'short_description'  => 'nullable|string',
            'bottom_header'      => 'nullable|string|max:255',
            'bottom_description' => 'nullable|string',

            'og_title'       => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_type'        => 'nullable|in:website,article,product',
            'og_url'         => 'nullable|url|max:500',
            'og_site_name'   => 'nullable|string|max:255',
            'og_locale'      => 'nullable|string|max:20',
            'og_images'      => 'nullable|array',
            'og_images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:5120',

            'twitter_card'        => 'nullable|in:summary,summary_large_image,app,player',
            'twitter_site'        => 'nullable|string|max:255',
            'twitter_creator'     => 'nullable|string|max:255',
            'twitter_title'       => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:500',
            'twitter_images'      => 'nullable|array',
            'twitter_images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:5120',

            'max_snippet'       => 'nullable|integer|min:-1',
            'max_image_preview' => 'nullable|in:none,standard,large',
            'max_video_preview' => 'nullable|integer|min:-1',
        ];
    }

    protected function seoMetaValidationMessages(): array
    {
        return [
            'canonical_url.url'  => 'The canonical URL must be a full URL, e.g. https://pv.market/about-us.',
            'og_url.url'         => 'The OG URL must be a full URL.',
            'og_images.*.max'    => 'Each Open Graph image must not be larger than 5 MB.',
            'twitter_images.*.max' => 'Each Twitter image must not be larger than 5 MB.',
        ];
    }

    /**
     * True when the editor has left the whole SEO block untouched, in which case
     * no record is created at all and the storefront keeps its own fallbacks.
     */
    protected function seoBlockIsEmpty(Request $request): bool
    {
        $textFields = [
            'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
            'og_title', 'og_description', 'og_url',
            'twitter_title', 'twitter_description',
            // Only present on forms that offer the on-page copy fields.
            'page_header', 'short_description', 'bottom_header', 'bottom_description',
        ];

        foreach ($textFields as $field) {
            if (trim((string) $request->input($field)) !== '') {
                return false;
            }
        }

        return !$request->hasFile('og_images') && !$request->hasFile('twitter_images');
    }

    /**
     * Create or update the SEO record identified by $identity.
     *
     * @param  array  $identity  the columns that uniquely locate the record,
     *                           e.g. ['page_key' => 'about', 'market_code' => 'ae'].
     *                           Also written onto the record itself.
     * @param  array  $extra     additional columns to persist (e.g. market_id).
     */
    protected function saveSeoMetaRecord(Request $request, array $identity, array $extra = []): ?SeoMetaData
    {
        $seoMeta = $this->findSeoMetaRecord($identity);

        if ($this->seoBlockIsEmpty($request)) {
            // Nothing authored. Leave an existing record alone rather than
            // silently blanking copy someone entered earlier.
            return $seoMeta;
        }

        $parent = array_merge($identity, $extra, [
            'meta_title'       => $this->seoValue($request, 'meta_title'),
            'meta_description' => $this->seoValue($request, 'meta_description'),
            'meta_keywords'    => $this->seoValue($request, 'meta_keywords'),
            'canonical_url'    => $this->seoValue($request, 'canonical_url'),
            'is_active'        => true,
        ]);

        // On-page copy is only offered on forms that own the page body (the SEO
        // Meta screen for category and brand pages). Static pages take their
        // body content from page sections instead and do not post these, so they
        // are written only when the form actually provides them - otherwise an
        // update would blank copy a different screen had authored.
        foreach (['page_header', 'short_description', 'bottom_header', 'bottom_description'] as $field) {
            if ($request->has($field)) {
                $parent[$field] = $this->seoValue($request, $field);
            }
        }

        if ($seoMeta) {
            $parent['updated_by'] = Auth::id();
            $seoMeta->update($parent);
        } else {
            $parent['created_by'] = Auth::id();
            // Keep nulls out of MongoDB, matching SeoMetaController::store().
            $seoMeta = SeoMetaData::create(array_filter(
                $parent,
                fn ($value) => $value !== null
            ));
        }

        $this->saveOpenGraph($request, $seoMeta);
        $this->saveTwitter($request, $seoMeta);
        $this->saveRobots($request, $seoMeta);

        return $seoMeta;
    }

    /** Locate an existing record by its identity columns. */
    protected function findSeoMetaRecord(array $identity): ?SeoMetaData
    {
        $query = SeoMetaData::query();

        foreach ($identity as $field => $value) {
            if ($value === null || $value === '') {
                // Absent dimensions must be absent on the record too, otherwise a
                // global record would match a market-scoped lookup.
                $query->where(function ($q) use ($field) {
                    $q->whereNull($field)->orWhere($field, '');
                });
                continue;
            }

            $query->where($field, $value);
        }

        return $query->first();
    }

    /** Open Graph is always written so the storefront has a complete card. */
    private function saveOpenGraph(Request $request, SeoMetaData $seoMeta): void
    {
        $ogMeta = $seoMeta->ogMeta()->updateOrCreate([], [
            'og_title'       => $this->seoValue($request, 'og_title') ?? $request->input('meta_title'),
            'og_description' => $this->seoValue($request, 'og_description') ?? $request->input('meta_description'),
            'og_type'        => $request->input('og_type') ?: 'website',
            'og_url'         => $this->seoValue($request, 'og_url'),
            'og_site_name'   => $this->seoValue($request, 'og_site_name') ?? 'PV Market',
            'og_locale'      => $this->seoValue($request, 'og_locale') ?? 'en_US',
            'is_active'      => true,
            'updated_by'     => Auth::id(),
        ]);

        $this->handleSeoOgImages($request, (string) $ogMeta->getKey());
    }

    /** Twitter is only written once an editor has filled something in. */
    private function saveTwitter(Request $request, SeoMetaData $seoMeta): void
    {
        $hasContent = $this->seoValue($request, 'twitter_title')
            || $this->seoValue($request, 'twitter_description')
            || $request->hasFile('twitter_images');

        $existing = $seoMeta->twitterMeta;

        if (!$hasContent && !$existing) {
            return;
        }

        $twitterMeta = $seoMeta->twitterMeta()->updateOrCreate([], [
            'twitter_card'        => $request->input('twitter_card') ?: 'summary_large_image',
            'twitter_site'        => $this->seoValue($request, 'twitter_site'),
            'twitter_creator'     => $this->seoValue($request, 'twitter_creator'),
            'twitter_title'       => $this->seoValue($request, 'twitter_title') ?? $request->input('meta_title'),
            'twitter_description' => $this->seoValue($request, 'twitter_description') ?? $request->input('meta_description'),
            'is_active'           => true,
            'updated_by'          => Auth::id(),
        ]);

        $this->handleSeoTwitterImages($request, (string) $twitterMeta->getKey());
    }

    /** Robots directives, always written so index/follow are explicit. */
    private function saveRobots(Request $request, SeoMetaData $seoMeta): void
    {
        $seoMeta->robotMeta()->updateOrCreate([], [
            'index'             => $request->boolean('robot_index'),
            'follow'            => $request->boolean('robot_follow'),
            'noarchive'         => $request->boolean('robot_noarchive'),
            'nosnippet'         => $request->boolean('robot_nosnippet'),
            'noimageindex'      => $request->boolean('robot_noimageindex'),
            'nocache'           => $request->boolean('robot_nocache'),
            'max_snippet'       => $request->input('max_snippet'),
            'max_image_preview' => $request->input('max_image_preview') ?: 'large',
            'max_video_preview' => $request->input('max_video_preview'),
            'is_active'         => true,
            'updated_by'        => Auth::id(),
        ]);
    }

    /** Uploads and deletions for Open Graph images. */
    private function handleSeoOgImages(Request $request, string $ogMetaId): void
    {
        if ($request->hasFile('og_images')) {
            foreach ($request->file('og_images') as $index => $file) {
                $filename = time().'_og_'.$index.'_'.$file->getClientOriginalName();
                $path = $file->storeAs('uploads/seo/og', $filename, 'public');

                SeoOGImage::create([
                    'og_meta_id'   => $ogMetaId,
                    'image_url'    => $path,
                    'image_alt'    => $request->input("og_images_alt.{$index}"),
                    'image_width'  => 1200,
                    'image_height' => 630,
                    'sort_order'   => $index,
                    'is_active'    => true,
                ]);
            }
        }

        if ($request->filled('delete_og_images')) {
            SeoOGImage::whereIn('_id', (array) $request->input('delete_og_images'))->delete();
        }
    }

    /** Uploads and deletions for Twitter images. */
    private function handleSeoTwitterImages(Request $request, string $twitterMetaId): void
    {
        if ($request->hasFile('twitter_images')) {
            foreach ($request->file('twitter_images') as $index => $file) {
                $filename = time().'_twitter_'.$index.'_'.$file->getClientOriginalName();
                $path = $file->storeAs('uploads/seo/twitter', $filename, 'public');

                SeoTwitterImage::create([
                    'twitter_meta_id' => $twitterMetaId,
                    'image_url'       => $path,
                    'image_alt'       => $request->input("twitter_images_alt.{$index}"),
                    'sort_order'      => $index,
                    'is_active'       => true,
                ]);
            }
        }

        if ($request->filled('delete_twitter_images')) {
            SeoTwitterImage::whereIn('_id', (array) $request->input('delete_twitter_images'))->delete();
        }
    }

    /** Trim to null so blank inputs are stored as absent rather than "". */
    private function seoValue(Request $request, string $field): ?string
    {
        $value = trim((string) $request->input($field));

        return $value === '' ? null : $value;
    }
}
