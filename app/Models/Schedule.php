<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\traits\HasTranslations;

class Schedule extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'schedules';

    protected $fillable = [
        'requester',
        'requester_email',
        'title',
        'date',
        'time',
        'duration',
        'status',
    ];
    public array $translatable = [
        'title',
    ];
}