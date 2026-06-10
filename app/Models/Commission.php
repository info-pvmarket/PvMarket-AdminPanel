<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class Commission extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'commissions';

    protected $fillable = [
        'category_id',
        'category_name',
        'commission_percentage',
        'slug',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'category_id'             => AsObjectId::class,
        'created_by'              => AsObjectId::class,
        'updated_by'              => AsObjectId::class,
        'commission_percentage'   => 'float',
    ];
    public array $translatable = [
        'category_name',
    ];
}