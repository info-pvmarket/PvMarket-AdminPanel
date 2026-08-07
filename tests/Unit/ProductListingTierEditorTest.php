<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductListingTierEditorTest extends TestCase
{
    public function test_legacy_zero_maximum_is_treated_as_an_open_ended_tier(): void
    {
        $contents = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/admin/product_listing/edit.blade.php'
        );

        $this->assertIsString($contents);
        $this->assertStringContainsString(
            'return Number.isFinite(maxQuantity) && maxQuantity > 0;',
            $contents
        );
        $this->assertStringContainsString(
            'max_quantity: hasSpecificMaxQuantity(slot) ? Number(slot.max_quantity) : null,',
            $contents
        );
        $this->assertStringContainsString(
            'if (hasSpecificMaxQuantity(s)) {',
            $contents
        );
    }
}
