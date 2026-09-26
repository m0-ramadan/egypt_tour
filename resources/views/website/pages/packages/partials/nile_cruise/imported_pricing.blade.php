<style>
    .nc-fare-card {
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: var(--etp-white, #ffffff);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .nc-fare-tier-title {
        margin: 28px 0 16px;
        text-align: center;
        color: var(--etp-navy-950, #061B3E);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.3rem;
        font-weight: 700;
    }

    .nc-fare-tier-title::after {
        content: '';
        display: block;
        width: 30px;
        height: 2px;
        margin: 9px auto 0;
        background: var(--etp-orange-500, #F36B0A);
    }

    .nc-fare-card summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 24px;
        cursor: pointer;
        list-style: none;
        background: var(--etp-navy-800, #1f405c);
        color: var(--etp-white, #ffffff);
        user-select: none;
    }

    .nc-fare-card summary::-webkit-details-marker {
        display: none;
    }

    .nc-fare-duration {
        flex: 1;
        text-align: left;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--etp-white, #ffffff);
        letter-spacing: 0.2px;
    }

    .nc-fare-period {
        flex: 1;
        text-align: center;
        font-size: 0.95rem;
        color: var(--etp-white, #ffffff);
        font-weight: 400;
    }

    .nc-fare-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    .nc-fare-from {
        color: var(--etp-orange-500, #F36B0A);
        font-weight: 700;
        font-size: 1.05rem;
        white-space: nowrap;
    }

    .nc-fare-chevron {
        color: var(--etp-orange-500, #F36B0A);
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }

    .nc-fare-card[open] .nc-fare-chevron {
        transform: rotate(180deg);
    }

    .nc-fare-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-top: 1px solid #f1f5f9;
        background: var(--etp-white, #ffffff);
    }

    .nc-fare-label {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--etp-navy-950, #061B3E);
    }

    .nc-fare-amount {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
    }

    .nc-fare-price-line {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }

    .nc-fare-currency {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .nc-fare-amount strong {
        color: var(--etp-orange-500, #F36B0A);
        font-family: 'Playfair Display', Georgia, serif;
        font-weight: 700;
        font-size: 1.55rem;
        line-height: 1;
    }

    .nc-fare-note {
        display: block;
        color: #64748b;
        font-size: 0.82rem;
        margin-top: 4px;
        font-weight: 400;
    }

    .nc-fare-hotels {
        padding: 14px;
        border-top: 1px solid var(--etp-navy-700, #294d6b);
        background: var(--etp-navy-900, #102a43);
    }

    .nc-fare-hotels-title {
        padding: 10px 14px;
        border-radius: 8px;
        background: var(--etp-navy-800, #1f405c);
        color: var(--etp-white, #fff);
        text-align: center;
        font-weight: 700;
    }

    .nc-fare-hotel {
        margin-top: 12px;
        padding: 14px;
        border: 1px solid var(--etp-navy-700, #294d6b);
        border-radius: 10px;
        background: var(--etp-navy-800, #1f405c);
    }

    .nc-fare-hotel-name { color: var(--etp-white, #fff); font-weight: 700; }
    .nc-fare-hotel-city { float: right; color: var(--etp-orange-200, #fdba74); font-size: .8rem; }
    .nc-fare-hotel-stars { color: var(--etp-orange-500, #F36B0A); margin-top: 4px; }

    .nc-fare-hotel small {
        color: var(--etp-orange-100, #ffedd5);
    }

    html[data-theme='dark'] .nc-fare-card {
        background: #142238;
        border-color: #33445a;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    html[data-theme='dark'] .nc-fare-row {
        background: #142238;
        border-color: #24354c;
    }

    html[data-theme='dark'] .nc-fare-label {
        color: #f1f5f9;
    }

    html[data-theme='dark'] .nc-fare-currency,
    html[data-theme='dark'] .nc-fare-note {
        color: #94a3b8;
    }

    @media (max-width: 640px) {
        .nc-fare-card summary {
            padding: 14px 16px;
            gap: 8px;
        }

        .nc-fare-duration {
            font-size: 1rem;
        }

        .nc-fare-period {
            font-size: 0.85rem;
        }

        .nc-fare-from {
            font-size: 0.95rem;
        }

        .nc-fare-row {
            padding: 16px;
        }

        .nc-fare-label {
            font-size: 0.95rem;
        }

        .nc-fare-amount strong {
            font-size: 1.35rem;
        }
    }
</style>
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
