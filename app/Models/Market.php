<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Market extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'markets';

    protected $fillable = [
        'code',
        'name',
        'default_currency',
        'default_locale',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domains()
    {
        return $this->hasMany(MarketDomain::class, 'market_id', '_id');
    }

    public function settings()
    {
        return $this->hasOne(MarketSettings::class, 'market_id', '_id');
    }
}
