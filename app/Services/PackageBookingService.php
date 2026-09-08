<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PaymentMethod;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PackageBookingService
{
    public function loadForCheckout(string $slug): Package
    {
        return Package::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'currency',
                'prices.currency',
                'nileCruiseDetail',
                'nileCruiseCabins',
                'nileCruiseDurations.currency',
                'nileCruiseDurations.seasonPrices.currency',
                'nileCruiseDurations.seasonPrices.items.cabin',
                'tourPackageAccommodations.hotels',
                'tourPackageAccommodations.seasons.currency',
                'tourPackageAccommodations.seasons.items',
            ])
            ->firstOrFail();
    }

    public function hasBookablePrice(Package $package): bool
    {
        return $package->package_type !== 'nile_cruise' && $this->pricingOptions($package)->isNotEmpty();
    }

    public function pricingOptions(Package $package, CarbonInterface|string|null $travelDate = null): Collection
    {
        $date = $travelDate ? Carbon::parse($travelDate)->startOfDay() : null;
        $options = collect();

        if ($package->package_type === 'nile_cruise') {
            foreach ($package->nileCruiseDurations->where('is_active', true) as $duration) {
                foreach ($duration->seasonPrices->where('is_active', true) as $season) {
                    if (! $this->dateMatches($date, $season->date_from, $season->date_to)) {
                        continue;
                    }

                    foreach ($season->items as $item) {
                        if ((float) $item->price <= 0) {
                            continue;
                        }

                        $cabin = $item->cabin;
                        $occupancy = strtolower((string) ($item->occupancy_type ?: 'custom'));
                        $label = $this->translated($cabin?->name)
                            ?: $this->translated($item->label)
                            ?: __(ucfirst($occupancy));
                        $currency = $season->currency ?: $duration->currency ?: $package->currency;

                        $options->push([
                            'id' => 'nile:' . $item->id,
                            'source' => 'nile_cruise',
                            'source_id' => $item->id,
                            'label' => $label,
                            'description' => collect([
                                $duration->title,
                                $season->display_season_name,
                                __(ucfirst($occupancy)),
                                $cabin?->bed_type,
                            ])->filter()->implode(' · '),
                            'amount' => (float) $item->price,
                            'price_unit' => 'per_person',
                            'currency_code' => strtoupper((string) ($currency?->code ?: 'USD')),
                            'currency_symbol' => (string) ($currency?->symbol ?: '$'),
                            'occupancy_type' => $occupancy,
                            'cabin_id' => $cabin?->id,
                            'available_rooms' => $cabin?->quantity,
                            'max_adults_per_room' => $this->occupancyAdults($occupancy, $cabin?->max_adults),
                            'max_children_per_room' => (int) ($cabin?->max_children ?? 0),
                            'valid_from' => $season->date_from?->toDateString(),
                            'valid_to' => $season->date_to?->toDateString(),
                        ]);
                    }
                }
            }

            // Prefer the cruise-specific matrix when it exists. Older cruises
            // can still use their generic package/base price below.
            if ($options->isNotEmpty()) {
                return $options->values();
            }
        }

        foreach ($package->tourPackageAccommodations->where('is_active', true) as $accommodation) {
            foreach ($accommodation->seasons->where('is_active', true) as $season) {
                if (! $this->dateMatches($date, $season->date_from, $season->date_to)) {
                    continue;
                }

                foreach ($season->items->where('is_active', true) as $item) {
                    if ((float) $item->price <= 0) {
                        continue;
                    }

                    $currency = $season->currency ?: $package->currency;
                    $occupancy = strtolower((string) ($item->occupancy_type ?: 'custom'));
                    $options->push([
                        'id' => 'tour:' . $item->id,
                        'source' => 'tour_package',
                        'source_id' => $item->id,
                        'label' => $this->translated($item->label) ?: $accommodation->name,
                        'description' => collect([$accommodation->name, $season->display_season_name, ucfirst($occupancy)])->filter()->implode(' · '),
                        'amount' => (float) $item->price,
                        'price_unit' => in_array($item->price_unit, ['per_person', 'per_room', 'per_booking'], true)
                            ? $item->price_unit
                            : 'per_person',
                        'currency_code' => strtoupper((string) ($currency?->code ?: 'USD')),
                        'currency_symbol' => (string) ($currency?->symbol ?: '$'),
                        'occupancy_type' => $occupancy,
                        'cabin_id' => null,
                        'available_rooms' => null,
                        'max_adults_per_room' => $this->occupancyAdults($occupancy),
                        'max_children_per_room' => 2,
                        'valid_from' => $season->date_from?->toDateString(),
                        'valid_to' => $season->date_to?->toDateString(),
                    ]);
                }
            }
        }

        foreach ($package->prices as $price) {
            if ((float) $price->amount <= 0 || ! $this->dateMatches($date, $price->valid_from, $price->valid_to)) {
                continue;
            }

            $currency = $price->currency ?: $package->currency;
            $priceUnit = $price->price_type === 'per_group' ? 'per_booking' : 'per_person';
            $options->push([
                'id' => 'price:' . $price->id,
                'source' => 'package_price',
                'source_id' => $price->id,
                'label' => $price->display_label ?: __('Package Price'),
                'description' => collect([$price->display_season_name, $price->room_type ? __(ucwords(str_replace('_', ' ', $price->room_type))) : null])->filter()->implode(' · '),
                'amount' => (float) $price->amount,
                'price_unit' => $priceUnit,
                'currency_code' => strtoupper((string) ($currency?->code ?: 'USD')),
                'currency_symbol' => (string) ($currency?->symbol ?: '$'),
                'occupancy_type' => $price->room_type,
                'cabin_id' => null,
                'available_rooms' => null,
                'max_adults_per_room' => $this->occupancyAdults((string) $price->room_type),
                'max_children_per_room' => 2,
                'pax_min' => $price->pax_min ?: $price->group_size_min,
                'pax_max' => $price->pax_max ?: $price->group_size_max,
                'valid_from' => $price->valid_from?->toDateString(),
                'valid_to' => $price->valid_to?->toDateString(),
            ]);
        }

        $rawTiers = $package->getRawOriginal('group_pricing_tiers');
        $hasExplicitTiers = is_array($rawTiers)
            ? $rawTiers !== []
            : trim((string) $rawTiers) !== '' && trim((string) $rawTiers) !== '[]';
        $hasExplicitTierColumns = collect([
            $package->price_1_person,
            $package->price_2_persons,
            $package->price_3_persons,
            $package->price_4_persons,
            $package->price_5_persons,
            $package->price_6_plus_persons,
        ])->contains(fn($value) => $value !== null && (float) $value > 0);

        if ($hasExplicitTiers || $hasExplicitTierColumns) {
            foreach ($package->group_pricing_tiers as $index => $tier) {
                if (! is_array($tier) || (float) ($tier['price_per_person'] ?? 0) <= 0) {
                    continue;
                }

                $options->push([
                    'id' => 'group:' . $index,
                    'source' => 'group_tier',
                    'source_id' => null,
                    'label' => (string) ($tier['title'] ?? __('Group Price')),
                    'description' => (string) ($tier['persons_label'] ?? ''),
                    'amount' => (float) $tier['price_per_person'],
                    'price_unit' => 'per_adult',
                    'currency_code' => strtoupper((string) ($package->currency?->code ?: 'USD')),
                    'currency_symbol' => (string) ($package->currency?->symbol ?: '$'),
                    'occupancy_type' => null,
                    'cabin_id' => null,
                    'available_rooms' => null,
                    'max_adults_per_room' => null,
                    'max_children_per_room' => null,
                    'pax_min' => (int) ($tier['min'] ?? $tier['persons_count'] ?? 1),
                    'pax_max' => isset($tier['max']) ? (int) $tier['max'] : null,
                    'valid_from' => null,
                    'valid_to' => null,
                ]);
            }
        }

        if ((float) ($package->adult_price ?? 0) > 0) {
            $options->push([
                'id' => 'category',
                'source' => 'category_price',
                'source_id' => null,
                'label' => __('Adult / Child Pricing'),
                'description' => __('Price calculated by traveler age category'),
                'amount' => (float) $package->adult_price,
                'price_unit' => 'category',
                'adult_price' => (float) $package->adult_price,
                'child_price' => (float) ($package->child_price ?? 0),
                'infant_price' => (float) ($package->infant_price ?? 0),
                'currency_code' => strtoupper((string) ($package->currency?->code ?: 'USD')),
                'currency_symbol' => (string) ($package->currency?->symbol ?: '$'),
                'occupancy_type' => null,
                'cabin_id' => null,
                'available_rooms' => null,
                'max_adults_per_room' => null,
                'max_children_per_room' => null,
                'valid_from' => null,
                'valid_to' => null,
            ]);
        }

        $baseAmount = (float) ($package->offer_price ?: ($package->price_from ?: $package->start_from_price));
        if ($baseAmount > 0 && $options->isEmpty()) {
            $options->push([
                'id' => 'base',
                'source' => 'package',
                'source_id' => $package->id,
                'label' => __('Standard Package'),
                'description' => __('Price per person'),
                'amount' => $baseAmount,
                'price_unit' => 'per_person',
                'currency_code' => strtoupper((string) ($package->currency?->code ?: 'USD')),
                'currency_symbol' => (string) ($package->currency?->symbol ?: '$'),
                'occupancy_type' => null,
                'cabin_id' => null,
                'available_rooms' => null,
                'max_adults_per_room' => null,
                'max_children_per_room' => null,
                'valid_from' => null,
                'valid_to' => null,
            ]);
        }

        return $options->unique('id')->values();
    }

    public function quote(
        Package $package,
        string $optionId,
        CarbonInterface|string $travelDate,
        int $adults,
        int $children,
        int $infants,
        int $rooms,
        array $roomsData = [],
        ?string $accommodation = null,
    ): array {
        $this->validateOperatingDay($package, $travelDate);

        if (
            \Illuminate\Support\Str::startsWith($optionId, 'travel_package')
            || ($package->package_type === 'travel_package' && ! empty($roomsData) && ($accommodation || $package->tourPackageAccommodations->isNotEmpty()))
        ) {
            $accId = $accommodation;
            if (! $accId && \Illuminate\Support\Str::startsWith($optionId, 'travel_package:')) {
                $parts = explode(':', $optionId);
                $accId = $parts[1] ?? null;
            }
            if (! $accId && $package->tourPackageAccommodations->isNotEmpty()) {
                $accId = $package->tourPackageAccommodations->first()->id;
            }
            return $this->quoteTravelPackage($package, $accId, $travelDate, $roomsData);
        }

        $option = $this->pricingOptions($package, $travelDate)->firstWhere('id', $optionId);

        if (! $option) {
            throw ValidationException::withMessages([
                'pricing_option' => __('The selected price is not available for this travel date.'),
            ]);
        }

        $travellers = $adults + $children + $infants;
        $payingTravellers = $adults + $children;
        $min = (int) ($option['pax_min'] ?? 0);
        $max = (int) ($option['pax_max'] ?? 0);

        if (($min > 0 && $travellers < $min) || ($max > 0 && $travellers > $max)) {
            throw ValidationException::withMessages([
                'pricing_option' => __('This price does not support the selected number of travelers.'),
            ]);
        }

        if ($option['cabin_id'] || $option['occupancy_type']) {
            $adultCapacity = (int) ($option['max_adults_per_room'] ?? 0) * $rooms;
            $childCapacity = (int) ($option['max_children_per_room'] ?? 0) * $rooms;

            if ($adultCapacity > 0 && $adults > $adultCapacity) {
                throw ValidationException::withMessages(['rooms' => __('Please add more rooms for the selected number of adults.')]);
            }
            if ($children > 0 && $childCapacity === 0) {
                throw ValidationException::withMessages(['pricing_option' => __('The selected cabin does not accept children.')]);
            }
            if ($childCapacity > 0 && $children > $childCapacity) {
                throw ValidationException::withMessages(['rooms' => __('Please add more rooms for the selected number of children.')]);
            }

            if ($option['cabin_id']) {
                $holdMinutes = max(1, (int) config('services.paymob.pending_hold_minutes', 30));
                $bookedRooms = \App\Models\BookingItem::query()
                    ->where('cabin_id', $option['cabin_id'])
                    ->whereHas('booking', fn($query) => $query
                        ->whereDate('travel_date', Carbon::parse($travelDate)->toDateString())
                        ->where(function ($statusQuery) use ($holdMinutes) {
                            $statusQuery->whereIn('status', ['confirmed', 'paid'])
                                ->orWhere(function ($pendingQuery) use ($holdMinutes) {
                                    $pendingQuery->where('status', 'pending')
                                        ->where('created_at', '>=', now()->subMinutes($holdMinutes));
                                });
                        }))
                    ->sum('room_count');
                $available = $option['available_rooms'];

                if ($available !== null && $rooms > max(0, (int) $available - (int) $bookedRooms)) {
                    throw ValidationException::withMessages(['rooms' => __('The requested number of cabins is no longer available.')]);
                }
            }
        }

        $total = match ($option['price_unit']) {
            'per_booking' => (float) $option['amount'],
            'per_room' => (float) $option['amount'] * $rooms,
            'per_adult' => (float) $option['amount'] * $adults,
            'category' => ((float) $option['adult_price'] * $adults)
                + ((float) $option['child_price'] * $children)
                + ((float) $option['infant_price'] * $infants),
            default => (float) $option['amount'] * max(1, $payingTravellers),
        };

        if ($total <= 0) {
            throw ValidationException::withMessages(['pricing_option' => __('This option cannot be booked online.')]);
        }

        return $option + [
            'rooms' => $rooms,
            'travellers' => $travellers,
            'quantity' => $option['price_unit'] === 'per_room' ? $rooms : max(1, $payingTravellers),
            'total' => round($total, 2),
        ];
    }

    /**
     * Day tours may only be booked on the weekdays selected in the package
     * editor. Empty schedules and legacy "daily" values mean every day.
     */
    public function validateOperatingDay(Package $package, CarbonInterface|string $travelDate): void
    {
        if ($package->package_type !== 'day_tour') {
            return;
        }

        $operatingDays = collect((array) $package->operating_days)
            ->map(fn ($day) => strtolower(trim((string) $day)))
            ->filter()
            ->values();

        if (
            $operatingDays->isEmpty()
            || $operatingDays->contains(fn ($day) => in_array($day, ['daily', 'everyday', 'every day', 'all'], true))
        ) {
            return;
        }

        $selectedDay = strtolower(Carbon::parse($travelDate)->englishDayOfWeek);
        $isAvailable = $operatingDays->contains(function ($day) use ($selectedDay) {
            return $day === $selectedDay || substr($day, 0, 3) === substr($selectedDay, 0, 3);
        });

        if (! $isAvailable) {
            throw ValidationException::withMessages([
                'travel_date' => __('This tour is not available on the selected date. Please choose an operating day.'),
            ]);
        }
    }

    public function paymentMethods(Package $package): Collection
    {
        $allowed = collect((array) ($package->allowed_payment_method_ids
            ?: $package->nileCruiseDetail?->allowed_payment_method_ids))
            ->map(fn($id) => (int) $id)
            ->filter();

        // Ensure configured payment gateways are marked active in DB if initially inactive
        if (
            (bool) config('services.paymob.enabled')
            && filled(config('services.paymob.secret_key'))
            && filled(config('services.paymob.public_key'))
            && filled(config('services.paymob.hmac_secret'))
            && (array) config('services.paymob.integration_ids') !== []
        ) {
            PaymentMethod::query()
                ->where(function ($q) {
                    $q->where('code', 'paymob')->orWhere('provider', 'paymob');
                })
                ->where('is_active', false)
                ->update(['is_active' => true]);
        }

        if (
            (bool) config('services.paypal.enabled')
            && filled(config('services.paypal.client_id'))
            && filled(config('services.paypal.secret'))
        ) {
            PaymentMethod::query()
                ->where(function ($q) {
                    $q->where('code', 'paypal')->orWhere('provider', 'paypal');
                })
                ->where('is_active', false)
                ->update(['is_active' => true]);
        }

        return PaymentMethod::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('code', ['paymob', 'paypal'])
                    ->orWhereIn('provider', ['paymob', 'paypal']);
            })
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->get()
            ->filter(function (PaymentMethod $method) use ($allowed) {
                if ($allowed->isNotEmpty() && ! $allowed->contains((int) $method->id)) {
                    return false;
                }

                $provider = strtolower((string) ($method->provider ?: $method->code));

                return match ($provider) {
                    'paymob' => (bool) config('services.paymob.enabled')
                        && filled(config('services.paymob.secret_key'))
                        && filled(config('services.paymob.public_key'))
                        && filled(config('services.paymob.hmac_secret'))
                        && (array) config('services.paymob.integration_ids') !== [],
                    'paypal' => (bool) config('services.paypal.enabled')
                        && filled(config('services.paypal.client_id'))
                        && filled(config('services.paypal.secret')),
                    default => false,
                };
            })
            ->map(function (PaymentMethod $method) {
                $provider = strtolower((string) ($method->provider ?: $method->code));
                // $configured = $this->providerConfigured($provider);

                return [
                    'model' => $method,
                    'provider' => $provider,
                    // 'available' => $method->is_active && $configured,
                    // 'is_active' => $method->is_active,
                    // 'is_configured' => $configured,
                    'name' => $provider === 'paymob' ? __('Visa / Mastercard') : __('PayPal'),
                    'description' => $provider === 'paymob'
                        ? __('Secure card payment powered by Paymob')
                        : __('Pay securely with your PayPal account'),
                    'image' => asset('website/images/payments/' . ($provider === 'paymob' ? 'visa-mastercard.svg' : 'paypal.svg')),
                ];
            })
            ->values();

        // return $includeUnavailable
        //     ? $methods
        //     : $methods->where('available', true)->values();
    }

    // private function providerConfigured(string $provider): bool
    // {
    //     return match ($provider) {
    //         'paymob' => (bool) config('services.paymob.enabled')
    //             && filled(config('services.paymob.secret_key'))
    //             && filled(config('services.paymob.public_key'))
    //             && filled(config('services.paymob.hmac_secret'))
    //             && (array) config('services.paymob.integration_ids') !== [],
    //         'paypal' => (bool) config('services.paypal.enabled')
    //             && filled(config('services.paypal.client_id'))
    //             && filled(config('services.paypal.secret')),
    //         default => false,
    //     };
    // }

    private function dateMatches(?CarbonInterface $date, mixed $from, mixed $to): bool
    {
        if (! $date) {
            return true;
        }

        return (! $from || $date->greaterThanOrEqualTo(Carbon::parse($from)->startOfDay()))
            && (! $to || $date->lessThanOrEqualTo(Carbon::parse($to)->endOfDay()));
    }

    private function occupancyAdults(string $occupancy, ?int $configured = null): int
    {
        if ($configured && $configured > 0) {
            return $configured;
        }

        return match (strtolower($occupancy)) {
            'single' => 1,
            'double', 'twin' => 2,
            'triple' => 3,
            'quad', 'quadruple' => 4,
            default => 2,
        };
    }

    private function translated(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return trim((string) ($value[app()->getLocale()] ?? $value['en'] ?? $value['ar'] ?? reset($value) ?? ''));
        }

        return trim((string) ($value ?? ''));
    }

    public function getTravelPackageMatrix(Package $package): array
    {
        $matrix = [];
        $accommodations = [];

        foreach ($package->tourPackageAccommodations->where('is_active', true) as $acc) {
            $accData = [
                'id' => $acc->id,
                'name' => $acc->name,
                'description' => $acc->description,
                'seasons' => [],
            ];

            $matrix[$acc->name] = [];
            $matrix[$acc->id] = [];

            foreach ($acc->seasons->where('is_active', true) as $season) {
                $seasonName = $season->display_season_name;
                $rates = [
                    'single' => 0.0,
                    'double' => 0.0,
                    'triple' => 0.0,
                ];

                foreach ($season->items->where('is_active', true) as $item) {
                    $occ = strtolower((string) $item->occupancy_type);
                    if (array_key_exists($occ, $rates)) {
                        $rates[$occ] = (float) $item->price;
                    }
                }

                $seasonKey = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($seasonName), ['may', 'summer']) ? 'summer' : 'winter';

                $accData['seasons'][] = [
                    'id' => $season->id,
                    'name' => $seasonName,
                    'key' => $seasonKey,
                    'date_from' => $season->date_from?->toDateString(),
                    'date_to' => $season->date_to?->toDateString(),
                    'rates' => $rates,
                ];

                $matrix[$acc->name][$seasonKey] = $rates;
                $matrix[$acc->id][$seasonKey] = $rates;
                $matrix[$acc->name][\Illuminate\Support\Str::slug($seasonName)] = $rates;
                $matrix[$acc->id][\Illuminate\Support\Str::slug($seasonName)] = $rates;
            }

            $accommodations[] = $accData;
        }

        // Fallback for packages that do not have explicit tourPackageAccommodations configured yet
        if (empty($accommodations)) {
            $base = (float) ($package->adult_price > 0 ? $package->adult_price : 150);
            $tiers = [
                'Standard' => ['mult' => 1.0, 'desc' => 'Standard 4-star hotels'],
                'Deluxe' => ['mult' => 1.3, 'desc' => '5-star luxury hotels'],
                'Luxury' => ['mult' => 1.7, 'desc' => '5-star ultra luxury & boutique hotels'],
            ];

            foreach ($tiers as $tierName => $tierInfo) {
                $tBase = round($base * $tierInfo['mult'], 2);
                $summerRates = [
                    'single' => round($tBase * 1.4, 2),
                    'double' => round($tBase, 2),
                    'triple' => round($tBase * 0.85, 2),
                ];
                $winterRates = [
                    'single' => round($tBase * 1.5, 2),
                    'double' => round($tBase * 1.15, 2),
                    'triple' => round($tBase * 0.95, 2),
                ];

                $matrix[$tierName] = [
                    'summer' => $summerRates,
                    'winter' => $winterRates,
                ];

                $accommodations[] = [
                    'id' => \Illuminate\Support\Str::slug($tierName),
                    'name' => $tierName,
                    'description' => $tierInfo['desc'],
                    'seasons' => [
                        [
                            'id' => 'summer_' . $tierName,
                            'name' => 'May to August',
                            'key' => 'summer',
                            'rates' => $summerRates,
                        ],
                        [
                            'id' => 'winter_' . $tierName,
                            'name' => 'September to April',
                            'key' => 'winter',
                            'rates' => $winterRates,
                        ],
                    ],
                ];
            }
        }

        return [
            'accommodations' => $accommodations,
            'matrix' => $matrix,
        ];
    }

    public function calculateRoomPrice(array $rates, int $adults, int $children): float
    {
        $single = (float) ($rates['single'] ?? 0);
        $double = (float) ($rates['double'] ?? 0);
        $triple = (float) ($rates['triple'] ?? 0);

        if ($adults === 1 && $children === 0) {
            return $single;
        }

        if ($adults === 1 && ($children === 1 || $children === 2)) {
            return $double * 2;
        }

        if ($adults === 2) {
            $base = $double * 2;
            $childRate = $double * 0.50;
            return $base + ($children * $childRate);
        }

        if ($adults === 3) {
            return $triple * 3;
        }

        if ($triple > 0) {
            return ($triple * $adults) + ($children * $triple * 0.50);
        }

        return ($double * $adults) + ($children * $double * 0.50);
    }

    public function quoteTravelPackage(
        Package $package,
        string|int|null $accommodationIdentifier,
        CarbonInterface|string $travelDate,
        array $roomsData
    ): array {
        $matrixData = $this->getTravelPackageMatrix($package);
        $accommodations = $matrixData['accommodations'] ?? [];
        $matrix = $matrixData['matrix'] ?? [];

        if (empty($accommodations) || empty($matrix)) {
            throw ValidationException::withMessages([
                'accommodation' => __('Please select an accommodation type.'),
            ]);
        }

        $defaultAcc = $accommodations[0];
        $selectedAcc = collect($accommodations)->first(function ($item) use ($accommodationIdentifier) {
            if (! $accommodationIdentifier) {
                return false;
            }
            return (string) ($item['id'] ?? '') === (string) $accommodationIdentifier
                || strcasecmp(trim($item['name'] ?? ''), trim((string) $accommodationIdentifier)) === 0;
        }) ?: $defaultAcc;

        $date = \Illuminate\Support\Carbon::parse($travelDate);
        $month = (int) $date->format('n');
        $targetSeasonKey = ($month >= 5 && $month <= 8) ? 'summer' : 'winter';
        $seasonName = $targetSeasonKey === 'summer' ? 'May to August' : 'September to April';

        $defaultAccName = $selectedAcc['name'] ?? 'Standard';
        $roomBreakdown = [];
        $total = 0.0;
        $totalAdults = 0;
        $totalChildren = 0;
        $accNamesInBooking = [];
        $lastRates = ['single' => 0.0, 'double' => 0.0, 'triple' => 0.0];

        if (empty($roomsData)) {
            $roomsData = [['accommodation' => $defaultAccName, 'adults' => 2, 'children' => 0]];
        }

        foreach ($roomsData as $index => $room) {
            $roomAccName = $room['accommodation'] ?? $defaultAccName;
            $rates = $matrix[$roomAccName][$targetSeasonKey]
                ?? $matrix[$defaultAccName][$targetSeasonKey]
                ?? reset($matrix)[$targetSeasonKey]
                ?? ['single' => 0.0, 'double' => 0.0, 'triple' => 0.0];

            $lastRates = $rates;
            $maxGuestsPerRoom = ((float) ($rates['triple'] ?? 0) > 0) ? 3 : 2;

            $adults = max(1, (int) ($room['adults'] ?? 1));
            $children = max(0, (int) ($room['children'] ?? 0));

            if (($adults + $children) > $maxGuestsPerRoom) {
                throw ValidationException::withMessages([
                    'rooms' => __('Room :room exceeds maximum capacity of :max guests.', [
                        'room' => $index + 1,
                        'max' => $maxGuestsPerRoom,
                    ]),
                ]);
            }

            $roomPrice = $this->calculateRoomPrice($rates, $adults, $children);
            $roomBreakdown[] = [
                'room_number' => $index + 1,
                'accommodation' => $roomAccName,
                'adults' => $adults,
                'children' => $children,
                'price' => round($roomPrice, 2),
            ];

            $accNamesInBooking[] = $roomAccName;
            $total += $roomPrice;
            $totalAdults += $adults;
            $totalChildren += $children;
        }

        $depositPercentage = (float) ($package->deposit_percentage ?: 50);
        if ($depositPercentage <= 0 || $depositPercentage > 100) {
            $depositPercentage = 50;
        }
        $depositAmount = round($total * ($depositPercentage / 100), 2);
        $remainingBalance = round($total - $depositAmount, 2);

        $currency = $package->currency;
        $uniqueAccommodations = array_values(array_unique($accNamesInBooking));
        $accLabel = implode(', ', $uniqueAccommodations);

        return [
            'id' => 'travel_package:' . ($selectedAcc['id'] ?? 'standard') . ':' . $targetSeasonKey,
            'source' => 'tour_package',
            'source_id' => $selectedAcc['id'] ?? null,
            'season_id' => null,
            'accommodation_id' => $selectedAcc['id'] ?? null,
            'accommodation_name' => $accLabel,
            'season_name' => $seasonName,
            'label' => $accLabel . ' (' . $seasonName . ')',
            'description' => collect([$accLabel, $seasonName])->filter()->implode(' · '),
            'currency_code' => strtoupper((string) ($currency?->code ?: 'USD')),
            'currency_symbol' => (string) ($currency?->symbol ?: '$'),
            'rates' => $lastRates,
            'rooms' => count($roomsData),
            'room_breakdown' => $roomBreakdown,
            'adults' => $totalAdults,
            'children' => $totalChildren,
            'infants' => 0,
            'travellers' => $totalAdults + $totalChildren,
            'occupancy_type' => 'room_based',
            'cabin_id' => null,
            'quantity' => count($roomsData),
            'price_unit' => 'travel_package',
            'amount' => round($total, 2),
            'total' => round($total, 2),
            'deposit_percentage' => $depositPercentage,
            'deposit_amount' => $depositAmount,
            'remaining_balance' => $remainingBalance,
            'valid_from' => null,
            'valid_to' => null,
        ];
    }
}
