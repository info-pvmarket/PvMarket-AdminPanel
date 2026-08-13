<?php

namespace Tests\Unit;

use App\Rules\PriceTierQuantityBelowTotal;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PriceTierQuantityBelowTotalTest extends TestCase
{
    public function test_price_tier_quantity_below_total_is_valid(): void
    {
        $validator = Validator::make(
            ['quantity' => 99],
            ['quantity' => [new PriceTierQuantityBelowTotal(100)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_price_tier_quantity_equal_to_total_is_invalid(): void
    {
        $validator = Validator::make(
            ['quantity' => 100],
            ['quantity' => [new PriceTierQuantityBelowTotal(100)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Each price tier quantity must be less than the total quantity.',
            $validator->errors()->first('quantity'),
        );
    }

    public function test_price_tier_quantity_above_total_is_invalid(): void
    {
        $validator = Validator::make(
            ['quantity' => 101],
            ['quantity' => [new PriceTierQuantityBelowTotal(100)]],
        );

        $this->assertTrue($validator->fails());
    }
}
