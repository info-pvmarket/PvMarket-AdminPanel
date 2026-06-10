<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class Offer extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'offers';

    protected $fillable = [
        'unique_id',
        'product_id',
        'product_name',
        'warehouse_id',
        'warehouse_name',
        'payment_status',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'product_id'   => AsObjectId::class,
        'warehouse_id' => AsObjectId::class,
    ];
    public array $translatable = [
        'product_name',
        'warehouse_name',
    ];
}