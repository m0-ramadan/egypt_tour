<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class MessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $body;
    public ?string $file;

    /**
     * Create a new message instance.
     *
     * @param string $title
     * @param string $message
     * @param string|null $file
     */
    public function __construct(
        string $title,
        string $message,
        ?string $file = null
    ) {
        $this->title = $title;
        $this->body = $message;
        $this->file = $file;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title, // Use dynamic title for subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.message',
            with: [
                'title' => $this->title,
                'body' => $this->body,
                'file' => $this->file,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->file) {
            return [
                Attachment::fromPath($this->file), // Attach the file if provided
            ];
        }

        return [];
    }
}
