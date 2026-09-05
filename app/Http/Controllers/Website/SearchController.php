<?php

namespace App\Http\Controllers\Website;

use App\Models\Language;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SearchController extends BaseWebsiteController
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('keyword', ''));

        $results = $keyword !== ''
            ? $this->searchPackages($keyword, 12)
            : new LengthAwarePaginator([], 0, 12, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        if ($keyword !== '') {
            $results->getCollection()->transform(fn (Package $package) => $this->mapResultCard($package));
        }

        return view('website.pages.search', [
            'keyword' => $keyword,
            'results' => $results,
            'hasSearch' => $keyword !== '',
            'suggestedLinks' => [
                [
                    'label' => __('Browse Tours'),
                    'url' => route('website.tours.all'),
                ],
                [
                    'label' => __('Explore Travel Packages'),
                    'url' => route('website.trips'),
                ],
                [
                    'label' => __('See Latest Offers'),
                    'url' => route('website.offers'),
                ],
                [
                    'label' => __('Plan a Tailor-Made Trip'),
                    'url' => route('website.tailor_made.index'),
                ],
            ],
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->input('keyword', ''));

        if (mb_strlen($keyword) < 2) {
            return response()->json([]);
        }

        $items = $this->searchPackages($keyword, 8, false)
            ->getCollection()
            ->map(function (Package $package) {
                return [
                    'title' => $this->translated($package->getRawOriginal('title') ?? $package->title),
                    'type' => $this->resultTypeLabel($package),
                    'url' => $this->packageRoute($package),
                ];
            })
            ->values();

        return response()->json($items);
    }

    private function searchPackages(string $keyword, int $perPage, bool $paginate = true): LengthAwarePaginator
    {
        $languages = $this->searchLocales();

        $query = Package::query()
            ->with(['currency', 'primaryCountry', 'cruise'])
            ->where('is_active', true)
            ->whereIn('package_type', ['travel_package', 'nile_cruise', 'day_tour', 'shore_excursion', 'deal', 'multi_country', 'custom'])
            ->when($keyword !== '', function ($builder) use ($keyword, $languages) {
                $builder->where(function ($q) use ($keyword, $languages) {
                    foreach ($languages as $locale) {
                        $q->orWhere("title->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("subtitle->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("short_description->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("description->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("schedule_text->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("destinations_text->{$locale}", 'like', "%{$keyword}%");
                    }

                    $q->orWhere('slug', 'like', "%{$keyword}%")
                        ->orWhere('tour_type', 'like', "%{$keyword}%")
                        ->orWhereHas('cruise', function ($cruiseQuery) use ($keyword) {
                            $cruiseQuery->where('ship_name', 'like', "%{$keyword}%")
                                ->orWhere('route_from', 'like', "%{$keyword}%")
                                ->orWhere('route_to', 'like', "%{$keyword}%")
                                ->orWhere('sailing_days', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('primaryCountry', function ($countryQuery) use ($keyword) {
                            $countryQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('slug', 'like', "%{$keyword}%");
                        });
                });
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->latest('id');

        if (!$paginate) {
            $collection = $query->limit($perPage)->get();

            return new LengthAwarePaginator(
                $collection,
                $collection->count(),
                $perPage,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    private function mapResultCard(Package $package): array
    {
        $type = $this->resultTypeLabel($package);
        $description = $this->shortText(
            $package->getRawOriginal('short_description')
            ?: $package->getRawOriginal('description'),
            170
        );

        $meta = $this->resultMeta($package);

        return [
            'title' => $this->translated($package->getRawOriginal('title') ?? $package->title),
            'description' => $description,
            'url' => $this->packageRoute($package),
            'image' => $this->imageUrl($package->featured_image ?? null, asset('website/photos/home2.webp')),
            'price' => $package->start_from_price !== null ? $this->packagePrice($package) : null,
            'button_text' => match ($package->package_type) {
                'nile_cruise' => __('View Cruise'),
                'day_tour', 'shore_excursion' => __('View Tour'),
                default => __('View Package'),
            },
            'type' => $type,
            'meta' => $meta,
        ];
    }

    private function resultMeta(Package $package): array
    {
        if ($package->package_type === 'nile_cruise') {
            $sailing = $this->packageScheduleLabel($package);
            $route = trim(collect([$package->cruise?->route_from, $package->cruise?->route_to])->filter()->implode(' - '));

            return array_values(array_filter([
                $sailing !== '' ? ['icon' => 'las la-ship', 'text' => $sailing] : null,
                $route !== '' ? ['icon' => 'las la-map-marker', 'text' => $route] : null,
            ]));
        }

        return array_values(array_filter([
            ['icon' => 'las la-clock', 'text' => $this->packageDuration($package)],
            trim((string) $package->tour_type) !== ''
                ? ['icon' => 'las la-users', 'text' => $this->packageTourTypeLabel($package)]
                : ['icon' => 'las la-tag', 'text' => $this->resultTypeLabel($package)],
        ]));
    }

    private function resultTypeLabel(Package $package): string
    {
        return match ($package->package_type) {
            'nile_cruise' => __('Cruise'),
            'day_tour', 'shore_excursion' => __('Tour'),
            default => __('Package'),
        };
    }

    private function searchLocales(): array
    {
        $locales = Language::query()
            ->where('is_active', true)
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        if (empty($locales)) {
            $locales = ['en', 'ar', 'es', 'ru', 'it', 'It', 'ch'];
        }

        return array_values(array_unique(array_merge($locales, ['en', 'ar', 'it', 'It', 'ch'])));
    }
}
