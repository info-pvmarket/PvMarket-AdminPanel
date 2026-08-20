<?php

namespace Tests\Unit;

use App\Services\InventoryHistoryService;
use PHPUnit\Framework\TestCase;

class InventoryHistoryServiceTest extends TestCase
{
    public function test_quantity_increase_matches_existing_inventory_transaction_structure(): void
    {
        $change = (new InventoryHistoryService)->quantityChange(10, 16, 'Supplier restock');

        $this->assertSame('stock_add', $change['transaction_type']);
        $this->assertSame(6, $change['quantity']);
        $this->assertSame(10, $change['quantity_before']);
        $this->assertSame(16, $change['quantity_after']);
        $this->assertSame(6, $change['quantity_change']);
        $this->assertSame('addition', $change['type']);
        $this->assertSame('Supplier restock', $change['notes']);
        $this->assertSame('Supplier restock', $change['reason']);
        $this->assertSame('listing', $change['reference_type']);
    }

    public function test_quantity_reduction_is_recorded_as_a_positive_movement_amount(): void
    {
        $change = (new InventoryHistoryService)->quantityChange(16, 9, 'Damaged units');

        $this->assertSame('stock_reduce', $change['transaction_type']);
        $this->assertSame(7, $change['quantity']);
        $this->assertSame(16, $change['quantity_before']);
        $this->assertSame(9, $change['quantity_after']);
        $this->assertSame('deduction', $change['type']);
    }

    public function test_unchanged_quantity_does_not_create_a_movement(): void
    {
        $this->assertNull((new InventoryHistoryService)->quantityChange(10, 10, 'No change'));
    }
}
