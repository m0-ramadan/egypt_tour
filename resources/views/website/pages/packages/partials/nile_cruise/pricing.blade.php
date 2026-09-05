@php
    $ncDurations = $package->nileCruiseDurations?->where('is_active', true)->values() ?? collect();
    $ncLegacyAddons = $package->nileCruiseAddons?->where('is_active', true) ?? collect();
    $ncAddons = isset($addons) && $addons->isNotEmpty() ? $addons : $ncLegacyAddons;
    $hasSeasonPrices = $ncDurations->contains(fn($d) => $d->seasonPrices
        ->where('is_active', true)
        ->contains(fn($season) => $season->items->contains(fn($item) => (float)$item->price > 0)));
@endphp

@if($hasSeasonPrices || $ncAddons->isNotEmpty())
    <section class="content-section" id="pricing-packages">
        <h2 class="section-header">{{ __('Pricing & Packages') }}</h2>
        <p class="section-subtitle">{{ __('Choose your preferred duration and season. Prices are shown using the cruise pricing configured for each cabin or occupancy option.') }}</p>

        @if($hasSeasonPrices)
            <div class="pricing-duration-accordions">
                @foreach($ncDurations as $durationIndex => $duration)
                    @php
                        $activeSeasons = $duration->seasonPrices->where('is_active', true)->values();
                        $priceLabels = $activeSeasons
                            ->flatMap(fn($season) => $season->items)
                            ->map(function($item){
                                return trim((string) ($item->cabin?->name ?: ($item->display_label ?: ucfirst((string)$item->occupancy_type))));
                            })
                            ->filter()->unique()->values();
                    @endphp
                    @if($activeSeasons->isNotEmpty())
                        <details class="nc-price-duration" {{ $durationIndex === 0 ? 'open' : '' }}>
                            <summary>
                                <span class="nc-price-duration-name">{{ $duration->title }}</span>
                                <span class="nc-price-seasons">{{ trans_choice(':count Season|:count Seasons', $activeSeasons->count(), ['count' => $activeSeasons->count()]) }}</span>
                                <span class="nc-price-from">
                                    @if($duration->start_from_price)
                                        {{ __('From') }}: {{ $duration->currency?->symbol ?: ($package->currency?->symbol ?: '$') }}{{ number_format((float)$duration->start_from_price, 0) }}
                                    @else
                                        {{ __('View Prices') }}
                                    @endif
                                </span>
                            </summary>
                            <div class="nc-price-body">
                                <table class="nc-price-matrix">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Season') }}</th>
                                            <th>{{ __('From') }}</th>
                                            <th>{{ __('To') }}</th>
                                            @foreach($priceLabels as $label)
                                                <th>{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeSeasons as $season)
                                            @php
                                                $itemsByLabel = $season->items->keyBy(function($item){
                                                    return trim((string) ($item->cabin?->name ?: ($item->display_label ?: ucfirst((string)$item->occupancy_type))));
                                                });
                                            @endphp
                                            <tr>
                                                <td>{{ $season->display_season_name ?: __('Season') }}</td>
                                                <td>{{ $season->date_from?->format('d M Y') ?: '—' }}</td>
                                                <td>{{ $season->date_to?->format('d M Y') ?: '—' }}</td>
                                                @foreach($priceLabels as $label)
                                                    @php $priceItem = $itemsByLabel->get($label); @endphp
                                                    <td>
                                                        @if($priceItem)
                                                            <span class="price-value">{{ $season->currency?->symbol ?: ($package->currency?->symbol ?: '$') }}{{ number_format((float)$priceItem->price, 0) }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                @endforeach
            </div>
        @endif

        @if($ncAddons->isNotEmpty())
            <div class="mt-4 pt-3 border-top" style="border-color:rgba(28,50,92,.08)!important;">
                <h3 class="fw-bold mb-3" style="color: var(--primary-navy, #1c325c); font-family: 'Playfair Display', serif;">
                    <i class="la la-tag me-2" style="color: var(--rich-gold, #c5955b);"></i>{{ __('Optional Cruise Excursions & Add-ons') }}
                </h3>
                <div class="row g-3">
                    @foreach($ncAddons as $addon)
                        <div class="col-md-6">
                            <div class="included-box h-100">
                                <h4 class="box-title">{{ $addon->title ?? $addon->name ?? __('Add-on') }}</h4>
                                @if($addon->description)<p class="mb-2">{{ $addon->description }}</p>@endif
                                @if($addon->price !== null)
                                    <strong style="color: var(--rich-gold, #c5955b); font-size: 1.05rem;">{{ $addon->currency?->symbol ?: ($package->currency?->symbol ?: '$') }}{{ number_format((float)$addon->price, 2) }}</strong>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endif
