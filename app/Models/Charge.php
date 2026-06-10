<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasTranslations;

class Charge extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'charges';

    protected $fillable = [
        'name',
        'charge',
        'slug',
    ];
    public array $translatable = [
        'name',
    ];
}