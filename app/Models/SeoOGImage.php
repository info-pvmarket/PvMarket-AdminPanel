<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class SeoOGImage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'seo_og_images';

    protected $fillable = [
        'og_meta_id',
        'image_url',
        'image_alt',
        'image_width',
        'image_height',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'og_meta_id'   => AsObjectId::class,
        'image_width'  => 'integer',
        'image_height' => 'integer',
        'sort_order'   => 'integer',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function ogMeta()
    {
        return $this->belongsTo(SeoOGMetaData::class, 'og_meta_id');
    }
}
