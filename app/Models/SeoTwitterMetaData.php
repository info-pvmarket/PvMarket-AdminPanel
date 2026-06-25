<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use App\Casts\AsObjectId;

class SeoTwitterMetaData extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'seo_twitter_meta_data';

    protected $fillable = [
        'seo_meta_id',
        'twitter_card',
        'twitter_site',
        'twitter_creator',
        'twitter_title',
        'twitter_description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'seo_meta_id' => AsObjectId::class,
        'is_active'   => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public array $translatable = [
        'twitter_title',
        'twitter_description',
    ];

    public function seoMeta()
    {
        return $this->belongsTo(SeoMetaData::class, 'seo_meta_id');
    }

    public function images()
    {
        return $this->hasMany(SeoTwitterImage::class, 'twitter_meta_id');
    }
}
