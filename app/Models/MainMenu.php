<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class MainMenu extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'categories';

    protected $fillable = [
        'category_name',
        'slug',
        'category_icon_image',
        'stock_value',
        'icon_alt_tag',
        'is_hold',
        'created_by',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_hold'    => 'boolean',
        'stock_value' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return $this->collection ?? parent::getTable();
    }

    public array $translatable = [
        'category_name',
    ];
}