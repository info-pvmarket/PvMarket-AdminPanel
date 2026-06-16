<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class Blog extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'blogs';

    protected $fillable = [
        'title',
        'image',
        'alt_tag',
        'slug',
        'related_ids',
        'related_blog_title',
        'data',
        'content',
        'is_faq',
        'is_active',
        'blog_comments',
    ];

    public array $translatable = ['title', 'data'];
}