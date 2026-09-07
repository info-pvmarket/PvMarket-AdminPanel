<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ListingTotalQuantityLimit implements ValidationRule
{
    /** @var array<string, array{maximum: int, unit: string}> */
    private const LIMITS = [
        'sell by pieces' => ['maximum' => 15000, 'unit' => 'pieces'],
        'sell by pallets' => ['maximum' => 450, 'unit' => 'pallets'],
        'sell by container' => ['maximum' => 20, 'unit' => 'containers'],
    ];

    public function __construct(private readonly string $sellType) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $sellType = strtolower(trim($this->sellType));
        $sellType = match ($sellType) {
            'sell by piece' => 'sell by pieces',
            'sell by pallet' => 'sell by pallets',
            'sell by containers' => 'sell by container',
            default => $sellType,
        };
        $limit = self::LIMITS[$sellType] ?? null;

        if ($limit !== null && (int) $value > $limit['maximum']) {
            $fail(sprintf(
                'Total quantity must be less than or equal to %s when selling by %s.',
                number_format($limit['maximum']),
                $limit['unit'],
            ));
        }
    }
}
