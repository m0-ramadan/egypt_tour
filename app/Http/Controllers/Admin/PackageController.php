<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attraction;
use App\Models\City;
use App\Models\Currency;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PaymentMethod;
use App\Services\PackageAiService;
use App\Services\NileCruisePackageService;
use App\Services\PackageTypeContentService;
use App\Traits\ApiResponseTrait;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    use HandlesTranslatedFields;

    private const PACKAGE_TYPES = [
        'travel_package',
        'nile_cruise',
        'day_tour',
        'shore_excursion',
        'deal',
        'multi_country',
        'custom',
    ];

    protected array $translatedFields = [
        'title',
        'subtitle',
        'short_description',
        'description',
        'schedule_text',
        'pickup_location',
        'dropoff_location',
        'destinations_text',
        'location_summary',
        'cancellation_policy',
        'terms_conditions',
        'seo_title',
        'seo_description',
        'breadcrumb_title',
    ];

    public function index(Request $request): View
    {
        $packages = Package::query()
            ->with(['category', 'primaryCountry', 'currency', 'destination.city'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch(
                    $query,
                    ['title', 'subtitle', 'short_description', 'description'],
                    $request->string('q')
                );
            })
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }
    protected function perPage(Request $request, int $default = 15): int
    {
        return max(5, min((int) $request->input('per_page', $default), 100));
    }
    public function create(): View
    {
        return view('admin.packages.create', [
            'categories' => PackageCategory::all(),
            'destinations' => $this->packageCities(),
            'currencies' => Currency::all(),
            'attractions' => $this->packageAttractionOptions(),
            'nileCruiseTypes' => \App\Models\NileCruiseType::where('is_active', true)->with('categories')->orderBy('sort_order')->get(),
            'paymentMethods' => PaymentMethod::active()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePackage($request);

        DB::transaction(function () use ($request, $validated) {
            $packageData = $this->preparePackageData($request, $validated);

            $package = Package::create($packageData);

            if ($request->has('facilities')) {
                $this->syncFacilities($package, $request);
            }
            $this->syncPackageAttractions($package, $request);
            $this->syncItineraries($package, $request);
            $this->syncInclusions($package, $request);
            $this->syncPrices($package, $request);
            $this->syncPackageCities($package, $request);
            app(PackageTypeContentService::class)->syncFromRequest($package, $request);
            app(NileCruisePackageService::class)->syncFromRequest($package, $request);
            app(\App\Services\PackagePricingService::class)->recalculate($package);
        });

        return redirect()->route('admin.packages.index')->with('success', 'تم إنشاء الرحلة بنجاح.');
    }

    public function show(Package $package): View
    {
        $package->load([
            'category',
            'destination.city',
            'currency',
            'facilities',
            'highlights',
            'packageAttractions.attraction',
            'itineraries',
            'inclusions',
            'prices.currency',
            'nileCruiseType',
            'nileCruiseCategory',
            'cities',
            'nileCruiseDetail',
            'cruise',
            'nileCruiseSchedules.departureCity',
            'nileCruiseSchedules.arrivalCity',
            'nileCruiseCabins',
            'nileCruiseAddons.currency',
            'addons.currency',
            'tourPackageDetail',
            'tourPackageAccommodations.seasons.items.currency',
            'tourPackageAccommodations.hotels',
            'tags',
            'nileCruiseDurations.currency',
            'nileCruiseDurations.departureCity',
            'nileCruiseDurations.arrivalCity',
            'nileCruiseDurations.itineraryDays.activities.attraction',
            'nileCruiseDurations.seasonPrices.currency',
            'nileCruiseDurations.seasonPrices.items.cabin',
        ]);

        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        $package->load([
            'facilities',
            'highlights',
            'packageAttractions.attraction',
            'itineraries',
            'inclusions',
            'prices',
            'cities',
            'nileCruiseDetail',
            'cruise',
            'nileCruiseSchedules.departureCity',
            'nileCruiseSchedules.arrivalCity',
            'nileCruiseCabins',
            'nileCruiseAddons.currency',
            'addons.currency',
            'tourPackageDetail',
            'tourPackageAccommodations.seasons.items.currency',
            'tourPackageAccommodations.hotels',
            'tags',
            'nileCruiseDurations.itineraryDays.activities.attraction',
            'nileCruiseDurations.seasonPrices.items.cabin',
        ]);

        return view('admin.packages.edit', [
            'package' => $package,
            'categories' => PackageCategory::all(),
            'destinations' => $this->packageCities(),
            'currencies' => Currency::all(),
            'attractions' => $this->packageAttractionOptions(
                $package->packageAttractions->pluck('attraction_id')->all()
            ),
            'nileCruiseTypes' => \App\Models\NileCruiseType::where('is_active', true)->with('categories')->orderBy('sort_order')->get(),
            'paymentMethods' => PaymentMethod::active()->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $this->validatePackage($request);

        DB::transaction(function () use ($request, $validated, $package) {
            $packageData = $this->preparePackageData($request, $validated, $package);

            $package->update($packageData);

            if ($request->has('facilities')) {
                $this->syncFacilities($package, $request);
            }
            $this->syncPackageAttractions($package, $request);
            $this->syncItineraries($package, $request);
            $this->syncInclusions($package, $request);
            $this->syncPrices($package, $request);
            $this->syncPackageCities($package, $request);
            app(PackageTypeContentService::class)->syncFromRequest($package, $request);
            app(NileCruisePackageService::class)->syncFromRequest($package, $request);
            app(\App\Services\PackagePricingService::class)->recalculate($package);
        });

        return $this->success('admin.packages.index', 'تم تعديل الرحلة بنجاح.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $package->delete();

        return $this->success('admin.packages.index', 'تم حذف الرحلة بنجاح.');
    }

    public function createWithAI(): View
    {
        return view('admin.packages.create-with-ai', [
            'destinations' => $this->packageCities(),
            'categories' => PackageCategory::all(),
            'currencies' => Currency::all(),
        ]);
    }

    public function storeWithAI(Request $request, PackageAiService $packageAiService): RedirectResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'category_id' => ['nullable', 'integer'],
            'destination_id' => ['nullable', 'integer'],
            'primary_country_id' => ['nullable', 'integer'],
            'currency_id' => ['nullable', 'integer'],
            'package_type' => ['nullable', 'string', 'in:' . implode(',', self::PACKAGE_TYPES)],
            'tour_type' => ['nullable', 'string'],
            'difficulty_level' => ['nullable', 'string'],
            'booking_mode' => ['nullable', 'string'],
            'duration_type' => ['nullable', 'string', 'in:days,hours'],
            'duration_days' => ['nullable', 'integer'],
            'duration_nights' => ['nullable', 'integer'],
            'duration_hours' => ['nullable', 'integer'],
            'route_text' => ['nullable', 'string'],
            'schedule_text' => ['nullable', 'string'],
            'luxury_level' => ['nullable', 'string'],
            'content_language' => ['nullable', 'string'],
            'extra_instructions' => ['nullable', 'string'],
        ]);

        $selectedCity = !empty($data['destination_id'])
            ? City::find($data['destination_id'])
            : null;
        $destination = $selectedCity
            ? $this->resolveDestinationAttractionFromCityId((int) $selectedCity->id)
            : null;

        $category = !empty($data['category_id'])
            ? PackageCategory::find($data['category_id'])
            : null;

        $aiData = $packageAiService->generate([
            'prompt' => $data['prompt'],
            'duration_days' => $data['duration_days'] ?? null,
            'duration_nights' => $data['duration_nights'] ?? null,
            'duration_hours' => $data['duration_hours'] ?? null,
            'route_text' => $data['route_text'] ?? null,
            'schedule_text' => $data['schedule_text'] ?? null,
            'luxury_level' => $data['luxury_level'] ?? null,
            'content_language' => $data['content_language'] ?? 'en',
            'extra_instructions' => $data['extra_instructions'] ?? null,
            'destination_name' => $this->adminTrans($selectedCity?->name),
            'category_name' => $this->adminTrans($category?->name),
        ]);

        if (!$aiData || !is_array($aiData)) {
            return back()->withInput()->with('error', 'فشل توليد بيانات الرحلة بالذكاء الاصطناعي.');
        }

        DB::transaction(function () use ($request, $data, $aiData, $destination, $selectedCity) {
            $finalData = array_merge($aiData, $data);

            unset(
                $finalData['prompt'],
                $finalData['luxury_level'],
                $finalData['content_language'],
                $finalData['extra_instructions']
            );

            $finalData['destination_id'] = $destination?->id;

            if (!empty($selectedCity?->country_id)) {
                $finalData['primary_country_id'] = $selectedCity->country_id;
            }

            $finalData['is_active'] = true;
            $finalData['is_featured'] = false;
            $finalData['is_best_seller'] = false;
            $finalData['is_ultra_luxury'] = false;
            $finalData['rating_avg'] = $finalData['rating_avg'] ?? 0;
            $finalData['reviews_count'] = $finalData['reviews_count'] ?? 0;
            $finalData['sort_order'] = $finalData['sort_order'] ?? 0;
            $finalData['duration_type'] = $finalData['duration_type']
                ?? (!empty($finalData['duration_hours']) ? 'hours' : 'days');

            if ($finalData['duration_type'] === 'hours') {
                $finalData['duration_days'] = null;
                $finalData['duration_nights'] = null;
            } else {
                $finalData['duration_hours'] = null;
            }

            $packageData = $this->prepareAiPackageData($finalData);

            $package = Package::create($packageData);

            $this->createFacilitiesFromArray($package, $aiData['facilities'] ?? []);
            $this->createItinerariesFromArray($package, $aiData['itinerary'] ?? []);
            $this->createInclusionsFromArray($package, $aiData['included'] ?? [], 'included');
            $this->createInclusionsFromArray($package, $aiData['excluded'] ?? [], 'excluded');
            $this->createPricesFromArray($package, $aiData['prices'] ?? [], $package->currency_id);
            $this->syncPackageCities($package);
        });

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'تم إنشاء الرحلة بالذكاء الاصطناعي.');
    }

    public function toggleStatus(Package $package): RedirectResponse
    {
        $package->update(['is_active' => !(bool) $package->is_active]);

        return back()->with('success', 'تم تحديث حالة الرحلة.');
    }

    public function toggleFeatured(Package $package): RedirectResponse
    {
        $package->update(['is_featured' => !(bool) $package->is_featured]);

        return back()->with('success', 'تم تحديث تمييز الرحلة.');
    }

    public function duplicate(Package $package): RedirectResponse
    {
        DB::transaction(function () use ($package) {
            $package->load(['facilities', 'packageAttractions', 'itineraries', 'inclusions', 'prices']);

            $copy = $package->replicate();
            $copy->slug = $package->slug . '-' . now()->timestamp;
            $copy->title = [
                'en' => ($package->display_title ?? $this->adminTrans($package->title)) . ' (Copy)',
                'ar' => ($package->display_title ?? $this->adminTrans($package->title)) . ' (Copy)',
            ];
            $copy->save();

            foreach ($package->facilities as $facility) {
                $copy->facilities()->create([
                    'title' => $facility->title,
                    'sort_order' => $facility->sort_order,
                ]);
            }

            foreach ($package->packageAttractions as $packageAttraction) {
                $copy->packageAttractions()->create($packageAttraction->only([
                    'attraction_id',
                    'title',
                    'teaser',
                    'image',
                    'sort_order',
                ]));
            }

            foreach ($package->itineraries as $day) {
                $copy->itineraries()->create($day->only([
                    'duration',
                    'day_number',
                    'title',
                    'description',
                    'meals',
                    'meals_breakfast',
                    'meals_lunch',
                    'meals_dinner',
                ]));
            }

            foreach ($package->inclusions as $item) {
                $copy->inclusions()->create($item->only([
                    'title',
                    'content',
                    'description',
                    'type',
                    'item_type',
                    'sort_order',
                ]));
            }

            foreach ($package->prices as $price) {
                $copy->prices()->create($price->only([
                    'label',
                    'season_name',
                    'price_type',
                    'room_type',
                    'pax_min',
                    'pax_max',
                    'group_size_min',
                    'group_size_max',
                    'amount',
                    'currency_id',
                    'valid_from',
                    'valid_to',
                    'notes',
                ]));
            }

            $this->syncPackageCities($copy);

            session()->flash('duplicated_package_id', $copy->id);
        });

        $copyId = session('duplicated_package_id');

        return redirect()
            ->route('admin.packages.edit', $copyId)
            ->with('success', 'تم نسخ الرحلة بنجاح.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $action = (string) $request->input('action');

        if ($action === 'delete') {
            Package::whereIn('id', $ids)->delete();
        }

        if ($action === 'activate') {
            Package::whereIn('id', $ids)->update(['is_active' => true]);
        }

        if ($action === 'deactivate') {
            Package::whereIn('id', $ids)->update(['is_active' => false]);
        }

        return back()->with('success', 'تم تنفيذ الإجراء بنجاح.');
    }

    public function statistics()
    {
        return response()->json([
            'total' => Package::count(),
            'active' => Package::where('is_active', true)->count(),
            'featured' => Package::where('is_featured', true)->count(),
        ]);
    }

    public function enhanceWithAI(Request $request)
    {
        return response()->json(['message' => 'Connect AI service here.']);
    }

    public function generateSeoWithAI(Request $request)
    {
        return response()->json([
            'meta_title' => $request->input('title'),
            'meta_description' => $request->input('short_description'),
        ]);
    }

    public function translateWithAI(Request $request)
    {
        return response()->json(['message' => 'Connect translation service here.']);
    }

    private function validatePackage(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['nullable', 'integer'],
            'destination_id' => ['nullable', 'integer'],
            'primary_country_id' => ['nullable', 'integer'],
            'package_type' => ['nullable', 'string', 'in:' . implode(',', self::PACKAGE_TYPES)],
            'nile_cruise_type_id' => ['nullable', 'integer', 'exists:nile_cruise_types,id'],
            'nile_cruise_category_id' => ['nullable', 'integer', 'exists:nile_cruise_categories,id'],
            'slug' => ['nullable', 'string', 'max:220'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],

            'duration_type' => ['required', 'string', 'in:days,hours'],
            'duration_days' => ['nullable', 'integer'],
            'duration_nights' => ['nullable', 'integer'],
            'duration_hours' => ['nullable', 'integer'],
            'duration_text' => ['nullable', 'string'],
            'route_text' => ['nullable', 'string'],

            'start_from_price' => ['nullable', 'numeric'],
            'compare_price' => ['nullable', 'numeric'],
            'adult_price' => ['nullable', 'numeric', 'min:0'],
            'child_price' => ['nullable', 'numeric', 'min:0'],
            'infant_price' => ['nullable', 'numeric', 'min:0'],
            'price_1_person' => ['nullable', 'numeric', 'min:0'],
            'price_2_persons' => ['nullable', 'numeric', 'min:0'],
            'price_3_persons' => ['nullable', 'numeric', 'min:0'],
            'price_4_persons' => ['nullable', 'numeric', 'min:0'],
            'price_5_persons' => ['nullable', 'numeric', 'min:0'],
            'price_6_plus_persons' => ['nullable', 'numeric', 'min:0'],
            'adult_min_age' => ['required', 'integer', 'min:0'],
            'child_min_age' => ['required', 'integer', 'min:0'],
            'child_max_age' => ['required', 'integer', 'gte:child_min_age'],
            'infant_min_age' => ['required', 'integer', 'min:0'],
            'infant_max_age' => ['required', 'integer', 'gte:infant_min_age'],
            'currency_id' => ['nullable', 'integer'],

            'schedule_text' => ['nullable', 'string'],
            'pickup_location' => ['nullable', 'string'],
            'dropoff_location' => ['nullable', 'string'],
            'destinations_text' => ['nullable', 'string'],
            'location_summary' => ['nullable', 'string'],
            'tour_type' => ['nullable', 'string'],
            'difficulty_level' => ['nullable', 'string'],
            'booking_mode' => ['nullable', 'string'],

            'rating_avg' => ['nullable', 'numeric'],
            'reviews_count' => ['nullable', 'integer'],
            'min_participants' => ['nullable', 'integer'],
            'max_participants' => ['nullable', 'integer'],
            'booking_lead_days' => ['nullable', 'integer'],

            'cancellation_policy' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'children_policy' => ['nullable', 'string'],
            'pickup_policy' => ['nullable', 'string'],
            'pricing_information' => ['nullable', 'string'],

            'video_url' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer'],

            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
            'breadcrumb_title' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'string'],

            'is_featured' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'is_ultra_luxury' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],

            'featured_image' => ['nullable', 'image', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['nullable', 'image', 'max:5120'],

            'facilities' => ['nullable', 'array'],
            'facilities.*.title' => ['nullable', 'string'],
            'facilities.*.sort_order' => ['nullable', 'integer'],

            'attraction_ids' => ['nullable', 'array'],
            'attraction_ids.*' => ['integer', 'distinct', 'exists:attractions,id'],

            'itinerary' => ['nullable', 'array'],
            'itinerary.*.duration' => ['nullable', 'string'],
            'itinerary.*.day_number' => ['nullable', 'integer'],
            'itinerary.*.title' => ['nullable', 'string'],
            'itinerary.*.description' => ['nullable', 'string'],
            'itinerary.*.start_time' => ['nullable', 'date_format:H:i'],
            'itinerary.*.end_time' => ['nullable', 'date_format:H:i'],
            'itinerary.*.overnight_location' => ['nullable', 'string'],
            'itinerary.*.accommodation' => ['nullable', 'string'],
            'itinerary.*.transport_notes' => ['nullable', 'string'],
            'itinerary.*.activities' => ['nullable', 'array'],
            'itinerary.*.activities.*.time' => ['nullable', 'string', 'max:50'],
            'itinerary.*.activities.*.title' => ['nullable', 'string', 'max:255'],
            'itinerary.*.activities.*.location' => ['nullable', 'string', 'max:255'],
            'itinerary.*.activities.*.duration' => ['nullable', 'string', 'max:100'],
            'itinerary.*.activities.*.description' => ['nullable', 'string'],
            'itinerary.*.meals' => ['nullable', 'array'],
            'itinerary.*.meals.*' => ['nullable', 'string'],
            'itinerary.*.meals_breakfast' => ['nullable', 'boolean'],
            'itinerary.*.meals_lunch' => ['nullable', 'boolean'],
            'itinerary.*.meals_dinner' => ['nullable', 'boolean'],

            'included' => ['nullable', 'array'],
            'included.*.title' => ['nullable', 'string'],

            'excluded' => ['nullable', 'array'],
            'excluded.*.title' => ['nullable', 'string'],

            'prices' => ['nullable', 'array'],
            'prices.*.label' => ['nullable', 'string'],
            'prices.*.season_name' => ['nullable', 'string'],
            'prices.*.price_type' => ['nullable', 'string'],
            'prices.*.room_type' => ['nullable', 'string'],
            'prices.*.pax_min' => ['nullable', 'integer', 'min:1'],
            'prices.*.pax_max' => ['nullable', 'integer', 'min:1'],
            'prices.*.amount' => ['nullable', 'numeric'],
            'prices.*.currency_id' => ['nullable', 'integer'],
            'prices.*.valid_from' => ['nullable', 'date'],
            'prices.*.valid_to' => ['nullable', 'date'],
            'prices.*.notes' => ['nullable', 'string'],

            'tour_city_ids' => ['nullable', 'array'],
            'tour_city_ids.*' => ['integer', 'distinct', 'exists:cities,id'],
            'highlights' => ['nullable'],
            'highlights.*.title' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable'],

            // Shared content used by Day Trip, Tour Package and Nile Cruise.
            'experience' => ['nullable', 'array'],
            'experience._present' => ['nullable', 'boolean'],
            'experience.what_to_bring' => ['nullable'],
            'experience.on_tour_languages' => ['nullable'],
            'experience.operating_days' => ['nullable', 'array'],
            'experience.operating_days.*' => ['nullable', 'string', 'max:20'],
            'experience.departure_times' => ['nullable'],
            'experience.tour_timezone' => ['nullable', 'string', 'max:100'],
            'experience.default_seat_capacity' => ['nullable', 'integer', 'min:1'],
            'experience.brochure' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'experience.remove_brochure' => ['nullable', 'boolean'],
            'experience.promotional_videos' => ['nullable'],
            'experience.deposit_policy' => ['nullable', 'string', 'in:inherit,required,not_required'],
            'experience.deposit_type' => ['nullable', 'string', 'in:percent,fixed'],
            'experience.deposit_value' => ['nullable', 'numeric', 'min:0'],
            'experience.allowed_payment_method_ids' => ['nullable', 'array'],
            'experience.allowed_payment_method_ids.*' => ['integer', 'exists:payment_methods,id'],
            'experience.focus_keyword' => ['nullable', 'string', 'max:255'],
            'experience.meta_keywords' => ['nullable'],
            'experience.og_title' => ['nullable', 'string', 'max:255'],
            'experience.og_description' => ['nullable', 'string', 'max:1000'],
            'experience.og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'experience.remove_og_image' => ['nullable', 'boolean'],
            'experience.twitter_card' => ['nullable', 'string', 'in:summary,summary_large_image'],
            'experience.twitter_title' => ['nullable', 'string', 'max:255'],
            'experience.twitter_description' => ['nullable', 'string', 'max:1000'],
            'experience.robots_index' => ['nullable', 'boolean'],
            'experience.robots_follow' => ['nullable', 'boolean'],
            'experience.itinerary_mode' => ['nullable', 'string', 'in:simple,advanced'],
            'experience.group_pricing_tiers' => ['nullable', 'array'],
            'experience.group_pricing_tiers.*.id' => ['nullable', 'string', 'max:80'],
            'experience.group_pricing_tiers.*.label' => ['nullable', 'string', 'max:120'],
            'experience.group_pricing_tiers.*.min' => ['nullable', 'integer', 'min:1'],
            'experience.group_pricing_tiers.*.max' => ['nullable', 'integer', 'min:1'],
            'experience.group_pricing_tiers.*.price_per_person' => ['nullable', 'numeric', 'min:0'],
            'experience.addons' => ['nullable', 'array'],
            'experience.addons.*.title' => ['nullable', 'string', 'max:255'],
            'experience.addons.*.description' => ['nullable', 'string'],
            'experience.addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'experience.addons.*.currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'experience.addons.*.price_unit' => ['nullable', 'string', 'max:80'],
            'experience.addons.*.is_active' => ['nullable', 'boolean'],

            // Tour Package-only details.
            'tour_package' => ['nullable', 'array'],
            'tour_package._present' => ['nullable', 'boolean'],
            'tour_package.accommodation_standard' => ['nullable', 'string', 'max:120'],
            'tour_package.meals_included' => ['nullable'],
            'tour_package.flexible_itinerary' => ['nullable', 'boolean'],
            'tour_package.additional_notes' => ['nullable', 'string'],

            // Nile Cruise extended content: intentionally nullable and only consumed for nile_cruise packages.
            'nile_cruise' => ['nullable', 'array'],
            'nile_cruise._present' => ['nullable', 'boolean'],
            'nile_cruise.facility_titles' => ['nullable', 'array'],
            'nile_cruise.facility_titles.*' => ['nullable', 'string', 'max:255'],
            'nile_cruise.decks' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.sun_beds' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.sun_deck_pergolas' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.tour_style' => ['nullable', 'string', 'max:255'],
            'nile_cruise.ship_name' => ['nullable', 'string', 'max:255'],
            'nile_cruise.cruise_class' => ['nullable', 'string', 'max:255'],
            'nile_cruise.star_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'nile_cruise.all_inclusive' => ['nullable', 'boolean'],
            'nile_cruise.what_to_bring' => ['nullable'],
            'nile_cruise.on_tour_languages' => ['nullable'],
            'nile_cruise.timezone' => ['nullable', 'string', 'max:100'],
            'nile_cruise.operating_days' => ['nullable', 'array'],
            'nile_cruise.operating_days.*' => ['nullable', 'string', 'max:20'],
            'nile_cruise.promotional_videos' => ['nullable', 'string'],
            'nile_cruise.deposit_policy' => ['nullable', 'string', 'in:inherit,required,not_required'],
            'nile_cruise.deposit_type' => ['nullable', 'string', 'in:percent,fixed'],
            'nile_cruise.deposit_value' => ['nullable', 'numeric', 'min:0'],
            'nile_cruise.allowed_payment_method_ids' => ['nullable', 'array'],
            'nile_cruise.allowed_payment_method_ids.*' => ['integer', 'exists:payment_methods,id'],
            'nile_cruise.focus_keyword' => ['nullable', 'string', 'max:255'],
            'nile_cruise.meta_keywords' => ['nullable'],
            'nile_cruise.og_title' => ['nullable', 'string', 'max:255'],
            'nile_cruise.og_description' => ['nullable', 'string', 'max:1000'],
            'nile_cruise.social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'nile_cruise.remove_social_image' => ['nullable', 'boolean'],
            'nile_cruise.twitter_card' => ['nullable', 'string', 'in:summary,summary_large_image'],
            'nile_cruise.twitter_title' => ['nullable', 'string', 'max:255'],
            'nile_cruise.twitter_description' => ['nullable', 'string', 'max:1000'],
            'nile_cruise.robots_index' => ['nullable', 'boolean'],
            'nile_cruise.robots_follow' => ['nullable', 'boolean'],
            'nile_cruise.addons' => ['nullable', 'array'],
            'nile_cruise.addons.*.name' => ['nullable', 'string', 'max:255'],
            'nile_cruise.addons.*.description' => ['nullable', 'string'],
            'nile_cruise.addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'nile_cruise.addons.*.currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'nile_cruise.addons.*.is_active' => ['nullable', 'boolean'],
            'nile_cruise.route_summary' => ['nullable', 'string', 'max:255'],
            'nile_cruise.pickup_notes' => ['nullable', 'string'],
            'nile_cruise.dropoff_notes' => ['nullable', 'string'],
            'nile_cruise.additional_notes' => ['nullable', 'string'],
            'nile_cruise.fact_sheet' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'nile_cruise.remove_fact_sheet' => ['nullable', 'boolean'],
            'nile_cruise.route_city_ids' => ['nullable', 'array'],
            'nile_cruise.route_city_ids.*' => ['nullable', 'integer', 'exists:cities,id'],
            'nile_cruise.schedules' => ['nullable', 'array'],
            'nile_cruise.schedules.*.departure_day' => ['nullable', 'string', 'max:50'],
            'nile_cruise.schedules.*.departure_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'nile_cruise.schedules.*.arrival_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'nile_cruise.schedules.*.direction' => ['nullable', 'string', 'max:255'],
            'nile_cruise.schedules.*.notes' => ['nullable', 'string'],
            'nile_cruise.schedules.*.is_active' => ['nullable', 'boolean'],
            'nile_cruise.cabins' => ['nullable', 'array'],
            'nile_cruise.cabins.*.id' => ['nullable', 'integer'],
            'nile_cruise.cabins.*.client_key' => ['nullable', 'string', 'max:100'],
            'nile_cruise.cabins.*.name' => ['nullable', 'string', 'max:255'],
            'nile_cruise.cabins.*.quantity' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.cabins.*.bed_type' => ['nullable', 'string', 'max:255'],
            'nile_cruise.cabins.*.size_sqm' => ['nullable', 'numeric', 'min:0'],
            'nile_cruise.cabins.*.max_adults' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.cabins.*.max_children' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.cabins.*.has_private_bathroom' => ['nullable', 'boolean'],
            'nile_cruise.cabins.*.has_private_terrace' => ['nullable', 'boolean'],
            'nile_cruise.cabins.*.amenities' => ['nullable'],
            'nile_cruise.cabins.*.description' => ['nullable', 'string'],
            'nile_cruise.cabins.*.existing_image' => ['nullable', 'string'],
            'nile_cruise.cabins.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'nile_cruise.durations' => ['nullable', 'array'],
            'nile_cruise.durations.*.title' => ['nullable', 'string', 'max:255'],
            'nile_cruise.durations.*.days' => ['nullable', 'integer', 'min:1'],
            'nile_cruise.durations.*.nights' => ['nullable', 'integer', 'min:0'],
            'nile_cruise.durations.*.direction' => ['nullable', 'string', 'max:255'],
            'nile_cruise.durations.*.departure_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'nile_cruise.durations.*.arrival_city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'nile_cruise.durations.*.departure_day' => ['nullable', 'string', 'max:50'],
            'nile_cruise.durations.*.currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'nile_cruise.durations.*.is_default' => ['nullable', 'boolean'],
            'nile_cruise.durations.*.is_active' => ['nullable', 'boolean'],
            'nile_cruise.durations.*.itinerary' => ['nullable', 'array'],
            'nile_cruise.durations.*.itinerary.*.day_number' => ['nullable', 'integer', 'min:1'],
            'nile_cruise.durations.*.itinerary.*.title' => ['nullable', 'string'],
            'nile_cruise.durations.*.itinerary.*.description' => ['nullable', 'string'],
            'nile_cruise.durations.*.itinerary.*.meals' => ['nullable'],
            'nile_cruise.durations.*.itinerary.*.overnight' => ['nullable', 'string'],
            'nile_cruise.durations.*.itinerary.*.activities' => ['nullable', 'array'],
            'nile_cruise.durations.*.itinerary.*.activities.*.title' => ['nullable', 'string'],
            'nile_cruise.durations.*.itinerary.*.activities.*.description' => ['nullable', 'string'],
            'nile_cruise.durations.*.itinerary.*.activities.*.attraction_id' => ['nullable', 'integer', 'exists:attractions,id'],
            'nile_cruise.durations.*.seasons' => ['nullable', 'array'],
            'nile_cruise.durations.*.seasons.*.season_name' => ['nullable', 'string', 'max:255'],
            'nile_cruise.durations.*.seasons.*.date_from' => ['nullable', 'date'],
            'nile_cruise.durations.*.seasons.*.date_to' => ['nullable', 'date'],
            'nile_cruise.durations.*.seasons.*.currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'nile_cruise.durations.*.seasons.*.notes' => ['nullable', 'string'],
            'nile_cruise.durations.*.seasons.*.is_active' => ['nullable', 'boolean'],
            'nile_cruise.durations.*.seasons.*.items' => ['nullable', 'array'],
            'nile_cruise.durations.*.seasons.*.items.*.cabin_key' => ['nullable', 'string', 'max:100'],
            'nile_cruise.durations.*.seasons.*.items.*.occupancy_type' => ['nullable', 'string', 'max:50'],
            'nile_cruise.durations.*.seasons.*.items.*.label' => ['nullable', 'string', 'max:255'],
            'nile_cruise.durations.*.seasons.*.items.*.price' => ['nullable', 'numeric', 'min:0'],

            'faq_json' => ['nullable', 'array'],
            'faq_json.*.question' => ['nullable', 'string'],
            'faq_json.*.answer' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $adultMinAge = (int) $request->input('adult_min_age', 12);
            $childMinAge = (int) $request->input('child_min_age', 2);
            $childMaxAge = (int) $request->input('child_max_age', 11);
            $infantMinAge = (int) $request->input('infant_min_age', 0);
            $infantMaxAge = (int) $request->input('infant_max_age', 1);

            if ($infantMaxAge >= $childMinAge) {
                $validator->errors()->add('infant_max_age', __('يجب أن يكون الحد الأعلى لعمر الرضع أقل من الحد الأدنى لعمر الأطفال.'));
            }

            if ($childMaxAge >= $adultMinAge) {
                $validator->errors()->add('child_max_age', __('يجب أن يكون الحد الأعلى لعمر الأطفال أقل من الحد الأدنى لعمر البالغين.'));
            }

            if ($adultMinAge <= $infantMaxAge) {
                $validator->errors()->add('adult_min_age', __('حد البالغين يجب أن يكون أكبر من الحد الأعلى للرضع.'));
            }

            $packageType = $request->input('package_type');

            if ($packageType === 'day_tour') {
                if ($request->input('duration_type') !== 'hours') {
                    $validator->errors()->add('duration_type', __('مدة الرحلة اليومية يجب أن تحسب بالساعات.'));
                }
                if ((int) $request->input('duration_hours', 0) < 1) {
                    $validator->errors()->add('duration_hours', __('يرجى تحديد مدة الرحلة اليومية بالساعات (ساعة واحدة على الأقل).'));
                }
            }

            if ($packageType === 'travel_package') {
                if ($request->input('duration_type') !== 'days') {
                    $validator->errors()->add('duration_type', __('مدة الباقة السياحية يجب أن تحسب بالأيام والليالي.'));
                }
                if ((int) $request->input('duration_days', 0) < 1) {
                    $validator->errors()->add('duration_days', __('يرجى تحديد عدد أيام الرحلة (يوم واحد على الأقل).'));
                }
                if ((int) $request->input('duration_nights', 0) < 0) {
                    $validator->errors()->add('duration_nights', __('عدد الليالي لا يمكن أن يكون بالسالب.'));
                }
            }
            $nileCruiseTypeId = $request->input('nile_cruise_type_id');
            $nileCruiseCategoryId = $request->input('nile_cruise_category_id');

            if ($packageType === 'nile_cruise') {
                if (empty($nileCruiseTypeId)) {
                    $validator->errors()->add('nile_cruise_type_id', __('يرجى اختيار نوع نايل كروز (Nile Cruise Type).'));
                } else {
                    $cruiseType = \App\Models\NileCruiseType::find($nileCruiseTypeId);
                    if ($cruiseType) {
                        $hasCategories = $cruiseType->categories()->count() > 0;
                        if ($hasCategories) {
                            if (empty($nileCruiseCategoryId)) {
                                $validator->errors()->add('nile_cruise_category_id', __('يرجى اختيار فئة نايل كروز (Nile Cruise Category) لهذا النوع.'));
                            } else {
                                $category = \App\Models\NileCruiseCategory::find($nileCruiseCategoryId);
                                if (!$category || (int) $category->nile_cruise_type_id !== (int) $cruiseType->id) {
                                    $validator->errors()->add('nile_cruise_category_id', __('فئة نايل كروز المختارة لا تنتمي لنوع نايل كروز المختار.'));
                                }
                            }
                        } else {
                            if (!empty($nileCruiseCategoryId)) {
                                $validator->errors()->add('nile_cruise_category_id', __('فئة نايل كروز يجب أن تكون فارغة لهذا النوع.'));
                            }
                        }
                    }
                }
            }

            if ($request->input('package_type') === 'nile_cruise') {
                foreach ((array) $request->input('nile_cruise.durations', []) as $durationIndex => $duration) {
                    foreach ((array) ($duration['seasons'] ?? []) as $seasonIndex => $season) {
                        $from = $season['date_from'] ?? null;
                        $to = $season['date_to'] ?? null;
                        if ($from && $to && strtotime((string) $to) < strtotime((string) $from)) {
                            $validator->errors()->add(
                                "nile_cruise.durations.{$durationIndex}.seasons.{$seasonIndex}.date_to",
                                'Season end date must be on or after the start date.'
                            );
                        }
                    }
                }
            }

            foreach ((array) $request->input('prices', []) as $index => $price) {
                $paxMin = $price['pax_min'] ?? null;
                $paxMax = $price['pax_max'] ?? null;

                if ($paxMin !== null && $paxMin !== '' && $paxMax !== null && $paxMax !== ''
                    && (int) $paxMax < (int) $paxMin) {
                    $validator->errors()->add(
                        "prices.{$index}.pax_max",
                        'الحد الأقصى لعدد الأفراد يجب أن يساوي أو يزيد عن الحد الأدنى.'
                    );
                }
            }

            foreach (app(\App\Services\PackageTagNormalizer::class)->validationErrors($request->input('tags')) as $message) {
                $validator->errors()->add('tags', $message);
            }
        });

        return $validator->validate();
    }

    private function preparePackageData(Request $request, array $validated, ?Package $package = null): array
    {
        $data = collect($validated)->except([
            'featured_image',
            'gallery_images',
            'facilities',
            'attraction_ids',
            'itinerary',
            'included',
            'excluded',
            'prices',
            'faq_json',
            'nile_cruise',
            'experience',
            'tour_package',
            'tour_city_ids',
            'highlights',
            'tags',
        ])->toArray();

        $selectedCity = !empty($data['destination_id'])
            ? City::find($data['destination_id'])
            : null;
        $selectedAttraction = $selectedCity
            ? $this->resolveDestinationAttractionFromCityId((int) $selectedCity->id)
            : null;

        $data['package_type'] = $this->normalizePackageType($data['package_type'] ?? null);
        if ($data['package_type'] === 'nile_cruise') {
            $data['destination_id'] = null;
        }

        $data['duration_type'] = match ($data['package_type']) {
            'day_tour' => 'hours',
            'travel_package' => 'days',
            default => $data['duration_type'] ?? 'days',
        };

        if ($data['package_type'] !== 'nile_cruise') {
            $data['nile_cruise_type_id'] = null;
            $data['nile_cruise_category_id'] = null;
        } elseif (!empty($data['nile_cruise_type_id'])) {
            $cruiseType = \App\Models\NileCruiseType::find($data['nile_cruise_type_id']);
            if ($cruiseType && $cruiseType->categories()->count() === 0) {
                $data['nile_cruise_category_id'] = null;
            }
        }

        if ($data['duration_type'] === 'hours') {
            $data['duration_days'] = null;
            $data['duration_nights'] = null;
        } else {
            $data['duration_hours'] = null;
        }

        if (empty($data['primary_country_id']) && !empty($selectedCity?->country_id)) {
            $data['primary_country_id'] = $selectedCity->country_id;
        }

        $data = $this->translateModelFields($data, $this->translatedFields);
        $data['faq_json'] = $this->normalizeFaqItems((array) $request->input('faq_json', []));

        $data['slug'] = !empty($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['title']['en'] ?? $data['title']['ar'] ?? 'package-' . time());

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_ultra_luxury'] = $request->boolean('is_ultra_luxury');

        [$priceFrom, $priceTo] = $this->resolveCategoryPriceBounds($data);
        $data['price_from'] = $priceFrom;
        $data['price_to'] = $priceTo;
        $data['start_from_price'] = $priceFrom;

        $data['adult_price'] = isset($data['adult_price']) && $data['adult_price'] !== '' && (float) $data['adult_price'] > 0
            ? (float) $data['adult_price']
            : ($priceFrom > 0 ? $priceFrom : 0);
        $data['child_price'] = isset($data['child_price']) && $data['child_price'] !== ''
            ? (float) $data['child_price']
            : 0;
        $data['infant_price'] = isset($data['infant_price']) && $data['infant_price'] !== ''
            ? (float) $data['infant_price']
            : 0;

        $data['tour_type'] = !empty($data['tour_type']) ? $data['tour_type'] : ($package?->tour_type ?? 'private');
        $data['booking_mode'] = !empty($data['booking_mode']) ? $data['booking_mode'] : ($package?->booking_mode ?? 'request');
        $data['difficulty_level'] = !empty($data['difficulty_level']) ? $data['difficulty_level'] : $package?->difficulty_level;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploadFile($request->file('featured_image'), 'packages');
        } elseif ($package) {
            unset($data['featured_image']);
        }

        if ($request->hasFile('gallery_images')) {
            $data['gallery_images'] = $this->uploadMultipleFiles($request->file('gallery_images'), 'packages/gallery');
        } elseif ($package) {
            unset($data['gallery_images']);
        }

        return $data;
    }

    private function normalizePackageType(?string $packageType): string
    {
        $packageType = strtolower(trim((string) $packageType));

        return match ($packageType) {
            'tailor_made' => 'custom',
            'travel-package' => 'travel_package',
            'nile-cruise' => 'nile_cruise',
            'day-tour' => 'day_tour',
            'shore-excursion' => 'shore_excursion',
            'multi-country' => 'multi_country',
            '' => 'travel_package',
            default => in_array($packageType, self::PACKAGE_TYPES, true) ? $packageType : 'travel_package',
        };
    }

    private function normalizeFaqItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question === '' && $answer === '') {
                continue;
            }

            $translated = $this->translateModelFields([
                'question' => $question,
                'answer' => $answer,
            ], ['question', 'answer']);

            $normalized[] = [
                'question' => $translated['question'] ?? ['en' => $question, 'ar' => $question],
                'answer' => $translated['answer'] ?? ['en' => $answer, 'ar' => $answer],
            ];
        }

        return $normalized;
    }

    private function resolveCategoryPriceBounds(array $data): array
    {
        $prices = collect([
            $data['adult_price'] ?? null,
            $data['child_price'] ?? null,
            $data['infant_price'] ?? null,
            $data['price_1_person'] ?? null,
            $data['price_2_persons'] ?? null,
            $data['price_3_persons'] ?? null,
            $data['price_4_persons'] ?? null,
            $data['price_5_persons'] ?? null,
            $data['price_6_plus_persons'] ?? null,
        ])->filter(fn ($price) => $price !== null && $price !== '');

        $paidPrices = $prices->filter(fn ($price) => (float) $price > 0);

        $priceFrom = (float) ($paidPrices->min() ?? 0);
        $priceTo = (float) ($prices->max() ?? 0);

        return [$priceFrom, $priceTo];
    }

    private function prepareAiPackageData(array $data): array
    {
        $packageData = collect($data)->except([
            'facilities',
            'itinerary',
            'included',
            'excluded',
            'prices',
        ])->toArray();

        $packageData = $this->normalizeTranslatedFields($packageData);

        $packageData['slug'] = !empty($packageData['slug'])
            ? Str::slug($packageData['slug'])
            : Str::slug(
                $packageData['title']['en']
                    ?? $packageData['title']['ar']
                    ?? 'package-' . time()
            );

        return $packageData;
    }

    private function packageCities()
    {
        return City::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function packageAttractionOptions(array $includeIds = [])
    {
        return Attraction::query()
            ->with('city')
            ->where(function ($query) use ($includeIds) {
                $query->where('is_active', true);

                if (!empty($includeIds)) {
                    $query->orWhereIn('id', $includeIds);
                }
            })
            ->orderBy('city_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function resolveDestinationAttractionFromCityId(int $cityId): ?Attraction
    {
        return Attraction::query()
            ->where('city_id', $cityId)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    private function normalizeTranslatedFields(array $data): array
    {
        foreach ($this->translatedFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            if (is_array($data[$field])) {
                $data[$field] = [
                    'en' => $data[$field]['en'] ?? $data[$field]['ar'] ?? '',
                    'ar' => $data[$field]['ar'] ?? $data[$field]['en'] ?? '',
                ];
                continue;
            }

            if (is_string($data[$field]) && trim($data[$field]) !== '') {
                $data[$field] = $this->translateModelFields([$field => $data[$field]], [$field])[$field];
                continue;
            }

            $data[$field] = ['en' => '', 'ar' => ''];
        }

        return $data;
    }

    private function syncFacilities(Package $package, Request $request): void
    {
        $package->facilities()->delete();

        foreach ((array) $request->input('facilities', []) as $index => $facility) {
            if (empty($facility['title'])) {
                continue;
            }

            $package->facilities()->create([
                'title' => $facility['title'],
                'sort_order' => $facility['sort_order'] ?? $index,
            ]);
        }
    }

    private function syncPackageAttractions(Package $package, Request $request): void
    {
        $selectedIds = collect((array) $request->input('attraction_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $attractions = Attraction::query()
            ->whereIn('id', $selectedIds)
            ->get()
            ->keyBy('id');

        $package->packageAttractions()->delete();

        foreach ($selectedIds as $sortOrder => $attractionId) {
            $attraction = $attractions->get($attractionId);

            if (!$attraction) {
                continue;
            }

            $package->packageAttractions()->create([
                'attraction_id' => $attraction->id,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function syncPackageCities(Package $package, ?Request $request = null): void
    {
        // Advanced Nile Cruise route order is managed by NileCruisePackageService.
        if ($package->package_type === 'nile_cruise') {
            $package->cities()->detach();
            return;
        }

        $primaryCityId = (int) ($request?->input('destination_id', 0) ?? 0);

        if ($request && $package->package_type === 'travel_package' && $request->has('tour_city_ids')) {
            $cityIds = collect((array) $request->input('tour_city_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($primaryCityId && !$cityIds->contains($primaryCityId)) {
                $cityIds->prepend($primaryCityId);
            }

            $syncData = [];
            foreach ($cityIds as $index => $cityId) {
                $syncData[$cityId] = [
                    'stop_order' => $index,
                    'is_primary' => $cityId === $primaryCityId || (!$primaryCityId && $index === 0),
                    'nights' => null,
                ];
            }
            $package->cities()->sync($syncData);
            return;
        }

        if ($request && $package->package_type === 'day_tour' && $primaryCityId) {
            $package->cities()->sync([
                $primaryCityId => ['stop_order' => 0, 'is_primary' => true, 'nights' => null],
            ]);
            return;
        }

        $package->loadMissing(['destination', 'packageAttractions.attraction']);
        $cityIds = collect();

        if ($package->destination?->city_id) {
            $cityIds->push($package->destination->city_id);
        }

        foreach ($package->packageAttractions as $pa) {
            if ($pa->attraction?->city_id) {
                $cityIds->push($pa->attraction->city_id);
            }
        }

        $syncData = [];
        foreach ($cityIds->filter()->unique()->values() as $index => $cityId) {
            $syncData[$cityId] = [
                'stop_order' => $index,
                'is_primary' => $index === 0,
                'nights' => null,
            ];
        }
        $package->cities()->sync($syncData);
    }

    private function syncItineraries(Package $package, Request $request): void
    {
        // Advanced Nile Cruises own their itinerary per duration/route. Preserve
        // any legacy generic itinerary rows as a fallback instead of deleting
        // them when the Nile Cruise editor is submitted.
        if ($package->package_type === 'nile_cruise' && $request?->boolean('nile_cruise._present')) {
            return;
        }

        $package->itineraries()->delete();
        $position = 0;

        foreach ((array) $request->input('itinerary', []) as $day) {
            if (empty($day['title']) && empty($day['description'])) {
                continue;
            }

            $mealsInput = [];
            if (isset($day['meals']) && is_array($day['meals'])) {
                $mealsInput = array_values(array_filter($day['meals']));
            }

            $hasBreakfast = in_array('breakfast', $mealsInput, true) || !empty($day['meals_breakfast']);
            $hasLunch = in_array('lunch', $mealsInput, true) || !empty($day['meals_lunch']);
            $hasDinner = in_array('dinner', $mealsInput, true) || !empty($day['meals_dinner']);

            $normalizedMeals = array_values(array_unique(array_filter(array_merge(
                $mealsInput,
                $hasBreakfast ? ['breakfast'] : [],
                $hasLunch ? ['lunch'] : [],
                $hasDinner ? ['dinner'] : []
            ))));

            $package->itineraries()->create([
                'duration' => $day['duration'] ?? null,
                'day_number' => $position + 1,
                'title' => $day['title'] ?? null,
                'description' => $day['description'] ?? null,
                'meals' => $package->package_type === 'day_tour' ? [] : $normalizedMeals,
                'meals_breakfast' => $package->package_type === 'day_tour' ? false : $hasBreakfast,
                'meals_lunch' => $package->package_type === 'day_tour' ? false : $hasLunch,
                'meals_dinner' => $package->package_type === 'day_tour' ? false : $hasDinner,
                'overnight_location' => $package->package_type === 'travel_package' ? ($day['overnight_location'] ?? null) : null,
                'accommodation' => $package->package_type === 'travel_package' ? ($day['accommodation'] ?? null) : null,
                'transport_notes' => $package->package_type === 'travel_package' ? ($day['transport_notes'] ?? null) : null,
                'activities' => $package->package_type === 'travel_package' ? $this->normalizeTourPackageActivities($day['activities'] ?? []) : [],
                'start_time' => $package->package_type === 'day_tour' ? ($day['start_time'] ?? null) : null,
                'end_time' => $package->package_type === 'day_tour' ? ($day['end_time'] ?? null) : null,
                'sort_order' => $position,
            ]);

            $position++;
        }
    }

    private function normalizeTourPackageActivities(mixed $activities): array
    {
        return collect((array) $activities)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) {
                return [
                    'time' => trim((string) ($row['time'] ?? '')) ?: null,
                    'title' => trim((string) ($row['title'] ?? '')) ?: null,
                    'location' => trim((string) ($row['location'] ?? '')) ?: null,
                    'duration' => trim((string) ($row['duration'] ?? '')) ?: null,
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                ];
            })
            ->filter(fn (array $row) => collect($row)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();
    }

    private function syncInclusions(Package $package, Request $request): void
    {
        $package->inclusions()->delete();

        foreach ((array) $request->input('included', []) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $package->inclusions()->create([
                'title' => $title,
                'content' => $title,
                'type' => 'included',
                'item_type' => 'included',
                'sort_order' => $index,
            ]);
        }

        foreach ((array) $request->input('excluded', []) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $package->inclusions()->create([
                'title' => $title,
                'content' => $title,
                'type' => 'excluded',
                'item_type' => 'excluded',
                'sort_order' => $index,
            ]);
        }
    }

    private function syncPrices(Package $package, Request $request): void
    {
        // Duration/season/cabin pricing for Nile Cruises is stored in the
        // Nile-specific relations. Keep legacy generic rows untouched so old
        // cruises still have a safe fallback if advanced data is removed.
        if ($package->package_type === 'nile_cruise' && $request?->boolean('nile_cruise._present')) {
            return;
        }

        $package->prices()->delete();

        foreach ((array) $request->input('prices', []) as $price) {
            if (empty($price['amount'])) {
                continue;
            }

            $package->prices()->create([
                'label' => $price['label'] ?? null,
                'season_name' => $price['season_name'] ?? null,
                'price_type' => $price['price_type'] ?? 'from',
                'room_type' => $price['room_type'] ?? null,
                'pax_min' => $price['pax_min'] ?? null,
                'pax_max' => $price['pax_max'] ?? null,
                'amount' => $price['amount'],
                'currency_id' => $price['currency_id'] ?? $package->currency_id,
                'valid_from' => $price['valid_from'] ?? null,
                'valid_to' => $price['valid_to'] ?? null,
                'notes' => $price['notes'] ?? null,
            ]);
        }
    }

    private function createFacilitiesFromArray(Package $package, array $facilities): void
    {
        foreach ($facilities as $index => $facility) {
            $title = is_array($facility) ? ($facility['title'] ?? null) : $facility;

            if (!$title) {
                continue;
            }

            $package->facilities()->create([
                'title' => $title,
                'sort_order' => $index,
            ]);
        }
    }

    private function createItinerariesFromArray(Package $package, array $itinerary): void
    {
        $position = 0;

        foreach ($itinerary as $day) {
            if (!is_array($day)) {
                continue;
            }

            $mealsInput = [];
            if (isset($day['meals']) && is_array($day['meals'])) {
                $mealsInput = array_values(array_filter($day['meals']));
            }

            $hasBreakfast = in_array('breakfast', $mealsInput, true) || !empty($day['meals_breakfast']);
            $hasLunch = in_array('lunch', $mealsInput, true) || !empty($day['meals_lunch']);
            $hasDinner = in_array('dinner', $mealsInput, true) || !empty($day['meals_dinner']);

            $normalizedMeals = array_values(array_unique(array_filter(array_merge(
                $mealsInput,
                $hasBreakfast ? ['breakfast'] : [],
                $hasLunch ? ['lunch'] : [],
                $hasDinner ? ['dinner'] : []
            ))));

            $package->itineraries()->create([
                'duration' => $day['duration'] ?? null,
                'day_number' => $position + 1,
                'title' => $day['title'] ?? null,
                'description' => $day['description'] ?? null,
                'meals' => $normalizedMeals,
                'meals_breakfast' => $hasBreakfast,
                'meals_lunch' => $hasLunch,
                'meals_dinner' => $hasDinner,
                'sort_order' => $position,
            ]);

            $position++;
        }
    }

    private function createInclusionsFromArray(Package $package, array $items, string $type): void
    {
        foreach ($items as $index => $item) {
            $title = is_array($item) ? ($item['title'] ?? null) : $item;

            if (!$title) {
                continue;
            }

            $package->inclusions()->create([
                'title' => $title,
                'content' => $title,
                'type' => $type,
                'item_type' => $type,
                'sort_order' => $index,
            ]);
        }
    }

    private function createPricesFromArray(Package $package, array $prices, ?int $currencyId = null): void
    {
        foreach ($prices as $price) {
            if (!is_array($price) || empty($price['amount'])) {
                continue;
            }

            $package->prices()->create([
                'label' => $price['label'] ?? $price['duration'] ?? null,
                'season_name' => $price['season_name'] ?? $price['season'] ?? null,
                'price_type' => $price['price_type'] ?? 'from',
                'room_type' => $price['room_type'] ?? null,
                'pax_min' => $price['pax_min'] ?? null,
                'pax_max' => $price['pax_max'] ?? null,
                'amount' => $price['amount'],
                'currency_id' => $price['currency_id'] ?? $currencyId,
                'valid_from' => $price['valid_from'] ?? null,
                'valid_to' => $price['valid_to'] ?? null,
                'notes' => $price['notes'] ?? null,
            ]);
        }
    }

    private function uploadFile($file, string $path): string
    {
        return 'storage/' . $file->store($path, 'public');
    }

    private function uploadMultipleFiles(array $files, string $path): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            if ($file) {
                $uploaded[] = $this->uploadFile($file, $path);
            }
        }

        return $uploaded;
    }

    private function adminTrans($value, array $preferred = ['ar', 'en']): string
    {
        if (!is_array($value)) {
            return (string) ($value ?? '');
        }

        foreach ($preferred as $lang) {
            if (!empty($value[$lang])) {
                return (string) $value[$lang];
            }
        }

        foreach ($value as $translation) {
            if (is_string($translation) && trim($translation) !== '') {
                return trim($translation);
            }
        }

        return '';
    }
}
