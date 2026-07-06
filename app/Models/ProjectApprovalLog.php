<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class ProjectApprovalLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'project_approval_logs';

    protected $fillable = [
        'project_id',
        'action',        // submitted | approved | rejected | note_added
        'performed_by',
        'notes',
    ];

    protected $casts = [
        'project_id'    => AsObjectId::class,
        'performed_by'  => AsObjectId::class,
    ];

    public function scopeForProject($query, string $projectId)
    {
        return $query->where('project_id', new \MongoDB\BSON\ObjectId($projectId));
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'submitted'   => 'Submitted',
            'approved'    => 'Approved',
            'rejected'    => 'Rejected',
            'note_added'  => 'Note Added',
            default       => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}