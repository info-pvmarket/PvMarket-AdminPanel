<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class Brand extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'can_show_menu',
        'is_hold',
        'alt_tag',
        'is_active',
        'menu_order',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_hold'     => 'boolean',
        'menu_order'  => 'integer',
    ];
    public array $translatable = ['name'];

    public function getTable(): string
    {
        return $this->collection ?? parent::getTable();
    }
}