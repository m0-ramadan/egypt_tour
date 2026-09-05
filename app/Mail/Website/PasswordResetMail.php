<?php

namespace App\Mail\Website;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(string $token, string $email)
    {
        // Build the front-end reset URL (change to your SPA route)
        $this->resetUrl = config('app.frontend_url')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($email);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'إعادة تعيين كلمة المرور',
            from: new Address(config('mail.from.address'), config('mail.from.name'))
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            markdown: 'emails.password-reset',
            with: ['resetUrl' => $this->resetUrl]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}