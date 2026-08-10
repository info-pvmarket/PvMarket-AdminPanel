<?php

namespace App\Services;

use App\Mail\ListingCreated;
use App\Mail\ListingUpdated;
use Illuminate\Support\Facades\Mail;

final class ListingUpdateService
{
    public const RECIPIENT = 'info@pv.market';

    public function requireReapproval(array $attributes, bool $isSuperAdmin = false): array
    {
        if ($isSuperAdmin) {
            return $attributes;
        }

        $attributes['verification_status'] = 'pending';

        return $attributes;
    }

    public function notify(string $productName, string $updatedBy, bool $isSuperAdmin = false): void
    {
        if ($isSuperAdmin) {
            return;
        }

        // Do not add CC/BCC recipients: listing-update mail is intentionally
        // restricted to the PV Market review inbox.
        Mail::to(self::RECIPIENT)->queue(
            new ListingUpdated($productName, $updatedBy)
        );
    }

    public function notifyCreated(string $productName, string $createdBy): void
    {
        // Listing-creation mail follows the same strict recipient policy as
        // listing updates: one explicit address and no CC/BCC recipients.
        Mail::to(self::RECIPIENT)->queue(
            new ListingCreated($productName, $createdBy)
        );
    }
}
