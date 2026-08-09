<?php

namespace Tests\Feature;

use App\Mail\ListingUpdated;
use App\Services\ListingUpdateService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ListingUpdateServiceTest extends TestCase
{
    public function test_listing_update_always_requires_reapproval(): void
    {
        $service = app(ListingUpdateService::class);

        $attributes = $service->requireReapproval([
            'verification_status' => 'verified',
            'total_quantity' => 100,
        ]);

        $this->assertSame('pending', $attributes['verification_status']);
        $this->assertSame(100, $attributes['total_quantity']);
    }

    public function test_listing_update_email_is_queued_only_to_pv_market(): void
    {
        Mail::fake();

        app(ListingUpdateService::class)->notify('Test product', 'seller@example.com');

        Mail::assertQueued(ListingUpdated::class, function (ListingUpdated $mail): bool {
            return $mail->hasTo(ListingUpdateService::RECIPIENT)
                && count($mail->to) === 1
                && count($mail->cc) === 0
                && count($mail->bcc) === 0;
        });
        Mail::assertQueuedCount(1);
    }
}
