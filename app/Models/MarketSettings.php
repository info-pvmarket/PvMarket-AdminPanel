<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use App\Casts\AsObjectId;

class MarketSettings extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'market_settings';

    protected $fillable = [
        'market_id',
        'site_name',
        'site_description',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_links',
        'gtm_container_id',
        'google_analytics_id',
        'available_currencies',
        'features',
        'metadata_base',
        'default_country_code',
        'filter_by_country',
    ];

    protected $casts = [
        'market_id' => AsObjectId::class,
        'filter_by_country' => 'boolean',
        // MongoDB stores arrays natively, no need for JSON casting
    ];

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id', '_id');
    }
}
