<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;


class LeadGeneration extends Model
{
    
    protected $connection = 'mongodb';
    protected $collection = 'lead_generations';

    protected $fillable = [
        'lead_type',
        'name',
        'email',
        'phone',
        'country_code',
        'lead_data',
        'lead_from',
        'lead_device',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'lead_type'   => 'integer',
        'lead_device' => 'integer',
        'status'      => 'integer',
    ];

    

    public function getLeadTypeLabelAttribute(): string
    {
        return match((int)$this->lead_type) {
            1 => 'Book Free',
            2 => 'Spot Price',
            3 => 'Contact',
            4 => 'Newsletter',
            default => 'Unknown',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match((int)$this->status) {
            0 => 'Pending',
            1 => 'Processed',
            2 => 'Rejected',
            default => 'Pending',
        };
    }
}