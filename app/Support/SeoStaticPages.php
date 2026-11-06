<?php

namespace App\Support;

/**
 * The static storefront pages (Homepage, About Us, ...) now manageable from
 * SEO Meta. Keys and labels mirror PageSectionController::$pages exactly -
 * these are the same `seo_meta_data` page_key records Static Pages already
 * writes, just made editable here too.
 */
final class SeoStaticPages
{
    public const PAGES = [
        'home'             => ['label' => 'Homepage', 'path' => '/'],
        'about'            => ['label' => 'About Us', 'path' => '/about-us'],
        'contact'          => ['label' => 'Contact Us', 'path' => '/contact-us'],
        'terms'            => ['label' => 'Terms & Conditions', 'path' => '/terms-and-conditions'],
        'delivery'         => ['label' => 'Delivery & Return Policy', 'path' => '/delivery-and-return-policy'],
        'disclaimer'       => ['label' => 'Disclaimer', 'path' => '/disclaimer'],
        'privacy'          => ['label' => 'Privacy Policy', 'path' => '/privacy-cookies-policy'],
        // No storefront route exists for this one yet (PageSectionController::$pageRoutes
        // omits it too) - kept here only so its existing SEO record stays editable.
        'customer_support' => ['label' => 'Customer Support', 'path' => null],
        'faq'              => ['label' => 'FAQ', 'path' => '/faq'],
    ];

    public static function contains(?string $key): bool
    {
        return isset(self::PAGES[$key ?? '']);
    }
}
