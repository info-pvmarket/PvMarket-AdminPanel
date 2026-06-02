<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use App\Casts\AsObjectId;

class Advertisement extends Model {
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'advertisements';
    protected $fillable = [
    'title',
    'alt_tag',
    'image',
    'redirect_link',
    'location_id',
    'is_active',
];
    protected $casts = [
    'location_id' => AsObjectId::class,
    'is_active'   => 'boolean',
];

    public array $translatable = ['title', 'alt_tag'];
}