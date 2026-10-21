<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rich text must reference uploaded images, never embed them as base64, and
 * every image needs alt text (TASK-6.4). Accepts an HTML string, an array of
 * them, or a JSON-encoded array (page section "extra").
 */
class RichTextImages implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->htmlStrings($value) as $html) {
            if (! preg_match_all('/<img\b[^>]*>/i', $html, $tags)) {
                continue;
            }
            foreach ($tags[0] as $tag) {
                if (preg_match('/\bsrc\s*=\s*["\']?\s*data:/i', $tag)) {
                    $fail('Images cannot be pasted into the text. Use the editor\'s image button to upload them.');
                    return;
                }
                if (! preg_match('/\balt\s*=\s*(["\'])\s*[^"\'\s][^"\']*\1/i', $tag)) {
                    $fail('Every image in the text needs alt text. Click the image in the editor to add it.');
                    return;
                }
            }
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