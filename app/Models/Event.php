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
        'end_date',     // last day of a multi-day event (TASK-6.12)
        'timezone',     // IANA zone event_date / end_date are in
        'description',
        'image',
        'alt_tag',
        'seo',
    ];

    public array $translatable = ['heading', 'description', 'place'];
}