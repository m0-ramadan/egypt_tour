<?php

namespace Tests\Feature;

use App\Mail\AdminActivityNotificationMail;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminNotificationServiceTest extends TestCase
{
    public function test_inquiry_notifications_use_the_configured_recipient_and_include_all_details(): void
    {
        Mail::fake();
        config()->set('mail.booking_recipient', 'alerts@example.test');

        AdminNotificationService::sendAdminNotification('New Tour Inquiry', [
            'Name' => 'Test Guest',
            'Email' => 'guest@example.test',
            'Tour' => 'Cairo Day Tour',
            'Travelers' => ['2 adults', '1 child'],
            'Message' => 'Please call me.',
        ]);

        Mail::assertSent(AdminActivityNotificationMail::class, function (AdminActivityNotificationMail $mail): bool {
            return $mail->hasTo('alerts@example.test')
                && $mail->notificationSubject === 'New Tour Inquiry'
                && $mail->details['Email'] === 'guest@example.test'
                && $mail->details['Travelers'] === '2 adults, 1 child'
                && $mail->details['Message'] === 'Please call me.';
        });
    }
}
