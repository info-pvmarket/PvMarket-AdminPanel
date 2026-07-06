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
        'grid_type',
        'phase_type',
        'polygon_coordinates',
        'monthly_consumption_kwh',
        'roof_area_sqm',
        'system_size_kw',
        'panel_count',
        'annual_production_kwh',
        'blueprint_base64',
        'analysis_results',
        'selected_tier',
        'selected_products',
        'hardware_cost',
        'bos_cost',
        'total_cost',
        'location_name',
        'latitude',
        'longitude',
        'layout_geometry',
        'panel_dimensions',
        'quotes',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_by' => AsObjectId::class,
        'reviewed_by'  => AsObjectId::class,
        'capacity_kw'  => 'float',
        'reviewed_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'polygon_coordinates' => 'array',
        'analysis_results' => 'array',
        'selected_products' => 'array',
        'layout_geometry' => 'array',
        'panel_dimensions' => 'array',
        'quotes' => 'array',
        'monthly_consumption_kwh' => 'float',
        'roof_area_sqm' => 'float',
        'system_size_kw' => 'float',
        'panel_count' => 'integer',
        'annual_production_kwh' => 'float',
        'hardware_cost' => 'float',
        'bos_cost' => 'float',
        'total_cost' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
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
