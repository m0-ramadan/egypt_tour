<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $details,
        public readonly bool $isTest = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $reference = $this->details['booking_number'] ?? 'New';

        return new Envelope(
            subject: ($this->isTest ? '[TEST] ' : '') . "New Booking {$reference} - Egypt Tour Pro",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-notification',
        );
    }
}
