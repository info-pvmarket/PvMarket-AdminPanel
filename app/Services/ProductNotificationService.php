<?php

namespace App\Services;

use App\Mail\ProductCreated;
use App\Mail\ProductUpdated;
use Illuminate\Support\Facades\Mail;

final class ProductNotificationService
{
    public const RECIPIENT = 'info@pv.market';

    public function requireReapproval(array $attributes, bool $isSuperAdmin = false): array
    {
        if (! $isSuperAdmin) {
            $attributes['verification_status'] = 'pending';
        }

        return $attributes;
    }

    public function notifyCreated(string $productName, string $createdBy, bool $isSuperAdmin = false): void
    {
        if ($isSuperAdmin) {
            return;
        }

        // Product-submission mail is intentionally restricted to one explicit
        // review inbox. Do not add administrator addresses, CC, or BCC.
        Mail::to(self::RECIPIENT)->queue(
            new ProductCreated($productName, $createdBy)
        );
    }

    public function notifyUpdated(string $productName, string $updatedBy, bool $isSuperAdmin = false): void
    {
        if ($isSuperAdmin) {
            return;
        }

        // Product-update mail follows the same fixed-recipient policy.
        Mail::to(self::RECIPIENT)->queue(
            new ProductUpdated($productName, $updatedBy)
        );
    }
}
