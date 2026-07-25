<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ProductController;
use PHPUnit\Framework\TestCase;

class ControllerAutoloadingTest extends TestCase
{
    public function test_product_controller_filename_matches_psr_4_class_case(): void
    {
        $controllerDirectory = dirname(__DIR__, 2).'/app/Http/Controllers/Admin';

        $this->assertContains(
            'ProductController.php',
            scandir($controllerDirectory),
            'ProductController must use the exact PSR-4 filename casing required by Linux.',
        );

        $this->assertTrue(class_exists(ProductController::class));
    }
}
