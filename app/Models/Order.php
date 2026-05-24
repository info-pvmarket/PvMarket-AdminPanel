<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class Order extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'unique_id',
        'user_id',
        'offer_id',
        'company_id',
        'total_qty',
        'slot_index',
        'address_id',
        'each_qty_price',
        'purchased_currency',
        'selled_currency',
        'payment_currency',
        'payment_currency_total',
        'payment_currency_advance',
        'sell_each_qty_price',
        'rate',
        'partial_payment_amount',
        'payment_status',
        'payment_verified',
        'payment_method',
        'payment_platform',
        'payment_id',
        'transaction_json',
        'transaction_upload',
        'price_type',
        'bid_id',
        'delivery_charge',
        'order_status',
        'delivery_type',
        'sell_type',
        'usd_currency_total',
        'usd_currency_advance',
        'eur_currency_total',
        'eur_currency_advance',
        'aed_currency_total',
        'aed_currency_advance',
        'is_active',
        'cart_id',
        'status_note',
        'status_notes',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'user_id'          => AsObjectId::class,
        'offer_id'         => AsObjectId::class,
        'company_id'       => AsObjectId::class,
        'address_id'       => AsObjectId::class,
        'bid_id'           => AsObjectId::class,
        'cart_id'           => AsObjectId::class,
        'created_by'       => AsObjectId::class,
        'updated_by'       => AsObjectId::class,
        'deleted_by'       => AsObjectId::class,
        'is_active'        => 'integer',
        'payment_method'   => 'integer',
        'payment_verified' => 'boolean',
        'payment_status'   => 'integer',
        'payment_platform' => 'integer',
        'price_type'       => 'integer',
        'delivery_type'    => 'integer',
        'sell_type'        => 'integer',
        'total_qty'        => 'integer',
        'slot_index'       => 'integer',
    ];

    public static function statusLabel(int $status): string
    {
        return match ($status) {
            0 => 'Pending under payment verification',
            1 => 'Confirmed',
            2 => 'Shipped',
            3 => 'Delivered',
            4 => 'Cancelled',
            default => 'Pending',
        };
    }

    public static function statusShortLabel(int $status): string
    {
        return match ($status) {
            0 => 'Pending',
            1 => 'Confirmed',
            2 => 'Shipped',
            3 => 'Delivered',
            4 => 'Cancelled',
            default => 'Pending',
        };
    }

    public static function statusColorClass($status): string
    {
        if (is_string($status)) {
            $lower = strtolower(trim($status));
            return match ($lower) {
                'pending under payment verification', 'pending' => 'status-orange',
                'confirmed' => 'status-blue',
                'shipped' => 'status-purple',
                'delivered' => 'status-green',
                'cancelled' => 'status-red',
                default => 'status-orange',
            };
        }
        return match ((int) $status) {
            0 => 'status-orange',
            1 => 'status-blue',
            2 => 'status-purple',
            3 => 'status-green',
            4 => 'status-red',
            default => 'status-orange',
        };
    }

    public static function statusColorClassFromMixed($status): string
    {
        return self::statusColorClass($status);
    }

    public static function statusLabelFromMixed($status): string
    {
        if (is_string($status)) {
            return $status;
        }
        return self::statusShortLabel((int) $status);
    }

    public static function statusToInt($status): int
    {
        if (is_int($status)) return $status;
        $lower = strtolower(trim((string) $status));
        return match ($lower) {
            'pending under payment verification', 'pending' => 0,
            'confirmed' => 1,
            'shipped' => 2,
            'delivered' => 3,
            'cancelled' => 4,
            default => 0,
        };
    }

    /**
     * Status/note history for display (newest first).
     */
    public function getStatusNotesHistory(): array
    {
        $history = $this->status_notes ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        if ($history === [] && !empty($this->status_note)) {
            $history[] = [
                'order_status' => $this->order_status ?? 'Pending',
                'note'         => (string) $this->status_note,
                'created_at'   => $this->updated_at ?? $this->created_at,
            ];
        }

        usort($history, function ($a, $b) {
            $ta = $a['created_at'] ?? '';
            $tb = $b['created_at'] ?? '';
            if ($ta instanceof \DateTimeInterface) {
                $ta = $ta->format('c');
            }
            if ($tb instanceof \DateTimeInterface) {
                $tb = $tb->format('c');
            }

            return strcmp((string) $tb, (string) $ta);
        });

        return $history;
    }

    public function recordStatusUpdate(int $orderStatus, string $note): void
    {
        $history = $this->status_notes ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        $statusLabel = self::statusLabel($orderStatus);

        $entry = [
            'order_status' => $statusLabel,
            'note'         => $note,
            'created_at'   => now()->toIso8601String(),
        ];

        if (auth()->id()) {
            $entry['created_by'] = (string) auth()->id();
        }

        $history[] = $entry;
        $this->status_notes  = $history;
        $this->order_status  = $statusLabel;
        if ($note !== '') {
            $this->status_note = $note;
        }
    }

    // ── Relationships ───────────────────────────────────────────

    /**
     * The product listing (offer) this order is for.
     */
    public function listing()
    {
        return $this->belongsTo(ProductListing::class, 'offer_id', '_id');
    }

    /**
     * The buyer (user who placed the order).
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}