<?php

namespace Tests\Unit;

use App\Jobs\TranslatePageJob;
use App\Services\TranslationNotifier;
use App\Services\TranslationService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class TranslatePageJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        FakeTranslatableRecord::$records = [];
        FakeTranslatableRecord::$translatableFields = ['name'];
    }

    public function test_explicit_translation_replaces_existing_value_when_result_matches_source(): void
    {
        $record = new FakeTranslatableRecord;
        $record->name = 'PV Market';
        $record->fr = ['name' => 'Ancienne traduction'];
        FakeTranslatableRecord::$records = [$record];

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->once()
            ->with('PV Market', 'fr', 'en', true)
            ->andReturn('PV Market');

        (new TestableTranslatePageJob('fr', 'fake'))->handle($translator);

        $this->assertSame(['name' => 'PV Market'], $record->fr);
    }

    public function test_explicit_translation_preserves_existing_value_when_translation_fails(): void
    {
        $record = new FakeTranslatableRecord;
        $record->name = 'PV Market';
        $record->fr = ['name' => 'Traduction existante'];
        FakeTranslatableRecord::$records = [$record];

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->once()
            ->with('PV Market', 'fr', 'en', true)
            ->andReturnNull();

        (new TestableTranslatePageJob('fr', 'fake'))->handle($translator);

        $this->assertSame(['name' => 'Traduction existante'], $record->fr);
    }

    public function test_explicit_translation_replaces_existing_array_values(): void
    {
        FakeTranslatableRecord::$translatableFields = ['tags'];

        $record = new FakeTranslatableRecord;
        $record->tags = ['PV Market'];
        $record->fr = ['tags' => ['Ancienne traduction']];
        FakeTranslatableRecord::$records = [$record];

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->once()
            ->with('PV Market', 'fr', 'en', true)
            ->andReturn('PV Market');

        (new TestableTranslatePageJob('fr', 'fake'))->handle($translator);

        $this->assertSame(['tags' => ['PV Market']], $record->fr);
    }

    public function test_static_page_extra_translates_nested_copy_and_preserves_structure(): void
    {
        FakeTranslatableRecord::$translatableFields = ['extra'];

        $record = new FakeTranslatableRecord;
        $record->extra = [
            'description' => 'Solar distribution',
            'items' => [
                [
                    'title' => 'Smart supply chain',
                    'desc' => 'Efficient operations',
                    'icon' => 'Boxes',
                ],
                [
                    'name' => 'Orange Group',
                    'logo' => 'https://cdn.example.com/orange.svg',
                ],
            ],
            'columns' => ['Dubai', 'Sharjah'],
            'rows' => [
                ['vehicle' => '3 TON', 'prices' => [250, 300]],
            ],
            'email' => 'support@example.com',
            'social_linkedin' => 'https://linkedin.com/company/example',
        ];
        $record->fr = ['extra' => ['description' => 'Ancien contenu']];
        FakeTranslatableRecord::$records = [$record];

        $translations = [
            'Solar distribution' => 'Distribution solaire',
            'Smart supply chain' => 'Chaîne logistique intelligente',
            'Efficient operations' => 'Opérations efficaces',
            'Dubai' => 'Dubaï',
            'Sharjah' => 'Charjah',
            '3 TON' => '3 TONNES',
        ];

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->times(count($translations))
            ->andReturnUsing(
                fn (string $text, string $target, string $source, bool $refresh): string => $translations[$text]
            );

        (new TestableTranslatePageJob('fr', 'fake'))->handle($translator);

        $this->assertSame('Distribution solaire', $record->fr['extra']['description']);
        $this->assertSame('Chaîne logistique intelligente', $record->fr['extra']['items'][0]['title']);
        $this->assertSame('Opérations efficaces', $record->fr['extra']['items'][0]['desc']);
        $this->assertSame('Boxes', $record->fr['extra']['items'][0]['icon']);
        $this->assertSame('Orange Group', $record->fr['extra']['items'][1]['name']);
        $this->assertSame('https://cdn.example.com/orange.svg', $record->fr['extra']['items'][1]['logo']);
        $this->assertSame(['Dubaï', 'Charjah'], $record->fr['extra']['columns']);
        $this->assertSame('3 TONNES', $record->fr['extra']['rows'][0]['vehicle']);
        $this->assertSame([250, 300], $record->fr['extra']['rows'][0]['prices']);
        $this->assertSame('support@example.com', $record->fr['extra']['email']);
        $this->assertSame(
            'https://linkedin.com/company/example',
            $record->fr['extra']['social_linkedin']
        );
    }

    public function test_static_page_extra_replaces_existing_value_when_result_matches_source(): void
    {
        FakeTranslatableRecord::$translatableFields = ['extra'];

        $record = new FakeTranslatableRecord;
        $record->extra = ['content' => 'PV Market'];
        $record->fr = ['extra' => ['content' => 'Ancienne traduction']];
        FakeTranslatableRecord::$records = [$record];

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->once()
            ->with('PV Market', 'fr', 'en', true)
            ->andReturn('PV Market');

        (new TestableTranslatePageJob('fr', 'fake'))->handle($translator);

        $this->assertSame(['content' => 'PV Market'], $record->fr['extra']);
    }

    public function test_completed_collection_notifies_the_requesting_admin_with_record_totals(): void
    {
        $record = new FakeTranslatableRecord;
        $record->name = 'PV Market';
        FakeTranslatableRecord::$records = [$record];

        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')
            ->once()
            ->with('PV Market', 'fr', 'en', true)
            ->andReturn('Marché PV');

        $notifier = Mockery::mock(TranslationNotifier::class);
        $notifier->shouldReceive('completed')
            ->once()
            ->with(
                'admin-id',
                'run-id',
                'French',
                'fr',
                'fake',
                [
                    'total' => 1,
                    'processed' => 1,
                    'updated' => 1,
                    'failed' => 0,
                ],
            );

        $job = new TestableTranslatePageJob(
            'fr',
            'fake',
            'admin-id',
            'French',
            'run-id',
        );

        $this->app->instance(TranslationService::class, $translator);
        $this->app->instance(TranslationNotifier::class, $notifier);
        $stats = $this->app->call([$job, 'handle']);

        $this->assertSame(1, $stats['processed']);
        $this->assertSame(1, $stats['updated']);
    }

    public function test_every_supported_page_maps_to_an_existing_translatable_model(): void
    {
        foreach (TranslatePageJob::PAGE_MODELS as $page => $modelClass) {
            $this->assertTrue(class_exists($modelClass), "Missing model for {$page}");
            $this->assertNotEmpty(
                (new $modelClass)->translatable ?? [],
                "Page {$page} must expose at least one translatable field"
            );
        }
    }

    public function test_non_translatable_workflow_pages_are_not_queued(): void
    {
        $this->assertNotContains('sales', TranslatePageJob::supportedPages());
        $this->assertNotContains('leads', TranslatePageJob::supportedPages());
        $this->assertNotContains('sub-admins', TranslatePageJob::supportedPages());
    }
}

class TestableTranslatePageJob extends TranslatePageJob
{
    protected function resolveModel(string $page): ?string
    {
        return FakeTranslatableRecord::class;
    }
}

class FakeTranslatableRecord
{
    public static array $records = [];
    public static array $translatableFields = ['name'];

    public string $_id = 'fake-record';
    public array $translatable;
    public string $name = '';
    public array $tags = [];
    public array $extra = [];
    public array $fr = [];

    public function __construct()
    {
        $this->translatable = self::$translatableFields;
    }

    public static function count(): int
    {
        return count(self::$records);
    }

    public static function chunk(int $size, callable $callback): void
    {
        $callback(self::$records);
    }

    public function newQuery(): FakeTranslationQuery
    {
        return new FakeTranslationQuery($this);
    }
}

class FakeTranslationQuery
{
    public function __construct(private FakeTranslatableRecord $record)
    {
    }

    public function where(string $field, string $value): self
    {
        return $this;
    }

    public function update(array $attributes): int
    {
        foreach ($attributes as $field => $value) {
            $this->record->{$field} = $value;
        }

        return 1;
    }
}
