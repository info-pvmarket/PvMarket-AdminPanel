<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasTranslations
{
    public function trans(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? session('admin_lang', 'en');
        $original = $this->translationScalarToString($this->{$field} ?? null) ?? '';

        if ($locale === 'en') {
            return $original;
        }

        // STEP 1: Check DB first
        $translations = $this->{$locale} ?? null;
        if (is_object($translations) && method_exists($translations, 'getArrayCopy')) {
            $translations = $translations->getArrayCopy();
        } elseif (is_object($translations)) {
            $translations = (array) $translations;
        }

        if (is_array($translations) && array_key_exists($field, $translations)) {
            $storedTranslation = $this->translationScalarToString($translations[$field]);

            if ($storedTranslation !== null && $storedTranslation !== '') {
                return $storedTranslation;
            }

            // This helper renders strings. Preserve structured data in MongoDB,
            // but safely fall back to the source value when displaying it.
            if (is_array($translations[$field]) || is_object($translations[$field])) {
                return $original;
            }
        }

        // STEP 2: Not in DB → call AWS
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
        $translated = $this->translationScalarToString($translated);

        // STEP 3: Save to DB so next request skips API entirely
        if ($translated && $translated !== $original) {
            try {
                $localeData = $this->{$locale} ?? null;
                if (is_object($localeData) && method_exists($localeData, 'getArrayCopy')) {
                    $existing = $localeData->getArrayCopy();
                } elseif (is_object($localeData)) {
                    $existing = (array) $localeData;
                } elseif (is_array($localeData)) {
                    $existing = $localeData;
                } else {
                    $existing = [];
                }
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

    private function translationScalarToString(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return null;
    }
}
