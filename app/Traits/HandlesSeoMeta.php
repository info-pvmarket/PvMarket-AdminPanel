<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Shared "SEO tags" block for the Knowledge Hub content types (blogs, news and
 * events).
 *
 * Every field is optional: the storefront falls back to the record's own title,
 * content and image when a tag is left blank, so an editor only fills in what
 * they want to override. The whole block is stored as a single `seo`
 * sub-document on the record itself, which is what the Go API hands to the
 * Next.js `generateMetadata()` of the matching detail page.
 */
trait HandlesSeoMeta
{
    /** Twitter card types offered in the form. */
    public static array $seoTwitterCards = ['summary', 'summary_large_image'];

    /**
     * Validation rules for the SEO block. Lengths follow what search engines
     * actually render, but are only advisory - nothing here is required.
     */
    protected function seoValidationRules(): array
    {
        return [
            'seo_meta_title'          => 'nullable|string|max:255',
            'seo_meta_description'    => 'nullable|string|max:500',
            'seo_meta_keywords'       => 'nullable|string|max:500',
            'seo_canonical_url'       => 'nullable|string|max:2048|url',
            'seo_og_title'            => 'nullable|string|max:255',
            'seo_og_description'      => 'nullable|string|max:500',
            'seo_og_image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'seo_og_image_alt'        => 'nullable|string|max:255',
            'seo_twitter_card'        => 'nullable|string|in:'.implode(',', self::$seoTwitterCards),
            'seo_twitter_title'       => 'nullable|string|max:255',
            'seo_twitter_description' => 'nullable|string|max:500',
            'seo_robots_index'        => 'nullable|boolean',
            'seo_robots_follow'       => 'nullable|boolean',
        ];
    }

    /** Human-readable messages for the few rules that can actually trip. */
    protected function seoValidationMessages(): array
    {
        return [
            'seo_canonical_url.url' => 'The canonical URL must be a full URL, e.g. https://pv.market/blogs/my-post.',
            'seo_og_image.max'      => 'The social share image must not be larger than 5 MB.',
            'seo_og_image.image'    => 'The social share image must be a JPG, PNG or WebP file.',
        ];
    }

    /**
     * Build the `seo` sub-document from the submitted form.
     *
     * @param  array|object|null  $existing  the record's current `seo` value, so
     *                                       an edit that does not re-upload the
     *                                       social image keeps the current one.
     * @param  string  $folder  storage folder for a newly uploaded social image.
     */
    protected function buildSeoData(Request $request, mixed $existing = null, string $folder = 'seo'): array
    {
        $existing = $this->normalizeSeoArray($existing);

        $ogImage = $existing['og_image'] ?? null;

        if ($request->boolean('seo_og_image_remove')) {
            $this->deleteSeoImage($ogImage);
            $ogImage = null;
        }

        if ($request->hasFile('seo_og_image') && $request->file('seo_og_image')->isValid()) {
            $this->deleteSeoImage($ogImage);
            $ogImage = $this->storeSeoImage(
                $request->file('seo_og_image'),
                $folder,
                $request->input('seo_og_image_alt'),
            );
        } elseif (is_array($ogImage) && $request->filled('seo_og_image_alt')) {
            // Alt text can be edited without re-uploading the image.
            $ogImage['alt'] = trim((string) $request->input('seo_og_image_alt'));
        }

        $twitterCard = (string) $request->input('seo_twitter_card', '');
        if (!in_array($twitterCard, self::$seoTwitterCards, true)) {
            $twitterCard = 'summary_large_image';
        }

        return [
            'meta_title'          => $this->seoString($request->input('seo_meta_title')),
            'meta_description'    => $this->seoString($request->input('seo_meta_description')),
            'meta_keywords'       => $this->seoString($request->input('seo_meta_keywords')),
            'canonical_url'       => $this->seoString($request->input('seo_canonical_url')),
            'og_title'            => $this->seoString($request->input('seo_og_title')),
            'og_description'      => $this->seoString($request->input('seo_og_description')),
            'og_image'            => $ogImage,
            'twitter_card'        => $twitterCard,
            'twitter_title'       => $this->seoString($request->input('seo_twitter_title')),
            'twitter_description' => $this->seoString($request->input('seo_twitter_description')),
            // Checkboxes are absent from the payload when unticked, so an
            // existing record keeps indexing on unless explicitly turned off.
            'robots_index'        => $request->boolean('seo_robots_index'),
            'robots_follow'       => $request->boolean('seo_robots_follow'),
            'updated_at'          => now()->toISOString(),
        ];
    }

    /** Trim to null so blank inputs are stored as absent rather than "". */
    private function seoString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Accepts the BSON document / array / JSON string shapes the `seo` field can
     * come back as and returns a plain array.
     */
    protected function normalizeSeoArray(mixed $value): array
    {
        if (is_object($value) && method_exists($value, 'getArrayCopy')) {
            $value = $value->getArrayCopy();
        } elseif ($value instanceof \JsonSerializable) {
            $value = json_decode(json_encode($value), true);
        } elseif (is_object($value)) {
            $value = (array) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (!is_array($value)) {
            return [];
        }

        // The nested image can itself be a BSON document.
        if (isset($value['og_image'])) {
            $image = $value['og_image'];
            if (is_object($image) && method_exists($image, 'getArrayCopy')) {
                $value['og_image'] = $image->getArrayCopy();
            } elseif (is_object($image)) {
                $value['og_image'] = (array) $image;
            }
        }

        return $value;
    }

    /** Store an uploaded social image in the same shape as the other uploads. */
    private function storeSeoImage($file, string $folder, ?string $alt): array
    {
        $filename = time().'_'.$file->getClientOriginalName();
        $path     = $file->storeAs($folder.'/seo', $filename, 'public');

        return [
            'size'          => $file->getSize(),
            'uploaded_at'   => now()->toISOString(),
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'url'           => $path,
            'mime_type'     => $file->getMimeType(),
            'alt'           => trim((string) ($alt ?? '')),
        ];
    }

    /** Remove a previously uploaded social image from disk. */
    private function deleteSeoImage(mixed $image): void
    {
        $path = is_array($image) ? ($image['path'] ?? null) : null;

        if (is_string($path) && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}
