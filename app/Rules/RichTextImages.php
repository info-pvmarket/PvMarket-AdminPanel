<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rich text must reference uploaded images, never embed them as base64, and
 * every new image needs alt text (TASK-6.4). Existing blog inline images can
 * round-trip until migrated to uploaded files. Accepts an HTML string, an array of
 * them, or a JSON-encoded array (page section "extra").
 */
class RichTextImages implements ValidationRule
{
    public function __construct(private readonly string $existingHtml = '') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existingSources = $this->inlineSources($this->existingHtml);
        $legacyWithoutAlt = $this->inlineSources($this->existingHtml, withoutAltOnly: true);
        foreach ($this->htmlStrings($value) as $html) {
            if (! preg_match_all('/<img\b[^>]*>/i', $html, $tags)) {
                continue;
            }
            foreach ($tags[0] as $tag) {
                if (preg_match('/\bsrc\s*=\s*["\']?\s*data:/i', $tag)) {
                    // A16: permit only the exact raster image already stored on
                    // this record. New inline images still require an upload.
                    $sources = $this->inlineSources($tag);
                    if (! $sources || ! in_array($sources[0], $existingSources, true)) {
                        $fail('Images cannot be pasted into the text. Use the editor\'s image button to upload them.');
                        return;
                    }
                    // Only grandfather missing alt if it was already missing.
                    if (in_array($sources[0], $legacyWithoutAlt, true)) {
                        continue;
                    }
                }
                if (! preg_match('/\balt\s*=\s*(["\'])\s*[^"\'\s][^"\']*\1/i', $tag)) {
                    $fail('Every image in the text needs alt text. Click the image in the editor to add it.');
                    return;
                }
            }
        }
    }

    private function inlineSources(string $html, bool $withoutAltOnly = false): array
    {
        if ($html === '') {
            return [];
        }
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<html><body>'.$html.'</body></html>', LIBXML_NONET);
            $sources = [];
            foreach ($document->getElementsByTagName('img') as $image) {
                if ($withoutAltOnly && trim($image->getAttribute('alt')) !== '') {
                    continue;
                }
                $src = $image->getAttribute('src');
                if (preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,/i', $src)) {
                    $sources[] = $src;
                }
            }
            return $sources;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function htmlStrings(mixed $value): array
    {
        if (is_array($value)) {
            return array_merge([], ...array_map(fn ($item) => $this->htmlStrings($item), array_values($value)));
        }
        if (! is_string($value)) {
            return [];
        }
        $trimmed = ltrim($value);
        if ((str_starts_with($trimmed, '{') || str_starts_with($trimmed, '['))
            && is_array($decoded = json_decode($value, true))) {
            return $this->htmlStrings($decoded);
        }
        return [$value];
    }
}
