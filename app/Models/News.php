<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class News extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'news';

    protected $fillable = [
    'title',
    'slug',
    'content',
    'image',
    'alt_tag',
    'description',
    'is_active',
];

protected $casts = [
    'is_active' => 'boolean',
];

public array $translatable = ['title', 'content'];
}