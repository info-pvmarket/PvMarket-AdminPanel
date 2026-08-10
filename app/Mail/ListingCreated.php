<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $productName,
        public string $createdBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Listing Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listing-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
