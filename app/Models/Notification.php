<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'metadata',
        'is_read',
        'read_at',
        'deleted_at',
    ];

    protected $casts = [
        'user_id' => \App\Casts\AsObjectId::class,
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    /**
     * Get the link for this notification based on type and metadata.
     */
    public function getLink(): string
    {
        $metadata = $this->metadata ?? [];

        return match($this->type) {
            'product_created' => isset($metadata['product_id'])
                ? route('admin.products.edit', $metadata['product_id'])
                : route('admin.products.index'),
            'listing_created', 'listing_updated' => isset($metadata['listing_id'])
                ? route('admin.product-listings.edit', $metadata['listing_id'])
                : route('admin.product-listings.index'),
            'low_stock_alert' => route('admin.stock-alerts.index'),
            'sale_created', 'sale_status_changed' => isset($metadata['sale_id'])
                ? route('admin.sales.show', $metadata['sale_id'])
                : route('admin.sales.index'),
            default => '#',
        };
    }

    /**
     * Get the time ago string.
     */
    public function getTimeAgo(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '';
    }

    /**
     * Scope to get notifications for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', new \MongoDB\BSON\ObjectId((string) $userId));
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to exclude deleted notifications.
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }
}
