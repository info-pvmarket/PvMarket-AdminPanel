<?php

namespace Tests\Unit;

use App\Services\PriceHistoryService;
use PHPUnit\Framework\TestCase;

class PriceHistoryServiceTest extends TestCase
{
    public function test_it_records_only_changed_tier_prices(): void
    {
        $changes = (new PriceHistoryService)->changes(
            [
                ['price' => 10, 'total_price' => 11],
                ['price' => 9, 'total_price' => 10],
            ],
            [
                ['price' => 10, 'total_price' => 11],
                ['price' => 8, 'total_price' => 9],
            ],
            'USD',
            'USD',
        );

        $this->assertCount(1, $changes);
        $this->assertSame(2, $changes[0]['tier_number']);
        $this->assertSame(9.0, $changes[0]['price_before']);
        $this->assertSame(8.0, $changes[0]['price_after']);
        $this->assertSame(-1.0, $changes[0]['price']);
        $this->assertSame(-1.0, $changes[0]['total_price']);
    }

    public function test_it_records_added_removed_and_currency_changes(): void
    {
        $service = new PriceHistoryService;

        $added = $service->changes([], [['price' => 10]], 'USD', 'USD');
        $removed = $service->changes([['price' => 10]], [], 'USD', 'USD');
        $currency = $service->changes([['price' => 10]], [['price' => 10]], 'USD', 'EUR');

        $this->assertSame('price_added', $added[0]['transaction_type']);
        $this->assertSame('price_removed', $removed[0]['transaction_type']);
        $this->assertSame('price_updated', $currency[0]['transaction_type']);
    }
}
