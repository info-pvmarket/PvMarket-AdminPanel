<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use App\Casts\AsObjectId;

class ProductListing extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'product_listings';

    protected $fillable = [
        'product_id',
        'total_quantity',
        'is_active',
        'is_hold',
        'user_id',
        'sell_type',
        'currency_id',
        'slots',
        'is_sold_off',
        'is_popular',
        'verification_status',
        'main_category_id',
        'sku_code',
        'discount_type',
        'sub_category_id',
        'warehouse_id',
        'lead_time',
        'is_paid',
        'created_by',
        'incoterms_id',
        'slug',
        'real_time_price',
        'is_solar_listing',
        'solar_grid_types',
        'solar_phase_types',
        'approved_at',
        'approved_by',
        'solar_tier',
    ];

    protected $casts = [
    'is_active'      => 'boolean',
    'is_hold'        => 'boolean',
    'is_paid'        => 'boolean',
    'is_sold_off'    => 'boolean',
    'is_popular'     => 'boolean',
    'lead_time'      => 'integer',
    'total_quantity' => 'integer',
    'incoterms_id'       => AsObjectId::class,
    'real_time_price'  => 'boolean',
    'is_solar_listing' => 'boolean',
    'solar_tier' => 'string',
    
];
public array $translatable = [
    'sell_type',
    'discount_type',
    'verification_status',
];

    // ── Relationships ───────────────────────────────────────────────

    public function product()
{
    return $this->belongsTo(\App\Models\Product::class, 'product_id', '_id');
}

public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id', '_id');
}

public function warehouse()
{
    return $this->belongsTo(\App\Models\Warehouse::class, 'warehouse_id', '_id');
}

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByVerification($query, string $status)
    {
        return $query->where('verification_status', $status);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public static function quantityUnitForSellType(mixed $sellType): string
    {
        if (! is_string($sellType) && ! is_int($sellType) && ! is_float($sellType)) {
            return 'pcs';
        }

        $normalizedSellType = strtolower(trim((string) $sellType));

        if (str_contains($normalizedSellType, 'pallet')) {
            return 'pallets';
        }

        if (str_contains($normalizedSellType, 'container')) {
            return 'container';
        }

        return 'pcs';
    }

    public static function sellTypeLabel(mixed $sellType): string
    {
        if (! is_string($sellType) && ! is_int($sellType) && ! is_float($sellType)) {
            return 'N/A';
        }

        $normalizedSellType = trim((string) $sellType);

        return $normalizedSellType === '' ? 'N/A' : ucwords($normalizedSellType);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    /** Number of price tiers (slots) */
    public function getTierCountAttribute(): int
    {
        return count($this->slots ?? []);
    }

    /** Human-readable status label */
    public function getVerificationLabelAttribute(): string
    {
        return match ($this->verification_status) {
            'verified' => 'Verified',
            'pending'  => 'Pending approval',
            'rejected' => 'Rejected',
            default    => ucfirst($this->verification_status),
        };
    }
public function images()
{
    return $this->hasMany(ProductListingImage::class, 'product_listing_id', '_id')
                ->orderBy('sort_order', 'asc');
}
public function getSlotsAttribute($value)
{
    if (empty($value)) return [];
    return collect($value)->map(function ($slot) {
        if (is_array($slot)) return $slot;
        if (is_object($slot)) return (array) $slot;
        return [];
    })->filter(fn($s) => !empty($s))->values()->toArray();
}

}
