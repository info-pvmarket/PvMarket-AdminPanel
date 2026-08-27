<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WarehouseListOrderingTest extends TestCase
{
    public function test_pending_warehouses_are_listed_before_paid_warehouses(): void
    {
        $controller = file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Controllers/Admin/WarehouseController.php'
        );

        $pendingOrder = strpos($controller, "orderBy('is_paid', 'asc')");
        $newestOrder = strpos($controller, "orderBy('created_at', 'desc')");

        $this->assertNotFalse($pendingOrder);
        $this->assertNotFalse($newestOrder);
        $this->assertLessThan($newestOrder, $pendingOrder);
    }
}
