@php
    $ncDetail = $package->nileCruiseDetail;
    $ncCruise = $package->cruise;
    $ncDurations = $package->nileCruiseDurations?->where('is_active', true)->values() ?? collect();
    $ncRoute = $package->cities?->sortBy(fn($city) => $city->pivot?->stop_order ?? 0)->values() ?? collect();
    $ncSchedules = $package->nileCruiseSchedules?->where('is_active', true) ?? collect();
    $ncOperatingDays = isset($operatingDays) && $operatingDays->isNotEmpty() ? $operatingDays : collect((array) ($ncDetail?->operating_days ?? []));
    $ncLanguages = isset($onTourLanguages) && $onTourLanguages->isNotEmpty() ? $onTourLanguages : collect((array) ($ncDetail?->on_tour_languages ?? []));

    $ncOperatingDays = $ncOperatingDays->filter(fn($d) => !empty(trim((string)$d)))->values();
    $ncLanguages = $ncLanguages->filter(fn($l) => !empty(trim((string)$l)))->values();

    $hasShipName = !empty(trim($ncCruise?->ship_name ?? ''));
    $hasCruiseClass = !empty(trim($ncCruise?->cruise_class ?? ''));
    $hasStarRating = !empty($ncCruise?->star_rating) && $ncCruise->star_rating > 0;
    $hasDuration = $ncDurations->isNotEmpty() || !empty(trim($durationText ?? ''));
    $hasRoute = $ncRoute->isNotEmpty() || !empty(trim($ncDetail?->route_summary ?? ''));
    $hasSchedule = $ncSchedules->isNotEmpty() || $ncOperatingDays->isNotEmpty();
    $hasLanguages = $ncLanguages->isNotEmpty();
    $hasAllInclusive = $ncDetail && !is_null($ncDetail->all_inclusive);
    $hasTourStyle = !empty(trim($ncDetail?->tour_style ?? '')) || !empty(trim($tourTypeText ?? ''));
    $hasDecks = !empty($ncDetail?->decks);
    $hasSunBeds = !empty($ncDetail?->sun_beds);
    $hasPergolas = !empty($ncDetail?->sun_deck_pergolas);
    $hasTimezone = !empty(trim($ncDetail?->timezone ?? ''));

    $hasDetailsGrid = $hasShipName || $hasCruiseClass || $hasStarRating || $hasDuration || $hasRoute || $hasSchedule || $hasLanguages || $hasAllInclusive || $hasTourStyle || $hasDecks || $hasSunBeds || $hasPergolas || $hasTimezone;
@endphp

@if($hasDetailsGrid)
    <section class="content-section" id="nile-cruise-details">
        <h2 class="section-header">{{ __('Nile Cruise Details') }}</h2>
        <div class="cruise-details">
            @if($hasShipName)
                <div class="detail-item">
                    <i class="la la-ship"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Cruise / Boat:') }}</strong>
                        <span class="detail-value">{{ $ncCruise->ship_name }}</span>
                    </div>
                </div>
            @endif

            @if($hasCruiseClass)
                <div class="detail-item">
                    <i class="la la-award"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Cruise Class:') }}</strong>
                        <span class="detail-value">{{ $ncCruise->cruise_class }}</span>
                    </div>
                </div>
            @endif

            @if($hasStarRating)
                <div class="detail-item">
                    <i class="la la-star"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Rating:') }}</strong>
                        <span class="detail-value text-warning fw-bold">{{ $ncCruise->star_rating }} ★</span>
                    </div>
                </div>
            @endif

            @if($ncDurations->isNotEmpty())
                <div class="detail-item">
                    <i class="la la-calendar"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Duration(s):') }}</strong>
                        <span class="detail-value">{{ $ncDurations->pluck('title')->filter()->implode(' / ') }}</span>
                    </div>
                </div>
            @elseif(!empty(trim($durationText ?? '')))
                <div class="detail-item">
                    <i class="la la-calendar"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Duration:') }}</strong>
                        <span class="detail-value">{{ $durationText }}</span>
                    </div>
                </div>
            @endif

            @if($ncRoute->isNotEmpty())
                <div class="detail-item">
                    <i class="la la-route"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Route:') }}</strong>
                        <span class="detail-value">{{ $ncRoute->map(fn($city)=>$city->display_name)->filter()->implode(' / ') }}</span>
                    </div>
                </div>
            @elseif(!empty(trim($ncDetail?->route_summary ?? '')))
                <div class="detail-item">
                    <i class="la la-route"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Route:') }}</strong>
                        <span class="detail-value">{{ $ncDetail->route_summary }}</span>
                    </div>
                </div>
            @endif

            @if($ncSchedules->isNotEmpty())
                <div class="detail-item">
                    <i class="la la-clock"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Sailing Schedule:') }}</strong>
                        <span class="detail-value">{{ $ncSchedules->map(fn($s)=>trim(($s->departure_day ?: '').' '.($s->departureCity?->display_name ? __('from').' '.$s->departureCity->display_name : '')))->filter()->implode(' · ') }}</span>
                    </div>
                </div>
            @elseif($ncOperatingDays->isNotEmpty())
                <div class="detail-item">
                    <i class="la la-clock"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Operating Days:') }}</strong>
                        <span class="detail-value">{{ $ncOperatingDays->implode(' · ') }}</span>
                    </div>
                </div>
            @endif

            @if($hasLanguages)
                <div class="detail-item">
                    <i class="la la-language"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Languages:') }}</strong>
                        <span class="detail-value">{{ $ncLanguages->implode(' · ') }}</span>
                    </div>
                </div>
            @endif

            @if($hasAllInclusive)
                <div class="detail-item">
                    <i class="la la-gem"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('All Inclusive:') }}</strong>
                        <span class="detail-value">{{ $ncDetail->all_inclusive ? __('Yes') : __('No') }}</span>
                    </div>
                </div>
            @endif

            @if($hasTourStyle)
                <div class="detail-item">
                    <i class="la la-compass"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Tour Type:') }}</strong>
                        <span class="detail-value">{{ $ncDetail?->tour_style ?: $tourTypeText }}</span>
                    </div>
                </div>
            @endif

            @if($hasDecks)
                <div class="detail-item">
                    <i class="la la-layer-group"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Decks:') }}</strong>
                        <span class="detail-value">{{ $ncDetail->decks }}</span>
                    </div>
                </div>
            @endif

            @if($hasSunBeds)
                <div class="detail-item">
                    <i class="la la-umbrella-beach"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Sun Beds:') }}</strong>
                        <span class="detail-value">{{ $ncDetail->sun_beds }}</span>
                    </div>
                </div>
            @endif

            @if($hasPergolas)
                <div class="detail-item">
                    <i class="la la-store-alt"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Pergolas:') }}</strong>
                        <span class="detail-value">{{ $ncDetail->sun_deck_pergolas }}</span>
                    </div>
                </div>
            @endif

            @if($hasTimezone)
                <div class="detail-item">
                    <i class="la la-globe"></i>
                    <div class="detail-text">
                        <strong class="detail-label">{{ __('Timezone:') }}</strong>
                        <span class="detail-value">{{ $ncDetail->timezone }}</span>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
