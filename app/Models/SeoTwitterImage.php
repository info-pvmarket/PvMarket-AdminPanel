<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class SeoTwitterImage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'seo_twitter_images';

    protected $fillable = [
        'twitter_meta_id',
        'image_url',
        'image_alt',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'twitter_meta_id' => AsObjectId::class,
        'sort_order'      => 'integer',
        'is_active'       => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function twitterMeta()
    {
        return $this->belongsTo(SeoTwitterMetaData::class, 'twitter_meta_id');
    }
}
