<?php

namespace App\Services;

use App\Models\Cruise;
use App\Models\NileCruiseCabin;
use App\Models\NileCruiseDetail;
use App\Models\NileCruiseDuration;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NileCruisePackageService
{
    /**
     * Persist the Nile-only extension without changing the normal Package flow.
     * This method is intentionally a no-op for every non-Nile package type.
     */
    public function syncFromRequest(Package $package, Request $request): void
    {
        if ($package->package_type !== 'nile_cruise') {
            return;
        }

        $payload = (array) $request->input('nile_cruise', []);

        // Only destructive-sync repeaters when the Nile Cruise editor was actually
        // present in the submitted form. This protects old cruises and partial forms.
        if (!empty($payload['_present'])) {
            $payload += [
                'route_city_ids' => [],
                'schedules' => [],
                'cabins' => [],
                'durations' => [],
            ];
        }

        $this->syncDetail($package, $request, $payload);
        $this->syncCruiseFacilities($package, $payload);
        $this->syncRouteCities($package, $payload);
        $this->syncSchedules($package, $payload);
        $this->syncAddons($package, $payload);
        $cabinsByClientKey = $this->syncCabins($package, $request, $payload);
        $this->syncDurations($package, $payload, $cabinsByClientKey);
        $this->syncLegacyCruiseRecord($package, $payload);
        $this->recalculateStartingPrice($package);
        $this->syncPackageSummaryFields($package);
    }

    private function syncDetail(Package $package, Request $request, array $payload): void
    {
        // If no advanced editor was posted and there is no detail yet, don't create
        // a blank child row just because the package happens to be a Nile Cruise.
        if (empty($payload['_present']) && !$package->nileCruiseDetail) {
            return;
        }

        $existing = $package->nileCruiseDetail;
        $factSheetPath = $existing?->fact_sheet_path;
        $socialImagePath = $existing?->social_image_path;

        if ($request->boolean('nile_cruise.remove_fact_sheet') && $factSheetPath) {
            Storage::disk('public')->delete($factSheetPath);
            $factSheetPath = null;
        }

        if ($request->hasFile('nile_cruise.fact_sheet')) {
            if ($factSheetPath) {
                Storage::disk('public')->delete($factSheetPath);
            }

            $factSheetPath = $request->file('nile_cruise.fact_sheet')
                ->store("packages/{$package->id}/nile-cruise/fact-sheets", 'public');
        }

        if ($request->boolean('nile_cruise.remove_social_image') && $socialImagePath) {
            Storage::disk('public')->delete($socialImagePath);
            $socialImagePath = null;
        }

        if ($request->hasFile('nile_cruise.social_image')) {
            if ($socialImagePath) {
                Storage::disk('public')->delete($socialImagePath);
            }
            $socialImagePath = $request->file('nile_cruise.social_image')
                ->store("packages/{$package->id}/nile-cruise/seo", 'public');
        }

        NileCruiseDetail::updateOrCreate(
            ['package_id' => $package->id],
            [
                'decks' => $this->nullableInt($payload['decks'] ?? null),
                'sun_beds' => $this->nullableInt($payload['sun_beds'] ?? null),
                'sun_deck_pergolas' => $this->nullableInt($payload['sun_deck_pergolas'] ?? null),
                'tour_style' => $this->nullableString($payload['tour_style'] ?? null),
                'route_summary' => $this->nullableString($payload['route_summary'] ?? null),
                'all_inclusive' => !empty($payload['all_inclusive']),
                // Shared Tour Type fields are now edited on Package itself. Preserve legacy
                // Nile values when the old Nile-only controls are not submitted.
                'what_to_bring' => array_key_exists('what_to_bring', $payload) ? $this->stringList($payload['what_to_bring']) : (array) ($existing?->what_to_bring ?? []),
                'on_tour_languages' => array_key_exists('on_tour_languages', $payload) ? $this->stringList($payload['on_tour_languages']) : (array) ($existing?->on_tour_languages ?? []),
                'operating_days' => array_key_exists('operating_days', $payload) ? $this->stringList($payload['operating_days']) : (array) ($existing?->operating_days ?? []),
                'promotional_videos' => array_key_exists('promotional_videos', $payload) ? $this->stringList($payload['promotional_videos']) : (array) ($existing?->promotional_videos ?? []),
                'timezone' => array_key_exists('timezone', $payload) ? $this->nullableString($payload['timezone']) : $existing?->timezone,
                'deposit_policy' => array_key_exists('deposit_policy', $payload) ? $this->nullableString($payload['deposit_policy']) : $existing?->deposit_policy,
                'deposit_type' => array_key_exists('deposit_type', $payload) ? $this->nullableString($payload['deposit_type']) : $existing?->deposit_type,
                'deposit_value' => array_key_exists('deposit_value', $payload) ? $this->nullableFloat($payload['deposit_value']) : $existing?->deposit_value,
                'allowed_payment_method_ids' => array_key_exists('allowed_payment_method_ids', $payload) ? $this->intList($payload['allowed_payment_method_ids']) : (array) ($existing?->allowed_payment_method_ids ?? []),
                'focus_keyword' => array_key_exists('focus_keyword', $payload) ? $this->nullableString($payload['focus_keyword']) : $existing?->focus_keyword,
                'meta_keywords' => array_key_exists('meta_keywords', $payload) ? $this->stringList($payload['meta_keywords']) : (array) ($existing?->meta_keywords ?? []),
                'og_title' => array_key_exists('og_title', $payload) ? $this->nullableString($payload['og_title']) : $existing?->og_title,
                'og_description' => array_key_exists('og_description', $payload) ? $this->nullableString($payload['og_description']) : $existing?->og_description,
                'social_image_path' => $socialImagePath,
                'twitter_card' => array_key_exists('twitter_card', $payload) ? $this->nullableString($payload['twitter_card']) : $existing?->twitter_card,
                'twitter_title' => array_key_exists('twitter_title', $payload) ? $this->nullableString($payload['twitter_title']) : $existing?->twitter_title,
                'twitter_description' => array_key_exists('twitter_description', $payload) ? $this->nullableString($payload['twitter_description']) : $existing?->twitter_description,
                'robots_index' => array_key_exists('robots_index', $payload) ? (bool) $payload['robots_index'] : (bool) ($existing?->robots_index ?? true),
                'robots_follow' => array_key_exists('robots_follow', $payload) ? (bool) $payload['robots_follow'] : (bool) ($existing?->robots_follow ?? true),
                'pickup_notes' => $this->nullableString($payload['pickup_notes'] ?? null),
                'dropoff_notes' => $this->nullableString($payload['dropoff_notes'] ?? null),
                'fact_sheet_path' => $factSheetPath,
                'additional_notes' => $this->nullableString($payload['additional_notes'] ?? null),
            ]
        );
    }

    private function syncCruiseFacilities(Package $package, array $payload): void
    {
        if (!isset($payload['facility_titles']) || !is_array($payload['facility_titles'])) {
            return;
        }

        $presetTitles = collect($this->facilityPresets());
        $legacyTitles = collect(['Lounge', 'Dining Room', 'Sun Deck']);
        $managedTitles = $presetTitles->merge($legacyTitles)->unique()->values();

        $selected = collect((array) $payload['facility_titles'])
            ->map(fn ($title) => trim((string) $title))
            ->filter(fn ($title) => $managedTitles->containsStrict($title))
            ->unique()
            ->values();

        // Reuse package_facilities. Only our Nile presets are managed here so
        // manually added/general Package facilities remain untouched.
        $package->facilities()->whereIn('title', $managedTitles->all())->delete();

        $sortOrder = (int) ($package->facilities()->max('sort_order') ?? -1) + 1;
        foreach ($selected as $offset => $title) {
            $package->facilities()->create([
                'title' => $title,
                'sort_order' => $sortOrder + $offset,
            ]);
        }
    }

    private function syncRouteCities(Package $package, array $payload): void
    {
        if (!array_key_exists('route_city_ids', $payload)) {
            return;
        }

        $cityIds = collect((array) $payload['route_city_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        // Empty advanced route leaves the normal PackageController city resolution
        // untouched instead of destroying it.
        if ($cityIds->isEmpty()) {
            return;
        }

        $syncData = [];
        foreach ($cityIds as $index => $cityId) {
            $syncData[$cityId] = [
                'stop_order' => $index,
                'is_primary' => $index === 0,
                'nights' => null,
            ];
        }

        $package->cities()->sync($syncData);
    }

    private function syncSchedules(Package $package, array $payload): void
    {
        if (!array_key_exists('schedules', $payload)) {
            return;
        }

        $package->nileCruiseSchedules()->delete();

        foreach ((array) $payload['schedules'] as $index => $schedule) {
            if (!is_array($schedule)) {
                continue;
            }

            $hasContent = collect([
                $schedule['departure_day'] ?? null,
                $schedule['departure_city_id'] ?? null,
                $schedule['arrival_city_id'] ?? null,
                $schedule['direction'] ?? null,
                $schedule['notes'] ?? null,
            ])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();

            if (!$hasContent) {
                continue;
            }

            $package->nileCruiseSchedules()->create([
                'departure_day' => $this->nullableString($schedule['departure_day'] ?? null),
                'departure_city_id' => $this->nullableInt($schedule['departure_city_id'] ?? null),
                'arrival_city_id' => $this->nullableInt($schedule['arrival_city_id'] ?? null),
                'direction' => $this->nullableString($schedule['direction'] ?? null),
                'notes' => $this->nullableString($schedule['notes'] ?? null),
                'is_active' => array_key_exists('is_active', $schedule) ? (bool) $schedule['is_active'] : true,
                'sort_order' => $index,
            ]);
        }
    }

    private function syncAddons(Package $package, array $payload): void
    {
        if (!array_key_exists('addons', $payload)) {
            return;
        }

        $package->nileCruiseAddons()->delete();

        foreach ((array) $payload['addons'] as $index => $addon) {
            if (!is_array($addon)) {
                continue;
            }

            $name = trim((string) ($addon['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $package->nileCruiseAddons()->create([
                'name' => $name,
                'description' => $this->nullableString($addon['description'] ?? null),
                'price' => max(0, (float) ($addon['price'] ?? 0)),
                'currency_id' => $this->nullableInt($addon['currency_id'] ?? $package->currency_id),
                'is_active' => array_key_exists('is_active', $addon) ? (bool) $addon['is_active'] : true,
                'sort_order' => $index,
            ]);
        }
    }

    /** @return array<string,int> */
    private function syncCabins(Package $package, Request $request, array $payload): array
    {
        if (!array_key_exists('cabins', $payload)) {
            return $package->nileCruiseCabins
                ->mapWithKeys(fn ($cabin) => [(string) $cabin->id => $cabin->id])
                ->all();
        }

        $oldCabins = $package->nileCruiseCabins->keyBy('id');
        $package->nileCruiseCabins()->delete();
        $createdByKey = [];
        $retainedImagePaths = [];

        foreach ((array) $payload['cabins'] as $index => $cabin) {
            if (!is_array($cabin) || trim((string) ($cabin['name'] ?? '')) === '') {
                continue;
            }

            $clientKey = (string) ($cabin['client_key'] ?? $index);
            $imagePath = $this->nullableString($cabin['existing_image'] ?? null);
            $oldId = $this->nullableInt($cabin['id'] ?? null);

            if (!$imagePath && $oldId && $oldCabins->has($oldId)) {
                $imagePath = $oldCabins[$oldId]->featured_image;
            }

            if ($request->hasFile("nile_cruise.cabins.{$index}.image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }

                $imagePath = $request->file("nile_cruise.cabins.{$index}.image")
                    ->store("packages/{$package->id}/nile-cruise/cabins", 'public');
            }

            if ($imagePath) {
                $retainedImagePaths[] = $imagePath;
            }

            $created = $package->nileCruiseCabins()->create([
                'name' => trim((string) $cabin['name']),
                'quantity' => $this->nullableInt($cabin['quantity'] ?? null),
                'bed_type' => $this->nullableString($cabin['bed_type'] ?? null),
                'size_sqm' => $this->nullableFloat($cabin['size_sqm'] ?? null),
                'max_adults' => $this->nullableInt($cabin['max_adults'] ?? null),
                'max_children' => $this->nullableInt($cabin['max_children'] ?? null),
                'has_private_bathroom' => !empty($cabin['has_private_bathroom']),
                'has_private_terrace' => !empty($cabin['has_private_terrace']),
                'amenities' => $this->stringList($cabin['amenities'] ?? []),
                'description' => $this->nullableString($cabin['description'] ?? null),
                'featured_image' => $imagePath,
                'sort_order' => $index,
            ]);

            $createdByKey[$clientKey] = $created->id;
            if ($oldId) {
                $createdByKey[(string) $oldId] = $created->id;
            }
        }

        // Delete images belonging to cabins explicitly removed in this submitted editor.
        foreach ($oldCabins as $oldCabin) {
            if ($oldCabin->featured_image && !in_array($oldCabin->featured_image, $retainedImagePaths, true)) {
                Storage::disk('public')->delete($oldCabin->featured_image);
            }
        }

        return $createdByKey;
    }

    /** @param array<string,int> $cabinsByClientKey */
    private function syncDurations(Package $package, array $payload, array $cabinsByClientKey): void
    {
        if (!array_key_exists('durations', $payload)) {
            return;
        }

        $package->nileCruiseDurations()->delete();
        $defaultAssigned = false;

        foreach ((array) $payload['durations'] as $durationIndex => $durationData) {
            if (!is_array($durationData)) {
                continue;
            }

            $days = (int) ($durationData['days'] ?? 0);
            $title = trim((string) ($durationData['title'] ?? ''));
            if ($days < 1 && $title === '') {
                continue;
            }

            if ($days < 1) {
                continue;
            }

            $nights = max(0, (int) ($durationData['nights'] ?? max(0, $days - 1)));
            if ($title === '') {
                $title = "{$nights} Nights / {$days} Days";
            }

            $isDefault = !$defaultAssigned && !empty($durationData['is_default']);
            if ($isDefault) {
                $defaultAssigned = true;
            }

            $duration = $package->nileCruiseDurations()->create([
                'title' => $title,
                'days' => $days,
                'nights' => $nights,
                'direction' => $this->nullableString($durationData['direction'] ?? null),
                'departure_city_id' => $this->nullableInt($durationData['departure_city_id'] ?? null),
                'arrival_city_id' => $this->nullableInt($durationData['arrival_city_id'] ?? null),
                'departure_day' => $this->nullableString($durationData['departure_day'] ?? null),
                'currency_id' => $this->nullableInt($durationData['currency_id'] ?? $package->currency_id),
                'is_default' => $isDefault,
                'is_active' => array_key_exists('is_active', $durationData) ? (bool) $durationData['is_active'] : true,
                'sort_order' => $durationIndex,
            ]);

            $this->createDurationItinerary($duration, (array) ($durationData['itinerary'] ?? []));
            $this->createDurationSeasons(
                $package,
                $duration,
                (array) ($durationData['seasons'] ?? []),
                $cabinsByClientKey
            );
        }

        if (!$defaultAssigned) {
            $package->nileCruiseDurations()->orderBy('sort_order')->first()?->update(['is_default' => true]);
        }
    }

    private function createDurationItinerary(NileCruiseDuration $duration, array $days): void
    {
        foreach ($days as $index => $dayData) {
            if (!is_array($dayData)) {
                continue;
            }

            $title = trim((string) ($dayData['title'] ?? ''));
            $description = trim((string) ($dayData['description'] ?? ''));
            $meals = $this->stringList($dayData['meals'] ?? []);
            $overnight = $this->nullableString($dayData['overnight'] ?? null);
            $activities = (array) ($dayData['activities'] ?? []);

            $hasActivityContent = collect($activities)->contains(function ($activityData) {
                return is_array($activityData) && (
                    trim((string) ($activityData['title'] ?? '')) !== '' ||
                    trim((string) ($activityData['description'] ?? '')) !== '' ||
                    !empty($activityData['attraction_id'])
                );
            });

            if ($title === '' && $description === '' && $meals === [] && $overnight === null && !$hasActivityContent) {
                continue;
            }

            $dayNumber = (int) ($dayData['day_number'] ?? ($index + 1));
            if ($dayNumber < 1) {
                $dayNumber = $index + 1;
            }

            $day = $duration->itineraryDays()->create([
                'day_number' => $dayNumber,
                'title' => $this->translated($title),
                'description' => $this->translated($description),
                'meals' => $meals,
                'overnight' => $this->translated($overnight),
                'sort_order' => $index,
            ]);

            foreach ($activities as $activityIndex => $activityData) {
                if (!is_array($activityData)) {
                    continue;
                }

                $activityTitle = trim((string) ($activityData['title'] ?? ''));
                $activityDescription = trim((string) ($activityData['description'] ?? ''));
                $attractionId = $this->nullableInt($activityData['attraction_id'] ?? null);

                if ($activityTitle === '' && $activityDescription === '' && $attractionId === null) {
                    continue;
                }

                $day->activities()->create([
                    'title' => $this->translated($activityTitle),
                    'description' => $this->translated($activityDescription),
                    'attraction_id' => $attractionId,
                    'sort_order' => $activityIndex,
                ]);
            }
        }
    }

    /** @param array<string,int> $cabinsByClientKey */
    private function createDurationSeasons(
        Package $package,
        NileCruiseDuration $duration,
        array $seasons,
        array $cabinsByClientKey
    ): void {
        $durationMin = null;

        foreach ($seasons as $seasonIndex => $seasonData) {
            if (!is_array($seasonData)) {
                continue;
            }

            $items = collect((array) ($seasonData['items'] ?? []))
                ->filter(fn ($item) => is_array($item) && ($item['price'] ?? '') !== '' && is_numeric($item['price']))
                ->values();

            $seasonName = $this->nullableString($seasonData['season_name'] ?? null);
            $seasonNotes = $this->nullableString($seasonData['notes'] ?? null);
            $hasSeasonContent = $seasonName !== null
                || !empty($seasonData['date_from'])
                || !empty($seasonData['date_to'])
                || $seasonNotes !== null
                || $items->isNotEmpty();

            if (!$hasSeasonContent) {
                continue;
            }

            $season = $duration->seasonPrices()->create([
                'package_id' => $package->id,
                'season_name' => $this->translated($seasonName),
                'date_from' => $seasonData['date_from'] ?? null,
                'date_to' => $seasonData['date_to'] ?? null,
                'currency_id' => $this->nullableInt(
                    $seasonData['currency_id'] ?? $duration->currency_id ?? $package->currency_id
                ),
                'notes' => $this->translated($seasonNotes),
                'is_active' => array_key_exists('is_active', $seasonData) ? (bool) $seasonData['is_active'] : true,
                'sort_order' => $seasonIndex,
            ]);

            foreach ($items as $itemIndex => $item) {
                $clientCabinKey = (string) ($item['cabin_key'] ?? '');
                $price = (float) $item['price'];

                $season->items()->create([
                    'nile_cruise_cabin_id' => $clientCabinKey !== ''
                        ? ($cabinsByClientKey[$clientCabinKey] ?? null)
                        : null,
                    'occupancy_type' => $this->nullableString($item['occupancy_type'] ?? null),
                    'label' => $this->translated($this->nullableString($item['label'] ?? null)),
                    'price' => $price,
                    'sort_order' => $itemIndex,
                ]);

                $durationMin = $durationMin === null ? $price : min($durationMin, $price);
            }
        }

        if ($durationMin !== null) {
            $duration->update(['start_from_price' => $durationMin]);
        }
    }

    /**
     * Keep the legacy cruises table synchronized because the existing website/search
     * already reads Package::cruise. This is integration, not a second source of truth.
     */
    private function syncLegacyCruiseRecord(Package $package, array $payload): void
    {
        $package->load([
            'cities',
            'facilities',
            'nileCruiseSchedules.departureCity',
            'nileCruiseSchedules.arrivalCity',
            'nileCruiseCabins',
            'nileCruiseDetail',
        ]);

        $routeCities = $package->cities
            ->sortBy(fn ($city) => $city->pivot?->stop_order ?? 0)
            ->values();

        $routeFrom = $routeCities->first()?->display_name;
        $routeTo = $routeCities->last()?->display_name;

        $sailingDays = $package->nileCruiseSchedules
            ->where('is_active', true)
            ->pluck('departure_day')
            ->filter()
            ->unique()
            ->implode(', ');

        if ($sailingDays === '') {
            $sailingDays = collect((array) $package->nileCruiseDetail?->operating_days)->filter()->implode(', ');
        }

        $shipName = $this->nullableString($payload['ship_name'] ?? null)
            ?: $package->cruise?->ship_name
            ?: $this->packageTitle($package)
            ?: 'Nile Cruise';

        Cruise::updateOrCreate(
            ['package_id' => $package->id],
            [
                'ship_name' => $shipName,
                'cruise_class' => $this->nullableString($payload['cruise_class'] ?? null)
                    ?: $package->cruise?->cruise_class,
                'route_from' => $routeFrom ?: $package->cruise?->route_from,
                'route_to' => $routeTo ?: $package->cruise?->route_to,
                'sailing_days' => $sailingDays !== '' ? $sailingDays : $package->cruise?->sailing_days,
                'star_rating' => $this->nullableInt($payload['star_rating'] ?? null)
                    ?? $package->cruise?->star_rating,
                'cabin_count' => $package->nileCruiseCabins->sum(fn ($cabin) => (int) ($cabin->quantity ?: 0)) ?: null,
                'onboard_features' => $package->facilities->pluck('title')->filter()->values()->all(),
            ]
        );
    }

    private function syncPackageSummaryFields(Package $package): void
    {
        $package->load([
            'nileCruiseDetail',
            'nileCruiseSchedules.departureCity',
            'nileCruiseSchedules.arrivalCity',
            'nileCruiseDurations',
            'cities',
            'cruise',
        ]);

        $updates = [];
        $durations = $package->nileCruiseDurations
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->values();

        if ($durations->isNotEmpty()) {
            $defaultDuration = $durations->firstWhere('is_default', true) ?: $durations->first();
            $updates['duration_type'] = 'days';
            $updates['duration_days'] = $defaultDuration?->days;
            $updates['duration_nights'] = $defaultDuration?->nights;
            $updates['duration_hours'] = null;
            $updates['duration_text'] = $durations->pluck('title')->filter()->implode(' / ');
        }

        $schedules = $package->nileCruiseSchedules
            ->where('is_active', true)
            ->sortBy('sort_order');

        if ($schedules->isNotEmpty()) {
            $scheduleText = $schedules->map(function ($schedule) {
                $parts = array_filter([
                    $schedule->departure_day,
                    $schedule->departureCity?->display_name
                        ? 'from ' . $schedule->departureCity->display_name
                        : null,
                ]);
                return implode(' ', $parts);
            })->filter()->implode(' · ');

            if ($scheduleText !== '') {
                $updates['schedule_text'] = $this->translated($scheduleText);
            }
        }

        $routeText = $this->nullableString($package->nileCruiseDetail?->route_summary);
        if ($routeText === null && $package->cities->isNotEmpty()) {
            $routeText = $package->cities
                ->sortBy(fn ($city) => $city->pivot?->stop_order ?? 0)
                ->map(fn ($city) => $city->display_name)
                ->filter()
                ->implode(' / ');
        }

        if ($routeText) {
            $updates['route_text'] = $routeText;
        }

        if ($updates !== []) {
            $package->forceFill($updates)->save();
        }
    }

    public function recalculateStartingPrice(Package $package): void
    {
        $durationPrices = $package->nileCruiseDurations()
            ->where('is_active', true)
            ->whereNotNull('start_from_price')
            ->pluck('start_from_price')
            ->map(fn ($price) => (float) $price)
            ->filter(fn ($price) => $price > 0);

        if ($durationPrices->isEmpty()) {
            return;
        }

        $itemMax = (float) $package->nileCruiseDurations()
            ->join('nile_cruise_season_prices', 'nile_cruise_durations.id', '=', 'nile_cruise_season_prices.nile_cruise_duration_id')
            ->join('nile_cruise_season_price_items', 'nile_cruise_season_prices.id', '=', 'nile_cruise_season_price_items.nile_cruise_season_price_id')
            ->where('nile_cruise_durations.package_id', $package->id)
            ->where('nile_cruise_durations.is_active', true)
            ->where('nile_cruise_season_prices.is_active', true)
            ->max('nile_cruise_season_price_items.price');

        $min = (float) $durationPrices->min();
        $max = $itemMax > 0 ? $itemMax : (float) $durationPrices->max();

        $package->forceFill([
            'start_from_price' => $min,
            'price_from' => $min,
            'price_to' => max($min, $max),
        ])->save();
    }

    /** @return string[] */
    public static function facilityPresets(): array
    {
        return [
            'WiFi',
            'Swimming Pool',
            'Air Conditioning',
            'Private Bathroom with Shower',
            'TV / Satellite Channels',
            'Mini Bar',
            'Doctor Available 24 Hours',
            'Gift Shop',
            'Lounge & Sun Bar',
            'Dining Room / Restaurant',
            'Sun Deck & Pergolas',
            'Sun Beds',
            'Gymnasium / Fitness Center',
            'Panoramic Windows',
            'Laundry Service & Housekeeping',
            'Safety Deposit Box',
        ];
    }

    private function packageTitle(Package $package): ?string
    {
        $value = $package->getRawOriginal('title') ?? $package->title;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }
        if (is_array($value)) {
            return $this->nullableString($value[app()->getLocale()] ?? $value['en'] ?? reset($value));
        }
        return $this->nullableString($value);
    }

    private function translated(?string $value): ?array
    {
        $value = $this->nullableString($value);
        if ($value === null) {
            return null;
        }

        return ['en' => $value, 'ar' => $value];
    }

    private function stringList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        return collect((array) $value)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function intList($value): array
    {
        return collect((array) $value)
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableInt($value): ?int
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    private function nullableFloat($value): ?float
    {
        return ($value === null || $value === '') ? null : (float) $value;
    }
}
