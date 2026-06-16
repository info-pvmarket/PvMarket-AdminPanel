<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasTranslations
{
    public function trans(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? session('admin_lang', 'en');

        if ($locale === 'en') {
            return (string) ($this->{$field} ?? '');
        }

        // STEP 1: Check DB first
        $translations = $this->{$locale};
        if (is_array($translations) && isset($translations[$field]) && $translations[$field] !== '') {
            return (string) $translations[$field];
        }

        // STEP 2: Not in DB → call AWS
        $original = (string) ($this->{$field} ?? '');
        if (empty($original)) return '';

        $cacheKey = 'trans_' . class_basename($this) . '_' . $this->_id . '_' . $field . '_' . $locale;

        $translated = Cache::remember($cacheKey, now()->addDays(30), function () use ($original, $locale) {
            try {
                return app(\App\Services\TranslationService::class)
                    ->translateText($original, $locale, 'en');
            } catch (\Exception $e) {
                return null;
            }
        });

        // STEP 3: Save to DB so next request skips API entirely
        if ($translated && $translated !== $original) {
            try {
                $existing = is_array($this->{$locale}) ? $this->{$locale} : [];
                $existing[$field] = $translated;

                // Raw query — no timestamps touched
                $this->newQuery()->where('_id', $this->_id)->update([$locale => $existing]);

                // Update in-memory so same request doesn't re-translate
                $this->setAttribute($locale, $existing);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('saveTranslationToDb: ' . $e->getMessage());
            }
        }

        return (string) ($translated ?? $original);
    }

    /**
     * Read a translated ARRAY field (e.g. PageSection.extra).
     *
     * Returns the stored translation for the locale merged over the
     * English original, so any key that was not translated (paths,
     * links, numbers, or newly-added keys) falls back to the original.
     */
    public function transArray(string $field, ?string $locale = null): array
    {
        $original = is_array($this->{$field}) ? $this->{$field} : [];

        $locale = $locale ?? session('admin_lang', 'en');
        if ($locale === 'en') return $original;

        $translations = $this->{$locale};
        if (is_array($translations)
            && isset($translations[$field])
            && is_array($translations[$field])) {
            return array_replace_recursive($original, $translations[$field]);
        }

        return $original;
    }
}