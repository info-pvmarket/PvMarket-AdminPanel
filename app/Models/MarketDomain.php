<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use App\Casts\AsObjectId;

class MarketDomain extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'market_domains';

    protected $fillable = [
        'market_id',
        'domain',
        'is_primary',
    ];

    protected $casts = [
        'market_id'  => AsObjectId::class,
        'is_primary' => 'boolean',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id', '_id');
    }
}
