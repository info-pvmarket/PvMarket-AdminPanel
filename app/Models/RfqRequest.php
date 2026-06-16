<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RfqRequest extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'rfqs';
    protected $table = 'rfqs';

    protected $fillable = [
        'rfq_no',
        'company_name',
        'contact_person',
        'business_email',
        'phone',
        'country_of_delivery',
        'buyer_type',
        'items',
        'incoterm',
        'destination',
        'delivery_date',
        'payment_terms',
        'currency',
        'quote_needed_by',
        'notes',
        'total_items',
        'total_quantity',
        'status',
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
        'quote_needed_by' => 'datetime',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
    ];
}
