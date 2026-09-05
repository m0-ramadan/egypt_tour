<?php

namespace App\Http\Controllers\Website;

use App\Models\Country;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ContactController extends BaseWebsiteController
{
    public function index(): View
    {
        return view('website.pages.contact-us', [
            'heroStats' => [
                ['icon' => 'la la-clock', 'title' => __('24/7 Support'), 'description' => __('Round-the-clock assistance')],
                ['icon' => 'la la-users', 'title' => __('Expert Team'), 'description' => __('Local travel specialists')],
                ['icon' => 'la la-trophy', 'title' => __('Award Winning'), 'description' => __('TripAdvisor Excellence')],
                ['icon' => 'la la-shield-alt', 'title' => __('Trusted Service'), 'description' => __('15+ years experience')],
            ],
            'contactMethods' => [
                [
                    'icon' => 'la la-phone',
                    'title' => __('Call Our Experts'),
                    'description' => __('Speak directly with our travel specialists for immediate assistance and personalized recommendations.'),
                    'highlight' => __('Available 24/7'),
                    'url' => 'tel:+201553383000',
                    'label' => '-',
                ],
                [
                    'icon' => 'lab la-whatsapp',
                    'title' => __('WhatsApp Chat'),
                    'description' => __('Get quick responses and share your travel preferences with our team through WhatsApp.'),
                    'highlight' => __('Instant responses'),
                    'url' => 'https://wa.me/201553383000',
                    'label' => __('Start WhatsApp Chat'),
                    'external' => true,
                ],
                [
                    'icon' => 'lab la-viber',
                    'title' => __('Viber Messages'),
                    'description' => __('Connect with us through Viber for smooth communication and trip planning support.'),
                    'highlight' => __('Quick and convenient'),
                    'url' => 'viber://chat?number=201553383000',
                    'label' => __('Message on Viber'),
                    'external' => true,
                ],
            ],
            'inquiryTypes' => [
                'general' => __('General Information'),
                'booking' => __('New Booking'),
                'existing' => __('Existing Booking'),
                'customization' => __('Trip Customization'),
                'complaint' => __('Complaint'),
                'other' => __('Other'),
            ],
            'countries' => $this->countryOptions(),
            'officeDetails' => [
                [
                    'icon' => 'la la-map-marker',
                    'title' => __('Address'),
                    'lines' => [
                        __('Al Qarnah, Al Qarna, Luxor Governorate 1341805, Egypt'),

                    ],
                ],
                [
                    'icon' => 'la la-clock',
                    'title' => __('Office Hours'),
                    'lines' => [
                        __('Sunday - Thursday: 9:00 AM - 6:00 PM'),
                        __('Friday - Saturday: 10:00 AM - 4:00 PM'),
                    ],
                ],
                [
                    'icon' => 'la la-envelope',
                    'title' => __('Email'),
                    'lines' => [
                        'info@etrotours.com',
                        'reservations@etrotours.com',
                    ],
                    'type' => 'email',
                ],
                [
                    'icon' => 'la la-phone',
                    'title' => __('Phone Numbers'),
                    'lines' => [
                        __('International: -'),
                        __('Local: +20 15 53383000'),
                    ],
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:150'],
            'inquiry_type' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'subject_line' => ['nullable', 'string', 'max:255'],
            'form_start_time' => ['nullable', 'integer'],
        ], [], [
            'first_name' => __('first name'),
            'last_name' => __('last name'),
            'email' => __('email address'),
            'phone' => __('phone number'),
            'country' => __('country'),
            'inquiry_type' => __('inquiry type'),
            'subject' => __('subject'),
            'message' => __('message'),
        ]);

        if ($this->looksLikeSpam($request)) {
            return redirect()
                ->route('website.contact.index')
                ->with('success', __('Your message has been sent successfully. We will contact you shortly.'));
        }

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $message = $this->buildMessage($validated, $request);

        $data = [
            'inquiry_type' => 'contact',
            'full_name' => $fullName,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'country_name' => $validated['country'] ?? null,
            'subject' => $validated['subject'],
            'source' => $request->fullUrl(),
            'message' => $message,
            'status' => 'new',
        ];

        if (Schema::hasTable('inquiries')) {
            $columns = Schema::getColumnListing('inquiries');
            $data = array_intersect_key(array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), array_flip($columns));
        }

        if (!empty($data)) {
            DB::table('inquiries')->insert($data);
        } else {
            Inquiry::create([
                'inquiry_type' => 'contact',
                'full_name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'country_name' => $validated['country'] ?? null,
                'message' => $message,
                'status' => 'new',
            ]);
        }

        return redirect()
            ->route('website.contact.index')
            ->with('success', __('Your message has been sent successfully. We will contact you shortly.'));
    }

    private function countryOptions(): array
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->get()
            ->map(fn(Country $country) => $country->display_name)
            ->filter()
            ->values();

        if ($countries->isNotEmpty()) {
            return $countries->all();
        }

        return [
            __('Egypt'),
            __('United States'),
            __('United Kingdom'),
            __('Canada'),
            __('Australia'),
            __('Germany'),
            __('France'),
            __('Italy'),
            __('Spain'),
            __('Saudi Arabia'),
            __('United Arab Emirates'),
        ];
    }

    private function buildMessage(array $validated, Request $request): string
    {
        $lines = [
            __('Contact page enquiry'),
            __('Name:') . ' ' . trim($validated['first_name'] . ' ' . $validated['last_name']),
            __('Email:') . ' ' . $validated['email'],
            __('Phone:') . ' ' . ($validated['phone'] ?? '-'),
            __('Country:') . ' ' . ($validated['country'] ?? '-'),
            __('Inquiry type:') . ' ' . $this->inquiryTypeLabel($validated['inquiry_type'] ?? null),
            __('Subject:') . ' ' . $validated['subject'],
            __('Source:') . ' ' . $request->fullUrl(),
            __('Message:'),
            trim($validated['message']),
        ];

        return implode(PHP_EOL, $lines);
    }

    private function inquiryTypeLabel(?string $value): string
    {
        return match ($value) {
            'booking' => __('New Booking'),
            'existing' => __('Existing Booking'),
            'customization' => __('Trip Customization'),
            'complaint' => __('Complaint'),
            'other' => __('Other'),
            default => __('General Information'),
        };
    }

    private function looksLikeSpam(Request $request): bool
    {
        foreach (['website', 'url', 'company_name', 'subject_line'] as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }

        if ($request->filled('form_start_time')) {
            $startedAt = (int) $request->input('form_start_time');
            if ($startedAt > 0 && (time() - $startedAt) < 3) {
                return true;
            }
        }

        return false;
    }
}
