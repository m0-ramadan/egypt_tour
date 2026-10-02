<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminActivityNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $notificationSubject,
        public readonly array $details,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->notificationSubject . ' - Egypt Tour Pro');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-activity-notification');
    }
}
