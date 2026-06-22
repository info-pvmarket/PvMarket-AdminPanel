<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class PageSetting extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'page_settings';

    protected $fillable = [
        'page',
        'location_id',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_image',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'location_id'  => AsObjectId::class,
    ];
}