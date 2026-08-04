<?php

namespace Tests\Feature;

use App\Services\WebsiteTranslationStore;
use Mockery;
use Tests\TestCase;

class WebsiteTranslationApiTest extends TestCase
{
    public function test_it_returns_the_nested_mongodb_translation_bundle(): void
    {
        $bundle = [
            'version' => 1,
            'language' => 'de',
            'source_language' => 'en',
            'source_hash' => 'source-hash',
            'content_hash' => 'content-hash',
            'generated_at' => '2026-07-30T10:00:00+00:00',
            'sections' => [
                'topbar' => ['events' => 'Veranstaltungen'],
                'footer' => ['home' => 'Startseite'],
            ],
        ];

        $store = Mockery::mock(WebsiteTranslationStore::class);
        $store->shouldReceive('find')
            ->once()
            ->with('de')
            ->andReturn($bundle);
        $this->app->instance(WebsiteTranslationStore::class, $store);

        $response = $this->getJson('/api/website-translations/de');

        $response
            ->assertOk()
            ->assertHeader('ETag', '"content-hash"')
            ->assertHeader('Cache-Control', 'max-age=300, public, stale-while-revalidate=3600')
            ->assertJsonPath('sections.topbar.events', 'Veranstaltungen')
            ->assertJsonPath('sections.footer.home', 'Startseite');
    }

    public function test_it_returns_not_found_when_a_language_has_not_been_translated(): void
    {
        $store = Mockery::mock(WebsiteTranslationStore::class);
        $store->shouldReceive('find')
            ->once()
            ->with('fr')
            ->andReturnNull();
        $this->app->instance(WebsiteTranslationStore::class, $store);

        $this->getJson('/api/website-translations/fr')
            ->assertNotFound()
            ->assertJsonPath(
                'message',
                'Website translation bundle not found.',
            );
    }

    public function test_a_translation_bundle_is_available_to_new_market_locations(): void
    {
        $bundle = [
            'version' => 1,
            'language' => 'de',
            'source_language' => 'en',
            'source_hash' => 'source-hash',
            'content_hash' => 'market-independent-content-hash',
            'generated_at' => '2026-08-04T10:00:00+00:00',
            'sections' => [
                'topbar' => ['events' => 'Veranstaltungen'],
            ],
        ];

        $store = Mockery::mock(WebsiteTranslationStore::class);
        $store->shouldReceive('find')
            ->once()
            ->with('de')
            ->andReturn($bundle);
        $this->app->instance(WebsiteTranslationStore::class, $store);

        $this->withHeader('X-Market-Code', 'new-market')
            ->getJson('/api/website-translations/de')
            ->assertOk()
            ->assertJsonPath('language', 'de')
            ->assertJsonPath('sections.topbar.events', 'Veranstaltungen');
    }
}
