<?php

namespace Tests\Unit;

use App\Models\Notification;
use ArrayObject;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function test_mongodb_array_metadata_is_returned_without_json_decoding(): void
    {
        $notification = new Notification;
        $notification->setRawAttributes([
            'metadata' => [
                'collection' => 'products',
                'translated_count' => 12,
            ],
        ]);

        $this->assertSame([
            'collection' => 'products',
            'translated_count' => 12,
        ], $notification->metadataArray());
    }

    public function test_array_object_and_legacy_json_metadata_are_normalized(): void
    {
        $notification = new Notification;
        $notification->setRawAttributes([
            'metadata' => new ArrayObject(['listing_id' => 'abc123']),
        ]);

        $this->assertSame(['listing_id' => 'abc123'], $notification->metadataArray());

        $notification->setRawAttributes([
            'metadata' => '{"product_id":"product-1"}',
        ]);

        $this->assertSame(['product_id' => 'product-1'], $notification->metadataArray());
    }

    public function test_translation_notification_links_to_language_management(): void
    {
        $notification = new Notification;
        $notification->type = 'translation_completed';
        $notification->metadata = ['collection' => 'products'];

        $this->assertSame(
            route('admin.setup.languages.index'),
            $notification->getLink(),
        );
    }

    public function test_known_notification_types_generate_registered_links(): void
    {
        $cases = [
            ['product_created', ['product_id' => 'product-1'], '/admin/products/product-1/edit'],
            ['product_updated', ['product_id' => 'product-2'], '/admin/products/product-2/edit'],
            ['listing_created', ['listing_id' => 'listing-1'], '/user/listings/listing-1/edit'],
            ['listing_updated', ['listing_id' => 'listing-2'], '/user/listings/listing-2/edit'],
            ['low_stock_alert', [], '/admin/inventory'],
            ['sale_status_changed', ['sale_id' => 'sale-1'], '/admin/sales'],
            ['order_status_changed', ['order_id' => 'order-1'], '/admin/sales'],
            ['order_note_added', ['order_id' => 'order-2'], '/admin/sales'],
        ];

        foreach ($cases as [$type, $metadata, $expectedPath]) {
            $notification = new Notification;
            $notification->type = $type;
            $notification->metadata = $metadata;

            $this->assertStringEndsWith($expectedPath, $notification->getLink(), $type);
        }
    }

    public function test_unknown_notification_type_has_a_safe_non_navigating_link(): void
    {
        $notification = new Notification;
        $notification->type = 'legacy_notification';

        $this->assertSame('#', $notification->getLink());
    }

    public function test_view_all_notifications_route_uses_dedicated_page(): void
    {
        $this->assertStringEndsWith(
            '/admin/notifications/all',
            route('admin.notifications.page'),
        );
    }

    public function test_notifications_page_uses_admin_safe_pagination_markup(): void
    {
        $view = file_get_contents(resource_path('views/admin/notifications/index.blade.php'));

        $this->assertStringContainsString('notifications-pagination-links', $view);
        $this->assertStringContainsString('$notifications->previousPageUrl()', $view);
        $this->assertStringContainsString('$notifications->nextPageUrl()', $view);
        $this->assertStringNotContainsString('{{ $notifications->links() }}', $view);
    }
}
