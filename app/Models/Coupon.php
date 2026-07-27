<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Carbon\CarbonInterface;
use MongoDB\Laravel\Eloquent\Model;

class Coupon extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';

    protected $collection = 'coupons';

    protected $fillable = [
        'code',
        'type',
        'plan_name',
        'products',
        'warehouses',
        'duration_days',
        'max_uses',
        'current_uses',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'products' => 'integer',
        'warehouses' => 'integer',
        'duration_days' => 'integer',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public array $translatable = ['plan_name'];

    public function subscriptionValidationError(?CarbonInterface $at = null): ?string
    {
        $at ??= now();

        if (! $this->is_active) {
            return 'Coupon is not active.';
        }

        if ($this->valid_from && $at->lt($this->valid_from)) {
            return 'Coupon is not yet valid.';
        }

        if ($this->valid_until && $at->gt($this->valid_until)) {
            return 'Coupon has expired.';
        }

        if ((int) $this->max_uses > 0 && (int) $this->current_uses >= (int) $this->max_uses) {
            return 'Coupon usage limit reached.';
        }

        if (strtolower((string) $this->type) !== 'free') {
            return 'Only free subscription coupons can activate a plan.';
        }

        if ((int) $this->duration_days < 1) {
            return 'Coupon subscription duration is invalid.';
        }

        return null;
    }
}
