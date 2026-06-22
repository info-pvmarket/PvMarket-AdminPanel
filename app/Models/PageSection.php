<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class PageSection extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'page_sections';

    protected $fillable = [
        'page',         // home | about | contact | terms | privacy | faq
        'section',      // hero | features | stats | cta | content
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_link',
        'image',
        'alt_tag',
        'extra',        // JSON for extra fields specific to each section
        'is_active',
        'order',
        'type',
        'location_id',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'order'       => 'integer',
        'location_id' => AsObjectId::class,
        //'extra'     => 'array',
    ];
}