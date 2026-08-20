<?php

namespace App\Services;

use App\Models\PriceTransaction;
use App\Models\ProductListing;
use MongoDB\BSON\ObjectId;

class PriceHistoryService
{
    private const TOLERANCE = 0.000001;

    /**
     * Return one history row for each tier whose base/total price or currency
     * changed. Added and removed tiers are retained as explicit events.
     */
    public function changes(
        array $oldSlots,
        array $newSlots,
        string $oldCurrency,
        string $newCurrency,
    ): array {
        $changes = [];
        $tierCount = max(count($oldSlots), count($newSlots));

        for ($index = 0; $index < $tierCount; $index++) {
            $oldSlot = array_key_exists($index, $oldSlots) ? $this->normalizeSlot($oldSlots[$index]) : null;
            $newSlot = array_key_exists($index, $newSlots) ? $this->normalizeSlot($newSlots[$index]) : null;

            if ($oldSlot === null && $newSlot === null) {
                continue;
            }

            $transactionType = 'price_updated';
            if ($oldSlot === null) {
                $transactionType = 'price_added';
            } elseif ($newSlot === null) {
                $transactionType = 'price_removed';
            } elseif ($this->samePrices($oldSlot, $newSlot) && $oldCurrency === $newCurrency) {
                continue;
            }

            $changes[] = [
                'tier_number' => $index + 1,
                'transaction_type' => $transactionType,
                'min_quantity_before' => $oldSlot['min_quantity'] ?? null,
                'min_quantity_after' => $newSlot['min_quantity'] ?? null,
                'max_quantity_before' => $oldSlot['max_quantity'] ?? null,
                'max_quantity_after' => $newSlot['max_quantity'] ?? null,
                'price_before' => $oldSlot['price'] ?? null,
                'price_after' => $newSlot['price'] ?? null,
                'total_price_before' => $oldSlot['total_price'] ?? null,
                'total_price_after' => $newSlot['total_price'] ?? null,
                'currency_before' => $oldCurrency,
                'currency_after' => $newCurrency,
                'price' => ($newSlot['price'] ?? 0.0) - ($oldSlot['price'] ?? 0.0),
                'total_price' => ($newSlot['total_price'] ?? 0.0) - ($oldSlot['total_price'] ?? 0.0),
            ];
        }

        return $changes;
    }

    public function record(
        ProductListing $listing,
        array $oldSlots,
        array $newSlots,
        string $oldCurrency,
        string $newCurrency,
        ?string $createdBy,
        string $source = 'admin',
    ): void {
        $changes = $this->changes($oldSlots, $newSlots, $oldCurrency, $newCurrency);
        if ($changes === []) {
            return;
        }

        $listingId = new ObjectId((string) $listing->_id);
        $productId = new ObjectId((string) $listing->product_id);
        $warehouseId = $listing->warehouse_id
            ? new ObjectId((string) $listing->warehouse_id)
            : null;
        $userId = new ObjectId((string) $listing->user_id);
        $createdById = $createdBy ? new ObjectId($createdBy) : null;

        foreach ($changes as $change) {
            PriceTransaction::create([
                ...$change,
                'listing_id' => $listingId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'notes' => sprintf('Tier %d price changed via %s', $change['tier_number'], $source),
                'reference_id' => $listingId,
                'reference_type' => 'listing',
                'created_by' => $createdById,
            ]);
        }
    }

    private function normalizeSlot(mixed $slot): array
    {
        $slot = (array) $slot;

        return [
            'min_quantity' => isset($slot['min_quantity']) ? (int) $slot['min_quantity'] : null,
            'max_quantity' => isset($slot['max_quantity']) && $slot['max_quantity'] !== ''
                ? (int) $slot['max_quantity']
                : null,
            'price' => isset($slot['price']) ? (float) $slot['price'] : 0.0,
            'total_price' => isset($slot['total_price']) ? (float) $slot['total_price'] : 0.0,
        ];
    }

    private function samePrices(array $oldSlot, array $newSlot): bool
    {
        return abs($oldSlot['price'] - $newSlot['price']) <= self::TOLERANCE
            && abs($oldSlot['total_price'] - $newSlot['total_price']) <= self::TOLERANCE;
    }
}
