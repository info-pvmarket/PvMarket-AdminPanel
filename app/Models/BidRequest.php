<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class BidRequest extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'bid_requests';

    protected $fillable = [
        'unique_id',
        'selected_pcs_qty',
        'quantity_unit',
        'sell_type',
        'purchased_currency',
        'bid_price_per_piece',
        'offer_id',
        'order_id',
        'final_price_per_pcs',
        'country_id',
        'request_type',
        'lead_time',
        'product_id',
        'product_name',
        'company_id',
        'company_name',
        'created_by',
        'updated_by',
        'status',
        'is_active',
        'completed_at',
    ];

    protected $casts = [
        'product_id'       => AsObjectId::class,
        'company_id'       => AsObjectId::class,
        'offer_id'         => AsObjectId::class,
        'order_id'         => AsObjectId::class,
        'country_id'       => AsObjectId::class,
        'created_by'       => AsObjectId::class,
        'updated_by'       => AsObjectId::class,
        'selected_pcs_qty' => 'integer',
        'sell_type'        => 'integer',
        'lead_time'        => 'integer',
        'status'           => 'integer',
        'is_active'        => 'integer',
    ];
    public array $translatable = [
        'product_name',
        'company_name',
    ];

    // ── Accessors ─────────────────────────────────────────────────
    public function getRequestTypeLabelAttribute(): string
    {
        return match($this->request_type) {
            'fair request' => 'Fair Request',
            'bid request'  => 'Bid Request',
            default        => 'Bid Request',
        };
    }

    public function getRequestTypeBadgeClassAttribute(): string
    {
        return match($this->request_type) {
            'fair request' => 'badge-fair',
            'bid request'  => 'badge-bid',
            default        => 'badge-bid',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match((int)$this->status) {
            0 => 'Pending',
            1 => 'Accepted',
            2 => 'Rejected',
            3 => 'Completed',
            default => 'Pending',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match((int)$this->status) {
            0 => 'status-pending',
            1 => 'status-accepted',
            2 => 'status-rejected',
            3 => 'status-completed',
            default => 'status-pending',
        };
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}