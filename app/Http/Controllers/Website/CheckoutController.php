<?php

namespace App\Http\Controllers\Website;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\PackageBookingService;
use App\Services\Payments\PaymobService;
use App\Services\Payments\PayPalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends BaseWebsiteController
{
    public function show(string $slug, PackageBookingService $bookingService): View
    {
        $package = $bookingService->loadForCheckout($slug);

        abort_unless($bookingService->hasBookablePrice($package), 404);

        $hasAccommodations = $package->tourPackageAccommodations->where('is_active', true)->isNotEmpty()
            || ! empty($bookingService->getTravelPackageMatrix($package)['accommodations']);
        $isTravelPackage = $package->package_type === 'travel_package'
            && $hasAccommodations
            && request('pricing_option') !== 'category'
            && (request('pricing_option') === 'travel_package' || request()->filled('accommodation') || request()->has('room_1_adults') || request()->has('room_1_accommodation') || $package->prices->isEmpty());
        $travelPackageQuote = null;
        $roomsData = [];
        $accommodation = request('accommodation');
        $roomsCount = max(1, (int) request('rooms', 1));

        for ($i = 1; $i <= $roomsCount; $i++) {
            if (request()->has("room_{$i}_adults") || request()->has("room_{$i}_children") || request()->has("room_{$i}_accommodation")) {
                $roomsData[] = [
                    'accommodation' => request("room_{$i}_accommodation") ?: $accommodation,
                    'adults' => max(1, (int) request("room_{$i}_adults", 1)),
                    'children' => max(0, (int) request("room_{$i}_children", 0)),
                ];
            }
        }

        if (empty($roomsData) && $isTravelPackage) {
            $adultsPerRoom = max(1, (int) request('adults', 2));
            $childrenPerRoom = max(0, (int) request('children', 0));
            for ($i = 1; $i <= $roomsCount; $i++) {
                $roomsData[] = [
                    'accommodation' => request("room_{$i}_accommodation") ?: $accommodation,
                    'adults' => $i === 1 ? $adultsPerRoom : 1,
                    'children' => $i === 1 ? $childrenPerRoom : 0,
                ];
            }
        }

        if ($isTravelPackage && request()->filled('travel_date')) {
            try {
                $travelPackageQuote = $bookingService->quoteTravelPackage(
                    $package,
                    $accommodation,
                    request('travel_date'),
                    $roomsData
                );
            } catch (\Exception $e) {
                // Ignore initial validation if date/accommodation incomplete
            }
        }

        $countries = \App\Support\CountryList::all();

        return view('website.pages.checkout.show', [
            'package' => $package,
            'title' => $package->display_title,
            'heroImage' => $package->image_url,
            'durationText' => $this->packageDuration($package),
            'pricingOptions' => $bookingService->pricingOptions($package),
            'paymentMethods' => $bookingService->paymentMethods($package),
            'isTravelPackage' => $isTravelPackage,
            'travelPackageQuote' => $travelPackageQuote,
            'roomsData' => $roomsData,
            'accommodation' => $accommodation,
            'countries' => $countries,
        ]);
    }

    public function store(
        Request $request,
        string $slug,
        PackageBookingService $bookingService,
        PaymobService $paymob,
        PayPalService $paypal,
    ): RedirectResponse {
        $package = $bookingService->loadForCheckout($slug);
        abort_unless($bookingService->hasBookablePrice($package), 404);

        if (! $request->has('travelers') && $request->filled('lead_first_name')) {
            $travelers = [
                [
                    'title' => $request->input('lead_title', 'Mr'),
                    'first_name' => $request->input('lead_first_name'),
                    'last_name' => $request->input('lead_last_name'),
                ]
            ];
            $totalPax = (int) $request->input('adults', 1) + (int) $request->input('children', 0) + (int) $request->input('infants', 0);
            for ($t = 2; $t <= $totalPax; $t++) {
                $travelers[] = [
                    'title' => $request->input("traveler_{$t}_title", 'Mr'),
                    'first_name' => $request->input("traveler_{$t}_first_name") ?: "Guest {$t}",
                    'last_name' => $request->input("traveler_{$t}_last_name") ?: $request->input('lead_last_name'),
                ];
            }
            $request->merge(['travelers' => $travelers]);
        }

        if ($request->filled('country') && ! $request->filled('nationality')) {
            $request->merge(['nationality' => $request->input('country')]);
        }

        $availableMethods = $bookingService->paymentMethods($package);
        $providers = $availableMethods->pluck('provider')->all();
        $data = $request->validate([
            'pricing_option' => ['required', 'string', 'max:100'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],
            'rooms' => ['required', 'integer', 'min:1', 'max:20'],
            'adults' => ['required', 'integer', 'min:1', 'max:40'],
            'children' => ['required', 'integer', 'min:0', 'max:40'],
            'infants' => ['required', 'integer', 'min:0', 'max:20'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'special_requests' => ['nullable', 'string', 'max:5000'],
            'travelers' => ['required', 'array', 'min:1', 'max:100'],
            'travelers.*.title' => ['required', Rule::in(['Mr', 'Mrs', 'Ms', 'Miss', 'Dr'])],
            'travelers.*.first_name' => ['required', 'string', 'max:120'],
            'travelers.*.last_name' => ['required', 'string', 'max:120'],
            'payment_method' => ['required', Rule::in($providers)],
            'terms' => ['accepted'],
        ]);

        $travelerCount = (int) $data['adults'] + (int) $data['children'] + (int) $data['infants'];
        if (count($data['travelers']) !== $travelerCount) {
            throw ValidationException::withMessages([
                'travelers' => __('Please enter the details of every traveler.'),
            ]);
        }

        $roomsData = [];
        $roomsCount = max(1, (int) $data['rooms']);
        for ($i = 1; $i <= $roomsCount; $i++) {
            if ($request->has("room_{$i}_adults") || $request->has("room_{$i}_children") || $request->has("room_{$i}_accommodation")) {
                $roomsData[] = [
                    'accommodation' => $request->input("room_{$i}_accommodation") ?: $request->input('accommodation'),
                    'adults' => max(1, (int) $request->input("room_{$i}_adults", 1)),
                    'children' => max(0, (int) $request->input("room_{$i}_children", 0)),
                ];
            }
        }

        $quote = $bookingService->quote(
            $package,
            $data['pricing_option'],
            $data['travel_date'],
            (int) $data['adults'],
            (int) $data['children'],
            (int) $data['infants'],
            (int) $data['rooms'],
            $roomsData,
            $request->input('accommodation')
        );

        $selectedMethod = $availableMethods->firstWhere('provider', $data['payment_method']);
        if (! $selectedMethod) {
            throw ValidationException::withMessages(['payment_method' => __('The selected payment method is unavailable.')]);
        }

        $booking = DB::transaction(function () use ($data, $package, $quote): Booking {
            $lead = $data['travelers'][0];
            $client = Client::query()->firstOrNew(['email' => strtolower($data['email'])]);
            $client->fill([
                'first_name' => $lead['first_name'],
                'last_name' => $lead['last_name'],
                'phone' => $data['phone'],
                'nationality' => $data['nationality'] ?? null,
                'is_active' => true,
                'last_activity' => now(),
            ])->save();

            do {
                $bookingNumber = 'WEB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
            } while (Booking::query()->where('booking_number', $bookingNumber)->exists());

            $booking = Booking::create([
                'client_id' => $client->id,
                'package_id' => $package->id,
                'booking_number' => $bookingNumber,
                'status' => 'pending',
                'total_amount' => $quote['total'],
                'paid_amount' => 0,
                'currency_code' => $quote['currency_code'],
                'payment_status' => 'unpaid',
                'booking_date' => today(),
                'travel_date' => $data['travel_date'],
                'adults' => $data['adults'],
                'children' => $data['children'],
                'infants' => $data['infants'],
                'pickup_location' => $data['pickup_location'] ?? null,
                'special_requests' => $data['special_requests'] ?? null,
                'checkout_details' => [
                    'pricing_option' => $quote['id'],
                    'option_label' => $quote['label'],
                    'rooms' => $quote['rooms'],
                    'room_breakdown' => $quote['room_breakdown'] ?? null,
                    'deposit_amount' => $quote['deposit_amount'] ?? null,
                    'remaining_balance' => $quote['remaining_balance'] ?? null,
                    'payment_provider' => $data['payment_method'],
                ],
            ]);

            $types = array_merge(
                array_fill(0, (int) $data['adults'], 'adult'),
                array_fill(0, (int) $data['children'], 'child'),
                array_fill(0, (int) $data['infants'], 'infant'),
            );
            foreach ($data['travelers'] as $index => $traveler) {
                $booking->travelers()->create([
                    'traveler_type' => $types[$index] ?? 'adult',
                    'title' => $traveler['title'],
                    'first_name' => $traveler['first_name'],
                    'last_name' => $traveler['last_name'],
                    'sort_order' => $index,
                ]);
            }

            $booking->items()->create([
                'pricing_source' => $quote['source'] ?? 'tour_package',
                'source_id' => $quote['source_id'] ?? null,
                'cabin_id' => $quote['cabin_id'] ?? null,
                'option_label' => $quote['label'] ?? '',
                'occupancy_type' => $quote['occupancy_type'] ?? null,
                'unit_price' => $quote['amount'] ?? $quote['total'],
                'quantity' => $quote['quantity'] ?? 1,
                'room_count' => $quote['rooms'] ?? 1,
                'total_amount' => $quote['total'],
                'meta' => [
                    'description' => $quote['description'] ?? null,
                    'price_unit' => $quote['price_unit'] ?? null,
                    'valid_from' => $quote['valid_from'] ?? null,
                    'valid_to' => $quote['valid_to'] ?? null,
                    'room_breakdown' => $quote['room_breakdown'] ?? null,
                    'deposit_amount' => $quote['deposit_amount'] ?? null,
                    'remaining_balance' => $quote['remaining_balance'] ?? null,
                ],
            ]);

            return $booking->fresh(['client', 'package', 'items', 'travelers']);
        }, 3);

        try {
            /** @var PaymentMethod $method */
            $method = $selectedMethod['model'];
            $payment = $data['payment_method'] === 'paymob'
                ? $paymob->createCheckout($booking)
                : $paypal->createCheckout($booking, $method);

            return redirect()->away((string) $payment->checkout_url);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'payment_method' => __('We could not start the payment. Please try again or contact us.'),
            ]);
        }
    }

    public function capturePayPal(Request $request, PayPalService $paypal): RedirectResponse
    {
        $request->validate([
            'reference' => ['required', 'string'],
            'token' => ['required', 'string'],
        ]);
        $payment = Payment::query()
            ->where('transaction_reference', $request->string('reference'))
            ->whereHas('paymentMethod', fn($query) => $query->where(function ($methodQuery) {
                $methodQuery->where('provider', 'paypal')->orWhere('code', 'paypal');
            }))
            ->firstOrFail();

        try {
            $paypal->capture($payment, (string) $request->string('token'));
            $result = 'success';
        } catch (\Throwable $exception) {
            report($exception);
            $payment->refresh();
            if ($payment->status === Payment::STATUS_PENDING) {
                $payment->forceFill([
                    'status' => Payment::STATUS_FAILED,
                    'failure_reason' => $exception->getMessage(),
                ])->save();
            }
            $result = 'failed';
        }

        return redirect(URL::temporarySignedRoute('website.checkout.status', now()->addHours(24), [
            'paymentReference' => $payment->transaction_reference,
            'result' => $result,
        ]));
    }

    public function status(string $paymentReference): View
    {
        $payment = Payment::query()
            ->with(['booking.package', 'paymentMethod'])
            ->where('transaction_reference', $paymentReference)
            ->firstOrFail();

        return view('website.pages.checkout.status', compact('payment'));
    }
}
