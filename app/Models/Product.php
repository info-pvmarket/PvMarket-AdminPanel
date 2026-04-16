<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Casts\AsArrayObject;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'sku_code',
        'product_name',
        'product_description',
        'brand_id',
        'brand_name',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'pieces_per_pallet',
        'pallets_per_container',
        'is_popular',
        'real_time_price',
        'product_details',
        'measurement_details',
        'datasheet',
        'verification_status',
        'updated_by',
    ];

    protected $casts = [
    'is_popular'          => 'boolean',
    'real_time_price'     => 'boolean',
    
    
];
}