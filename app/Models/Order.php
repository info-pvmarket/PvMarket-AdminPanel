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
        'product_id',
        'seller_id',
        'buyer_id',
        'total_qty',
        'each_qty_price',
        'payment_currency',
        'payment_currency_total',
        'purchased_currency',
        'payment_method',
        'transaction_upload',
        'partial_payment_amount',
        'payment_verified',
        'order_status',
        'delivery_charge',
        'buyer_company_name',
        'seller_company_name',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'product_id'   => AsObjectId::class,
        'seller_id'    => AsObjectId::class,
        'buyer_id'     => AsObjectId::class,
        'updated_by'   => AsObjectId::class,
        'is_active'    => 'integer',
        'order_status' => 'integer',
        'payment_method' => 'integer',
        'payment_verified' => 'integer',
    ];
    public array $translatable = [
        'buyer_company_name',
        'seller_company_name',
    ];
}