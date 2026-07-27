<?php

namespace App\Models;

use App\Casts\AsObjectId;
use ArrayObject;
use Illuminate\Support\Facades\Route;
use MongoDB\BSON\ObjectId;
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
        'user_id' => AsObjectId::class,
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
     * Return notification metadata in a consistent array format.
     *
     * MongoDB returns embedded documents as arrays/ArrayObjects, so Laravel's
     * JSON "array" cast must not be used for this field.
     */
    public function metadataArray(): array
    {
        $metadata = $this->attributes['metadata'] ?? null;

        if ($metadata instanceof ArrayObject) {
            return $metadata->getArrayCopy();
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_object($metadata)) {
            return (array) $metadata;
        }

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Get the link for this notification based on type and metadata.
     */
    public function getLink(): string
    {
        $metadata = $this->metadataArray();

        return match ($this->type) {
            'product_created', 'product_updated' => isset($metadata['product_id'])
                ? $this->routeOrFallback('admin.products.edit', $metadata['product_id'], 'admin.products.index')
                : $this->routeOrFallback('admin.products.index'),
            'listing_created', 'listing_updated' => isset($metadata['listing_id'])
                ? $this->routeOrFallback('product_listing.edit', $metadata['listing_id'], 'product_listing.index')
                : $this->routeOrFallback('product_listing.index'),
            'low_stock_alert' => $this->routeOrFallback('admin.inventory.index'),
            'sale_created', 'sale_status_changed', 'order_status_changed', 'order_note_added' => $this->routeOrFallback('admin.sales.index'),
            'translation_completed', 'translation_completed_with_errors', 'translation_failed' => $this->routeOrFallback('admin.setup.languages.index'),
            default => '#',
        };
    }

    /**
     * Generate a notification link without allowing stale notification types
     * or renamed routes to break the complete notification response.
     */
    private function routeOrFallback(
        string $name,
        mixed $parameters = [],
        ?string $fallback = null,
    ): string {
        if (Route::has($name)) {
            return route($name, $parameters);
        }

        if ($fallback !== null && Route::has($fallback)) {
            return route($fallback);
        }

        return '#';
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
        return $query->where('user_id', new ObjectId((string) $userId));
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
