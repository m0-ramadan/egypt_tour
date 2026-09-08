<?php

namespace App\Http\Controllers\Website;

use App\Models\City;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends BaseWebsiteController
{
    public function index(Request $request): View
    {
        $selectedType = $request->input('type');
        $duration = $request->input('duration') ?: $request->input('days');
        $destinationSlug = trim((string) ($request->input('destination') ?: $request->input('city', '')));
        $search = trim((string) $request->input('q', ''));
        $category = trim((string) $request->input('category', ''));

        if ($selectedType === 'travel_package' && !$duration && $destinationSlug === '' && $search === '' && $category === '') {
            return app(TravelPackageController::class)->index($request);
        }

        $durationTitle = $duration ? __(':days Days Egypt Travel Packages', ['days' => $duration]) : __('Egypt Travel Packages');
        $durationSubtitle = $duration
            ? __('Browse our handpicked :days-day private Egypt vacation packages and itineraries.', ['days' => $duration])
            : __('Browse a curated selection of private Egypt travel packages, vacations, and tailor-made journeys.');

        return $this->renderListingPage(
            $request,
            ['travel_package'],
            [
                'badge' => __('Travel Packages'),
                'title' => $durationTitle,
                'subtitle' => $durationSubtitle,
                'overview_title' => __('Find your ideal Egypt travel package'),
                'overview_text' => __('Explore flexible multi-day travel packages designed around comfort, discovery, and memorable pharaonic experiences across Egypt.'),
                'empty_title' => __('No travel packages found'),
                'empty_text' => __('Try changing the search filters or browse our other travel packages.'),
                'button_text' => __('View Package'),
            ]
        );
    }

    public function tours(Request $request): View
    {
        $selectedType = $request->input('type');
        $destinationSlug = trim((string) ($request->input('destination') ?: $request->input('city', '')));
        $search = trim((string) $request->input('q', ''));
        $category = trim((string) $request->input('category', ''));

        if ($selectedType === 'day_tour' && $destinationSlug === '' && $search === '' && $category === '') {
            return app(DayTourController::class)->index($request);
        }

        $destinationCity = $destinationSlug !== '' ? City::where('slug', $destinationSlug)->first() : null;

        $cityName = $destinationCity
            ? $this->translated($destinationCity->getRawOriginal('name') ?? $destinationCity->name)
            : null;

        $pageTitle = $destinationCity
            ? __(':city Day Tours & Excursions', ['city' => $cityName])
            : __('Egypt Day Tours & Shore Excursions');

        $pageSubtitle = $destinationCity
            ? __('Discover private day tours, sightseeing landmarks, and authentic excursions in :city.', ['city' => $cityName])
            : __('Discover expertly planned day tours and shore excursions with seamless logistics and unforgettable highlights.');

        return $this->renderListingPage(
            $request,
            ['day_tour', 'shore_excursion'],
            [
                'badge' => $destinationCity ? __(':city Day Tours', ['city' => $cityName]) : __('Browse Tours'),
                'title' => $pageTitle,
                'subtitle' => $pageSubtitle,
                'overview_title' => $destinationCity ? __('Day Tours & Excursions in :city', ['city' => $cityName]) : __('Choose a tour that fits your schedule'),
                'overview_text' => $destinationCity
                    ? __('Explore handpicked private day tours and guided excursions in :city with licensed English-speaking Egyptologists and private transfers.', ['city' => $cityName])
                    : __('From quick cultural discoveries to full-day private adventures, explore flexible experiences built around your pace.'),
                'empty_title' => $destinationCity ? __('No tours found in :city', ['city' => $cityName]) : __('No tours found'),
                'empty_text' => __('Try changing the search filters or browse our other tours.'),
                'button_text' => __('View Tour'),
            ]
        );
    }

    public function show(Request $request, ?string $country = null, string $slug = null)
    {
        if ($slug === null) {
            $slug = $country;
            $country = null;
        }

        return app(\App\Http\Controllers\Website\TripController::class)->show($slug);
    }

    private function renderListingPage(Request $request, array $allowedTypes, array $pageContent): View
    {
        $selectedType = $request->filled('type') && in_array($request->string('type')->toString(), $allowedTypes, true)
            ? $request->string('type')->toString()
            : null;

        $search = trim((string) $request->input('q', ''));
        $selectedCategorySlug = trim((string) $request->input('category', ''));
        $selectedDestinationSlug = trim((string) ($request->input('destination') ?: $request->input('city', '')));
        $duration = $request->input('duration') ?: $request->input('days');
        $luxury = $request->boolean('luxury');

        $categories = PackageCategory::query()
            ->where('is_active', true)
            ->whereIn('category_type', $allowedTypes)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $selectedCategory = $selectedCategorySlug !== ''
            ? $categories->firstWhere('slug', $selectedCategorySlug)
            : null;

        $destinations = City::query()
            ->where('is_active', true)
            ->where(function ($q) use ($allowedTypes) {
                $q->whereHas('packages', fn($sub) => $sub->where('is_active', true)->whereIn('package_type', $allowedTypes))
                    ->orWhereHas('attractions.packageAttractions.package', fn($sub) => $sub->where('is_active', true)->whereIn('package_type', $allowedTypes));
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($destinations->isEmpty()) {
            $destinations = City::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        $selectedDestination = $selectedDestinationSlug !== ''
            ? City::query()->where('slug', $selectedDestinationSlug)->first()
            : null;

        $packages = Package::query()
            ->with(['currency', 'primaryCountry', 'highlights', 'tags', 'cruise', 'category', 'cities'])
            ->where('is_active', true)
            ->whereIn('package_type', $allowedTypes)
            ->when($selectedType, fn($query) => $query->where('package_type', $selectedType))
            ->when($selectedCategory, fn($query) => $query->where('category_id', $selectedCategory->id))
            ->when($selectedDestination, function ($query) use ($selectedDestination) {
                $cityId = $selectedDestination->id;
                $citySlug = $selectedDestination->slug;
                $rawName = $selectedDestination->getRawOriginal('name');
                $nameEn = is_array($rawName) ? ($rawName['en'] ?? '') : '';
                $nameAr = is_array($rawName) ? ($rawName['ar'] ?? '') : '';

                $query->where(function ($q) use ($cityId, $citySlug, $nameEn, $nameAr) {
                    $q->whereHas('cities', fn($sub) => $sub->where('cities.id', $cityId))
                        ->orWhereHas('destination', fn($sub) => $sub->where('city_id', $cityId))
                        ->orWhereHas('packageAttractions.attraction', fn($sub) => $sub->where('city_id', $cityId))
                        ->orWhere('destinations_text', 'like', "%{$citySlug}%");

                    if ($nameEn !== '') {
                        $q->orWhere('destinations_text', 'like', "%{$nameEn}%")
                            ->orWhere('title', 'like', "%{$nameEn}%")
                            ->orWhere('slug', 'like', "%{$citySlug}%");
                    }
                    if ($nameAr !== '') {
                        $q->orWhere('destinations_text', 'like', "%{$nameAr}%")
                            ->orWhere('title', 'like', "%{$nameAr}%");
                    }
                });
            })
            ->when($duration, function ($query) use ($duration) {
                $durationInt = (int) $duration;
                $query->where(function ($q) use ($durationInt) {
                    $q->where('duration_days', $durationInt)
                        ->orWhere('title', 'like', "{$durationInt} Day%")
                        ->orWhere('title', 'like', "% {$durationInt} Day%")
                        ->orWhere('slug', 'like', "{$durationInt}-day%")
                        ->orWhere('slug', 'like', "%-{$durationInt}-day%");
                });
            })
            ->when($luxury, function ($query) {
                $query->where(function ($q) {
                    $q->where('is_ultra_luxury', true)
                        ->orWhere('title', 'like', '%luxury%')
                        ->orWhere('slug', 'like', '%luxury%');
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('schedule_text', 'like', "%{$search}%")
                        ->orWhere('destinations_text', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $packages->getCollection()->transform(
            fn(Package $package) => $this->packageListingCard($package, $pageContent['button_text'])
        );

        $typeOptions = collect($allowedTypes)->map(fn(string $type) => [
            'value' => $type,
            'label' => $this->typeLabel($type),
        ])->values()->all();

        return view('website.pages.packages.index', [
            'packages' => $packages,
            'pageContent' => $pageContent,
            'selectedType' => $selectedType,
            'selectedCategorySlug' => $selectedCategorySlug,
            'selectedDestinationSlug' => $selectedDestinationSlug,
            'selectedDestinationName' => $selectedDestination
                ? $this->translated($selectedDestination->getRawOriginal('name') ?? $selectedDestination->name)
                : null,
            'destinations' => $destinations->map(fn(City $city) => [
                'slug' => $city->slug,
                'name' => $this->translated($city->getRawOriginal('name') ?? $city->name),
            ])->values(),
            'search' => $search,
            'categories' => $categories->map(fn(PackageCategory $category) => [
                'slug' => $category->slug,
                'name' => $this->translated($category->getRawOriginal('name') ?? $category->name),
            ])->values(),
            'selectedCategoryName' => $selectedCategory
                ? $this->translated($selectedCategory->getRawOriginal('name') ?? $selectedCategory->name)
                : null,
            'typeOptions' => $typeOptions,
            'stats' => [
                'count' => $packages->total(),
                'categories' => $categories->count(),
                'featured' => Package::query()
                    ->where('is_active', true)
                    ->whereIn('package_type', $allowedTypes)
                    ->where('is_featured', true)
                    ->count(),
            ],
        ]);
    }
}
