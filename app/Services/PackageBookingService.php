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
        return $this->pricingOptions($package)->isNotEmpty();
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
    ): array {
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
}
