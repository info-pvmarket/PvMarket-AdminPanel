<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;
use App\Traits\HasTranslations;

class InventoryTransaction extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'inventory_transactions';

    protected $fillable = [
    'listing_id',
    'product_id',
    'warehouse_id',
    'user_id',
    'transaction_type',
    'quantity',
    'quantity_before',   
    'quantity_after',    
    'quantity_change',   
    'type',              
    'reason',            
    'reference_type',
    'reference_id',
    'notes',
    'created_by',
];

    protected $casts = [
        'listing_id'          => AsObjectId::class,
        'product_id'          => AsObjectId::class,
        'warehouse_id'        => AsObjectId::class,
        'user_id'             => AsObjectId::class,
        'reference_id'        => AsObjectId::class,
        'created_by'          => AsObjectId::class,
        'quantity'            => 'integer',
    ];
    public array $translatable = [
        'notes',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function listing()
    {
        return $this->belongsTo(\App\Models\ProductListing::class, 'listing_id', '_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', '_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'warehouse_id', '_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', '_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', '_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForListing($query, string $listingId)
{
    return $query->where('listing_id', new \MongoDB\BSON\ObjectId($listingId));
}

public function scopeForUser($query, string $userId)
{
    return $query->where('user_id', new \MongoDB\BSON\ObjectId($userId));
}

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeStockMovements($query)
    {
        return $query->whereIn('transaction_type', [
            'initial_stock', 'stock_add', 'stock_reduce',
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Calculate current stock for a listing by summing all transactions.
     */
    public static function currentStock(string $listingId): int
{
    $addTypes    = ['initial_stock', 'stock_add'];
    $removeTypes = ['stock_reduce'];

    $added = static::where('listing_id', new \MongoDB\BSON\ObjectId($listingId))
                   ->whereIn('transaction_type', $addTypes)
                   ->sum('quantity');

    $removed = static::where('listing_id', new \MongoDB\BSON\ObjectId($listingId))
                     ->whereIn('transaction_type', $removeTypes)
                     ->sum('quantity');

        return max(0, (int) $added - (int) $removed);
    }


    /**
     * Human-readable label for transaction type.
     */
    public function getTransactionLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'initial_stock'  => 'Initial Stock',
            'stock_add'      => 'Stock Added',
            'stock_reduce'   => 'Stock Reduced',
            'alert_settings' => 'Alert Settings',
            default          => ucfirst(str_replace('_', ' ', $this->transaction_type)),
        };
    }

    /**
     * Whether this transaction increases stock.
     */
    public function getIsAdditionAttribute(): bool
    {
        return in_array($this->transaction_type, ['initial_stock', 'stock_add']);
    }
}