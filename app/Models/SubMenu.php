<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class SubMenu extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'sub_categories';


    protected $fillable = [
        'sub_category_name',
        'category_id',
        'slug',
        'category_name',
        'is_hold',
        'is_active',
        'stock_value',
        'pallet_applicable',
        'container_applicable',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_hold'     => 'boolean',
        'is_active'   => 'boolean',
        'stock_value' => 'boolean',
        'pallet_applicable'    => 'boolean',   
        'container_applicable' => 'boolean', 
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function getTable(): string
    {
        return $this->collection ?? parent::getTable();
    }

    public function scopeAvailableForDropdown($query)
    {
        return $query->where('is_active', true);
    }

    public array $translatable = [
        'sub_category_name',
    ];
}
