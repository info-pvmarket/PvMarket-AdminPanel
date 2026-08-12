<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $productName,
        public string $updatedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.product-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
