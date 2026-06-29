<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Coupon extends Model
{
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
        'products'      => 'integer',
        'warehouses'    => 'integer',
        'duration_days' => 'integer',
        'max_uses'      => 'integer',
        'current_uses'  => 'integer',
        'is_active'     => 'boolean',
        'valid_from'    => 'datetime',
        'valid_until'   => 'datetime',
    ];
}
