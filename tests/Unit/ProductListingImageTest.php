<?php

namespace Tests\Unit;

use App\Models\ProductListingImage;
use PHPUnit\Framework\TestCase;

class ProductListingImageTest extends TestCase
{
    public function test_it_prefers_the_stored_r2_url(): void
    {
        $url = ProductListingImage::resolvePublicUrl([
            'url' => 'https://cdn.example.com/listings/photo.webp',
            'path' => 'listings/legacy-photo.webp',
        ], 'https://fallback.example.com');

        $this->assertSame('https://cdn.example.com/listings/photo.webp', $url);
    }

    public function test_it_builds_an_r2_url_for_older_path_only_images(): void
    {
        $url = ProductListingImage::resolvePublicUrl([
            'path' => '/listings/photo.webp',
        ], 'https://cdn.example.com/');

        $this->assertSame('https://cdn.example.com/listings/photo.webp', $url);
    }

    public function test_user_listings_tab_uses_the_normalized_r2_url(): void
    {
        $view = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/admin/users/edit.blade.php'
        );

        $this->assertStringContainsString('$firstImage?->public_url', $view);
        $this->assertStringNotContainsString(
            "asset('storage/' . \$firstImage->image['path'])",
            $view
        );
    }

    public function test_listing_edit_reconciles_existing_images_and_uses_the_r2_uploader(): void
    {
        $controller = file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Controllers/Admin/ProductListingController.php'
        );
        $view = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/admin/product_listing/edit.blade.php'
        );

        $this->assertStringContainsString('syncListingImages($request, $listing)', $controller);
        $this->assertStringContainsString('listingImageService->store($file)', $controller);
        $this->assertStringNotContainsString("storeAs('product-listings', \$filename, 'public')", $controller);
        $this->assertStringContainsString('name="image_manifest_present"', $view);
    }
}
