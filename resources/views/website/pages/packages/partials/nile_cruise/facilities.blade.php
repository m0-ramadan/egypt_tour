@php
    if (!isset($nileFacilityStats)) {
        $nileDetailForFacilities = $package->nileCruiseDetail;
        $nileCabinTotal = $package->nileCruiseCabins?->sum(fn($cabin) => (int) ($cabin->quantity ?? 0)) ?? 0;
        $nileFacilityStats = collect([
            $nileDetailForFacilities?->decks ? ['label' => $nileDetailForFacilities->decks . ' ' . __('Decks'), 'icon' => 'deck'] : null,
            $nileCabinTotal > 0 ? ['label' => $nileCabinTotal . ' ' . __('Cabins / Suites'), 'icon' => 'cabin'] : null,
            $nileDetailForFacilities?->sun_beds ? ['label' => $nileDetailForFacilities->sun_beds . ' ' . __('Sun Beds'), 'icon' => 'sun'] : null,
            $nileDetailForFacilities?->sun_deck_pergolas ? ['label' => $nileDetailForFacilities->sun_deck_pergolas . ' ' . __('Sun Deck Private Pergolas'), 'icon' => 'sun'] : null,
        ])->filter()->values();
    }
    $hasDynamicNileFacilities = (isset($facilities) && $facilities->isNotEmpty()) || (isset($nileFacilityStats) && $nileFacilityStats->isNotEmpty());
@endphp

@if($hasDynamicNileFacilities)
    <section class="content-section" id="cruise-facilities">
        <h2 class="section-header">{{ __('Cruise Facilities & Ship Amenities') }}</h2>
        <div class="facilities-grid">
            @if(isset($nileFacilityStats) && $nileFacilityStats->isNotEmpty())
                @foreach($nileFacilityStats as $facilityStat)
                    <div class="facility-card">
                        <span class="facility-icon" aria-hidden="true">
                            @if($facilityStat['icon'] === 'sun')
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
                            @elseif($facilityStat['icon'] === 'cabin')
                                <svg viewBox="0 0 24 24"><path d="M4 20V7l8-4 8 4v13"></path><path d="M8 20v-6h8v6"></path><path d="M9 9h.01M15 9h.01"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M4 20V8h16v12"></path><path d="M7 8V4h10v4"></path><path d="M8 12h8M8 16h8"></path></svg>
                            @endif
                        </span>
                        <span>{{ $facilityStat['label'] }}</span>
                    </div>
                @endforeach
            @endif

            @if(isset($facilities))
                @foreach ($facilities as $facility)
                    @php
                        $normalized = mb_strtolower((string)$facility->display_title, 'UTF-8');
                        $facilityIconName = match(true) {
                            str_contains($normalized, 'wifi') || str_contains($normalized, 'internet') => 'wifi',
                            str_contains($normalized, 'pool') || str_contains($normalized, 'swim') || str_contains($normalized, 'مسبح') => 'pool',
                            str_contains($normalized, 'air') || str_contains($normalized, 'ac') || str_contains($normalized, 'تكييف') => 'snowflake',
                            str_contains($normalized, 'bath') || str_contains($normalized, 'shower') || str_contains($normalized, 'حمام') => 'bath',
                            str_contains($normalized, 'tv') || str_contains($normalized, 'screen') || str_contains($normalized, 'شاشة') => 'tv',
                            str_contains($normalized, 'bar') || str_contains($normalized, 'lounge') || str_contains($normalized, 'drink') || str_contains($normalized, 'مشروبات') => 'glass',
                            str_contains($normalized, 'doctor') || str_contains($normalized, 'medical') || str_contains($normalized, 'طبيب') => 'medical',
                            str_contains($normalized, 'gym') || str_contains($normalized, 'fitness') || str_contains($normalized, 'رياضة') => 'gym',
                            str_contains($normalized, 'sun') || str_contains($normalized, 'deck') || str_contains($normalized, 'سطح') => 'sun',
                            default => 'check',
                        };
                    @endphp
                    <div class="facility-card">
                        <span class="facility-icon" aria-hidden="true">
                            @switch($facilityIconName)
                                @case('wifi')
                                    <svg viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"></path><path d="M8.5 16.5a5 5 0 0 1 7 0"></path><path d="M12 20h.01"></path></svg>
                                    @break
                                @case('pool')
                                    <svg viewBox="0 0 24 24"><path d="M4 18c2 0 2-1 4-1s2 1 4 1 2-1 4-1 2 1 4 1"></path><path d="M4 21c2 0 2-1 4-1s2 1 4 1 2-1 4-1 2 1 4 1"></path><path d="M8 17V5a3 3 0 0 1 6 0"></path><path d="M8 9h8"></path></svg>
                                    @break
                                @case('snowflake')
                                    <svg viewBox="0 0 24 24"><path d="M12 2v20"></path><path d="m17 5-5 5-5-5"></path><path d="m17 19-5-5-5 5"></path><path d="M2 12h20"></path></svg>
                                    @break
                                @case('bath')
                                    <svg viewBox="0 0 24 24"><path d="M4 12h16v4a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-4Z"></path><path d="M7 12V6a3 3 0 0 1 5.1-2.1"></path></svg>
                                    @break
                                @case('tv')
                                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="12" rx="2"></rect><path d="M8 21h8"></path><path d="M12 17v4"></path></svg>
                                    @break
                                @case('glass')
                                    <svg viewBox="0 0 24 24"><path d="M8 3h8l-1 8a3 3 0 0 1-6 0L8 3Z"></path><path d="M12 14v7"></path><path d="M9 21h6"></path></svg>
                                    @break
                                @case('medical')
                                    <svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"></rect><path d="M12 10v7M8.5 13.5h7"></path></svg>
                                    @break
                                @case('gym')
                                    <svg viewBox="0 0 24 24"><path d="m6.5 6.5 11 11"></path><path d="m21 21-1-1"></path><path d="m3 3 1 1"></path><path d="m18 22 4-4"></path><path d="m2 6 4-4"></path></svg>
                                    @break
                                @case('sun')
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @endswitch
                        </span>
                        <span>{{ __($facility->display_title) }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    </section>
@endif
