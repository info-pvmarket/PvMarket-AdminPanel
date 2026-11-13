<?php

namespace App\Services;

use App\Models\BidRequest;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * In-app notifications for website/app users when an admin acts on their data.
 *
 * The admin panel updates MongoDB directly, so the Go API's notification calls
 * never run for these actions. This writes the same documents the Go
 * NotificationService writes (same collection, field names and type values),
 * so GET /notifications in the API, the website and the mobile app show them.
 *
 * A failed notification must never break the admin action, so every write is
 * wrapped and only logged.
 */
class UserNotifier
{
    /** Order status labels, as in admin/sales/index.blade.php */
    private const ORDER_STATUSES = [
        0 => 'Pending',
        1 => 'Confirmed',
        2 => 'Shipped',
        3 => 'Delivered',
        4 => 'Cancelled',
        5 => 'Payment Confirmed',
        6 => 'Seller Confirmed',
    ];

    public function productReviewed(Product $product, bool $approved): void
    {
        $name = (string) ($product->product_name ?? 'your product');

        $this->send(
            $product->created_by,
            $approved ? 'product_approved' : 'product_rejected',
            $approved ? 'Product Approved' : 'Product Rejected',
            $approved
                ? "Your product '{$name}' has been approved and is now live on the marketplace."
                : "Your product '{$name}' has been rejected.",
            ['product_id' => (string) $product->_id, 'product_name' => $name],
        );
    }

    public function listingReviewed(ProductListing $listing, bool $approved): void
    {
        $name = $this->listingName($listing);

        $this->send(
            $listing->user_id,
            $approved ? 'listing_approved' : 'listing_rejected',
            $approved ? 'Listing Approved' : 'Listing Rejected',
            $approved
                ? "Your listing for '{$name}' has been approved and is now live on the marketplace."
                : "Your listing for '{$name}' has been rejected.",
            ['listing_id' => (string) $listing->_id, 'product_name' => $name],
        );
    }

    public function listingPaymentApproved(ProductListing $listing): void
    {
        $name = $this->listingName($listing);

        $this->send(
            $listing->user_id,
            'general',
            'Listing Payment Approved',
            "The payment for your listing '{$name}' has been approved.",
            ['listing_id' => (string) $listing->_id, 'product_name' => $name],
        );
    }

    public function warehousePaid(Warehouse $warehouse): void
    {
        $name = (string) ($warehouse->warehouse_name ?? 'your warehouse');

        $this->send(
            $warehouse->user_id,
            'general',
            'Warehouse Activated',
            "Your warehouse '{$name}' has been marked as paid and is now active.",
            ['warehouse_id' => (string) $warehouse->_id, 'warehouse_name' => $name],
        );
    }

    /** Tells the buyer, and the seller of the listing, that the order status changed. */
    public function orderStatusChanged(Order $order, int $status): void
    {
        $label = self::ORDER_STATUSES[$status] ?? 'Updated';
        $number = (string) ($order->unique_id ?? '');
        $orderId = (string) $order->_id;

        // same texts as SendOrderStatusNotification / SendSaleStatusChangedNotification in the API
        $this->send(
            $order->user_id,
            'order_status_changed',
            'Order Status Updated',
            "Your order #{$number} status has been updated to: {$label}",
            ['order_id' => $orderId, 'order_number' => $number, 'new_status' => $label],
        );

        $listing = $order->offer_id ? ProductListing::find((string) $order->offer_id) : null;
        if ($listing) {
            $this->send(
                $listing->user_id,
                'sale_status_changed',
                'Sale Status Updated',
                "Sale #{$number} status has been updated to: {$label}",
                ['sale_id' => $orderId, 'order_number' => $number, 'new_status' => $label],
            );
        }
    }

    public function orderPaymentVerified(Order $order): void
    {
        $number = (string) ($order->unique_id ?? '');

        $this->send(
            $order->user_id,
            'order_status_changed',
            'Payment Verified',
            "The payment for your order #{$number} has been verified.",
            ['order_id' => (string) $order->_id, 'order_number' => $number, 'new_status' => 'Payment Verified'],
        );
    }

    public function bidStatusChanged(BidRequest $bid): void
    {
        $label = $bid->status_label; // Pending / Accepted / Rejected / Completed
        $type = $bid->request_type === 'fair request' ? 'fair price request' : 'bid request';

        $this->send(
            $bid->user_id,
            'general',
            'Request ' . $label,
            "Your {$type} status has been updated to: {$label}",
            ['bid_id' => (string) $bid->_id, 'new_status' => $label],
        );
    }

    private function listingName(ProductListing $listing): string
    {
        $product = $listing->product_id ? Product::find((string) $listing->product_id) : null;

        return (string) ($product?->product_name ?? 'your product');
    }

    private function send(mixed $userId, string $type, string $title, string $message, array $metadata): void
    {
        if (empty($userId)) {
            return;
        }

        try {
            Notification::create([
                'user_id' => (string) $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'metadata' => $metadata,
                'is_read' => false,
            ]);
        } catch (Throwable $e) {
            Log::warning("User notification '{$type}' failed: {$e->getMessage()}");
        }
    }
}
