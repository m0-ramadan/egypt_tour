@php
    $ncSchedules = $package->nileCruiseSchedules?->where('is_active', true) ?? collect();
    $ncCabins = $package->nileCruiseCabins ?? collect();
    $ncRoute = $package->cities?->sortBy(fn($city) => $city->pivot?->stop_order ?? 0)->values() ?? collect();
@endphp

@if($ncSchedules->isNotEmpty() || $ncCabins->isNotEmpty() || $ncRoute->isNotEmpty())
    <section class="content-section" id="schedule-cabins-route">
        @if($ncSchedules->isNotEmpty())
            <div class="nc-subsection" id="cruise-schedule">
                <h3 class="nc-subsection-title">
                    <span class="title-left"><i class="la la-calendar-check"></i> {{ __('Cruise Schedule & Departures') }}</span>
                </h3>
                <div class="nc-schedule-grid">
                    @foreach($ncSchedules as $scheduleItem)
                        <div class="nc-schedule">
                            <strong style="color: var(--primary-navy, #1c325c); font-size: .92rem;">{{ $scheduleItem->departure_day ?: __('Scheduled Sailing') }}</strong>
                            @if($scheduleItem->direction)
                                <div class="mt-1" style="color:#5f6d7e;font-size:.8rem;">{{ $scheduleItem->direction }}</div>
                            @elseif($scheduleItem->departureCity || $scheduleItem->arrivalCity)
                                <div class="mt-1" style="color:#5f6d7e;font-size:.8rem;">
                                    {{ $scheduleItem->departureCity?->display_name }}
                                    @if($scheduleItem->arrivalCity) <span style="color:#c5955b">→</span> {{ $scheduleItem->arrivalCity->display_name }} @endif
                                </div>
                            @endif
                            @if($scheduleItem->notes)
                                <small class="d-block mt-2 text-muted">{{ $scheduleItem->notes }}</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($ncCabins->isNotEmpty())
            <div class="nc-subsection" id="cabins-suites">
                <h3 class="nc-subsection-title">
                    <span class="title-left"><i class="la la-home"></i> {{ __('Cabins & Luxury Suites') }}</span>
                </h3>
                <div class="nc-cabin-grid">
                    @foreach($ncCabins as $cabin)
                        <article class="nc-cabin">
                            @if($cabin->featured_image)
                                <img src="{{ asset('storage/'.$cabin->featured_image) }}" alt="{{ $cabin->name }}" loading="lazy">
                            @endif
                            <div class="nc-cabin-body">
                                <h4 style="color:var(--primary-navy,#1c325c);font-family:'Playfair Display',serif;font-size:1rem;font-weight:800;margin-bottom:8px;">{{ $cabin->name }}</h4>
                                <div class="nc-cabin-meta">
                                    @if($cabin->quantity)<span class="nc-pill">{{ $cabin->quantity }} {{ __('Cabins') }}</span>@endif
                                    @if($cabin->bed_type)<span class="nc-pill">{{ $cabin->bed_type }}</span>@endif
                                    @if($cabin->size_sqm)<span class="nc-pill">{{ rtrim(rtrim(number_format((float)$cabin->size_sqm,2), '0'), '.') }} m²</span>@endif
                                    @if($cabin->has_private_bathroom)<span class="nc-pill">{{ __('Private Bathroom') }}</span>@endif
                                    @if($cabin->has_private_terrace)<span class="nc-pill">{{ __('Private Terrace') }}</span>@endif
                                </div>
                                @if($cabin->description)
                                    <p class="mt-3 mb-2 text-muted" style="font-size:.8rem;line-height:1.55;">{{ $cabin->description }}</p>
                                @endif
                                @if(!empty($cabin->amenities))
                                    <ul class="mb-0 ps-3 small text-muted">
                                        @foreach($cabin->amenities as $amenity)
                                            <li>{{ $amenity }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        @if($ncRoute->isNotEmpty())
            <div class="nc-subsection" id="cruise-route">
                <h3 class="nc-subsection-title">
                    <span class="title-left"><i class="la la-map-marked"></i> {{ __('Places You\'ll Visit / Sailing Route') }}</span>
                </h3>
                <div class="nc-route-line" style="--route-count:{{ max(1, $ncRoute->count()) }}">
                    @foreach($ncRoute as $routeIndex => $city)
                        <div class="nc-route-stop">
                            <span class="nc-route-dot"><i class="la {{ $routeIndex === 0 ? 'la-ship' : ($loop->last ? 'la-map-pin' : 'la-map-marker') }}"></i></span>
                            <div class="nc-route-name">{{ $city->display_name }}</div>
                            @if($city->pivot?->notes)
                                <div class="nc-route-note">{{ $city->pivot->notes }}</div>
                            @elseif($routeIndex === 0)
                                <div class="nc-route-note">{{ __('Embarkation') }}</div>
                            @elseif($loop->last)
                                <div class="nc-route-note">{{ __('Disembarkation') }}</div>
                            @else
                                <div class="nc-route-note">{{ __('Sailing Stop') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endif
