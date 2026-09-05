@if($package->package_type === 'travel_package')
    @php
        $tourPackageCities = ($packageCities ?? collect())->map(fn($city) => $city->display_name)->filter()->values();
        $packageDetail = $tourPackageDetail ?? null;
        $mealLabels = collect((array) ($packageDetail?->meals_included ?? []))->filter(fn($m) => !empty(trim((string)$m)))->values();
        $groupText = null;
        if ($package->min_participants && $package->max_participants) {
            $groupText = $package->min_participants . '–' . $package->max_participants . ' ' . __('Pax');
        } elseif ($package->max_participants) {
            $groupText = __('Up to') . ' ' . $package->max_participants . ' ' . __('Pax');
        } elseif ($package->min_participants) {
            $groupText = __('From') . ' ' . $package->min_participants . ' ' . __('Pax');
        }

        $hasDuration = !empty(trim($durationText ?? ''));
        $hasRoute = $tourPackageCities->isNotEmpty() || !empty(trim($destinations ?? ''));
        $hasGroup = !empty($groupText);
        $hasDifficulty = !empty(trim($package->difficulty ?? ''));
        $hasLanguages = ($onTourLanguages ?? collect())->filter(fn($l) => !empty(trim((string)$l)))->isNotEmpty();
        $hasAccommodation = !empty(trim($packageDetail?->accommodation_standard ?? ''));
        $hasMeals = $mealLabels->isNotEmpty();
        $hasFlexible = $packageDetail && !is_null($packageDetail->flexible_itinerary);

        $hasTourDetails = $hasDuration || $hasRoute || $hasGroup || $hasDifficulty || $hasLanguages || $hasAccommodation || $hasMeals || $hasFlexible;
        $hasOperatingDays = ($operatingDays ?? collect())->filter(fn($d) => !empty(trim((string)$d)))->isNotEmpty();
        $hasNotes = !empty(trim($packageDetail?->additional_notes ?? ''));
    @endphp

    @if($hasTourDetails || $hasOperatingDays || $hasNotes)
        <section class="content-section" id="tour-package-overview">
            <h2 class="section-header">{{ __('Tour Package Details') }}</h2>
            @if($hasTourDetails)
                <div class="cruise-details">
                    @if($hasDuration)
                        <div class="detail-item"><i class="la la-calendar"></i><div class="detail-text"><strong class="detail-label">{{ __('Duration:') }}</strong><span class="detail-value">{{ $durationText }}</span></div></div>
                    @endif
                    @if($tourPackageCities->isNotEmpty())
                        <div class="detail-item"><i class="la la-route"></i><div class="detail-text"><strong class="detail-label">{{ __('Cities / Route:') }}</strong><span class="detail-value">{{ $tourPackageCities->implode(' / ') }}</span></div></div>
                        <div class="detail-item"><i class="la la-map-marker"></i><div class="detail-text"><strong class="detail-label">{{ __('Primary Start City:') }}</strong><span class="detail-value">{{ $tourPackageCities->first() }}</span></div></div>
                    @elseif(!empty(trim($destinations ?? '')))
                        <div class="detail-item"><i class="la la-route"></i><div class="detail-text"><strong class="detail-label">{{ __('Cities / Route:') }}</strong><span class="detail-value">{{ $destinations }}</span></div></div>
                    @endif
                    @if($hasGroup)
                        <div class="detail-item"><i class="la la-users"></i><div class="detail-text"><strong class="detail-label">{{ __('Group Size:') }}</strong><span class="detail-value">{{ $groupText }}</span></div></div>
                    @endif
                    @if($hasDifficulty)
                        <div class="detail-item"><i class="la la-mountain"></i><div class="detail-text"><strong class="detail-label">{{ __('Difficulty:') }}</strong><span class="detail-value">{{ __(\Illuminate\Support\Str::headline((string)$package->difficulty)) }}</span></div></div>
                    @endif
                    @if($hasLanguages)
                        <div class="detail-item"><i class="la la-language"></i><div class="detail-text"><strong class="detail-label">{{ __('Languages:') }}</strong><span class="detail-value">{{ $onTourLanguages->filter(fn($l) => !empty(trim((string)$l)))->implode(' · ') }}</span></div></div>
                    @endif
                    @if($hasAccommodation)
                        <div class="detail-item"><i class="la la-hotel"></i><div class="detail-text"><strong class="detail-label">{{ __('Accommodation:') }}</strong><span class="detail-value">{{ $packageDetail->accommodation_standard }}</span></div></div>
                    @endif
                    @if($hasMeals)
                        <div class="detail-item"><i class="la la-utensils"></i><div class="detail-text"><strong class="detail-label">{{ __('Meals Included:') }}</strong><span class="detail-value">{{ $mealLabels->implode(' · ') }}</span></div></div>
                    @endif
                    @if($hasFlexible)
                        <div class="detail-item"><i class="la la-sliders-h"></i><div class="detail-text"><strong class="detail-label">{{ __('Flexible Itinerary:') }}</strong><span class="detail-value">{{ $packageDetail->flexible_itinerary ? __('Available on request') : __('Fixed itinerary') }}</span></div></div>
                    @endif
                </div>
            @endif

            @if($hasOperatingDays)
                <div class="mt-4">
                    <h3>{{ __('Departure Days') }}</h3>
                    <p>{{ $operatingDays->filter(fn($d) => !empty(trim((string)$d)))->implode(' · ') }}</p>
                    @if(!empty($sharedTimezone))<small class="price-meta">{{ __('Timezone:') }} {{ $sharedTimezone }}</small>@endif
                </div>
            @endif

            @if($hasNotes)
                <div class="about-content mt-3">{!! nl2br(e($packageDetail->additional_notes)) !!}</div>
            @endif
        </section>
    @endif
@endif
