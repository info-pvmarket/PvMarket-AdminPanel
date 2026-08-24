<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProductListingLeadTimeValidationTest extends TestCase
{
    public function test_create_and_update_require_a_lead_time_of_at_least_one(): void
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

        $this->assertSame(2, substr_count($controller, "'lead_time'                        => 'required|integer|min:1'"));
        $this->assertStringContainsString('name="lead_time"', $createView);
        $this->assertStringContainsString('min="1" required', $createView);
        $this->assertStringContainsString('name="lead_time"', $editView);
        $this->assertStringContainsString('min="1" required', $editView);
    }
}
