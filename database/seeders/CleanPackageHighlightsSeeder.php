<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Rebuild package highlights from the package's own structured content.
 *
 * The legacy import copied the same marketing paragraphs to a large number of
 * tours (and also mixed inclusions into highlights).  Keeping a hard-coded list
 * of replacements would repeat the same problem, so this seeder derives every
 * list from its package's attractions, itinerary and destinations instead.
 */
class CleanPackageHighlightsSeeder extends Seeder
{
    private const MAX_HIGHLIGHTS = 9;

    public function run(): void
    {
        $updated = 0;
        $skipped = 0;
        $dayToursCleaned = 0;

        Package::query()
            ->with([
                'highlights',
                'packageAttractions.attraction',
                'itineraries',
                'cities',
            ])
            ->orderBy('id')
            ->chunkById(100, function (Collection $packages) use (&$updated, &$skipped, &$dayToursCleaned): void {
                foreach ($packages as $package) {
                    if ($package->package_type === 'day_tour') {
                        $this->removeDayTourAccommodation($package);
                        $dayToursCleaned++;
                    }

                    $highlights = $this->highlightsFor($package);

                    // Never erase the only available content on an incomplete draft.
                    if ($highlights->isEmpty()) {
                        $skipped++;
                        continue;
                    }

                    DB::transaction(function () use ($package, $highlights): void {
                        $package->highlights()->delete();

                        foreach ($highlights as $index => $highlight) {
                            $package->highlights()->create([
                                'title' => $highlight['title'],
                                'description' => $highlight['description'],
                                'icon' => 'las la-check-circle',
                                'sort_order' => $index + 1,
                            ]);
                        }
                    });

                    $updated++;
                }
            });

        $this->command?->info(
            "Organized highlights for {$updated} packages; cleaned accommodation from {$dayToursCleaned} day tours; skipped {$skipped} incomplete packages."
        );
    }

    private function removeDayTourAccommodation(Package $package): void
    {
        DB::transaction(function () use ($package): void {
            // Day tours do not contain overnight stays. Clear historical data
            // left by legacy imports from every accommodation data source.
            $package->itineraries()->update(['accommodation' => null]);
            $package->tourPackageAccommodations()->delete();
            $package->tourPackageDetail()->update(['accommodation_standard' => null]);

            $accommodationHighlightIds = $package->highlights
                ->filter(function ($highlight): bool {
                    $title = $this->translations($this->rawValue($highlight, 'title'));
                    $description = $this->translations($this->rawValue($highlight, 'description'));

                    return $this->isAccommodationHighlight(
                        $this->normalized(implode(' ', $title).' '.implode(' ', $description))
                    );
                })
                ->pluck('id')
                ->filter();

            if ($accommodationHighlightIds->isNotEmpty()) {
                $package->highlights()->whereKey($accommodationHighlightIds)->delete();
            }
        });
    }

    /**
     * @return Collection<int, array{title: array<string, string>, description: array<string, string>}>
     */
    private function highlightsFor(Package $package): Collection
    {
        $items = collect();

        // Named sights are the clearest, most useful tour highlights.
        foreach ($package->packageAttractions as $item) {
            $title = $this->translations($this->rawValue($item, 'title'));
            if ($this->translationsAreBlank($title) && $item->attraction) {
                $title = $this->translations($this->rawValue($item->attraction, 'name'));
            }

            $this->pushUnique(
                $items,
                $title,
                $this->plainTextTranslations($this->translations($this->rawValue($item, 'teaser')))
            );
        }

        // Itinerary headings make the result specific even when no attractions
        // were linked during the old import.
        foreach ($package->itineraries as $day) {
            $this->pushUnique(
                $items,
                $this->translations($this->rawValue($day, 'title')),
                $this->plainTextTranslations($this->translations($this->rawValue($day, 'description')))
            );
        }

        // Destinations are a final structured fallback, ordered by the route.
        foreach ($package->cities->sortBy(fn ($city) => (int) ($city->pivot->stop_order ?? 0)) as $city) {
            $this->pushUnique(
                $items,
                $this->translations($this->rawValue($city, 'name')),
                $this->plainTextTranslations($this->translations($this->rawValue($city, 'short_description')))
            );
        }

        // Retain genuinely package-specific legacy entries only as a last
        // fallback. Known imported boilerplate and inclusion-like rows are not
        // allowed back into the rebuilt section.
        foreach ($package->highlights as $highlight) {
            $title = $this->translations($this->rawValue($highlight, 'title'));
            $description = $this->translations($this->rawValue($highlight, 'description'));
            $searchable = $this->normalized(implode(' ', $title).' '.implode(' ', $description));

            if (
                $this->isLegacyBoilerplate($searchable)
                || ($package->package_type === 'day_tour' && $this->isAccommodationHighlight($searchable))
            ) {
                continue;
            }

            $this->pushUnique($items, $title, $this->plainTextTranslations($description));
        }

        if ($package->package_type === 'day_tour') {
            $items = $items->reject(function (array $item): bool {
                $searchable = $this->normalized(
                    implode(' ', $item['title']).' '.implode(' ', $item['description'])
                );

                return $this->isAccommodationHighlight($searchable);
            });
        }

        return $items->take(self::MAX_HIGHLIGHTS)->values();
    }

    /** @param Collection<int, array{title: array<string, string>, description: array<string, string>}> $items */
    private function pushUnique(Collection $items, array $title, array $description = []): void
    {
        $title = array_filter($title, fn ($value) => trim($value) !== '');
        if ($title === []) {
            return;
        }

        $key = $this->normalized($title['en'] ?? (string) reset($title));
        if ($key === '' || $items->contains(fn ($item) => $this->normalized($item['title']['en'] ?? (string) reset($item['title'])) === $key)) {
            return;
        }

        $items->push([
            'title' => $title,
            'description' => array_filter($description, fn ($value) => trim($value) !== ''),
        ]);
    }

    /** @return array<string, string> */
    private function translations(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : ['en' => $value];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($text, $locale) => is_string($locale) && is_scalar($text))
            ->map(fn ($text) => trim((string) $text))
            ->all();
    }

    /** @param array<string, string> $translations */
    private function plainTextTranslations(array $translations): array
    {
        return collect($translations)
            ->map(fn ($text) => Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? ''), 240))
            ->filter()
            ->all();
    }

    private function translationsAreBlank(array $translations): bool
    {
        return collect($translations)->every(fn ($text) => trim((string) $text) === '');
    }

    private function rawValue(object $model, string $attribute): mixed
    {
        return $model->getRawOriginal($attribute) ?? $model->getAttribute($attribute);
    }

    private function normalized(string $value): string
    {
        return trim(preg_replace('/[^\pL\pN]+/u', ' ', Str::lower(strip_tags($value))) ?? '');
    }

    private function isLegacyBoilerplate(string $value): bool
    {
        foreach ([
            'experience the mysteries and treasures of egypt',
            'embark on a leisurely paced 3 night cruise',
            'you ll cover the highlights of egypt from millennia old temples',
            'your private egyptologist guide provides undivided attention',
            'the comprehensive tour allows you to skip the headache',
            'full private tour you can full customize',
            'accommodation lunches activities and hotel pickup and drop off are included',
            'tour is suitable for all ages and all genders',
        ] as $boilerplate) {
            if (str_contains($value, $boilerplate)) {
                return true;
            }
        }

        return false;
    }

    private function isAccommodationHighlight(string $value): bool
    {
        return preg_match('/\b(accommodation|hotel stay|overnight stay|hotel in)\b/u', $value) === 1;
    }
}
