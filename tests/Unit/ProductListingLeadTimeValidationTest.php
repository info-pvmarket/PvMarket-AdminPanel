<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProductListingLeadTimeValidationTest extends TestCase
{
    public function test_available_offers_require_positive_lead_time_with_a_sold_off_exception(): void
    {
        $controller = file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Controllers/Admin/ProductListingController.php'
        );
        $createView = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/admin/product_listing/create.blade.php'
        );
        $editView = file_get_contents(
            dirname(__DIR__, 2).'/resources/views/admin/product_listing/edit.blade.php'
        );

        $this->assertStringContainsString("'lead_time'                        => 'required|integer|min:1'", $controller);
        $this->assertStringContainsString("'lead_time'                        => \$isSoldOff ? 'required|integer|min:0' : 'required|integer|min:1'", $controller);
        $this->assertStringContainsString('name="lead_time"', $createView);
        $this->assertStringContainsString('min="1" required', $createView);
        $this->assertStringContainsString('name="lead_time"', $editView);
        $this->assertStringContainsString('min="1" required', $editView);
    }
}
