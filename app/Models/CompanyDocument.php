<?php

namespace App\Models;

use App\Casts\AsObjectId;
use MongoDB\Laravel\Eloquent\Model;

class CompanyDocument extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'company_documents';

    protected $fillable = [
        'company_id',
        'document_type',
        'filename',
        'original_name',
        'path',
        'url',
        'mime_type',
        'size',
        'uploaded_at',
        'is_verified',
        'deleted_at',
    ];

    protected $casts = [
        'company_id'  => AsObjectId::class,
        'uploaded_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
        'is_verified' => 'boolean',
        'size'        => 'integer',
    ];
}
