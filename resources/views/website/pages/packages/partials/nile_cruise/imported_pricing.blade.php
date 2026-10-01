@vite('resources/css/pages/nile-cruise-pricing.css')
@php
    $fareAccommodations = $package->tourPackageAccommodations->where('is_active', true)->filter(
        fn($accommodation) => $accommodation->seasons->where('is_active', true)->contains(
            fn($season) => $season->items->where('is_active', true)->contains(fn($item) => (float) $item->price > 0)
        )
    );
@endphp
@foreach ($fareAccommodations as $accommodation)
    <h3 class="nc-fare-tier-title">{{ $accommodation->name }}</h3>
    @foreach ($accommodation->seasons->where('is_active', true)->values() as $seasonIndex => $season)
        @php
            $fareItems = $season->items->where('is_active', true)->filter(fn($item) => (float) $item->price > 0);
            $fareSymbol = $season->currency?->symbol ?: $currencySymbol ?? ($package->currency?->symbol ?? '$');
            $fareCode = $season->currency?->code ?: ($package->currency?->code ?: 'USD');
            $seasonPeriod = $season->period;
            if (empty($seasonPeriod)) {
                if ($season->date_from && $season->date_to) {
                    $seasonPeriod =
                        $season->date_from->format('F') . ' ' . __('to') . ' ' . $season->date_to->format('F');
                } else {
                    $seasonPeriod = $seasonIndex % 2 === 0 ? __('May to August') : __('September to April');
                }
            }
        @endphp
        @if ($fareItems->isNotEmpty())
            <details class="nc-fare-card">
                <summary>
                    <span class="nc-fare-duration">{{ $seasonPeriod ?: $season->display_season_name }}</span>
                    <div class="nc-fare-right">
                        <span class="nc-fare-from">{{ __('From') }}: {{ $fareSymbol }}{{ number_format((float) $fareItems->min('price'), 0) }}</span>
                        <svg class="nc-fare-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </summary>
                @foreach ($fareItems as $item)
                    @php
                        $cabinLabels = [
                            'triple' => $package->package_type === 'travel_package' ? __('Per Person in Triple Room') : __('Triple Cabin'),
                            'double' => $package->package_type === 'travel_package' ? __('Per Person in Double Room') : __('Double Cabin'),
                            'single' => $package->package_type === 'travel_package' ? __('Per Person in Single Room') : __('Single Cabin'),
                        ];
                        $cabinNotes = [
                            'triple' => __('per adult in a triple share cabin'),
                            'double' => __('per adult in a double share cabin'),
                            'single' => __('per adult in a single share cabin'),
                        ];
                        $label =
                            $cabinLabels[strtolower((string) $item->occupancy_type)] ??
                            str_ireplace(
                                'Room',
                                'Cabin',
                                $item->display_label ?: ucfirst((string) $item->occupancy_type),
                            );
                        $note =
                            $cabinNotes[strtolower((string) $item->occupancy_type)] ??
                            ($item->price_unit === 'per_person' ? __('per person') : __($item->price_unit));
                    @endphp
                    <div class="nc-fare-row">
                        <span class="nc-fare-label">{{ $label }}</span>
                        <div class="nc-fare-amount">
                            <div class="nc-fare-price-line">
                                <span class="nc-fare-currency">{{ $fareCode }}</span>
                                <strong>{{ $fareSymbol }}{{ number_format((float) $item->price, 0) }}</strong>
                            </div>
                            <small class="nc-fare-note">{{ $note }}</small>
                        </div>
                    </div>
                @endforeach
                @if ($package->package_type === 'travel_package' && $accommodation->hotels->where('is_active', true)->isNotEmpty())
                    <div class="nc-fare-hotels">
                        <div class="nc-fare-hotels-title"><i class="la la-hotel me-1"></i> {{ __('Hotels') }}</div>
                        @foreach ($accommodation->hotels->where('is_active', true) as $hotel)
                            <div class="nc-fare-hotel">
                                <span class="nc-fare-hotel-city">{{ $hotel->city_name ?: $hotel->city?->display_name }}</span>
                                <div class="nc-fare-hotel-name">{{ $hotel->hotel_name }}</div>
                                @if ($hotel->star_rating)<div class="nc-fare-hotel-stars">{{ str_repeat('★', (int) $hotel->star_rating) }}</div>@endif
                                @if ($hotel->description)<small>{{ $hotel->description }}</small>@endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </details>
        @endif
    @endforeach
@endforeach
