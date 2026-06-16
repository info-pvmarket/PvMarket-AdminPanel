<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class Order extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
    'unique_id',
    'user_id',
    'offer_id',
    'company_id',
    'total_qty',
    'slot_index',
    'address_id',
    'each_qty_price',
    'purchased_currency',
    'selled_currency',
    'payment_currency',
    'payment_currency_total',
    'payment_currency_advance',
    'sell_each_qty_price',
    'rate',
    'partial_payment_amount',
    'payment_status',
    'payment_verified',
    'payment_method',
    'payment_platform',
    'transaction_upload',
    'price_type',
    'delivery_charge',
    'order_status',
    'delivery_type',
    'sell_type',
    'usd_currency_total',
    'usd_currency_advance',
    'eur_currency_total',
    'eur_currency_advance',
    'aed_currency_total',
    'aed_currency_advance',
    'is_active',
    'created_by',
];

protected $casts = [
    'user_id'    => AsObjectId::class,
    'offer_id'   => AsObjectId::class,
    'company_id' => AsObjectId::class,
    'address_id' => AsObjectId::class,
    'created_by' => AsObjectId::class,
    'is_active'        => 'boolean',
    'payment_verified' => 'boolean',
    'order_status'     => 'integer',
    'payment_method'   => 'integer',
    'payment_platform' => 'integer',
    'payment_status'   => 'integer',
    'price_type'       => 'integer',
    'delivery_type'    => 'integer',
    'sell_type'        => 'integer',
    'slot_index'       => 'integer',
    'delivery_charge'  => 'integer',
];

public array $translatable = [];
}