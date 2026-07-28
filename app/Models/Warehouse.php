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
        'country',          // stored as ObjectId
        'country_name',     // stored as plain string
        'zip_code',
        'street',
        'apartment_suite',
        'city',
        'warehouse_email',
        'contact_name',
        'contact_mobile',
        'ddp_deliverable_countries', // array of ObjectIds
        'is_active',
        'is_paid',
        'payment_status',
        'is_primary',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'                 => 'boolean',
        'is_paid'                   => 'boolean',
        'ddp_deliverable_countries' => 'array',
        'is_primary'                => 'boolean',
    ];

    public array $translatable = [
        'warehouse_name',
        'city',
        'country_name',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->warehouse_name;
    }

    public function getPaymentStatusAttribute($value): string
    {
        if ($this->is_paid) {
            return 'paid';
        }

        return $value ?: 'pending';
    }

    public function getTable(): string
    {
        return $this->collection ?? parent::getTable();
    }
}
