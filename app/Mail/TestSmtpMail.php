<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestSmtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('اختبار إعدادات البريد الإلكتروني')
                    ->view('emails.test-smtp')
                    ->with([
                        'date' => now()->format('Y-m-d H:i:s'),
                        'app_name' => config('app.name'),
                    ]);
    }
}