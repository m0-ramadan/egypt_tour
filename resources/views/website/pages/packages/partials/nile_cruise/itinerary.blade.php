@php
    $ncDurations = $package->nileCruiseDurations?->where('is_active', true)->values() ?? collect();
@endphp

@if($ncDurations->isNotEmpty())
    <section class="content-section" id="cruise-itineraries">
        <h2 class="section-header">{{ $title }} {{ __('Itinerary') }}</h2>
        <p class="section-subtitle">{{ __('Explore day-by-day sailing schedules and guided tours for each duration option.') }}</p>

        <div class="nc-duration-tabs mb-4" role="tablist">
            @foreach($ncDurations as $durationIndex => $duration)
                <button type="button" class="nc-duration-tab {{ ($duration->is_default || (!$ncDurations->contains('is_default', true) && $durationIndex===0)) ? 'active' : '' }}" data-nc-duration-target="nc-duration-{{ $duration->id }}">
                    <i class="la la-clock me-1"></i> {{ $duration->title }}
                </button>
            @endforeach
        </div>

        @foreach($ncDurations as $durationIndex => $duration)
            @php $isActiveDuration = $duration->is_default || (!$ncDurations->contains('is_default', true) && $durationIndex===0); @endphp
            <div class="nc-duration-panel {{ $isActiveDuration ? 'active' : '' }}" id="nc-duration-{{ $duration->id }}">
                <div class="nc-duration-summary d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 rounded-4">
                    <div>
                        <strong class="d-block nc-duration-title" style="font-size: 1.1rem;">{{ $duration->title }}</strong>
                        @if($duration->direction)<small class="text-muted d-block">{{ __('Direction:') }} {{ $duration->direction }}</small>@endif
                    </div>
                    @if($duration->departure_day)
                        <div>
                            <span class="nc-pill"><i class="la la-calendar-check me-1" style="color:var(--rich-gold,#c5955b)"></i> {{ __('Departure:') }} {{ $duration->departure_day }}</span>
                        </div>
                    @endif
                    @if($duration->start_from_price)
                        <div>
                            <small class="text-muted d-block text-end">{{ __('Starting From') }}</small>
                            <strong style="color: var(--rich-gold, #c5955b); font-size: 1.25rem;">{{ $duration->currency?->symbol ?: ($package->currency?->symbol ?: '$') }}{{ number_format((float)$duration->start_from_price, 0) }}</strong>
                        </div>
                    @endif
                </div>

                @if($duration->itineraryDays->isNotEmpty())
                    <div class="itinerary-section">
                        @foreach($duration->itineraryDays as $day)
                            <div class="day-card">
                                <button type="button" class="day-header"
                                    data-collapse-target="nc-day-{{ $duration->id }}-{{ $day->id }}"
                                    aria-controls="nc-day-{{ $duration->id }}-{{ $day->id }}"
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    <div class="day-number" style="color: white !important;">
                                        {{ $day->day_number }}</div>
                                    <div>
                                        <h3 class="day-title">
                                            {{ __('Day') }} {{ $day->day_number }}@if ($day->display_title): {{ $day->display_title }}@endif
                                        </h3>
                                    </div>
                                    <i class="la la-chevron-down collapse-icon" style="margin-left:auto"></i>
                                </button>
                                <div class="collapsible-content {{ $loop->first ? 'open active' : '' }}"
                                    id="nc-day-{{ $duration->id }}-{{ $day->id }}">
                                    <div class="day-content">
                                        @if($day->display_description)
                                            <div class="mb-3 day-description" style="line-height: 1.85;">{!! nl2br(e($day->display_description)) !!}</div>
                                        @endif

                                        @if($day->activities->isNotEmpty())
                                            <div class="activities-list mt-3">
                                                <strong class="d-block mb-2 activity-header" style="font-size: 0.95rem;">
                                                    <i class="la la-map-pin" style="color: var(--rich-gold, #c5955b);"></i> {{ __('Key Activities & Visits:') }}
                                                </strong>
                                                @foreach($day->activities as $activity)
                                                    @php $activityHeading = $activity->display_title ?: $activity->attraction?->display_name; @endphp
                                                    <div class="nc-activity ps-3 border-start border-3 mb-2">
                                                        @if($activityHeading)
                                                            @if($activity->attraction?->slug)
                                                                <strong><a href="{{ route('website.attractions.show', $activity->attraction->slug) }}" class="activity-link">{{ $activityHeading }}</a></strong>
                                                            @else
                                                                <strong class="activity-title">{{ $activityHeading }}</strong>
                                                            @endif
                                                        @endif
                                                        @if($activity->display_description)
                                                            <div class="small text-muted mt-1">{!! nl2br(e($activity->display_description)) !!}</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($day->display_overnight)
                                            <p class="mt-3 mb-0 overnight-info">
                                                <strong><i class="la la-moon" style="color: var(--rich-gold, #c5955b);"></i> {{ __('Overnight:') }}</strong> {{ $day->display_overnight }}
                                            </p>
                                        @endif

                                        @php
                                            $dayMeals = [];
                                            if (!empty($day->meals) && (is_array($day->meals) || $day->meals instanceof \Illuminate\Support\Collection)) {
                                                $dayMeals = is_array($day->meals) ? $day->meals : $day->meals->toArray();
                                            }
                                            if (!empty($day->meals_breakfast) && !in_array('breakfast', $dayMeals)) $dayMeals[] = 'breakfast';
                                            if (!empty($day->meals_lunch) && !in_array('lunch', $dayMeals)) $dayMeals[] = 'lunch';
                                            if (!empty($day->meals_dinner) && !in_array('dinner', $dayMeals)) $dayMeals[] = 'dinner';
                                        @endphp

                                        @if (!empty($dayMeals))
                                            <div class="meals-included-card mt-3 p-3 rounded-3">
                                                <div class="fw-bold mb-2" style="font-size: 0.9rem;">{{ __('Meals Included') }}</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($dayMeals as $m)
                                                        @php
                                                            $mLower = strtolower((string)$m);
                                                            if (in_array($mLower, ['breakfast', 'إفطار', 'افطار'])) {
                                                                $mealText = __('Breakfast');
                                                            } elseif (in_array($mLower, ['lunch', 'غداء'])) {
                                                                $mealText = __('Lunch');
                                                            } elseif (in_array($mLower, ['dinner', 'عشاء'])) {
                                                                $mealText = __('Dinner');
                                                            } else {
                                                                $mealText = __(ucfirst($mLower));
                                                            }
                                                        @endphp
                                                        <span class="badge px-3 py-2 rounded-pill fw-medium meal-badge">
                                                            {{ $mealText }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-state">{{ __('No itinerary details configured for this duration yet.') }}</p>
                @endif
            </div>
        @endforeach
    </section>
@endif
