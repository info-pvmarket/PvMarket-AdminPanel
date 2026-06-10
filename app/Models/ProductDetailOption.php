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
        'option_name',
        'data_type',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'unit_ids',
        'unit_names',
    ];

    protected $casts = [
        'unit_ids'   => 'array',
        'unit_names' => 'array',
    ];
    public array $translatable = [
        'option_name',
        'category_name',
        'sub_category_name',
        'unit_names',
    ];
}