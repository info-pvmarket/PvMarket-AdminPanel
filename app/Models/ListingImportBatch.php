<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ListingImportBatch extends Model 
{
    protected $connection = 'mongodb';
    protected $collection = 'listing_import_batches';
    protected $fillable = ['user_id', 'file_name', 'status', 'success_count', 'error_count', 'errors', 'completed_at'];
    protected $casts = ['success_count' => 'integer', 'error_count' => 'integer', 'errors' => 'array', 'completed_at' => 'datetime',];
}