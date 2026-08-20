<?php

namespace App\Models;

use App\Casts\AsObjectId;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class PriceTransaction extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'price_transactions';

    protected $fillable = [
        'listing_id',
        'product_id',
        'warehouse_id',
        'user_id',
        'transaction_type',
        'tier_number',
        'min_quantity_before',
        'min_quantity_after',
        'max_quantity_before',
        'max_quantity_after',
        'price',
        'price_before',
        'price_after',
        'total_price',
        'total_price_before',
        'total_price_after',
        'currency_before',
        'currency_after',
        'notes',
        'reference_id',
        'reference_type',
        'created_by',
    ];

    protected $casts = [
        'listing_id' => AsObjectId::class,
        'product_id' => AsObjectId::class,
        'warehouse_id' => AsObjectId::class,
        'user_id' => AsObjectId::class,
        'reference_id' => AsObjectId::class,
        'created_by' => AsObjectId::class,
        'tier_number' => 'integer',
        'min_quantity_before' => 'integer',
        'min_quantity_after' => 'integer',
        'max_quantity_before' => 'integer',
        'max_quantity_after' => 'integer',
        'price' => 'float',
        'price_before' => 'float',
        'price_after' => 'float',
        'total_price' => 'float',
        'total_price_before' => 'float',
        'total_price_after' => 'float',
    ];

    public function scopeForListing($query, string $listingId)
    {
        return $query->where('listing_id', new ObjectId($listingId));
    }

    public function listing()
    {
        return $this->belongsTo(ProductListing::class, 'listing_id', '_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', '_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', '_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', '_id');
    }

    public function getTransactionLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'price_added' => 'Added',
            'price_removed' => 'Removed',
            default => 'Updated',
        };
    }
}
