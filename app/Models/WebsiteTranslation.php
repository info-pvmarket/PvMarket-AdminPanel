<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class WebsiteTranslation extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'website_translations';

    protected $fillable = [
        'version',
        'language',
        'source_language',
        'source_hash',
        'content_hash',
        'generated_at',
        'sections',
    ];

    protected $casts = [
        'version' => 'integer',
        'generated_at' => 'datetime',
        'sections' => 'array',
    ];
}
