<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class Warehouse extends Model
{
        use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'warehouses';

    protected $fillable = [
        'user_id',
        'warehouse_name',
        'country',
        'zip_code',
        'street',
        'apartment_suite',
        'city',
        'warehouse_email',
        'contact_name',
        'contact_mobile',
        'ddp_deliverable_countries',
        'is_paid',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active'                 => 'boolean',
        'is_paid'                   => 'boolean',
        'ddp_deliverable_countries' => 'array',
    ];
    
    public array $translatable = [
    'warehouse_name',
    'city',
    'country',
];

    public function getNameAttribute(): ?string
{
    return $this->warehouse_name;
}

    public function getTable(): string
    {
        return $this->collection ?? parent::getTable();
    }
}