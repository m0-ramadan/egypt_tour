<?php

namespace App\Services;

use App\Mail\BookingNotificationMail;
use App\Mail\AdminActivityNotificationMail;
use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    public static function sendBookingNotification(Booking $booking, ?string $recipient = null, bool $isTest = false): void
    {
        try {
            $booking->loadMissing(['client', 'package', 'items', 'travelers']);
            $checkout = $booking->checkout_details ?? [];
            $item = $booking->items->first();
            $travelers = $booking->travelers->map(function ($traveler): string {
                return trim(implode(' ', array_filter([
                    ucfirst((string) $traveler->traveler_type),
                    $traveler->title,
                    $traveler->first_name,
                    $traveler->last_name,
                ])));
            })->implode("\n");

            $roomBreakdown = $checkout['room_breakdown'] ?? $item?->meta['room_breakdown'] ?? null;
            if (is_array($roomBreakdown)) {
                $roomBreakdown = collect($roomBreakdown)->map(function ($room, $index): string {
                    if (! is_array($room)) {
                        return 'Room ' . ($index + 1) . ': ' . (string) $room;
                    }

                    return 'Room ' . ($index + 1) . ': ' . implode(', ', array_filter([
                        $room['accommodation'] ?? null,
                        isset($room['adults']) ? $room['adults'] . ' adult(s)' : null,
                        isset($room['children']) ? $room['children'] . ' child(ren)' : null,
                        $room['occupancy_type'] ?? null,
                    ]));
                })->implode("\n");
            }

            $money = fn($value): string => $value === null || $value === ''
                ? 'N/A'
                : $booking->currency_code . ' ' . number_format((float) $value, 2);

            $details = [
                'booking_number' => $booking->booking_number,
                'submitted_at' => now()->timezone('Africa/Cairo')->format('Y-m-d H:i:s'),
                'sections' => [
                    'Booking details' => [
                        'Booking number' => $booking->booking_number,
                        'Status' => ucfirst((string) $booking->status),
                        'Booking date' => $booking->booking_date?->format('Y-m-d'),
                        'Travel date' => $booking->travel_date?->format('Y-m-d'),
                        'Package' => $booking->package?->display_title ?? $booking->package?->title ?? 'N/A',
                        'Pricing option' => $checkout['option_label'] ?? $item?->option_label ?? 'N/A',
                    ],
                    'Customer details' => [
                        'Name' => $booking->client_name,
                        'Email' => $booking->client?->email,
                        'Phone' => $booking->client?->phone,
                        'Nationality' => $booking->client?->nationality,
                    ],
                    'Travelers' => [
                        'Adults' => $booking->adults,
                        'Children' => $booking->children,
                        'Infants' => $booking->infants,
                        'Traveler names' => $travelers ?: 'N/A',
                    ],
                    'Price and payment' => [
                        'Total' => $money($booking->total_amount),
                        'Deposit' => $money($checkout['deposit_amount'] ?? $item?->meta['deposit_amount'] ?? null),
                        'Remaining balance' => $money($checkout['remaining_balance'] ?? $item?->meta['remaining_balance'] ?? null),
                        'Payment method' => strtoupper((string) ($checkout['payment_provider'] ?? 'N/A')),
                        'Payment status' => ucfirst((string) $booking->payment_status),
                    ],
                    'Trip requirements' => [
                        'Rooms' => $checkout['rooms'] ?? $item?->room_count ?? 1,
                        'Room breakdown' => $roomBreakdown ?: 'N/A',
                        'Pickup location' => $booking->pickup_location ?: 'N/A',
                        'Special requests' => $booking->special_requests ?: 'N/A',
                    ],
                ],
            ];

            $recipient ??= config('mail.booking_recipient')
                ?: Setting::where('key', 'admin_email')->value('value')
                ?: Setting::where('key', 'site_email')->value('value')
                ?: config('mail.from.address');

            if ($recipient) {
                Mail::to($recipient)->send(new BookingNotificationMail($details, $isTest));
            }
        } catch (\Throwable $e) {
            Log::error('Booking notification email failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'recipient' => $recipient,
            ]);
        }
    }

    /**
     * Send email notification to admin upon booking/inquiry submission.
     */
    public static function sendAdminNotification(string $subject, array $details): void
    {
        try {
            $adminEmail = config('mail.booking_recipient')
                ?: Setting::where('key', 'admin_email')->value('value')
                ?: Setting::where('key', 'site_email')->value('value')
                ?: config('mail.from.address')
                ?: 'info@egypttourpro.com';

            if (empty($adminEmail)) {
                return;
            }

            $formattedDetails = [];
            foreach ($details as $key => $val) {
                if (is_array($val)) {
                    $val = collect($val)->flatten()->implode(', ');
                }
                $formattedDetails[ucfirst(str_replace('_', ' ', (string) $key))] = (string) $val;
            }

            Mail::to($adminEmail)->send(new AdminActivityNotificationMail($subject, $formattedDetails));
        } catch (\Throwable $e) {
            Log::error('AdminNotificationService failed to send email: ' . $e->getMessage(), [
                'subject' => $subject,
                'details' => $details,
            ]);
        }
    }
}
