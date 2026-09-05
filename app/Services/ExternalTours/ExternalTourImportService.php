<?php

namespace App\Services\ExternalTours;

use App\Models\Attraction;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageTag;
use App\Models\TourPackageAccommodation;
use App\Models\TourPackageHotel;
use App\Models\TourPackagePriceItem;
use App\Models\TourPackageSeason;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ExternalTourImportService
{
    public function __construct(
        protected LuxorAndAswanTourPageParser $parser,
        protected ExternalTourContentRewriter $rewriter,
        protected ExternalTourImageDownloader $imageDownloader
    ) {}

    /**
     * Import a tour from an external URL.
     *
     * @param string $url
     * @param array<string, mixed> $options
     * @return array{
     *     package: Package,
     *     is_update: bool,
     *     warnings: array<string>,
     *     stats: array<string, mixed>
     * }
     */
    public function import(string $url, array $options = []): array
    {
        $warnings = [];

        // 1. SSRF & Host Validation
        $this->validateUrl($url);

        Log::info('Tour import started', ['url' => $url]);

        // 2. Fetch remote HTML content
        $html = $this->fetchHtml($url);

        // 3. Parse HTML to structured factual array
        $parsedData = $this->parser->parse($html, $url);
        if (!empty($parsedData['warnings'])) {
            $warnings = array_merge($warnings, $parsedData['warnings']);
        }

        $sourceId = $parsedData['source_id'];

        // 4. Duplicate / Update check
        $existingPackage = Package::where('source_type', 'external_url')
            ->where('source_remote_id', $sourceId)
            ->first();

        $updateMode = (bool) ($options['update'] ?? config('tour_import.update_existing', false));

        if ($existingPackage && !$updateMode) {
            $warnings[] = "Tour already imported (Package ID: {$existingPackage->id}). Re-run with --update to update.";
            return [
                'package' => $existingPackage,
                'is_update' => false,
                'warnings' => $warnings,
                'stats' => [
                    'tour_type' => $existingPackage->package_type,
                    'cities' => implode(', ', $parsedData['cities'] ?? []),
                    'cities_count' => count($parsedData['cities'] ?? []),
                    'pricing_levels_count' => count($parsedData['pricing']['accommodations'] ?? []),
                    'images_discovered_count' => count($parsedData['images'] ?? []),
                    'images_downloaded_count' => 0,
                ],
            ];
        }

        // 5. Rewrite marketing copy (OUTSIDE DB transaction)
        $rewriteContent = (bool) ($options['rewrite'] ?? config('tour_import.rewrite_content', true));
        if ($rewriteContent) {
            $rewriteResult = $this->rewriter->rewrite($parsedData);
            $data = $rewriteResult['data'];
            if (!empty($rewriteResult['warnings'])) {
                $warnings = array_merge($warnings, $rewriteResult['warnings']);
            }
        } else {
            $data = $this->rewriter->fallbackRewrite($parsedData);
        }

        // 6. Resolve Taxonomy & Dependencies
        $resolvedTaxonomy = $this->resolveTaxonomy($data, $warnings);
        $warnings = $resolvedTaxonomy['warnings'];

        // 7. Atomic DB Transaction (All writes in single transaction)
        $package = DB::transaction(function () use ($data, $resolvedTaxonomy, $existingPackage, $updateMode) {
            return $this->persistPackage($data, $resolvedTaxonomy, $existingPackage, $updateMode);
        });

        // 8. Download Images (OUTSIDE DB transaction)
        $downloadImages = (bool) ($options['download_images'] ?? config('tour_import.download_images', true));
        $downloadedCount = 0;

        if ($downloadImages && !empty($data['images'])) {
            $imageResult = $this->imageDownloader->download($package->id, $data['images'], $options);
            if (!empty($imageResult['warnings'])) {
                $warnings = array_merge($warnings, $imageResult['warnings']);
            }

            if (!empty($imageResult['featured_image']) || !empty($imageResult['gallery_images'])) {
                $package->update([
                    'featured_image' => $imageResult['featured_image'] ?? $package->featured_image,
                    'gallery_images' => $imageResult['gallery_images'] ?? $package->gallery_images,
                ]);
            }

            $downloadedCount = ($imageResult['featured_image'] ? 1 : 0) + count($imageResult['gallery_images']);
        }

        Log::info('Tour import completed successfully', [
            'package_id' => $package->id,
            'source_id' => $sourceId,
            'warnings_count' => count($warnings),
        ]);

        return [
            'package' => $package,
            'is_update' => (bool) $existingPackage,
            'warnings' => $warnings,
            'stats' => [
                'tour_type' => $package->package_type,
                'cities' => implode(', ', $data['cities'] ?? []),
                'cities_count' => count($data['cities'] ?? []),
                'pricing_levels_count' => count($data['pricing']['accommodations'] ?? []),
                'images_discovered_count' => count($data['images'] ?? []),
                'images_downloaded_count' => $downloadedCount,
            ],
        ];
    }

    /**
     * Validate URL against SSRF, allowed schemes, and allowed hosts.
     */
    public function validateUrl(string $url): void
    {
        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException("Tour import failed: Invalid URL scheme [{$scheme}]. Only HTTP and HTTPS are permitted.");
        }

        if (empty($host)) {
            throw new InvalidArgumentException("Tour import failed: Malformed URL [{$url}].");
        }

        // Host whitelist validation
        $allowedHosts = config('tour_import.allowed_hosts', ['luxorandaswan.com', 'www.luxorandaswan.com']);
        $hostMatched = false;
        foreach ($allowedHosts as $allowed) {
            $allowed = strtolower(trim($allowed));
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                $hostMatched = true;
                break;
            }
        }

        if (!$hostMatched) {
            throw new InvalidArgumentException("Tour import failed: Host [{$host}] is not in the allowed hosts list.");
        }

        // Private / Localhost IP SSRF protection
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            throw new InvalidArgumentException("Tour import failed: Forbidden host [{$host}].");
        }

        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException("Tour import failed: Host [{$host}] could not be resolved.");
        }

        $isPublicIp = (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if (!$isPublicIp) {
            throw new InvalidArgumentException("Tour import failed: Host resolves to a private or reserved IP address [{$ip}].");
        }
    }

    /**
     * Fetch remote HTML content using Laravel HTTP client.
     */
    protected function fetchHtml(string $url): string
    {
        $timeout = (int) config('tour_import.timeout', 30);
        $connectTimeout = (int) config('tour_import.connect_timeout', 10);
        $userAgent = (string) config('tour_import.user_agent', 'TravelNest Tour Importer/1.0 (+https://travelnest.com)');

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->withUserAgent($userAgent)
                ->withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new RuntimeException("Tour import failed: Source page returned HTTP {$response->status()}.");
            }

            $html = $response->body();
            if (empty(trim($html))) {
                throw new RuntimeException('Tour import failed: Source page returned empty content.');
            }

            return $html;
        } catch (\Throwable $e) {
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException("Tour import failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Resolve related models (Currency, Category, Country, Cities, Attractions, Tags).
     */
    protected function resolveTaxonomy(array $data, array $warnings): array
    {
        // 1. Currency
        $currencyCode = strtoupper((string) ($data['currency'] ?? config('tour_import.default_currency', 'USD')));
        $currency = Currency::whereRaw('UPPER(code) = ?', [$currencyCode])->first();

        if (!$currency) {
            $warnings[] = "Currency [{$currencyCode}] not found in database. Setting currency to null.";
        }

        // 2. Country
        $defaultCountryCode = (string) config('tour_import.default_country_code', 'EG');
        $country = Country::where('code', $defaultCountryCode)->first();

        // 3. Category
        $title = $data['title'] ?? '';
        $packageType = $data['package_type'] ?? 'travel_package';
        $breadcrumbs = $data['breadcrumbs'] ?? [];

        $category = $this->resolveCategory($packageType, $warnings, $title, $breadcrumbs);
        if (!$category) {
            $warnings[] = "No matching category found for package type [{$packageType}]. Setting category_id to null.";
        } else {
            if (!empty($category->category_type)) {
                $packageType = $category->category_type;
                $data['package_type'] = $packageType;
            }
        }

        // 4. Cities
        $matchedCities = [];
        $allDbCities = City::where('is_active', true)->get();

        foreach ($data['cities'] ?? [] as $index => $cityName) {
            $cityModel = $this->findMatchingCity($cityName, $allDbCities);
            if ($cityModel) {
                $matchedCities[] = [
                    'model' => $cityModel,
                    'stop_order' => $index + 1,
                    'is_primary' => $index === 0,
                    'nights' => null,
                ];
            } else {
                $warnings[] = "City [{$cityName}] not found in database; skipped pivot association.";
            }
        }

        // 5. Attractions
        $matchedAttractions = [];
        $allDbAttractions = Attraction::all();
        $seenAttractionIds = [];

        foreach ($data['attractions'] ?? [] as $attractionName) {
            $attractionName = trim($attractionName);
            if (empty($attractionName)) {
                continue;
            }

            $attractionModel = $this->findMatchingAttraction($attractionName, $allDbAttractions);
            if (!$attractionModel) {
                $cityModel = $this->guessCityForAttraction($attractionName, $allDbCities);
                $attractionModel = $this->createAttraction($attractionName, $cityModel);
                $allDbAttractions->push($attractionModel);
            }

            if ($attractionModel && !in_array($attractionModel->id, $seenAttractionIds, true)) {
                $matchedAttractions[] = $attractionModel;
                $seenAttractionIds[] = $attractionModel->id;
            }
        }

        // 6. Tags
        $tags = [];
        $tagCandidates = array_merge(
            $data['cities'] ?? [],
            [
                $packageType === 'travel_package' ? 'Travel Package' : ($packageType === 'nile_cruise' ? 'Nile Cruise' : 'Day Tour'),
                ucfirst($data['tour_type'] ?? 'Private') . ' Tour',
            ]
        );

        foreach (array_unique($tagCandidates) as $tagName) {
            $slug = Str::slug($tagName);
            if (!empty($slug)) {
                $tags[] = PackageTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => ['en' => $tagName, 'ar' => '']]
                );
            }
        }

        return [
            'currency' => $currency,
            'country' => $country,
            'category' => $category,
            'cities' => $matchedCities,
            'all_cities' => $allDbCities,
            'attractions' => $matchedAttractions,
            'tags' => $tags,
            'warnings' => $warnings,
        ];
    }

    /**
     * Resolve PackageCategory based on package type, standard slugs, and breadcrumbs.
     *
     * User Rule: Nile Cruise ONLY if it belongs to one of these three:
     * - Lake Nasser Cruise
     * - Dahabiya Nile Cruise
     * - Luxor and Aswan Nile Cruises
     * Otherwise, assign to its actual category (travel_package, day_tour, shore_excursion).
     */
    protected function resolveCategory(string $packageType, array &$warnings, ?string $title = null, array $breadcrumbs = []): ?PackageCategory
    {
        // 1. Resolve based on package_type (which was detected using strict category rules)
        $slugCandidates = match ($packageType) {
            'nile_cruise' => ['nile cruise', 'nile-cruises', 'nile-cruise', 'nile-trip'],
            'day_tour' => ['day_tour', 'day-tours', 'day-tour', 'excursions'],
            'shore_excursion' => ['shore_excursions', 'shore-excursions', 'shore_excursion'],
            default => ['tour_packages', 'tour-packages', 'travel-packages', 'packages'],
        };

        $category = PackageCategory::whereIn('slug', $slugCandidates)->first();
        if ($category) {
            return $category;
        }

        $category = PackageCategory::where('category_type', $packageType)->first();
        if ($category) {
            return $category;
        }

        // 2. Fallback: Check breadcrumbs if standard match did not resolve
        foreach (array_reverse($breadcrumbs) as $crumb) {
            $lowerCrumb = strtolower($crumb);

            if (
                str_contains($lowerCrumb, 'lake nasser') ||
                str_contains($lowerCrumb, 'dahabiya') ||
                (str_contains($lowerCrumb, 'luxor') && str_contains($lowerCrumb, 'aswan') && str_contains($lowerCrumb, 'cruise')) ||
                str_contains($lowerCrumb, 'luxor and aswan nile cruise')
            ) {
                $found = PackageCategory::where('category_type', 'nile_cruise')
                    ->orWhere('slug', 'nile cruise')
                    ->orWhere('slug', 'like', '%cruise%')
                    ->first();
                if ($found) {
                    return $found;
                }
            }

            if (str_contains($lowerCrumb, 'day tour') || str_contains($lowerCrumb, 'excursions')) {
                $found = PackageCategory::where('category_type', 'day_tour')
                    ->orWhere('slug', 'day_tour')
                    ->first();
                if ($found) {
                    return $found;
                }
            }

            if (str_contains($lowerCrumb, 'shore excursion')) {
                $found = PackageCategory::where('category_type', 'shore_excursion')
                    ->orWhere('slug', 'shore_excursions')
                    ->first();
                if ($found) {
                    return $found;
                }
            }

            if (str_contains($lowerCrumb, 'vacation') || str_contains($lowerCrumb, 'tour package') || str_contains($lowerCrumb, 'travel package')) {
                $found = PackageCategory::where('category_type', 'travel_package')
                    ->orWhere('slug', 'tour_packages')
                    ->orWhere('slug', 'tour-packages')
                    ->first();
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Match a city name safely against DB cities without fuzzy LIKE errors.
     */
    protected function findMatchingCity(string $name, $cities): ?City
    {
        $lower = strtolower(trim($name));

        // Exact match on English name or slug
        foreach ($cities as $city) {
            $enName = strtolower(trim($city->getTranslation('name', 'en') ?: ''));
            if ($enName === $lower || $city->slug === Str::slug($lower)) {
                return $city;
            }
        }

        // Word boundary match
        foreach ($cities as $city) {
            $enName = strtolower(trim($city->getTranslation('name', 'en') ?: ''));
            if (!empty($enName) && (str_contains($enName, $lower) || str_contains($lower, $enName))) {
                return $city;
            }
        }

        return null;
    }

    /**
     * Match an attraction name safely against DB attractions.
     */
    protected function findMatchingAttraction(string $name, $attractions): ?Attraction
    {
        $lower = strtolower(trim($name));
        $targetSlug = Str::slug($lower);

        foreach ($attractions as $attraction) {
            $enName = strtolower(trim($attraction->getTranslation('name', 'en') ?: ''));
            if ($enName === $lower || $attraction->slug === $targetSlug) {
                return $attraction;
            }
        }

        foreach ($attractions as $attraction) {
            $enName = strtolower(trim($attraction->getTranslation('name', 'en') ?: ''));
            if (!empty($enName) && (str_contains($lower, $enName) || str_contains($enName, $lower))) {
                return $attraction;
            }
        }

        return null;
    }

    /**
     * Guess the appropriate city for an attraction based on keywords or city names.
     */
    protected function guessCityForAttraction(string $name, $cities): ?City
    {
        $lowerName = strtolower(trim($name));

        // 1. Direct city name mention in attraction title
        foreach ($cities as $city) {
            $enName = strtolower(trim($city->getTranslation('name', 'en') ?: ''));
            if (!empty($enName) && strlen($enName) > 2 && str_contains($lowerName, $enName)) {
                return $city;
            }
        }

        // 2. Keyword dictionaries mapped to well-known destination cities
        $landmarkCityMap = [
            'alexandria' => ['alexandria', 'amphitheatre', 'amphitheater', 'catacomb', 'shuqqafa', 'pompey', 'library', 'bibliotheca', 'qaitbay', 'montaza'],
            'luxor' => ['luxor', 'hatshepsut', 'karnak', 'kings', 'queens', 'memnon', 'habu', 'dendera'],
            'cairo' => ['cairo', 'giza', 'pyramid', 'sphinx', 'saqqara', 'sakkara', 'dahshur', 'memphis', 'museum', 'khalili', 'tahrir', 'citadel', 'bazaar'],
            'aswan' => ['aswan', 'philae', 'high dam', 'unfinished obelisk', 'obelisk', 'kom ombo', 'edfu', 'abu simbel', 'nubian', 'felucca'],
            'hurghada' => ['hurghada', 'giftun', 'makadi', 'el gouna', 'soma bay'],
            'sharm el sheikh' => ['sharm', 'ras mohammed', 'naama', 'tiran', 'dahab'],
        ];

        foreach ($landmarkCityMap as $cityName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lowerName, $kw)) {
                    $matched = $this->findMatchingCity($cityName, $cities);
                    if ($matched) {
                        return $matched;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Create a missing attraction in the database and associate with city.
     */
    protected function createAttraction(string $name, ?City $city): Attraction
    {
        $baseSlug = Str::slug($name) ?: 'attraction';
        $slug = $baseSlug;
        $counter = 1;
        while (Attraction::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return Attraction::create([
            'city_id' => $city?->id,
            'slug' => $slug,
            'name' => ['en' => $name, 'ar' => ''],
            'short_description' => ['en' => "Visit {$name}", 'ar' => ''],
            'description' => ['en' => "Explore {$name} as part of your tour itinerary.", 'ar' => ''],
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Persist package record and all its related structures into DB.
     */
    protected function persistPackage(array $data, array $taxonomy, ?Package $existingPackage, bool $updateMode): Package
    {
        $title = $data['title'];
        $slug = $existingPackage?->slug ?? $this->generateUniqueSlug($title);

        $packageAttributes = [
            'category_id' => $taxonomy['category']?->id,
            'primary_country_id' => $taxonomy['country']?->id,
            'currency_id' => $taxonomy['currency']?->id,
            'package_type' => $data['package_type'],
            'slug' => $slug,

            'title' => ['en' => $title, 'ar' => ''],
            'subtitle' => ['en' => $data['subtitle'] ?? '', 'ar' => ''],
            'short_description' => ['en' => $data['short_description'] ?? '', 'ar' => ''],
            'description' => ['en' => $data['description'] ?? '', 'ar' => ''],

            'duration_days' => $data['duration_days'],
            'duration_nights' => $data['duration_nights'],
            'duration_text' => $data['duration_text'],
            'route_text' => $data['route_text'],

            'start_from_price' => $data['start_from_price'],
            'compare_price' => $data['compare_price'] ?? null,
            'price_from' => $data['price_from'],
            'price_to' => $data['price_to'],
            'adult_price' => $data['adult_price'],

            'schedule_text' => ['en' => $data['schedule_text'], 'ar' => ''],
            'pickup_location' => ['en' => $data['pickup_location'], 'ar' => ''],
            'dropoff_location' => ['en' => $data['dropoff_location'], 'ar' => ''],
            'destinations_text' => ['en' => $data['route_text'], 'ar' => ''],
            'location_summary' => ['en' => $data['route_text'], 'ar' => ''],

            'tour_type' => $data['tour_type'],

            'pricing_information' => $data['policies']['pricing_information'] ?? null,
            'children_policy' => $data['policies']['children_policy'] ?? null,
            'cancellation_policy' => ['en' => $data['policies']['cancellation_policy'] ?? '', 'ar' => ''],
            'terms_conditions' => ['en' => $data['policies']['terms_conditions'] ?? '', 'ar' => ''],
            'pickup_policy' => $data['policies']['pickup_policy'] ?? null,

            'faq_json' => $data['faq'],

            'seo_title' => ['en' => $data['seo_title'], 'ar' => ''],
            'seo_description' => ['en' => $data['seo_description'], 'ar' => ''],
            'breadcrumb_title' => ['en' => $data['breadcrumb_title'], 'ar' => ''],
            'canonical_url' => null, // Leave null for TravelNest frontend to manage

            'source_type' => 'external_url',
            'source_remote_id' => $data['source_id'],
            'source_remote_slug' => $data['source_slug'],
            'source_synced_at' => now(),

            'is_active' => true,
        ];

        if ($existingPackage && $updateMode) {
            $existingPackage->update($packageAttributes);
            $package = $existingPackage->fresh();

            // Delete existing child relations before recreating
            $package->highlights()->delete();
            $package->itineraries()->delete();
            $package->inclusions()->delete();
            $package->packageAttractions()->delete();
            $package->tourPackageAccommodations()->delete();
            $package->cities()->detach();
            $package->tags()->detach();
        } else {
            $package = Package::create($packageAttributes);
        }

        // 1. Create Highlights
        foreach ($data['highlights'] ?? [] as $index => $highlight) {
            $hTitle = is_array($highlight) ? ($highlight['title'] ?? '') : (string) $highlight;
            $hDesc = is_array($highlight) ? ($highlight['description'] ?? $hTitle) : $hTitle;

            $package->highlights()->create([
                'title' => ['en' => $hTitle, 'ar' => ''],
                'description' => ['en' => $hDesc, 'ar' => ''],
                'sort_order' => $index + 1,
            ]);
        }

        // 2. Create Inclusions & Exclusions
        foreach ($data['inclusions'] ?? [] as $index => $inc) {
            $package->inclusions()->create([
                'type' => 'included',
                'item_type' => 'included',
                'title' => Str::limit($inc['title'] ?? $inc['content'], 190, ''),
                'content' => ['en' => $inc['content'], 'ar' => ''],
                'description' => $inc['content'],
                'sort_order' => $index + 1,
            ]);
        }

        foreach ($data['exclusions'] ?? [] as $index => $exc) {
            $package->inclusions()->create([
                'type' => 'excluded',
                'item_type' => 'excluded',
                'title' => Str::limit($exc['title'] ?? $exc['content'], 190, ''),
                'content' => ['en' => $exc['content'], 'ar' => ''],
                'description' => $exc['content'],
                'sort_order' => $index + 1,
            ]);
        }

        // 3. Create Daily Itineraries
        $seenDayNumbers = [];
        foreach ($data['itinerary'] ?? [] as $index => $day) {
            $dayNumber = (int) ($day['day_number'] ?? ($index + 1));
            if (in_array($dayNumber, $seenDayNumbers, true)) {
                $dayNumber = count($seenDayNumbers) > 0 ? max($seenDayNumbers) + 1 : ($index + 1);
            }
            $seenDayNumbers[] = $dayNumber;

            $package->itineraries()->create([
                'day_number' => $day['day_number'] ?? ($index + 1),
                'day_number' => $dayNumber,
                'title' => ['en' => $day['title'], 'ar' => ''],
                'description' => ['en' => $day['description'], 'ar' => ''],
                'meals' => $day['meals'] ?? [],
                'meals_breakfast' => (bool) ($day['meals_breakfast'] ?? false),
                'meals_lunch' => (bool) ($day['meals_lunch'] ?? false),
                'meals_dinner' => (bool) ($day['meals_dinner'] ?? false),
                'overnight_location' => ['en' => $day['overnight_location'] ?? '', 'ar' => ''],
                'accommodation' => ['en' => $day['accommodation'] ?? '', 'ar' => ''],
                'transport_notes' => ['en' => $day['transport_notes'] ?? '', 'ar' => ''],
                'activities' => $day['activities'] ?? [],
                'sort_order' => $day['day_number'] ?? ($index + 1),
                'sort_order' => $dayNumber,
            ]);
        }

        // 4. Create Accommodation Pricing Levels, Seasons, Prices, and Hotels
        $accommodationsData = $data['pricing']['accommodations'] ?? [];
        $hotelsByLevel = $data['hotels'] ?? [];

        foreach ($accommodationsData as $accName => $accData) {
            /** @var TourPackageAccommodation $accommodation */
            $accommodation = $package->tourPackageAccommodations()->create([
                'name' => $accName,
                'description' => $accData['description'] ?? "{$accName} Accommodation Level",
                'sort_order' => $accData['sort_order'] ?? 1,
                'is_active' => true,
            ]);

            // Seasons & Prices
            foreach ($accData['seasons'] ?? [] as $seasonData) {
                /** @var TourPackageSeason $season */
                $season = $accommodation->seasons()->create([
                    'package_id' => $package->id,
                    'name' => ['en' => $seasonData['name'], 'ar' => $seasonData['name']],
                    'currency_id' => $taxonomy['currency']?->id,
                    'is_active' => true,
                    'sort_order' => $seasonData['sort_order'] ?? 1,
                ]);

                foreach ($seasonData['items'] ?? [] as $priceItem) {
                    $season->items()->create([
                        'occupancy_type' => $priceItem['occupancy_type'] ?? 'double',
                        'label' => ['en' => $priceItem['label'], 'ar' => $priceItem['label']],
                        'price' => $priceItem['price'],
                        'price_unit' => $priceItem['price_unit'] ?? 'per_person',
                        'sort_order' => $priceItem['sort_order'] ?? 1,
                        'is_active' => true,
                    ]);
                }
            }

            // Hotels / Cruises for this level
            $levelHotels = $hotelsByLevel[$accName] ?? [];
            $allCities = $taxonomy['all_cities'] ?? City::where('is_active', true)->get();

            foreach ($levelHotels as $hIndex => $hotelData) {
                $cityId = null;
                if (!empty($hotelData['city_name']) && $hotelData['city_name'] !== 'Nile Cruise') {
                    $matchedCity = $this->findMatchingCity($hotelData['city_name'], $allCities);
                    $cityId = $matchedCity?->id;
                }

                $accommodation->hotels()->create([
                    'city_id' => $cityId,
                    'city_name' => $hotelData['city_name'] ?? 'Cairo',
                    'hotel_name' => $hotelData['hotel_name'],
                    'star_rating' => $hotelData['star_rating'] ?? 5,
                    'description' => null,
                    'room_type' => $hotelData['room_type'] ?? 'Standard Room',
                    'meal_plan' => $hotelData['meal_plan'] ?? 'Bed & Breakfast',
                    'alternative_note' => $hotelData['alternative_note'] ?? null,
                    'sort_order' => $hIndex + 1,
                    'is_active' => true,
                ]);
            }
        }

        // 5. Attach Cities pivot
        foreach ($taxonomy['cities'] ?? [] as $cityInfo) {
            $package->cities()->attach($cityInfo['model']->id, [
                'stop_order' => $cityInfo['stop_order'],
                'is_primary' => $cityInfo['is_primary'],
                'nights' => $cityInfo['nights'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Attach Tags
        if (!empty($taxonomy['tags'])) {
            $tagIds = array_map(fn($t) => $t->id, $taxonomy['tags']);
            $package->tags()->syncWithoutDetaching($tagIds);
        }

        // 7. Attach Attractions
        $seenAttractionIds = [];
        foreach ($taxonomy['attractions'] ?? [] as $attraction) {
            if (in_array($attraction->id, $seenAttractionIds, true)) {
                continue;
            }
            $seenAttractionIds[] = $attraction->id;

            $package->packageAttractions()->create([
                'attraction_id' => $attraction->id,
                'title' => ['en' => $attraction->getTranslation('name', 'en'), 'ar' => ''],
                'teaser' => ['en' => $attraction->getTranslation('short_description', 'en') ?: '', 'ar' => ''],
                'image' => $attraction->image,
                'sort_order' => count($seenAttractionIds),
            ]);
        }

        return $package;
    }

    /**
     * Generate unique slug for Package.
     */
    protected function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'tour-package';
        $slug = $baseSlug;
        $counter = 1;

        while (Package::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
