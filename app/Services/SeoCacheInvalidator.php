<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeoCacheInvalidator
{
    public function invalidate(?string $canonicalUrl): bool
    {
        if (blank($canonicalUrl)) {
            return false;
        }

        $endpoint = config('services.frontend.seo_revalidate_url');
        $secret = config('services.frontend.revalidate_secret');
        if (blank($endpoint) || blank($secret)) {
            Log::warning('SEO route cache was not revalidated because the frontend endpoint is not configured.', [
                'canonical_url' => $canonicalUrl,
            ]);
            return false;
        }

        try {
            Http::asJson()
                ->acceptJson()
                ->withToken($secret)
                ->timeout(5)
                ->post($endpoint, ['canonical_url' => $canonicalUrl])
                ->throw();

            return true;
        } catch (\Throwable $exception) {
            // Saving SEO content must remain durable even during a frontend
            // restart. The next uncached request still sees the new record and
            // this warning makes a missed invalidation observable.
            Log::warning('Failed to revalidate frontend SEO route cache.', [
                'canonical_url' => $canonicalUrl,
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }
}
