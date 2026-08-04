<?php

namespace Tests\Unit;

use App\Jobs\TranslateWebsiteStaticTextJob;
use App\Models\WebsiteTranslation;
use App\Services\TranslationService;
use App\Services\WebsiteTranslationStore;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class TranslateWebsiteStaticTextJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_translates_the_frontend_catalog_and_replaces_mongodb_bundles(): void
    {
        config()->set('services.frontend.url', 'https://frontend.example');

        Http::fake([
            'https://frontend.example/api/website-translations/source' => Http::response([
                'version' => 1,
                'language' => 'en',
                'sections' => [
                    'topbar' => [
                        'events' => 'Events',
                        'results' => '{count} results',
                    ],
                    'homepage' => [
                        'viewAll' => 'View All',
                    ],
                ],
            ]),
        ]);

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->times(3)
            ->andReturnUsing(
                fn (string $text): string => match ($text) {
                    'Events' => 'Événements',
                    '__PVMARKET_TOKEN_0__ results' => '__PVMARKET_TOKEN_0__ résultats',
                    'View All' => 'Tout voir',
                },
            );

        $storedBundles = [];
        $store = Mockery::mock(WebsiteTranslationStore::class);
        $store->shouldReceive('replace')
            ->twice()
            ->andReturnUsing(
                function (string $language, array $payload) use (&$storedBundles) {
                    $storedBundles[$language] = $payload;

                    return Mockery::mock(WebsiteTranslation::class);
                },
            );

        $stats = (new TranslateWebsiteStaticTextJob('fr'))->handle(
            $translator,
            $store,
        );

        $this->assertSame('en', $storedBundles['en']['language']);
        $this->assertSame('fr', $storedBundles['fr']['language']);
        $this->assertSame(
            'Événements',
            $storedBundles['fr']['sections']['topbar']['events'],
        );
        $this->assertSame(
            '{count} résultats',
            $storedBundles['fr']['sections']['topbar']['results'],
        );
        $this->assertSame(
            'Tout voir',
            $storedBundles['fr']['sections']['homepage']['viewAll'],
        );
        $this->assertNotSame(
            $storedBundles['fr']['source_hash'],
            $storedBundles['fr']['content_hash'],
        );
        $this->assertSame([
            'total' => 3,
            'processed' => 3,
            'updated' => 3,
            'failed' => 0,
        ], $stats);
    }

    public function test_it_keeps_english_fallback_text_when_one_translation_fails(): void
    {
        config()->set('services.frontend.url', 'https://frontend.example');

        Http::fake([
            '*' => Http::response([
                'version' => 1,
                'language' => 'en',
                'sections' => [
                    'footer' => [
                        'contactUs' => 'Contact Us',
                        'home' => 'Home',
                    ],
                ],
            ]),
        ]);

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->times(4)
            ->andReturnUsing(
                fn (string $text): ?string => $text === 'Contact Us'
                    ? null
                    : 'Startseite',
            );

        $storedBundles = [];
        $store = Mockery::mock(WebsiteTranslationStore::class);
        $store->shouldReceive('replace')
            ->twice()
            ->andReturnUsing(
                function (string $language, array $payload) use (&$storedBundles) {
                    $storedBundles[$language] = $payload;

                    return Mockery::mock(WebsiteTranslation::class);
                },
            );

        $stats = (new TranslateWebsiteStaticTextJob('de'))->handle(
            $translator,
            $store,
        );

        $this->assertSame(
            'Contact Us',
            $storedBundles['de']['sections']['footer']['contactUs'],
        );
        $this->assertSame(
            'Startseite',
            $storedBundles['de']['sections']['footer']['home'],
        );
        $this->assertSame(1, $stats['failed']);
        $this->assertSame(1, $stats['updated']);
    }

    public function test_it_fails_the_job_instead_of_reporting_a_zero_value_replacement(): void
    {
        config()->set('services.frontend.url', 'https://frontend.example');

        Http::fake([
            '*' => Http::response([
                'version' => 1,
                'language' => 'en',
                'sections' => [
                    'footer' => ['contactUs' => 'Contact Us'],
                ],
            ]),
        ]);

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->times(3)
            ->andReturnNull();

        $store = Mockery::mock(WebsiteTranslationStore::class);
        $store->shouldNotReceive('replace');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Website static text translation failed for every text value (1/1).',
        );

        (new TranslateWebsiteStaticTextJob('fr'))->handle(
            $translator,
            $store,
        );
    }
}
