<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class ProductListingImage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'product_listings_images';

    protected $fillable = [
        'product_listing_id',
        'image',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'product_listing_id' => AsObjectId::class,
        'created_by'         => AsObjectId::class,
        'sort_order'         => 'integer',
    ];
}