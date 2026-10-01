@if ($package->package_type === 'nile_cruise')
    @php
        $ncDetail = $package->nileCruiseDetail;
        $ncSchedules = $package->nileCruiseSchedules?->where('is_active', true) ?? collect();
        $ncCabins = $package->nileCruiseCabins ?? collect();
        $ncLegacyAddons = $package->nileCruiseAddons?->where('is_active', true) ?? collect();
        $ncAddons = isset($addons) && $addons->isNotEmpty() ? $addons : $ncLegacyAddons;
        $ncOperatingDays =
            isset($operatingDays) && $operatingDays->isNotEmpty()
                ? $operatingDays
                : collect((array) ($ncDetail?->operating_days ?? []));
        $ncLanguages =
            isset($onTourLanguages) && $onTourLanguages->isNotEmpty()
                ? $onTourLanguages
                : collect((array) ($ncDetail?->on_tour_languages ?? []));
        $ncWhatToBring =
            isset($whatToBring) && $whatToBring->isNotEmpty()
                ? $whatToBring
                : collect((array) ($ncDetail?->what_to_bring ?? []));
        $ncVideos =
            isset($promotionalVideos) && $promotionalVideos->isNotEmpty()
                ? $promotionalVideos
                : collect((array) ($ncDetail?->promotional_videos ?? []));
        $ncDepositPolicy = $package->deposit_policy ?: $ncDetail?->deposit_policy ?? null;
        $ncDepositType = $package->deposit_type ?: $ncDetail?->deposit_type ?? null;
        $ncDepositValue = $package->deposit_value ?? ($ncDetail?->deposit_value ?? null);
        $ncCruise = $package->cruise;
        $ncDurations = $package->nileCruiseDurations?->where('is_active', true)->values() ?? collect();
        $ncRoute = $package->cities?->sortBy(fn($city) => $city->pivot?->stop_order ?? 0)->values() ?? collect();

        $hasNcDetailData =
            $ncDetail &&
            (!empty(trim($ncDetail->route_summary ?? '')) ||
                !is_null($ncDetail->all_inclusive) ||
                !empty(trim($ncDetail->tour_style ?? '')) ||
                !empty($ncDetail->decks) ||
                !empty($ncDetail->sun_beds) ||
                !empty($ncDetail->sun_deck_pergolas) ||
                !empty($ncDetail->pickup_notes) ||
                !empty($ncDetail->dropoff_notes) ||
                !empty($ncDetail->fact_sheet_path) ||
                !empty($ncDetail->timezone) ||
                collect((array) $ncDetail->operating_days)->filter(fn($d) => !empty(trim((string) $d)))->isNotEmpty() ||
                collect((array) $ncDetail->on_tour_languages)
                    ->filter(fn($l) => !empty(trim((string) $l)))
                    ->isNotEmpty() ||
                collect((array) $ncDetail->what_to_bring)->filter(fn($w) => !empty(trim((string) $w)))->isNotEmpty() ||
                collect((array) $ncDetail->promotional_videos)
                    ->filter(fn($v) => !empty(trim((string) $v)))
                    ->isNotEmpty());

        $hasNcCruiseData =
            $ncCruise &&
            (!empty(trim($ncCruise->ship_name ?? '')) ||
                !empty(trim($ncCruise->cruise_class ?? '')) ||
                (!empty($ncCruise->star_rating) && $ncCruise->star_rating > 0));

        $hasInclusionsExclusions =
            (!empty($included) && $included->isNotEmpty()) ||
            (!empty($excluded) && $excluded->isNotEmpty()) ||
            (!empty($inclusions) && count($inclusions) > 0) ||
            (!empty($exclusions) && count($exclusions) > 0);
        $hasPoliciesData =
            !empty($package->getTranslation('children_policy')) ||
            !empty($package->getTranslation('cancellation_policy')) ||
            !empty($package->getTranslation('terms_conditions')) ||
            !empty($package->getTranslation('pickup_policy'));

        $hasFacilitiesData = !empty($facilities) && $facilities->isNotEmpty();
        $hasNcExtendedData =
            $hasNcDetailData ||
            $hasNcCruiseData ||
            $ncSchedules->isNotEmpty() ||
            $ncCabins->isNotEmpty() ||
            $ncAddons->isNotEmpty() ||
            $ncDurations->isNotEmpty() ||
            ($package->tourPackageAccommodations && $package->tourPackageAccommodations->isNotEmpty()) ||
            ($package->package_type !== 'day_tour' &&
                $package->tourPackageAccommodations &&
                $package->tourPackageAccommodations->isNotEmpty()) ||
            $hasInclusionsExclusions ||
            $hasPoliciesData ||
            $hasFacilitiesData;
    @endphp

    @if ($hasNcExtendedData)
        @vite('resources/css/pages/nile-cruise-details.css')

        {{-- 1. Nile Cruise Details Section --}}
        @include('website.pages.packages.partials.nile_cruise.details_grid')

        {{-- 2. Schedule, Cabins & Route Section --}}
        @include('website.pages.packages.partials.nile_cruise.schedule_cabins_route')

        {{-- 3. Itinerary Section --}}
        @include('website.pages.packages.partials.nile_cruise.itinerary')

        {{-- 4. Pricing & Packages Section --}}
        @include('website.pages.packages.partials.nile_cruise.pricing')

        {{-- 5. Important Information & Policies Section --}}
        @include('website.pages.packages.partials.nile_cruise.important_info')

        {{-- 6. Includes / Excludes Section --}}
        @include('website.pages.packages.partials.nile_cruise.includes_excludes')

        {{-- 7. Cruise Facilities Section --}}
        @include('website.pages.packages.partials.nile_cruise.facilities')

        @vite('resources/js/pages/nile-cruise-details.js')
    @endif
@endif
