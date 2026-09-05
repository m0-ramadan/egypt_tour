@if($package->package_type === 'nile_cruise')
    @php
        $ncDetail = $package->nileCruiseDetail;
        $ncSchedules = $package->nileCruiseSchedules?->where('is_active', true) ?? collect();
        $ncCabins = $package->nileCruiseCabins ?? collect();
        $ncLegacyAddons = $package->nileCruiseAddons?->where('is_active', true) ?? collect();
        $ncAddons = isset($addons) && $addons->isNotEmpty() ? $addons : $ncLegacyAddons;
        $ncOperatingDays = isset($operatingDays) && $operatingDays->isNotEmpty() ? $operatingDays : collect((array) ($ncDetail?->operating_days ?? []));
        $ncLanguages = isset($onTourLanguages) && $onTourLanguages->isNotEmpty() ? $onTourLanguages : collect((array) ($ncDetail?->on_tour_languages ?? []));
        $ncWhatToBring = isset($whatToBring) && $whatToBring->isNotEmpty() ? $whatToBring : collect((array) ($ncDetail?->what_to_bring ?? []));
        $ncVideos = isset($promotionalVideos) && $promotionalVideos->isNotEmpty() ? $promotionalVideos : collect((array) ($ncDetail?->promotional_videos ?? []));
        $ncDepositPolicy = $package->deposit_policy ?: ($ncDetail?->deposit_policy ?? null);
        $ncDepositType = $package->deposit_type ?: ($ncDetail?->deposit_type ?? null);
        $ncDepositValue = $package->deposit_value ?? ($ncDetail?->deposit_value ?? null);
        $ncCruise = $package->cruise;
        $ncDurations = $package->nileCruiseDurations?->where('is_active', true)->values() ?? collect();
        $ncRoute = $package->cities?->sortBy(fn($city) => $city->pivot?->stop_order ?? 0)->values() ?? collect();

        $hasNcDetailData = $ncDetail && (
            !empty(trim($ncDetail->route_summary ?? '')) ||
            !is_null($ncDetail->all_inclusive) ||
            !empty(trim($ncDetail->tour_style ?? '')) ||
            !empty($ncDetail->decks) ||
            !empty($ncDetail->sun_beds) ||
            !empty($ncDetail->sun_deck_pergolas) ||
            !empty($ncDetail->pickup_notes) ||
            !empty($ncDetail->dropoff_notes) ||
            !empty($ncDetail->fact_sheet_path) ||
            !empty($ncDetail->timezone) ||
            collect((array)$ncDetail->operating_days)->filter(fn($d) => !empty(trim((string)$d)))->isNotEmpty() ||
            collect((array)$ncDetail->on_tour_languages)->filter(fn($l) => !empty(trim((string)$l)))->isNotEmpty() ||
            collect((array)$ncDetail->what_to_bring)->filter(fn($w) => !empty(trim((string)$w)))->isNotEmpty() ||
            collect((array)$ncDetail->promotional_videos)->filter(fn($v) => !empty(trim((string)$v)))->isNotEmpty()
        );

        $hasNcCruiseData = $ncCruise && (
            !empty(trim($ncCruise->ship_name ?? '')) ||
            !empty(trim($ncCruise->cruise_class ?? '')) ||
            (!empty($ncCruise->star_rating) && $ncCruise->star_rating > 0)
        );

        $hasInclusionsExclusions = (!empty($included) && $included->isNotEmpty()) || (!empty($excluded) && $excluded->isNotEmpty()) || (!empty($inclusions) && count($inclusions) > 0) || (!empty($exclusions) && count($exclusions) > 0);
        $hasPoliciesData = !empty($package->getTranslation('children_policy')) || !empty($package->getTranslation('cancellation_policy')) || !empty($package->getTranslation('terms_conditions')) || !empty($package->getTranslation('pickup_policy'));

        $hasFacilitiesData = !empty($facilities) && $facilities->isNotEmpty();
        $hasNcExtendedData = $hasNcDetailData || $hasNcCruiseData || $ncSchedules->isNotEmpty() || $ncCabins->isNotEmpty() || $ncAddons->isNotEmpty() || $ncDurations->isNotEmpty() || $hasInclusionsExclusions || $hasPoliciesData || $hasFacilitiesData;
    @endphp

    @if($hasNcExtendedData)
        <style>
            .nc-schedule-grid,.nc-cabin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
            .nc-schedule,.nc-cabin{border:1px solid rgba(28, 50, 92, 0.12);border-radius:14px;padding:18px;background:#ffffff;box-shadow:0 4px 15px rgba(0,0,0,0.03)}
            .nc-cabin-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
            .nc-pill{display:inline-flex;padding:6px 12px;border-radius:999px;background:var(--pearl-luxury, #faf8f3);color:var(--primary-navy, #1c325c);font-size:0.82rem;font-weight:600;border:1px solid rgba(197, 149, 91, 0.25)}
            .nc-duration-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
            .nc-duration-tab{border:1.5px solid var(--rich-gold, #c5955b);background:#ffffff;color:var(--primary-navy, #1c325c);padding:10px 18px;border-radius:999px;cursor:pointer;font-weight:700;font-size:0.92rem;transition:all 0.25s ease}
            .nc-duration-tab.active,.nc-duration-tab:hover{background:var(--gradient-gold, #c5955b);color:var(--primary-navy, #1c325c);border-color:var(--rich-gold, #c5955b);box-shadow:0 4px 12px rgba(197, 149, 91, 0.3)}
            .nc-duration-panel{display:none}
            .nc-duration-panel.active{display:block}
            .nc-day{border:1px solid rgba(28, 50, 92, 0.12);border-radius:14px;margin-bottom:14px;background:#ffffff;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.02)}
            .nc-day summary{cursor:pointer;padding:16px 20px;font-weight:700;color:var(--primary-navy, #1c325c);font-size:1.05rem}
            .nc-day-body{padding:0 20px 20px}
            .nc-activity{padding:12px 0;border-top:1px solid rgba(28, 50, 92, 0.08)}
            .nc-addon-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
            .nc-addon{border:1px solid rgba(28, 50, 92, 0.12);border-radius:14px;padding:16px;background:#ffffff}
            .nc-cabin img{width:100%;height:200px;object-fit:cover;border-radius:10px;margin-bottom:14px}
            .nc-fact-sheet{display:inline-flex;align-items:center;gap:8px}

            /* Theme Integration & Dark Mode Support */
            html[data-theme='dark'] .nc-schedule,
            html[data-theme='dark'] .nc-cabin,
            html[data-theme='dark'] .nc-day,
            html[data-theme='dark'] .nc-addon {
                background: #151d30 !important;
                border-color: rgba(255, 255, 255, 0.12) !important;
                color: #e2e8f0 !important;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
            }
            html[data-theme='dark'] .nc-day summary { color: #ffffff !important; }
            html[data-theme='dark'] .nc-pill { background: rgba(197, 149, 91, 0.18) !important; color: #f4c36a !important; border-color: rgba(197, 149, 91, 0.3) !important; }
            html[data-theme='dark'] .nc-duration-tab { background: #151d30 !important; color: #e2e8f0 !important; border-color: rgba(197, 149, 91, 0.4) !important; }
            html[data-theme='dark'] .nc-duration-tab.active { background: var(--gradient-gold, #c5955b) !important; color: #1c325c !important; }

            /* Itinerary Dark Mode Overrides */
            html[data-theme='dark'] .nc-duration-summary {
                background: #151d30 !important;
                background-color: #151d30 !important;
                border-color: rgba(244, 195, 106, 0.3) !important;
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .nc-duration-summary strong,
            html[data-theme='dark'] .nc-duration-summary .nc-duration-title {
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .nc-duration-summary small,
            html[data-theme='dark'] .nc-duration-summary .text-muted {
                color: #94a3b8 !important;
            }
            html[data-theme='dark'] .itinerary-section .day-card,
            html[data-theme='dark'] .itinerary-section .collapsible-content,
            html[data-theme='dark'] .itinerary-section .day-content {
                background: #151d30 !important;
                background-color: #151d30 !important;
                color: #f1f5f9 !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
            html[data-theme='dark'] .itinerary-section .day-header {
                background: #1c273e !important;
                background-color: #1c273e !important;
                color: #ffffff !important;
                border-bottom-color: rgba(255, 255, 255, 0.08) !important;
            }
            html[data-theme='dark'] .itinerary-section .day-title {
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .itinerary-section .day-description,
            html[data-theme='dark'] .itinerary-section p,
            html[data-theme='dark'] .itinerary-section span:not(.badge):not(.day-number) {
                color: #cbd5e1 !important;
            }
            html[data-theme='dark'] .itinerary-section strong {
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .nc-activity {
                border-left-color: #f4c36a !important;
                color: #cbd5e1 !important;
            }
            html[data-theme='dark'] .nc-activity .activity-link,
            html[data-theme='dark'] .nc-activity a {
                color: #f4c36a !important;
            }
            html[data-theme='dark'] .nc-activity .activity-title {
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .nc-activity .text-muted {
                color: #94a3b8 !important;
            }
            html[data-theme='dark'] .overnight-info strong,
            html[data-theme='dark'] .activity-header {
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .meals-included-card {
                background: rgba(244, 195, 106, 0.12) !important;
                background-color: rgba(244, 195, 106, 0.12) !important;
                border-left-color: #f4c36a !important;
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .meals-included-card .fw-bold {
                color: #f8fafc !important;
            }
            html[data-theme='dark'] .meals-included-card .meal-badge {
                background-color: #f4c36a !important;
                color: #0f172a !important;
                font-weight: 700 !important;
            }
            .nc-media-box {
                background: transparent !important;
                border: 1px solid rgba(197, 149, 91, 0.25) !important;
            }
            html[data-theme='dark'] .nc-media-box {
                background: transparent !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
            }

            @media(max-width:900px){.nc-schedule-grid,.nc-cabin-grid{grid-template-columns:1fr}.nc-addon-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
            @media(max-width:520px){.nc-addon-grid{grid-template-columns:1fr}}
        </style>

        {{-- 1. Nile Cruise Details Section --}}
        @include('website.pages.packages.partials.nile_cruise.details_grid')

        {{-- 2. Schedule, Cabins & Route Section --}}
        @include('website.pages.packages.partials.nile_cruise.schedule_cabins_route')

        {{-- 3. Itinerary Section --}}
        @include('website.pages.packages.partials.nile_cruise.itinerary')

        {{-- 4. Includes / Excludes Section --}}
        @include('website.pages.packages.partials.nile_cruise.includes_excludes')

        {{-- 5. Pricing & Packages Section --}}
        @include('website.pages.packages.partials.nile_cruise.pricing')

        {{-- 6. Cruise Facilities Section --}}
        @include('website.pages.packages.partials.nile_cruise.facilities')

        {{-- 7. Important Information & Policies Section --}}
        @include('website.pages.packages.partials.nile_cruise.important_info')

        <script>
            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('.nc-duration-tab').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        const scope = btn.closest('#cruise-itineraries');
                        if (scope) {
                            scope.querySelectorAll('.nc-duration-tab').forEach(x => x.classList.remove('active'));
                            scope.querySelectorAll('.nc-duration-panel').forEach(x => x.classList.remove('active'));
                            btn.classList.add('active');
                            const target = document.getElementById(btn.dataset.ncDurationTarget);
                            if (target) {
                                target.classList.add('active');
                                target.querySelectorAll('[data-collapse-target]').forEach(function(trigger){
                                    const content = document.getElementById(trigger.dataset.collapseTarget);
                                    if (content && (content.classList.contains('open') || content.classList.contains('active'))) {
                                        content.style.maxHeight = content.scrollHeight + 'px';
                                    }
                                });
                            }
                        }
                    });
                });
            });
        </script>
    @endif
@endif
