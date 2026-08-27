<?php

namespace Tests\Unit;

use App\Models\News;
use PHPUnit\Framework\TestCase;

class NewsImageTest extends TestCase
{
    public function test_it_prefers_an_absolute_stored_r2_url(): void
    {
        $url = News::resolvePublicImageUrl([
            'url' => 'https://cdn.example.com/news/article.webp',
            'path' => 'news/article.webp',
        ], 'https://admin.example.com/storage');

        $this->assertSame('https://cdn.example.com/news/article.webp', $url);
    }

    public function test_it_builds_a_public_storage_url_for_relative_paths(): void
    {
        $url = News::resolvePublicImageUrl([
            'path' => '/news/article.webp',
        ], 'https://admin.example.com/storage/');

        $this->assertSame('https://admin.example.com/storage/news/article.webp', $url);
    }

    public function test_it_accepts_legacy_scalar_and_storage_prefixed_images(): void
    {
        $url = News::resolvePublicImageUrl(
            '/storage/news/legacy.jpg',
            'https://admin.example.com/storage'
        );

        $this->assertSame('https://admin.example.com/storage/news/legacy.jpg', $url);
    }

    public function test_news_views_use_the_normalized_image_url(): void
    {
        $view = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/admin/knowledge-hub/news/news.blade.php'
        );

        $this->assertStringContainsString('$item->image_display_url', $view);
        $this->assertStringContainsString('$record->image_display_url', $view);
        $this->assertStringNotContainsString("asset('storage/' . \$item->image['path'])", $view);
    }
}
