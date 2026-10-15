<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ProductImportBatch extends Model 
{
    protected $connection = 'mongodb';
    protected $collection = 'product_import_batches';
     protected $fillable = [
        'user_id', 'category_id', 'sub_category_id', 'file_name',
        'status', 'success_count', 'error_count', 'errors', 'completed_at',
    ];

    protected $casts = [
        'success_count' => 'integer',
        'error_count'   => 'integer',
        'completed_at'  => 'datetime',
    ];
}