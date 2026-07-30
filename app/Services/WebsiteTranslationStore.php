<?php

namespace App\Services;

use App\Models\WebsiteTranslation;
use Illuminate\Support\Facades\Cache;

class WebsiteTranslationStore
{
    private const CACHE_SECONDS = 86400;

    /**
     * Replace one complete language bundle while preserving the nested section
     * structure supplied by the frontend translation files.
     *
     * @param  array<string, mixed>  $payload
     */
    public function replace(string $language, array $payload): WebsiteTranslation
    {
        $bundle = WebsiteTranslation::query()->updateOrCreate(
            ['language' => $language],
            $payload,
        );

        Cache::forget($this->cacheKey($language));

        return $bundle;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $language): ?array
    {
        return Cache::remember(
            $this->cacheKey($language),
            self::CACHE_SECONDS,
            function () use ($language): ?array {
                $bundle = WebsiteTranslation::query()
                    ->where('language', $language)
                    ->first();

                if (! $bundle) {
                    return null;
                }

                return [
                    'version' => (int) $bundle->version,
                    'language' => (string) $bundle->language,
                    'source_language' => (string) $bundle->source_language,
                    'source_hash' => (string) $bundle->source_hash,
                    'content_hash' => (string) $bundle->content_hash,
                    'generated_at' => $bundle->generated_at?->toIso8601String(),
                    'sections' => (array) $bundle->sections,
                ];
            },
        );
    }

    private function cacheKey(string $language): string
    {
        return "website-translations:{$language}";
    }
}
