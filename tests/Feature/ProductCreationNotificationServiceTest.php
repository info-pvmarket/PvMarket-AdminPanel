<?php

namespace Tests\Feature;

use App\Mail\ProductCreated;
use App\Mail\ProductUpdated;
use App\Services\ProductNotificationService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductCreationNotificationServiceTest extends TestCase
{
    public function test_product_updates_require_reapproval(): void
    {
        $attributes = app(ProductNotificationService::class)->requireReapproval([
            'verification_status' => 'verified',
            'product_name' => 'Updated product',
        ]);

        $this->assertSame('pending', $attributes['verification_status']);
        $this->assertSame('Updated product', $attributes['product_name']);
    }

    public function test_super_admin_product_updates_require_reapproval(): void
    {
        $attributes = app(ProductNotificationService::class)->requireReapproval([
            'verification_status' => 'verified',
            'product_name' => 'Updated product',
        ], true);

        $this->assertSame('pending', $attributes['verification_status']);
    }

    public function test_product_creation_email_is_only_queued_to_pv_market(): void
    {
        Mail::fake();

        app(ProductNotificationService::class)->notifyCreated(
            'Test Product',
            'creator@example.com',
        );

        Mail::assertQueued(ProductCreated::class, function (ProductCreated $mail): bool {
            return $mail->hasTo(ProductNotificationService::RECIPIENT)
                && count($mail->to) === 1
                && empty($mail->cc)
                && empty($mail->bcc);
        });
        Mail::assertQueuedCount(1);
    }

    public function test_product_update_email_is_only_queued_to_pv_market(): void
    {
        Mail::fake();

        app(ProductNotificationService::class)->notifyUpdated(
            'Test Product',
            'updater@example.com',
        );

        Mail::assertQueued(ProductUpdated::class, function (ProductUpdated $mail): bool {
            return $mail->hasTo(ProductNotificationService::RECIPIENT)
                && count($mail->to) === 1
                && empty($mail->cc)
                && empty($mail->bcc);
        });
        Mail::assertQueuedCount(1);
    }

    public function test_super_admin_product_creation_does_not_queue_email(): void
    {
        Mail::fake();

        app(ProductNotificationService::class)->notifyCreated(
            'Test Product',
            'superadmin@example.com',
            true,
        );

        Mail::assertNothingQueued();
    }

    public function test_super_admin_product_update_does_not_queue_email(): void
    {
        Mail::fake();

        app(ProductNotificationService::class)->notifyUpdated(
            'Test Product',
            'superadmin@example.com',
            true,
        );

        Mail::assertNothingQueued();
    }
}
