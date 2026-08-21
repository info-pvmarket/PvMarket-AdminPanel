<?php

namespace Tests\Unit;

use App\Models\Product;
use ArrayObject;
use PHPUnit\Framework\TestCase;

class ProductDatasheetPresentationTest extends TestCase
{
    public function test_it_reads_an_existing_datasheet_from_an_array(): void
    {
        $product = new Product();
        $product->datasheet = [
            'original_name' => 'module-datasheet.pdf',
            'url' => 'https://cdn.example.com/module-datasheet.pdf',
        ];

        $this->assertSame('module-datasheet.pdf', $product->datasheet_display_name);
        $this->assertSame('https://cdn.example.com/module-datasheet.pdf', $product->datasheet_display_url);
    }

    public function test_it_reads_an_existing_datasheet_from_a_mongodb_style_object(): void
    {
        $product = new Product();
        $product->datasheet = new ArrayObject([
            'filename' => 'stored-datasheet.pdf',
            'url' => 'https://cdn.example.com/stored-datasheet.pdf',
        ]);

        $this->assertSame('stored-datasheet.pdf', $product->datasheet_display_name);
        $this->assertSame('https://cdn.example.com/stored-datasheet.pdf', $product->datasheet_display_url);
    }
}
