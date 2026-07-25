<?php

namespace Tests\Unit;

use App\Traits\HasTranslations;
use ArrayObject;
use PHPUnit\Framework\TestCase;

class HasTranslationsTest extends TestCase
{
    public function test_reads_translation_from_mongodb_array_object(): void
    {
        $record = new FakeTranslatedModel;
        $record->name = 'United Arab Emirates';
        $record->ar = new ArrayObject([
            'name' => 'الإمارات العربية المتحدة',
        ]);

        $this->assertSame('الإمارات العربية المتحدة', $record->trans('name', 'ar'));
    }

    public function test_falls_back_to_source_for_english(): void
    {
        $record = new FakeTranslatedModel;
        $record->name = 'United Arab Emirates';

        $this->assertSame('United Arab Emirates', $record->trans('name', 'en'));
    }
}

class FakeTranslatedModel
{
    use HasTranslations;

    public string $_id = 'fake';
    public string $name = '';
    public mixed $ar = null;
}
