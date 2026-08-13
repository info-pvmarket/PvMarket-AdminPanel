<?php

namespace Tests\Unit;

use App\Rules\PriceTierQuantityAtMostTotal;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PriceTierQuantityAtMostTotalTest extends TestCase
{
    public function test_price_tier_quantity_below_total_is_valid(): void
    {
        $validator = Validator::make(
            ['quantity' => 99],
            ['quantity' => [new PriceTierQuantityAtMostTotal(100)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_price_tier_quantity_equal_to_total_is_valid(): void
    {
        $validator = Validator::make(
            ['quantity' => 100],
            ['quantity' => [new PriceTierQuantityAtMostTotal(100)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_price_tier_quantity_above_total_is_invalid(): void
    {
        $validator = Validator::make(
            ['quantity' => 101],
            ['quantity' => [new PriceTierQuantityAtMostTotal(100)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Each price tier quantity must be less than or equal to the total quantity.',
            $validator->errors()->first('quantity'),
        );
    }
}
