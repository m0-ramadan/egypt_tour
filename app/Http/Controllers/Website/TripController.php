<?php

namespace App\Http\Controllers\Website;

use App\Models\Country;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TripController extends BaseWebsiteController
{
    public function index(Request $request)
    {
        $packages = Package::query()
            ->with(['currency', 'category', 'primaryCountry'])
            ->where('is_active', true)
            ->whereIn('package_type', ['travel_package', 'nile_cruise', 'deal', 'multi_country', 'custom'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . $request->q . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('subtitle', 'like', $search)
                        ->orWhere('short_description', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when($request->filled('type'), fn($query) => $query->where('package_type', $request->type))
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->paginate(12)
            ->withQueryString();

        return view('website.pages.trips.index', compact('packages'));
    }

    public function show(string $slug)
    {
        $package = Package::query()
            ->with([
                'currency',
                'category',
                'primaryCountry',
                'destination.city',
                'highlights',
                'facilities',
                'itineraries',
                'inclusions',
                'prices.currency',
                'packageAttractions.attraction.city',
                'cruise',
                'cities',
                'tags',
                'addons.currency',
                'tourPackageDetail',
                'tourPackageAccommodations.hotels',
                'tourPackageAccommodations.seasons.currency',
                'tourPackageAccommodations.seasons.items',
                'nileCruiseType',
                'nileCruiseCategory',
                'nileCruiseDetail',
                'nileCruiseSchedules.departureCity',
                'nileCruiseSchedules.arrivalCity',
                'nileCruiseCabins',
                'nileCruiseDurations.currency',
                'nileCruiseDurations.departureCity',
                'nileCruiseDurations.arrivalCity',
                'nileCruiseDurations.itineraryDays.activities.attraction',
                'nileCruiseDurations.seasonPrices.currency',
                'nileCruiseDurations.seasonPrices.items.cabin',
                'reviews',
                'testimonials',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $title = $this->translated($package->getRawOriginal('title') ?? $package->title);
        $subtitle = $this->translated($package->getRawOriginal('subtitle') ?? $package->subtitle);
        $shortDescription = $this->translated($package->getRawOriginal('short_description') ?? $package->short_description);
        $description = $this->translated($package->getRawOriginal('description') ?? $package->description) ?: $shortDescription;
        $breadcrumbTitle = $this->translated(
            $package->getRawOriginal('breadcrumb_title') ?? $package->breadcrumb_title
        ) ?: $title;
        $packageTypeText = match ((string) $package->package_type) {
            'day_tour' => __('Day Trip'),
            'travel_package' => __('Tour Package'),
            'nile_cruise' => __('Nile Cruise'),
            default => $this->typeLabel((string) $package->package_type),
        };
        $bookingModeText = match ($package->booking_mode) {
            'instant' => __('Instant Confirmation'),
            'request' => __('On Request'),
            default => $this->localizedUiText($package->booking_mode),
        };
        $countryText = $this->localizedModelText($package->primaryCountry, 'name');
        $schedule = $this->packageScheduleLabel($package);
        $pickup = $this->translated($package->getRawOriginal('pickup_location') ?? $package->pickup_location);
        $dropoff = $this->translated($package->getRawOriginal('dropoff_location') ?? $package->dropoff_location);
        $selectedDestination = $this->localizedModelText($package->destination?->city, 'name')
            ?: $this->localizedModelText($package->destination, 'name');
        $destinationsText = $this->translated($package->getRawOriginal('destinations_text') ?? $package->destinations_text);
        $routeText = $this->translated($package->getRawOriginal('route_text') ?? $package->route_text);
        $locationSummary = $this->translated($package->getRawOriginal('location_summary') ?? $package->location_summary);
        $destinations = collect([$selectedDestination, $destinationsText])
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->implode(' - ');
        $heroImage = $this->imageUrl($package->featured_image, asset('website/photos/home2.webp'));
        $durationText = $this->packageDuration($package);
        $tourTypeText = $this->packageTourTypeLabel($package);
        $videoEmbedUrl = $this->videoEmbedUrl($package->video_url);
        $listingUrl = in_array($package->package_type, ['day_tour', 'shore_excursion'], true)
            ? route('website.tours.all')
            : route('website.trips');
        $listingLabel = in_array($package->package_type, ['day_tour', 'shore_excursion'], true)
            ? __('Tours')
            : __('Trips');
        $rawCanonicalUrl = trim((string) ($package->canonical_url ?? ''));

        if ($rawCanonicalUrl === '' || trim($rawCanonicalUrl, '/') === trim((string) $package->slug, '/')) {
            $canonicalUrl = $this->packageRoute($package);
        } elseif (Str::startsWith($rawCanonicalUrl, ['http://', 'https://'])) {
            $canonicalUrl = $rawCanonicalUrl;
        } else {
            $canonicalUrl = url('/' . ltrim($rawCanonicalUrl, '/'));
        }

        $gallery = [];
        $galleryImages = $package->getRawOriginal('gallery_images') ?? $package->gallery_images;

        if (!empty($galleryImages)) {
            $decodedGallery = is_array($galleryImages) ? $galleryImages : json_decode((string) $galleryImages, true);

            if (is_array($decodedGallery)) {
                foreach ($decodedGallery as $img) {
                    $gallery[] = $this->imageUrl(
                        is_array($img) ? ($img['path'] ?? $img['url'] ?? null) : $img,
                        $heroImage
                    );
                }
            }
        }

        $imageRows = DB::table('images')
            ->where('imageable_type', 'App\\Models\\Package')
            ->where('imageable_id', $package->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        foreach ($imageRows as $img) {
            $gallery[] = $this->imageUrl($img->path, $heroImage);
        }

        $gallery = array_values(array_unique(array_filter($gallery)));

        if (empty($gallery)) {
            $gallery[] = $heroImage;
        }

        $canonicalTypes = ['day_tour', 'travel_package', 'nile_cruise'];
        $isDayTour = $package->package_type === 'day_tour'
            || (!in_array($package->package_type, $canonicalTypes, true)
                && ($package->duration_type === 'hours' || (int) $package->duration_days <= 1));
        $itineraryUnit = $isDayTour ? __('Step') : __('Day');

        $itineraries = $package->itineraries
            ->map(function ($item) use ($itineraryUnit) {
                $item->display_title = $this->transValue(
                    $item->getRawOriginal('title') ?? $item->title,
                    ''
                );
                $item->display_description = $this->transValue($item->getRawOriginal('description') ?? $item->description, '');
                $item->display_overnight = $this->transValue($item->getRawOriginal('overnight_location') ?? ($item->overnight_location ?? ''), '');
                $item->display_accommodation = $this->transValue($item->getRawOriginal('accommodation') ?? ($item->accommodation ?? ''), '');
                $item->display_transport_notes = $this->transValue($item->getRawOriginal('transport_notes') ?? ($item->transport_notes ?? ''), '');
                $item->display_activities = collect((array) ($item->activities ?? []))
                    ->map(function ($activity) {
                        if (!is_array($activity)) return null;
                        return [
                            'time' => trim((string) ($activity['time'] ?? '')),
                            'title' => trim((string) ($activity['title'] ?? '')),
                            'location' => trim((string) ($activity['location'] ?? '')),
                            'duration' => trim((string) ($activity['duration'] ?? '')),
                            'description' => trim((string) ($activity['description'] ?? '')),
                        ];
                    })
                    ->filter(fn ($activity) => $activity && collect($activity)->filter()->isNotEmpty())
                    ->values();
                return $item;
            })
            ->values();

        $highlights = $package->highlights
            ->map(function ($highlight) {
                $highlight->display_title = $this->transValue(
                    $highlight->getRawOriginal('title') ?? $highlight->title,
                    ''
                );
                $highlight->display_description = $this->transValue(
                    $highlight->getRawOriginal('description') ?? $highlight->description,
                    ''
                );

                return $highlight;
            })
            ->filter(fn($highlight) => $highlight->display_title !== '' || $highlight->display_description !== '')
            ->values();

        $facilities = $package->facilities
            ->map(function ($facility) {
                $facility->display_title = $this->transValue(
                    $facility->getRawOriginal('title') ?? $facility->title,
                    ''
                );

                return $facility;
            })
            ->filter(fn($facility) => $facility->display_title !== '')
            ->values();

        $inclusions = $package->inclusions
            ->map(function ($item) {
                $rawContent = method_exists($item, 'getRawOriginal')
                    ? ($item->getRawOriginal('content') ?? $item->content)
                    : $item->content;

                $fallback = $item->description ?? $item->title ?? '';
                $item->display_content = __($this->transValue($rawContent, $fallback));

                return $item;
            })
            ->values();

        $included = $inclusions
            ->filter(fn($item) => in_array(($item->item_type ?? $item->type), ['included', null], true) && trim((string) $item->display_content) !== '')
            ->values();

        $excluded = $inclusions
            ->filter(fn($item) => in_array(($item->item_type ?? $item->type), ['excluded'], true) && trim((string) $item->display_content) !== '')
            ->values();

        $prices = $package->prices
            ->map(function ($price) use ($package) {
                $rawLabel = method_exists($price, 'getRawOriginal') ? ($price->getRawOriginal('label') ?? $price->label) : $price->label;
                $rawSeasonName = method_exists($price, 'getRawOriginal') ? ($price->getRawOriginal('season_name') ?? $price->season_name) : $price->season_name;
                $rawNotes = method_exists($price, 'getRawOriginal') ? ($price->getRawOriginal('notes') ?? $price->notes) : $price->notes;

                $price->display_label = $this->transValue($rawLabel, $price->room_type ?: $price->price_type ?: __('Package Price'));
                $price->display_season_name = $this->transValue($rawSeasonName, '');
                $price->display_notes = $this->transValue($rawNotes, '');
                $price->display_price_type = match ($price->price_type) {
                    'from' => __('Starts From'),
                    'fixed' => __('Fixed'),
                    'seasonal' => __('Seasonal'),
                    'per_person' => __('Per Person'),
                    'per_group' => __('Per Group'),
                    default => __((string) Str::headline((string) $price->price_type)),
                };
                $price->display_room_type = $price->room_type
                    ? __(Str::headline((string) $price->room_type))
                    : '';
                $price->display_valid_from = $price->valid_from?->format('M j, Y');
                $price->display_valid_to = $price->valid_to?->format('M j, Y');
                $price->formatted_amount = $this->money($price->amount, $price->currency?->symbol ?: ($package->currency?->symbol ?: '$'));

                return $price;
            })
            ->filter(fn($price) => (float) $price->amount > 0)
            ->values();

        $faqs = collect($package->faq_json ?? [])
            ->map(function ($faq) {
                if (!is_array($faq)) {
                    return null;
                }

                return [
                    'question' => trim($this->transValue($faq['question'] ?? '', '')),
                    'answer' => trim($this->transValue($faq['answer'] ?? '', '')),
                ];
            })
            ->filter(fn($faq) => $faq && ($faq['question'] !== '' || $faq['answer'] !== ''))
            ->values();

        $testimonials = $package->testimonials
            ->where('is_active', true)
            ->sortByDesc('is_featured')
            ->take(6)
            ->values();

        $reviews = $package->reviews
            ->where('is_approved', true)
            ->sortByDesc('id')
            ->take(6)
            ->values();

        $legacyNileDetail = $package->package_type === 'nile_cruise' ? $package->nileCruiseDetail : null;

        $onTourLanguages = collect($package->on_tour_languages ?: ($legacyNileDetail?->on_tour_languages ?? []))
            ->map(function ($value) {
                $value = trim((string) $value);
                return match (Str::lower($value)) {
                    'english', 'en' => __('English'),
                    'german', 'de' => __('German'),
                    'french', 'fr' => __('French'),
                    default => $value !== '' ? __(Str::headline($value)) : '',
                };
            })->filter()->unique()->values();
        $whatToBring = collect($package->what_to_bring ?: ($legacyNileDetail?->what_to_bring ?? []))
            ->map(fn ($value) => trim((string) $value))->filter()->unique()->values();
        $operatingDays = collect($package->operating_days ?: ($legacyNileDetail?->operating_days ?? []))
            ->map(fn ($value) => trim((string) $value))->filter()->unique()->values();
        $departureTimes = $package->package_type === 'day_tour'
            ? collect($package->departure_times ?? [])->map(fn ($value) => trim((string) $value))->filter()->values()
            : collect();
        $promotionalVideos = collect($package->promotional_videos ?: ($legacyNileDetail?->promotional_videos ?? []))
            ->map(fn ($value) => trim((string) $value))->filter()->unique()->values();
        $addons = $package->addons->where('is_active', true)->values();
        if ($addons->isEmpty() && $package->package_type === 'nile_cruise' && method_exists($package, 'nileCruiseAddons')) {
            $addons = $package->nileCruiseAddons->where('is_active', true)->values();
        }
        $tourPackageDetail = $package->package_type === 'travel_package' ? $package->tourPackageDetail : null;
        $packageCities = $package->cities->sortBy(fn ($city) => $city->pivot?->stop_order ?? 999)->values();
        $brochureUrl = $package->brochure_path ? asset('storage/' . ltrim((string) $package->brochure_path, '/')) : null;
        $sharedTimezone = trim((string) ($package->tour_timezone ?: ($legacyNileDetail?->timezone ?? '')));
        $sharedDepositPolicy = trim((string) ($package->deposit_policy ?: ($legacyNileDetail?->deposit_policy ?? '')));
        $bookingService = app(\App\Services\PackageBookingService::class);
        $bookingPricingOptions = $bookingService->pricingOptions($package);
        $hasBookablePrice = $bookingPricingOptions->isNotEmpty();

        $countries = Country::query()
            ->orderBy('id')
            ->get()
            ->map(fn($country) => $country->display_name)
            ->filter()
            ->values();

        if ($countries->isEmpty()) {
            $countries = collect([
                'Egypt',
                'United States',
                'United Kingdom',
                'Canada',
                'Australia',
                'Germany',
                'France',
                'Italy',
                'Spain',
                'Japan',
                'China',
                'India',
                'Saudi Arabia',
                'United Arab Emirates',
                'Morocco',
                'Jordan',
                'Turkey',
            ]);
        }

        $relatedPackages = Package::query()
            ->with(['currency'])
            ->where('is_active', true)
            ->where('id', '!=', $package->id)
            ->when($package->category_id, fn($query) => $query->where('category_id', $package->category_id))
            ->limit(3)
            ->get()
            ->map(function (Package $related) {
                return [
                    'title' => $this->translated($related->getRawOriginal('title') ?? $related->title),
                    'image' => $this->imageUrl($related->featured_image, asset('website/photos/home2.webp')),
                    'url' => $this->packageRoute($related),
                    'button_text' => in_array($related->package_type, ['day_tour', 'shore_excursion'], true)
                        ? __('View Tour')
                        : __('View Trip'),
                ];
            });

        return view('website.pages.packages.show', compact(
            'package',
            'relatedPackages',
            'title',
            'subtitle',
            'shortDescription',
            'description',
            'breadcrumbTitle',
            'canonicalUrl',
            'durationText',
            'packageTypeText',
            'tourTypeText',
            'videoEmbedUrl',
            'listingUrl',
            'listingLabel',
            'bookingModeText',
            'countryText',
            'schedule',
            'pickup',
            'dropoff',
            'destinations',
            'routeText',
            'locationSummary',
            'heroImage',
            'gallery',
            'highlights',
            'facilities',
            'itineraries',
            'included',
            'excluded',
            'prices',
            'faqs',
            'testimonials',
            'reviews',
            'countries',
            'onTourLanguages',
            'whatToBring',
            'operatingDays',
            'departureTimes',
            'promotionalVideos',
            'addons',
            'tourPackageDetail',
            'packageCities',
            'brochureUrl',
            'sharedTimezone',
            'sharedDepositPolicy',
            'itineraryUnit',
            'isDayTour',
            'hasBookablePrice',
            'bookingPricingOptions'
        ));
    }

    public function legacyShow(string $country, string $slug)
    {
        return $this->show($slug);
    }

    private function videoEmbedUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $videoId = null;

        if (Str::contains($host, 'youtu.be')) {
            $videoId = Str::before($path, '/');
        } elseif (Str::contains($host, 'youtube.com') || Str::contains($host, 'youtube-nocookie.com')) {
            parse_str((string) ($parts['query'] ?? ''), $query);

            if ($path === 'watch') {
                $videoId = $query['v'] ?? null;
            } elseif (Str::startsWith($path, ['embed/', 'shorts/'])) {
                $videoId = Str::after($path, '/');
            }
        }

        if ($videoId) {
            return 'https://www.youtube.com/embed/' . rawurlencode(Str::before((string) $videoId, '/'));
        }

        if (Str::contains($host, 'vimeo.com') && preg_match('/(?:video\/)?(\d+)/', $path, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return $url;
    }
}
