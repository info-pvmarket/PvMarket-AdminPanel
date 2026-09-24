<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations; 

class Event extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'events';

    protected $fillable = [
        'heading',
        'place',
        'event_date',
        'description',
        'image',
        'alt_tag',
        'seo',
    ];

    public array $translatable = ['heading', 'description', 'place'];
}