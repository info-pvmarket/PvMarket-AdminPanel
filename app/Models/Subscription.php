<?php

namespace App\Models;

use App\Casts\AsObjectId;
use Carbon\CarbonInterface;
use MongoDB\Laravel\Eloquent\Model;

class Subscription extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'subscriptions';

    protected $fillable = [
        'user_id',
        'plan_name',
        'products',
        'warehouses',
        'start_date',
        'end_date',
        'status',
        'coupon_id',
        'coupon_code',
        'payment_id',
        'payment_url',
        'payment_status',
        'amount_paid',
        'currency',
        'is_free_subscription',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'deleted_at',
    ];

    protected $casts = [
        'user_id' => AsObjectId::class,
        'coupon_id' => AsObjectId::class,
        'created_by' => AsObjectId::class,
        'cancelled_by' => AsObjectId::class,
        'products' => 'integer',
        'warehouses' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount_paid' => 'float',
        'is_free_subscription' => 'boolean',
        'cancelled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function isActiveAt(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->status === 'active'
            && $this->start_date !== null
            && $this->end_date !== null
            && $this->start_date->lte($at)
            && $this->end_date->gte($at);
    }

    public function statusForDisplay(?CarbonInterface $at = null): string
    {
        $at ??= now();

        if ($this->status === 'active' && $this->end_date?->lt($at)) {
            return 'expired';
        }

        return strtolower((string) ($this->status ?: 'pending'));
    }
}
