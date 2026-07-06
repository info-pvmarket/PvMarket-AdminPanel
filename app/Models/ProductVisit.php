<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;


class ProductVisit extends Model
{
    
    protected $connection = 'mongodb';
    protected $collection = 'product_visits';

    protected $fillable = [
        'user_id',
        'product_id',
        'offer_id',
        'no_of_times',
        'visit_date',
        'last_visited_at',
        'products',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'user_id'     => AsObjectId::class,
        'product_id'  => AsObjectId::class,
        'offer_id'    => AsObjectId::class,
        'no_of_times' => 'integer',
        'is_active'   => 'integer',
    ];
    
}
