<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class ProductDetailOption extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'specifications';

    public function getTable(): string  
    {
        return 'specifications';
    }

   protected $fillable = [
        'name',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'unit_ids',
        'unit_names',
        'is_tag',
        'is_active',
    ];

    protected $casts = [
        'unit_ids'   => 'array',
        'unit_names' => 'array',
    ];
    public array $translatable = [
        'name',
        'category_name',
        'sub_category_name',
        'unit_names',
    ];
}
