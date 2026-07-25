<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class Location extends Model
{
    use HasTranslations, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'locations';

    protected $fillable = [
        'country_id',
        'country_name',
        'domain_name',
    ];

    protected $casts = [
        'country_id' => AsObjectId::class,
    ];

    public array $translatable = ['country_name'];
}
