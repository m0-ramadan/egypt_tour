<?php

namespace App\Http\Controllers\Website;

use App\Models\Inquiry;
use App\Models\TailorMadeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TailorMadeController extends BaseWebsiteController
{
    public function index(): View
    {
        return view('website.pages.tailor_made', [
            'heroFeatures' => [
                [
                    'icon' => 'la la-magic',
                    'title' => 'Personalized Itinerary',
                    'description' => 'Custom-designed journeys based on your preferences',
                ],
                [
                    'icon' => 'la la-users',
                    'title' => 'Expert Team',
                    'description' => 'Professional travel specialists with local expertise',
                ],
                [
                    'icon' => 'la la-shield-alt',
                    'title' => '24/7 Support',
                    'description' => 'Round-the-clock assistance throughout your journey',
                ],
                [
                    'icon' => 'la la-award',
                    'title' => 'Award Winning',
                    'description' => 'TripAdvisor Excellence',
                ],
            ],
            'sidebarFeatures' => [
                ['icon' => 'la la-user-tie', 'label' => 'Personal Travel Consultant'],
                ['icon' => 'la la-clock', 'label' => '24/7 Travel Support'],
                ['icon' => 'la la-shield-alt', 'label' => 'Free Consultation'],
                ['icon' => 'la la-magic', 'label' => 'Completely Customizable'],
                ['icon' => 'la la-award', 'label' => 'Best Price Guarantee'],
                ['icon' => 'la la-heart', 'label' => 'Handpicked Experiences'],
            ],
            'accommodationOptions' => $this->accommodationOptions(),
            'budgetMinOptions' => [
                '500' => '$500',
                '1000' => '$1,000',
                '1500' => '$1,500',
                '2000' => '$2,000',
                '3000' => '$3,000',
                '5000' => '$5,000+',
            ],
            'budgetMaxOptions' => [
                '1000' => '$1,000',
                '2000' => '$2,000',
                '3000' => '$3,000',
                '5000' => '$5,000',
                '10000' => '$10,000',
                'unlimited' => 'No Limit',
            ],
            'occasionOptions' => $this->occasionOptions(),
            'interestOptions' => $this->interestOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'days' => ['nullable', 'integer', 'min:1', 'max:60'],
            'acommodation' => ['nullable', 'string', 'max:50'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'infants' => ['nullable', 'integer', 'min:0', 'max:20'],
            'budget_min' => ['nullable', 'string', 'max:50'],
            'budget_max' => ['nullable', 'string', 'max:50'],
            'occasion' => ['nullable', 'string', 'max:100'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string', 'max:100'],
            'dietary' => ['nullable', 'string', 'max:1000'],
            'mobility' => ['nullable', 'string', 'max:1000'],
            'comment' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'subject_line' => ['nullable', 'string', 'max:255'],
            'form_start_time' => ['nullable', 'integer'],
        ]);

        if ($this->looksLikeSpam($request)) {
            return back()->with('success', __('Your travel request has been sent successfully. We will contact you shortly.'));
        }

        $summary = $this->buildInquiryMessage($validated);

        $inquiry = Inquiry::create([
            'inquiry_type' => 'tailor_made',
            'full_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'country_name' => $validated['nationality'] ?? null,
            'travel_date' => $validated['start_date'] ?? null,
            'budget' => $this->extractBudgetValue($validated['budget_max'] ?? null)
                ?? $this->extractBudgetValue($validated['budget_min'] ?? null),
            'adults' => (int) $validated['adults'],
            'children' => (int) ($validated['children'] ?? 0),
            'source' => $request->fullUrl(),
            'message' => $summary,
            'status' => 'new',
        ]);

        TailorMadeRequest::create([
            'inquiry_id' => $inquiry->id,
            'full_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'country_of_residence' => $validated['nationality'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'trip_duration' => $validated['days'] ?? null,
            'accommodation_preference' => $validated['acommodation'] ?? null,
            'adults' => (int) $validated['adults'],
            'children' => (int) ($validated['children'] ?? 0),
            'infants' => (int) ($validated['infants'] ?? 0),
            'budget_min' => $this->extractBudgetValue($validated['budget_min'] ?? null),
            'budget_max' => $validated['budget_max'] ?? null,
            'occasion' => $validated['occasion'] ?? null,
            'interests' => array_values($validated['interests'] ?? []),
            'dietary_requirements' => $validated['dietary'] ?? null,
            'mobility_requirements' => $validated['mobility'] ?? null,
            'special_requests' => $validated['comment'],
            'source' => $request->fullUrl(),
            'status' => 'new',
        ]);

        return redirect()
            ->route('website.tailor_made.index')
            ->with('success', __('Your travel request has been sent successfully. We will contact you shortly.'));
    }

    private function buildInquiryMessage(array $validated): string
    {
        $accommodation = $this->accommodationOptions()[$validated['acommodation'] ?? ''] ?? null;
        $occasion = $this->occasionOptions()[$validated['occasion'] ?? ''] ?? null;
        $interestOptions = $this->interestOptions();
        $interestLabels = collect($validated['interests'] ?? [])
            ->map(fn (string $key) => $interestOptions[$key] ?? $key)
            ->filter()
            ->implode(', ');

        $lines = [
            'Tailor-made request',
            'Name: ' . $validated['name'],
            'Email: ' . $validated['email'],
            'Phone: ' . ($validated['phone'] ?? '-'),
            'Country of residence: ' . ($validated['nationality'] ?? '-'),
            'Travel dates: ' . $this->formatTravelDates($validated['start_date'] ?? null, $validated['end_date'] ?? null),
            'Trip duration: ' . (($validated['days'] ?? null) ? $validated['days'] . ' days' : '-'),
            'Accommodation: ' . ($accommodation ?? '-'),
            'Travelers: ' . (int) $validated['adults'] . ' adults, ' . (int) ($validated['children'] ?? 0) . ' children, ' . (int) ($validated['infants'] ?? 0) . ' infants',
            'Budget range: ' . $this->formatBudgetRange($validated['budget_min'] ?? null, $validated['budget_max'] ?? null),
            'Occasion: ' . ($occasion ?? '-'),
            'Interests: ' . ($interestLabels !== '' ? $interestLabels : '-'),
            'Dietary requirements: ' . ($validated['dietary'] ?? '-'),
            'Mobility requirements: ' . ($validated['mobility'] ?? '-'),
            'Additional comments:',
            trim($validated['comment']),
        ];

        return implode(PHP_EOL, $lines);
    }

    private function formatTravelDates(?string $startDate, ?string $endDate): string
    {
        if (!$startDate && !$endDate) {
            return '-';
        }

        $start = $startDate ? Carbon::parse($startDate)->format('Y-m-d') : '-';
        $end = $endDate ? Carbon::parse($endDate)->format('Y-m-d') : '-';

        return $start . ' to ' . $end;
    }

    private function formatBudgetRange(?string $budgetMin, ?string $budgetMax): string
    {
        if (!$budgetMin && !$budgetMax) {
            return '-';
        }

        return trim(($budgetMin ?: '-') . ' - ' . ($budgetMax ?: '-'));
    }

    private function extractBudgetValue(?string $value): ?float
    {
        if (!$value || $value === 'unlimited') {
            return null;
        }

        return (float) preg_replace('/[^0-9.]/', '', $value);
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

    private function accommodationOptions(): array
    {
        return [
            'luxury' => 'Luxury Hotels (5 Star)',
            'premium' => 'Premium Hotels (4 Star)',
            'standard' => 'Standard Hotels (3 Star)',
            'mixed' => 'Mix of Categories',
        ];
    }

    private function occasionOptions(): array
    {
        return [
            'honeymoon' => 'Honeymoon',
            'anniversary' => 'Anniversary',
            'birthday' => 'Birthday Celebration',
            'family-reunion' => 'Family Reunion',
            'retirement' => 'Retirement Trip',
            'other' => 'Other Celebration',
        ];
    }

    private function interestOptions(): array
    {
        return [
            'ancient-sites' => 'Ancient Sites & Temples',
            'nile-cruise' => 'Nile River Cruise',
            'museums' => 'Museums & Culture',
            'desert-safari' => 'Desert Safari',
            'red-sea' => 'Red Sea & Beaches',
            'local-cuisine' => 'Local Cuisine',
            'photography' => 'Photography Tours',
            'adventure' => 'Adventure Activities',
        ];
    }
}
