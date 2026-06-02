<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use App\Casts\AsObjectId;

class Slider extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'sliders';

    protected $fillable = [
        'name',
        'image',
        'alt_tag',
        'redirect_link',
        'slider_type',
        'is_active',
        'location_id',
        'sort_order',
    ];

    protected $casts = [
        'location_id' => AsObjectId::class,
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatable = ['name', 'alt_tag'];
}