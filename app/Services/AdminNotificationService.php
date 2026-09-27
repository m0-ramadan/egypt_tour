<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    /**
     * Send email notification to admin upon booking/inquiry submission.
     */
    public static function sendAdminNotification(string $subject, array $details): void
    {
        try {
            $adminEmail = Setting::where('key', 'admin_email')->value('value')
                ?: Setting::where('key', 'site_email')->value('value')
                ?: config('mail.from.address')
                ?: 'info@egypttourpro.com';

            if (empty($adminEmail)) {
                return;
            }

            $content = "=== " . strtoupper($subject) . " ===\n\n";
            foreach ($details as $key => $val) {
                if (is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
                $content .= ucfirst(str_replace('_', ' ', (string) $key)) . ": " . (string) $val . "\n";
            }
            $content .= "\nSubmitted at: " . now()->format('Y-m-d H:i:s') . "\n";

            Mail::raw($content, function ($message) use ($adminEmail, $subject) {
                $message->to($adminEmail)
                    ->subject('🔔 ' . $subject . ' - Egypt Tour Pro');
            });
        } catch (\Throwable $e) {
            Log::error('AdminNotificationService failed to send email: ' . $e->getMessage(), [
                'subject' => $subject,
                'details' => $details,
            ]);
        }
    }
}
