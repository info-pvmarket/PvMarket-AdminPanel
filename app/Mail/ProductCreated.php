<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $productName,
        public string $createdBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Product Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.product-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
