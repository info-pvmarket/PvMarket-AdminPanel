<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class Schedule extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'schedules';

    protected $fillable = [
        'user_id',
        'requester_name',
        'requester_email',
        'requester_mobile',
        'attendee_emails',
        'event_title',
        'event_date_time',
        'duration',
        'description',
        'status',
        'assigned_admin_id',
    ];

    protected $casts = [
        'event_date_time' => 'datetime',
        'attendee_emails' => 'array',
    ];

    public array $translatable = [
        'event_title',
        'description',
    ];

    /**
     * Get the admin this schedule is assigned to.
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    /**
     * Get the user who created this schedule.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}