<?php

namespace App\Services;

use App\Mail\ListingUpdated;
use Illuminate\Support\Facades\Mail;

final class ListingUpdateService
{
    public const RECIPIENT = 'info@pv.market';

    public function requireReapproval(array $attributes): array
    {
        $attributes['verification_status'] = 'pending';

        return $attributes;
    }

    public function notify(string $productName, string $updatedBy): void
    {
        // Do not add CC/BCC recipients: listing-update mail is intentionally
        // restricted to the PV Market review inbox.
        Mail::to(self::RECIPIENT)->queue(
            new ListingUpdated($productName, $updatedBy)
        );
    }
}
