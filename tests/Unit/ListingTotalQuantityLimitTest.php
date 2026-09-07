<?php

namespace Tests\Unit;

use App\Rules\ListingTotalQuantityLimit;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ListingTotalQuantityLimitTest extends TestCase
{
    public static function validQuantities(): array
    {
        return [
            'pieces boundary' => ['sell by pieces', 15000],
            'pallets boundary' => ['sell by pallets', 450],
            'containers boundary' => ['sell by container', 20],
        ];
    }

    public static function excessiveQuantities(): array
    {
        return [
            'pieces above boundary' => ['sell by pieces', 15001, '15,000', 'pieces'],
            'pallets above boundary' => ['sell by pallets', 451, '450', 'pallets'],
            'containers above boundary' => ['sell by container', 21, '20', 'containers'],
        ];
    }

    #[DataProvider('validQuantities')]
    public function test_quantity_at_the_sell_type_limit_is_valid(string $sellType, int $quantity): void
    {
        $validator = Validator::make(
            ['total_quantity' => $quantity],
            ['total_quantity' => [new ListingTotalQuantityLimit($sellType)]],
        );

        $this->assertTrue($validator->passes());
    }

    #[DataProvider('excessiveQuantities')]
    public function test_quantity_above_the_sell_type_limit_is_invalid(
        string $sellType,
        int $quantity,
        string $maximum,
        string $unit,
    ): void {
        $validator = Validator::make(
            ['total_quantity' => $quantity],
            ['total_quantity' => [new ListingTotalQuantityLimit($sellType)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            "Total quantity must be less than or equal to {$maximum} when selling by {$unit}.",
            $validator->errors()->first('total_quantity'),
        );
    }
}
