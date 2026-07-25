<?php

namespace Tests\Unit;

use App\Jobs\TranslatePageJob;
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
