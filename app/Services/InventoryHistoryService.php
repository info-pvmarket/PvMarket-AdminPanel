<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\ProductListing;
use MongoDB\BSON\ObjectId;

class InventoryHistoryService
{
    /**
     * Build the same stock movement fields used by the existing Adjust Stock
     * workflow. No transaction is needed when the quantity is unchanged.
     */
    public function quantityChange(int $before, int $after, string $notes): ?array
    {
        if ($before === $after) {
            return null;
        }

        $isAddition = $after > $before;
        $quantity = abs($after - $before);

        return [
            'transaction_type' => $isAddition ? 'stock_add' : 'stock_reduce',
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'quantity_change' => $quantity,
            'type' => $isAddition ? 'addition' : 'deduction',
            'reason' => $notes,
            'notes' => $notes,
            'reference_type' => 'listing',
        ];
    }

    public function recordTotalQuantityChange(
        ProductListing $listing,
        int $before,
        int $after,
        string $notes,
        string $createdBy,
    ): void {
        $change = $this->quantityChange($before, $after, $notes);
        if ($change === null) {
            return;
        }

        $listingId = new ObjectId((string) $listing->_id);

        InventoryTransaction::create([
            ...$change,
            'listing_id' => $listingId,
            'product_id' => new ObjectId((string) $listing->product_id),
            'warehouse_id' => $listing->warehouse_id
                ? new ObjectId((string) $listing->warehouse_id)
                : null,
            'user_id' => $listing->user_id
                ? new ObjectId((string) $listing->user_id)
                : null,
            'reference_id' => $listingId,
            'created_by' => new ObjectId($createdBy),
        ]);
    }
}
