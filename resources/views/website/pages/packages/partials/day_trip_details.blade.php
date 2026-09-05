@if($package->package_type === 'day_tour')
    @php
        $dayTripCityNames = ($packageCities ?? collect())->map(fn($city) => $city->display_name)->filter()->values();
        $dayTripPrimaryCity = $dayTripCityNames->first() ?: ($destinations ?? '');
        $groupText = null;
        if ($package->min_participants && $package->max_participants) {
            $groupText = $package->min_participants . '–' . $package->max_participants . ' ' . __('Pax');
        } elseif ($package->max_participants) {
            $groupText = __('Up to') . ' ' . $package->max_participants . ' ' . __('Pax');
        } elseif ($package->min_participants) {
            $groupText = __('From') . ' ' . $package->min_participants . ' ' . __('Pax');
        }

        $hasDuration = !empty(trim($durationText ?? ''));
        $hasCity = !empty(trim($dayTripPrimaryCity ?? ''));
        $hasGroup = !empty($groupText);
        $hasDifficulty = !empty(trim($package->difficulty ?? ''));
        $hasLanguages = ($onTourLanguages ?? collect())->filter(fn($l) => !empty(trim((string)$l)))->isNotEmpty();
        $hasDayTripDetails = $hasDuration || $hasCity || $hasGroup || $hasDifficulty || $hasLanguages;

        $hasOperatingDays = ($operatingDays ?? collect())->filter(fn($d) => !empty(trim((string)$d)))->isNotEmpty();
        $hasDepartureTimes = ($departureTimes ?? collect())->filter(fn($t) => !empty(trim((string)$t)))->isNotEmpty();
    @endphp

    @if($hasDayTripDetails || $hasOperatingDays || $hasDepartureTimes)
        <section class="content-section" id="day-trip-overview">
            <h2 class="section-header">{{ __('Day Trip Details') }}</h2>
            @if($hasDayTripDetails)
                <div class="cruise-details">
                    @if($hasDuration)
                        <div class="detail-item"><i class="la la-clock"></i><div class="detail-text"><strong class="detail-label">{{ __('Duration:') }}</strong><span class="detail-value">{{ $durationText }}</span></div></div>
                    @endif
                    @if($hasCity)
                        <div class="detail-item"><i class="la la-map-marker"></i><div class="detail-text"><strong class="detail-label">{{ __('Primary City:') }}</strong><span class="detail-value">{{ $dayTripPrimaryCity }}</span></div></div>
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
                </div>
            @endif

            @if($hasOperatingDays || $hasDepartureTimes)
                <div class="mt-4">
                    <h3>{{ __('Availability & Departure Times') }}</h3>
                    @if($hasOperatingDays)
                        <p><strong>{{ __('Operating Days:') }}</strong> {{ $operatingDays->filter(fn($d) => !empty(trim((string)$d)))->implode(' · ') }}</p>
                    @endif
                    @if($hasDepartureTimes)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($departureTimes->filter(fn($t) => !empty(trim((string)$t))) as $time)
                                <span class="meal-badge"><i class="la la-clock"></i> {{ substr((string)$time, 0, 5) }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($sharedTimezone))
                        <small class="price-meta d-block mt-2">{{ __('Timezone:') }} {{ $sharedTimezone }}</small>
                    @endif
                </div>
            @endif
        </section>
    @endif
@endif
