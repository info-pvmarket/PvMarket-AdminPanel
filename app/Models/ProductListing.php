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

    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
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
        return match (self::normalizeSellType($sellType)) {
            'sell by pallets' => 'pallets',
            'sell by container' => 'container',
            default => 'pcs',
        };
    }

    public static function normalizeSellType(mixed $sellType): string
    {
        if (! is_string($sellType) && ! is_int($sellType) && ! is_float($sellType)) {
            return 'sell by pieces';
        }

        $normalizedSellType = strtolower(trim((string) $sellType));

        if (str_contains($normalizedSellType, 'pallet')) {
            return 'sell by pallets';
        }

        if (str_contains($normalizedSellType, 'container')) {
            return 'sell by container';
        }

        return 'sell by pieces';
    }

    public static function quantityMultiplier(mixed $sellType, ?Product $product): ?int
    {
        return match (self::normalizeSellType($sellType)) {
            'sell by pallets' => self::positivePackagingValue($product?->pieces_per_pallet),
            'sell by container' => self::containerQuantityMultiplier($product),
            default => 1,
        };
    }

    public static function quantityForDisplay(int $quantityInPieces, mixed $sellType, ?Product $product): int
    {
        $multiplier = self::quantityMultiplier($sellType, $product);

        return $multiplier === null ? $quantityInPieces : intdiv($quantityInPieces, $multiplier);
    }

    public static function quantityInPieces(int $displayQuantity, mixed $sellType, ?Product $product): ?int
    {
        $multiplier = self::quantityMultiplier($sellType, $product);

        return $multiplier === null ? null : $displayQuantity * $multiplier;
    }

    public static function sellTypeLabel(mixed $sellType): string
    {
        if (! is_string($sellType) && ! is_int($sellType) && ! is_float($sellType)) {
            return 'N/A';
        }

        if (trim((string) $sellType) === '') {
            return 'N/A';
        }

        return match (self::normalizeSellType($sellType)) {
            'sell by pallets' => 'Sell By Pallets',
            'sell by container' => 'Sell By Container',
            default => 'Sell By Pieces',
        };
    }

    private static function positivePackagingValue(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private static function containerQuantityMultiplier(?Product $product): ?int
    {
        $piecesPerPallet = self::positivePackagingValue($product?->pieces_per_pallet);
        $palletsPerContainer = self::positivePackagingValue($product?->pallets_per_container);

        return $piecesPerPallet !== null && $palletsPerContainer !== null
            ? $piecesPerPallet * $palletsPerContainer
            : null;
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
