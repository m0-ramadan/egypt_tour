<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageTag;
use App\Models\TourPackageDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageTypeContentService
{
    public function __construct(private readonly PackageTagNormalizer $tagNormalizer) {}

    /**
     * Save shared editor content for the three canonical tour types only.
     * Legacy package types stay untouched for backward compatibility.
     */
    public function syncFromRequest(Package $package, Request $request): void
    {
        if (!in_array($package->package_type, ['day_tour', 'travel_package', 'nile_cruise'], true)) {
            return;
        }

        $payload = (array) $request->input('experience', []);
        if (!empty($payload['_present'])) {
            $this->syncSharedPackageFields($package, $request, $payload);
            $this->syncAddons($package, $payload);
        }

        if ($request->has('highlights')) {
            $this->syncHighlights($package, $request->input('highlights', []));
        }

        if ($request->has('tags')) {
            $this->syncTags($package, $request->input('tags'));
        }

        if ($package->package_type === 'travel_package') {
            $this->syncTourPackageDetail($package, (array) $request->input('tour_package', []));
            $this->syncTourPackagePricing($package, $request);
        }
    }

    private function syncSharedPackageFields(Package $package, Request $request, array $payload): void
    {
        $brochurePath = $package->brochure_path;
        $ogImagePath = $package->og_image_path;

        if ($request->boolean('experience.remove_brochure') && $brochurePath) {
            Storage::disk('public')->delete($brochurePath);
            $brochurePath = null;
        }

        if ($request->hasFile('experience.brochure')) {
            if ($brochurePath) {
                Storage::disk('public')->delete($brochurePath);
            }
            $brochurePath = $request->file('experience.brochure')
                ->store("packages/{$package->id}/brochures", 'public');
        }

        if ($request->boolean('experience.remove_og_image') && $ogImagePath) {
            Storage::disk('public')->delete($ogImagePath);
            $ogImagePath = null;
        }

        if ($request->hasFile('experience.og_image')) {
            if ($ogImagePath) {
                Storage::disk('public')->delete($ogImagePath);
            }
            $ogImagePath = $request->file('experience.og_image')
                ->store("packages/{$package->id}/seo", 'public');
        }

        $departureTimes = $package->package_type === 'day_tour'
            ? $this->stringList($payload['departure_times'] ?? [])
            : [];

        $package->update([
            'what_to_bring' => $this->stringList($payload['what_to_bring'] ?? []),
            'on_tour_languages' => $this->stringList($payload['on_tour_languages'] ?? []),
            'operating_days' => $this->stringList($payload['operating_days'] ?? []),
            'departure_times' => $departureTimes,
            'tour_timezone' => $this->nullableString($payload['tour_timezone'] ?? null),
            'default_seat_capacity' => $this->nullableInt($payload['default_seat_capacity'] ?? null),
            'brochure_path' => $brochurePath,
            'promotional_videos' => $this->stringList($payload['promotional_videos'] ?? []),
            'deposit_policy' => $this->nullableString($payload['deposit_policy'] ?? null),
            'deposit_type' => $this->nullableString($payload['deposit_type'] ?? null),
            'deposit_value' => $this->nullableFloat($payload['deposit_value'] ?? null),
            'allowed_payment_method_ids' => $this->intList($payload['allowed_payment_method_ids'] ?? []),
            'focus_keyword' => $this->nullableString($payload['focus_keyword'] ?? null),
            'meta_keywords' => $this->stringList($payload['meta_keywords'] ?? []),
            'og_title' => $this->nullableString($payload['og_title'] ?? null),
            'og_description' => $this->nullableString($payload['og_description'] ?? null),
            'og_image_path' => $ogImagePath,
            'twitter_card' => $this->nullableString($payload['twitter_card'] ?? null),
            'twitter_title' => $this->nullableString($payload['twitter_title'] ?? null),
            'twitter_description' => $this->nullableString($payload['twitter_description'] ?? null),
            'robots_index' => array_key_exists('robots_index', $payload) ? (bool) $payload['robots_index'] : true,
            'robots_follow' => array_key_exists('robots_follow', $payload) ? (bool) $payload['robots_follow'] : true,
            'itinerary_mode' => $package->package_type === 'travel_package'
                ? ($this->nullableString($payload['itinerary_mode'] ?? null) ?: 'simple')
                : null,
            'group_pricing_tiers' => $this->normalizeGroupPricingTiers($payload['group_pricing_tiers'] ?? []),
        ]);
    }

    private function syncTourPackageDetail(Package $package, array $payload): void
    {
        if (empty($payload['_present']) && !$package->tourPackageDetail) {
            return;
        }

        TourPackageDetail::updateOrCreate(
            ['package_id' => $package->id],
            [
                'accommodation_standard' => $this->nullableString($payload['accommodation_standard'] ?? null),
                'meals_included' => $this->stringList($payload['meals_included'] ?? []),
                'flexible_itinerary' => !empty($payload['flexible_itinerary']),
                'additional_notes' => $this->nullableString($payload['additional_notes'] ?? null),
            ]
        );
    }

    private function syncAddons(Package $package, array $payload): void
    {
        if (!array_key_exists('addons', $payload)) {
            return;
        }

        $package->addons()->delete();

        foreach ((array) $payload['addons'] as $index => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $package->addons()->create([
                'title' => $title,
                'description' => $this->nullableString($row['description'] ?? null),
                'price' => $this->nullableFloat($row['price'] ?? null),
                'currency_id' => !empty($row['currency_id']) ? (int) $row['currency_id'] : $package->currency_id,
                'price_unit' => $this->nullableString($row['price_unit'] ?? null),
                'is_active' => array_key_exists('is_active', $row) ? !empty($row['is_active']) : true,
                'sort_order' => $index,
            ]);
        }
    }

    private function syncHighlights(Package $package, mixed $items): void
    {
        $package->highlights()->delete();

        if (is_string($items)) {
            $items = preg_split('/[\r\n]+/', $items) ?: [];
        }

        foreach ((array) $items as $index => $row) {
            $titleData = null;
            $descData = null;

            if (is_array($row)) {
                $rawTitle = $row['title'] ?? $row['text'] ?? $row['description'] ?? null;
                $rawDesc = $row['description'] ?? $row['title'] ?? $row['text'] ?? null;

                if (is_array($rawTitle)) {
                    $titleData = $rawTitle;
                } elseif (is_string($rawTitle) && trim($rawTitle) !== '') {
                    $t = trim($rawTitle);
                    $titleData = ['en' => $t, 'ar' => $t];
                }

                if (is_array($rawDesc)) {
                    $descData = $rawDesc;
                } elseif (is_string($rawDesc) && trim($rawDesc) !== '') {
                    $d = trim($rawDesc);
                    $descData = ['en' => $d, 'ar' => $d];
                }
            } elseif (is_string($row) && trim($row) !== '') {
                $t = trim($row);
                $titleData = ['en' => $t, 'ar' => $t];
                $descData = ['en' => $t, 'ar' => $t];
            }

            if (empty($titleData) && empty($descData)) {
                continue;
            }

            $titleData = $titleData ?: ($descData ?: ['en' => '', 'ar' => '']);
            $descData = $descData ?: $titleData;

            $package->highlights()->create([
                'title' => $titleData,
                'description' => $descData,
                'sort_order' => $index,
            ]);
        }
    }


    private function syncTags(Package $package, mixed $raw): void
    {
        $names = $this->tagNormalizer->normalizeList($raw);
        $ids = [];

        foreach ($names as $name) {
            $slug = $this->tagNormalizer->slug($name);

            $tag = PackageTag::firstOrCreate(
                ['slug' => $slug],
                ['name' => ['en' => $name, 'ar' => $name]]
            );
            $ids[] = $tag->id;
        }

        $package->tags()->sync(array_values(array_unique($ids)));
    }

    public function syncTourPackagePricing(Package $package, Request $request): void
    {
        if ($package->package_type !== 'travel_package' || !$request->has('tour_package_accommodations')) {
            return;
        }

        $accommodationsData = (array) $request->input('tour_package_accommodations', []);
        $keepAccIds = [];

        foreach ($accommodationsData as $accIndex => $accData) {
            $name = trim((string) ($accData['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $acc = $package->tourPackageAccommodations()->updateOrCreate(
                ['id' => $accData['id'] ?? null],
                [
                    'name' => $name,
                    'description' => $this->nullableString($accData['description'] ?? null),
                    'sort_order' => $accIndex,
                    'is_active' => array_key_exists('is_active', $accData) ? !empty($accData['is_active']) : true,
                ]
            );
            $keepAccIds[] = $acc->id;

            // Sync Seasons
            if (isset($accData['seasons']) && is_array($accData['seasons'])) {
                $keepSeasonIds = [];
                foreach ($accData['seasons'] as $sIndex => $sData) {
                    $sName = trim((string) (is_array($sData['name'] ?? null) ? ($sData['name']['en'] ?? '') : ($sData['name'] ?? '')));
                    if ($sName === '') {
                        continue;
                    }

                    $seasonNameJson = is_array($sData['name'] ?? null) ? $sData['name'] : ['en' => $sName, 'ar' => $sName];

                    $season = $acc->seasons()->updateOrCreate(
                        ['id' => $sData['id'] ?? null],
                        [
                            'package_id' => $package->id,
                            'name' => $seasonNameJson,
                            'date_from' => !empty($sData['date_from']) ? $sData['date_from'] : null,
                            'date_to' => !empty($sData['date_to']) ? $sData['date_to'] : null,
                            'currency_id' => !empty($sData['currency_id']) ? (int) $sData['currency_id'] : $package->currency_id,
                            'sort_order' => $sIndex,
                            'is_active' => array_key_exists('is_active', $sData) ? !empty($sData['is_active']) : true,
                        ]
                    );
                    $keepSeasonIds[] = $season->id;

                    // Sync Price Items
                    if (isset($sData['items']) && is_array($sData['items'])) {
                        $season->items()->delete();
                        foreach ($sData['items'] as $iIndex => $iData) {
                            $price = $this->nullableFloat($iData['price'] ?? null);
                            if ($price === null) continue;

                            $labelStr = trim((string) (is_array($iData['label'] ?? null) ? ($iData['label']['en'] ?? '') : ($iData['label'] ?? '')));
                            $labelJson = is_array($iData['label'] ?? null) ? $iData['label'] : ($labelStr !== '' ? ['en' => $labelStr, 'ar' => $labelStr] : null);

                            $season->items()->create([
                                'occupancy_type' => $this->nullableString($iData['occupancy_type'] ?? null),
                                'label' => $labelJson,
                                'price' => $price,
                                'price_unit' => $this->nullableString($iData['price_unit'] ?? 'per_person'),
                                'sort_order' => $iIndex,
                                'is_active' => array_key_exists('is_active', $iData) ? !empty($iData['is_active']) : true,
                            ]);
                        }
                    }
                }
                $acc->seasons()->whereNotIn('id', $keepSeasonIds)->delete();
            }

            // Sync Hotels
            if (isset($accData['hotels']) && is_array($accData['hotels'])) {
                $acc->hotels()->delete();
                foreach ($accData['hotels'] as $hIndex => $hData) {
                    $hotelName = trim((string) ($hData['hotel_name'] ?? ''));
                    if ($hotelName === '') continue;

                    $acc->hotels()->create([
                        'city_id' => !empty($hData['city_id']) ? (int) $hData['city_id'] : null,
                        'city_name' => $this->nullableString($hData['city_name'] ?? null),
                        'hotel_name' => $hotelName,
                        'star_rating' => $this->nullableInt($hData['star_rating'] ?? null),
                        'description' => $this->nullableString($hData['description'] ?? null),
                        'room_type' => $this->nullableString($hData['room_type'] ?? null),
                        'meal_plan' => $this->nullableString($hData['meal_plan'] ?? null),
                        'alternative_note' => $this->nullableString($hData['alternative_note'] ?? null),
                        'sort_order' => $hIndex,
                        'is_active' => array_key_exists('is_active', $hData) ? !empty($hData['is_active']) : true,
                    ]);
                }
            }
        }

        $package->tourPackageAccommodations()->whereNotIn('id', $keepAccIds)->delete();
    }

    private function normalizeGroupPricingTiers(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        $rows = [];
        foreach ((array) $value as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $min = isset($row['min']) && $row['min'] !== '' ? max(1, (int) $row['min']) : (isset($row['persons_count']) ? (int)$row['persons_count'] : null);
            $max = isset($row['max']) && $row['max'] !== '' ? max(1, (int) $row['max']) : null;
            $price = isset($row['price_per_person']) && $row['price_per_person'] !== '' ? max(0, (float) $row['price_per_person']) : (isset($row['price']) ? (float)$row['price'] : null);
            if ($min === null && $max === null && $price === null) {
                continue;
            }
            $rows[] = [
                'id' => (string) ($row['id'] ?? ('tier-' . ($index + 1))),
                'title' => trim((string) ($row['title'] ?? $row['label'] ?? '')),
                'label' => trim((string) ($row['label'] ?? $row['title'] ?? '')),
                'persons_count' => $min ?: 1,
                'min' => $min,
                'max' => $max,
                'persons_label' => trim((string) ($row['persons_label'] ?? $row['title'] ?? '')),
                'price_per_person' => $price,
                'badge' => trim((string) ($row['badge'] ?? $row['badge_label'] ?? '')),
                'badge_label' => trim((string) ($row['badge_label'] ?? $row['badge'] ?? '')),
                'is_featured' => !empty($row['is_featured']),
                'is_popular' => !empty($row['is_popular']),
                'is_best_value' => !empty($row['is_best_value']),
            ];
        }

        return $rows;
    }

    private function stringList(mixed $value): array
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

    private function intList(mixed $value): array
    {
        return collect((array) $value)
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
