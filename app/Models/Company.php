<?php

namespace App\Models;

use App\Casts\AsObjectId;
use MongoDB\Laravel\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'companies';

    protected $fillable = [
        'country_id',
        'registered_country',
        'user_id',
        'vat_no',
        'name',
        'company_name',
        'company_type',
        'vat_verified',
        'seller_verified',
        'is_editable',
        'allow_doc',
        'is_active',
        'address',
        'created_by',
        'company_verified',
        'show_verified_batch',
    ];

    protected $casts = [
        'country_id'          => AsObjectId::class,
        'user_id'             => AsObjectId::class,
        'created_by'          => AsObjectId::class,
        'vat_verified'        => 'boolean',
        'seller_verified'     => 'boolean',
        'is_editable'         => 'boolean',
        'allow_doc'           => 'boolean',
        'is_active'           => 'boolean',
        'company_verified'    => 'boolean',
        'show_verified_batch' => 'boolean',
    ];
}
