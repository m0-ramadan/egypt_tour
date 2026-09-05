<?php

namespace App\Http\Controllers\Website;

use App\Models\Attraction;
use App\Models\City;
use App\Models\Country;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DestinationController extends BaseWebsiteController
{
    public function index(Request $request)
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedCountry = $request->filled('country')
            ? $countries->firstWhere('slug', $request->country)
            : null;

        $destinations = City::query()
            ->with('country')
            ->withCount([
                'attractions' => fn ($query) => $query->where('is_active', true),
                'packages' => fn ($query) => $query->where('packages.is_active', true),
            ])
            ->where('is_active', true)
            ->when($selectedCountry, function ($query) use ($selectedCountry) {
                $query->where('country_id', $selectedCountry->id);
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        $highlightCity = City::query()
            ->with('country')
            ->where('is_active', true)
            ->when($selectedCountry, fn ($query) => $query->where('country_id', $selectedCountry->id))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->first();

        $heroImage = $this->imageUrl(
            $highlightCity?->hero_image ?: $highlightCity?->featured_image,
            asset('website/photos/home2.webp')
        );

        $pageTitle = $selectedCountry
            ? $selectedCountry->display_name . ' ' . __('Destinations')
            : __('Destinations');

        $heroBadge = $selectedCountry?->display_name ?: __('Luxury Travel Experiences');
        $heroTitle = $selectedCountry
            ? $selectedCountry->display_name
            : __('Explore Extraordinary Destinations');

        $heroSubtitle = $highlightCity?->display_short_description
            ?: $highlightCity?->display_description
            ?: __('Discover extraordinary multi-country adventures across magnificent destinations');

        $overviewTitle = $selectedCountry
            ? __('Discover') . ' ' . $selectedCountry->display_name
            : __('Explore Extraordinary Destinations');

        $overviewText = $highlightCity
            ? Str::limit(trim(strip_tags($highlightCity->display_description ?: $highlightCity->display_short_description)), 520)
            : __('Discover extraordinary multi-country adventures across magnificent destinations');

        $sectionTitle = $selectedCountry
            ? $selectedCountry->display_name . ' ' . __('Destinations')
            : __('Explore Extraordinary Destinations');

        $sectionSubtitle = $selectedCountry
            ? ($highlightCity?->display_short_description ?: __('Discover extraordinary multi-country adventures across magnificent destinations'))
            : __('Discover extraordinary multi-country adventures across magnificent destinations');

        $destinations->getCollection()->transform(function (City $city) {
            return [
                'title' => $city->display_name,
                'country' => $city->country?->display_name ?: __('Destination'),
                'description' => Str::limit(trim(strip_tags($city->display_short_description ?: $city->display_description)), 150),
                'image' => $this->imageUrl($city->featured_image ?: $city->hero_image, 'website/photos/home2.webp'),
                'url' => route('website.destinations.show', $city->slug, false),
                'attractions_count' => (int) $city->attractions_count,
                'packages_count' => (int) $city->packages_count,
            ];
        });

        return view('website.pages.destinations.index', compact(
            'destinations',
            'countries',
            'selectedCountry',
            'heroImage',
            'pageTitle',
            'heroBadge',
            'heroTitle',
            'heroSubtitle',
            'overviewTitle',
            'overviewText',
            'sectionTitle',
            'sectionSubtitle'
        ));
    }

    public function show(Request $request, string $slug): View
    {
        $destination = City::query()
            ->with(['country', 'attractions' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $packageIds = DB::table('package_cities')
            ->where('city_id', $destination->id)
            ->pluck('package_id');

        $matchesDestination = function ($query) use ($destination, $packageIds) {
            $query->whereIn('id', $packageIds)
                ->orWhereHas('destination', function ($attractionQuery) use ($destination) {
                    $attractionQuery->where('city_id', $destination->id);
                })
                ->orWhereHas('packageAttractions.attraction', function ($attractionQuery) use ($destination) {
                    $attractionQuery->where('city_id', $destination->id);
                });
        };

        $allowedTypes = [
            'travel_package',
            'nile_cruise',
            'day_tour',
            'shore_excursion',
            'deal',
            'multi_country',
            'custom',
        ];

        $selectedType = $request->filled('type') && in_array($request->string('type')->toString(), $allowedTypes, true)
            ? $request->string('type')->toString()
            : null;

        $search = trim((string) $request->input('q', ''));

        $statsQuery = Package::query()
            ->where('is_active', true)
            ->where($matchesDestination);

        $packages = Package::query()
            ->with(['currency', 'primaryCountry', 'highlights', 'tags', 'cruise', 'category'])
            ->where('is_active', true)
            ->where($matchesDestination)
            ->when($selectedType, fn ($query) => $query->where('package_type', $selectedType))
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
            ->paginate(9)
            ->withQueryString();

        $packages->getCollection()->transform(
            fn (Package $package) => $this->packageListingCard($package)
        );

        $heroImage = $this->imageUrl(
            $destination->hero_image ?: $destination->featured_image,
            asset('website/photos/home2.webp')
        );

        $shortDescription = trim(strip_tags(
            $destination->display_short_description ?: $destination->display_description
        ));

        $descriptionHtml = $this->cleanHtml($destination->display_description ?: '');
        $overviewText = Str::limit(
            trim(strip_tags($descriptionHtml ?: $destination->display_short_description)),
            420
        );

        $pageTitle = $destination->getTranslation('seo_title')
            ?: $destination->display_name . ' ' . __('Tours & Trips');

        $pageDescription = $destination->getTranslation('seo_description')
            ?: Str::limit(
                $shortDescription !== ''
                    ? $shortDescription
                    : __('Explore private tours, travel packages, and unforgettable attractions in :destination.', [
                        'destination' => $destination->display_name,
                    ]),
                170
            );

        $attractions = $destination->attractions
            ->take(6)
            ->map(function (Attraction $attraction) {
                return [
                    'slug' => $attraction->slug,
                    'title' => $attraction->display_name,
                    'description' => Str::limit(
                        trim(strip_tags($attraction->display_short_description ?: $attraction->display_description)),
                        130
                    ),
                    'image' => $this->imageUrl($attraction->image, 'website/photos/home2.webp'),
                    'opening_hours' => $this->translated($attraction->getRawOriginal('opening_hours') ?? $attraction->opening_hours),
                    'map_url' => trim((string) $attraction->map_url),
                    'url' => route('website.attractions.show', $attraction->slug),
                ];
            })
            ->values();

        $primaryDestinationTypes = [
            'day_tour',
            'nile_cruise',
            'travel_package',
        ];

        $availableTypes = (clone $statsQuery)
            ->select('package_type')
            ->distinct()
            ->pluck('package_type')
            ->filter()
            ->values();

        $typeCounts = (clone $statsQuery)
            ->select('package_type', DB::raw('COUNT(*) as total'))
            ->whereNotNull('package_type')
            ->groupBy('package_type')
            ->pluck('total', 'package_type');

        $typeOptions = $availableTypes
            ->merge($primaryDestinationTypes)
            ->unique()
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => $this->typeLabel($type),
            ])
            ->values()
            ->all();

        $typeImageMap = [
            'day_tour' => 'website/photos/home2.webp',
            'nile_cruise' => 'website/images/nile-cruises/luxor-aswan.jpg',
            'travel_package' => 'website/photos/Dest/Egypt.jpg',
        ];

        $typeCards = collect($primaryDestinationTypes)
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => $this->typeLabel($type),
                'count' => (int) ($typeCounts[$type] ?? 0),
                'image' => $typeImageMap[$type] ?? 'website/photos/home2.webp',
                'url' => route('website.destinations.show', [
                    'slug' => $destination->slug,
                    'type' => $type,
                ], false) . '#destination-journeys',
                'active' => $selectedType === $type,
            ])
            ->values();

        $stats = [
            'count' => (clone $statsQuery)->count(),
            'featured' => (clone $statsQuery)->where('is_featured', true)->count(),
            'attractions' => $destination->attractions->count(),
        ];

        return view('website.pages.destinations.show', compact(
            'destination',
            'packages',
            'heroImage',
            'pageTitle',
            'pageDescription',
            'descriptionHtml',
            'overviewText',
            'shortDescription',
            'attractions',
            'typeOptions',
            'typeCards',
            'selectedType',
            'search',
            'stats'
        ));
    }

    public function legacyShow(string $slug)
    {
        $slug = str_replace('.html', '', $slug);

        $destination = City::query()
            ->where('slug', $slug)
            ->orWhere('slug', Str::slug($slug))
            ->first();

        if ($destination) {
            return redirect()->route('website.destinations.show', $destination->slug, 301);
        }

        abort(404);
    }
}
