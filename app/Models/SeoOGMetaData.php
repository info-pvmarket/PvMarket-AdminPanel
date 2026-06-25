<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use App\Casts\AsObjectId;

class SeoOGMetaData extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'seo_og_meta_data';

    protected $fillable = [
        'seo_meta_id',
        'og_title',
        'og_description',
        'og_type',
        'og_url',
        'og_site_name',
        'og_locale',
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
        'og_title',
        'og_description',
    ];

    public function seoMeta()
    {
        return $this->belongsTo(SeoMetaData::class, 'seo_meta_id');
    }

    public function images()
    {
        return $this->hasMany(SeoOGImage::class, 'og_meta_id');
    }
}
