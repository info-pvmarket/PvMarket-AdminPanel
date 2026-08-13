<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PriceTierQuantityBelowTotal implements ValidationRule
{
    public function __construct(private readonly int $totalQuantity) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_numeric($value) && (int) $value >= $this->totalQuantity) {
            $fail('Each price tier quantity must be less than the total quantity.');
        }
    }
}
