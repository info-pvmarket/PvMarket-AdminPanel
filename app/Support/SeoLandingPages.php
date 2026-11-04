<?php

namespace App\Support;

/** Standalone market-scoped pages managed by SEO Meta, not Static Pages. */
final class SeoLandingPages
{
    public const PAGES = [
        'all_categories' => ['label' => 'All Categories', 'path' => '/all-categories'],
        'brands' => ['label' => 'Brands', 'path' => '/brands'],
        'discount_center' => ['label' => 'Discount Center', 'path' => '/discount-center'],
        'request_for_quote' => ['label' => 'Request for Quote', 'path' => '/request-for-quote'],
    ];

    public static function contains(?string $key): bool
    {
        return isset(self::PAGES[$key ?? '']);
    }
}
