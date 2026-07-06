<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class Project extends Model
{
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'projects';

    protected $fillable = [
        'project_name',
        'customer_name',
        'project_type',      // residential | commercial | industrial
        'capacity_kw',
        'location',
        'submitted_by',
        'description',
        'status',             // pending | approved | rejected
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'submitted_by' => AsObjectId::class,
        'reviewed_by'  => AsObjectId::class,
        'capacity_kw'  => 'float',
        'reviewed_at'  => 'datetime',
    ];

    public array $translatable = [
        'description',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by', '_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', '_id');
    }

    public function logs()
    {
        return $this->hasMany(ProjectApprovalLog::class, 'project_id', '_id');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => 'Pending',
        };
    }
}