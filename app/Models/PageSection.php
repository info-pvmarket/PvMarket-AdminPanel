<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class PageSection extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'page_sections';

    /** Fields translated by TranslatePageJob / TranslationService. */
    public array $translatable = ['title', 'subtitle', 'description', 'button_text', 'extra'];

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
        //'extra'     => 'array',
    ];
}