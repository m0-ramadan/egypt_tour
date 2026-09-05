<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Package extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'category_id',
        'destination_id',
        'primary_country_id',
        'package_type',
        'nile_cruise_type_id',
        'nile_cruise_category_id',
        'slug',
        'title',
        'subtitle',
        'short_description',
        'description',
        'duration_type',
        'duration_days',
        'duration_hours',
        'duration_nights',
        'duration_text',
        'route_text',
        'start_from_price',
        'compare_price',
        'adult_price',
        'child_price',
        'infant_price',
        'adult_min_age',
        'child_min_age',
        'child_max_age',
        'infant_min_age',
        'infant_max_age',
        'price_from',
        'price_to',
        'currency_id',
        'schedule_text',
        'pickup_location',
        'dropoff_location',
        'destinations_text',
        'location_summary',
        'tour_type',
        'difficulty_level',
        'booking_mode',
        'rating_avg',
        'reviews_count',
        'is_featured',
        'is_best_seller',
        'is_ultra_luxury',
        'is_active',
        'min_participants',
        'max_participants',
        'booking_lead_days',
        'cancellation_policy',
        'terms_conditions',
        'pricing_information',
        'children_policy',
        'pickup_policy',
        'faq_json',
        'video_url',
        'featured_image',
        'gallery_images',
        'published_at',
        'sort_order',
        'seo_title',
        'seo_description',
        'breadcrumb_title',
        'canonical_url',
        'created_by',
        'updated_by',
        'offer_price',
        'source_type',
        'source_remote_id',
        'source_remote_slug',
        'source_synced_at',
        'what_to_bring',
        'on_tour_languages',
        'operating_days',
        'departure_times',
        'tour_timezone',
        'default_seat_capacity',
        'brochure_path',
        'promotional_videos',
        'deposit_policy',
        'deposit_type',
        'deposit_value',
        'allowed_payment_method_ids',
        'focus_keyword',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image_path',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'robots_index',
        'robots_follow',
        'itinerary_mode',
        'group_pricing_tiers',
        'price_1_person',
        'price_2_persons',
        'price_3_persons',
        'price_4_persons',
        'price_5_persons',
        'price_6_plus_persons',
    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'schedule_text' => 'array',
        'pickup_location' => 'array',
        'dropoff_location' => 'array',
        'destinations_text' => 'array',
        'location_summary' => 'array',
        'cancellation_policy' => 'array',
        'terms_conditions' => 'array',
        'seo_title' => 'array',
        'seo_description' => 'array',
        'breadcrumb_title' => 'array',
        'start_from_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'price_from' => 'decimal:2',
        'price_to' => 'decimal:2',
        'price_1_person' => 'decimal:2',
        'price_2_persons' => 'decimal:2',
        'price_3_persons' => 'decimal:2',
        'price_4_persons' => 'decimal:2',
        'price_5_persons' => 'decimal:2',
        'price_6_plus_persons' => 'decimal:2',
        'rating_avg' => 'decimal:2',
        'duration_days' => 'integer',
        'duration_nights' => 'integer',
        'adult_min_age' => 'integer',
        'child_min_age' => 'integer',
        'child_max_age' => 'integer',
        'infant_min_age' => 'integer',
        'infant_max_age' => 'integer',
        'reviews_count' => 'integer',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'booking_lead_days' => 'integer',
        'faq_json' => 'array',
        'what_to_bring' => 'array',
        'on_tour_languages' => 'array',
        'operating_days' => 'array',
        'departure_times' => 'array',
        'promotional_videos' => 'array',
        'allowed_payment_method_ids' => 'array',
        'meta_keywords' => 'array',
        'group_pricing_tiers' => 'array',
        'deposit_value' => 'decimal:2',
        'default_seat_capacity' => 'integer',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'gallery_images' => 'array',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_ultra_luxury' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'category_id');
    }

    public function primaryCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'primary_country_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Attraction::class, 'destination_id');
    }
    public function facilities(): HasMany
    {
        return $this->hasMany(PackageFacility::class)->orderBy('sort_order');
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function nileCruiseType(): BelongsTo
    {
        return $this->belongsTo(NileCruiseType::class, 'nile_cruise_type_id');
    }

    public function nileCruiseCategory(): BelongsTo
    {
        return $this->belongsTo(NileCruiseCategory::class, 'nile_cruise_category_id');
    }

    public function nileCruiseDetail(): HasOne
    {
        return $this->hasOne(NileCruiseDetail::class);
    }

    public function nileCruiseSchedules(): HasMany
    {
        return $this->hasMany(NileCruiseSchedule::class)->orderBy('sort_order');
    }

    public function nileCruiseCabins(): HasMany
    {
        return $this->hasMany(NileCruiseCabin::class)->orderBy('sort_order');
    }

    public function nileCruiseDurations(): HasMany
    {
        return $this->hasMany(NileCruiseDuration::class)->orderBy('sort_order');
    }

    public function nileCruiseAddons(): HasMany
    {
        return $this->hasMany(NileCruiseAddon::class)->orderBy('sort_order');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(PackageAddon::class)->orderBy('sort_order');
    }

    public function tourPackageDetail(): HasOne
    {
        return $this->hasOne(TourPackageDetail::class);
    }

    public function tourPackageAccommodations(): HasMany
    {
        return $this->hasMany(TourPackageAccommodation::class)->orderBy('sort_order');
    }

    public function tourPackageSeasons(): HasMany
    {
        return $this->hasMany(TourPackageSeason::class)->orderBy('sort_order');
    }

    public function scopeNileCruises($query)
    {
        return $query->where('package_type', 'nile_cruise');
    }

    public function scopeForNileCruiseType($query, $typeIdOrSlug)
    {
        return $query->where('package_type', 'nile_cruise')
            ->where(function ($q) use ($typeIdOrSlug) {
                if (is_numeric($typeIdOrSlug)) {
                    $q->where('nile_cruise_type_id', (int) $typeIdOrSlug);
                } else {
                    $q->whereHas('nileCruiseType', fn($sub) => $sub->where('slug', $typeIdOrSlug));
                }
            });
    }

    public function scopeForNileCruiseCategory($query, $categoryIdOrSlug)
    {
        return $query->where('package_type', 'nile_cruise')
            ->where(function ($q) use ($categoryIdOrSlug) {
                if (is_numeric($categoryIdOrSlug)) {
                    $q->where('nile_cruise_category_id', (int) $categoryIdOrSlug);
                } else {
                    $q->whereHas('nileCruiseCategory', fn($sub) => $sub->where('slug', $categoryIdOrSlug));
                }
            });
    }



    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'package_cities', 'package_id', 'city_id')
            ->withPivot(['stop_order', 'is_primary', 'nights'])
            ->withTimestamps();
    }

    public function destinations(): BelongsToMany
    {
        return $this->cities();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PackageTag::class, 'package_tag_items', 'package_id', 'tag_id')->withTimestamps();
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(PackageHighlight::class)->orderBy('sort_order');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class)->orderBy('day_number');
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(PackageInclusion::class)->orderBy('sort_order');
    }

    public function packageAttractions(): HasMany
    {
        return $this->hasMany(PackageAttraction::class)->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PackagePrice::class);
    }

    public function priceCalendar(): HasMany
    {
        return $this->hasMany(PriceCalendar::class);
    }

    public function cruise(): HasOne
    {
        return $this->hasOne(Cruise::class);
    }

    public function dealCampaigns(): HasMany
    {
        return $this->hasMany(DealCampaign::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function recommendedBy(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'recommended_package_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable', 'translatable_type', 'translatable_id');
    }

    public function seoMeta(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'model', 'model_type', 'model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }

    public function getNameAttribute(): string
    {
        return $this->display_title;
    }

    public function getDisplaySubtitleAttribute(): string
    {
        return $this->translatedValue('subtitle');
    }

    public function getDisplayShortDescriptionAttribute(): string
    {
        return $this->translatedValue('short_description');
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return $this->translatedValue('description');
    }

    public function getDisplayScheduleTextAttribute(): string
    {
        return $this->translatedValue('schedule_text');
    }

    public function getDisplayPickupLocationAttribute(): string
    {
        return $this->translatedValue('pickup_location');
    }

    public function getDisplayDropoffLocationAttribute(): string
    {
        return $this->translatedValue('dropoff_location');
    }

    public function getDisplayDestinationsTextAttribute(): string
    {
        return $this->translatedValue('destinations_text');
    }

    public function getDisplayLocationSummaryAttribute(): string
    {
        return $this->translatedValue('location_summary');
    }

    public function getDisplayCancellationPolicyAttribute(): string
    {
        return $this->translatedValue('cancellation_policy');
    }

    public function getDisplayTermsConditionsAttribute(): string
    {
        return $this->translatedValue('terms_conditions');
    }

    public function getDisplaySeoTitleAttribute(): string
    {
        return $this->translatedValue('seo_title');
    }

    public function getDisplaySeoDescriptionAttribute(): string
    {
        return $this->translatedValue('seo_description');
    }

    public function getDisplayBreadcrumbTitleAttribute(): string
    {
        return $this->translatedValue('breadcrumb_title');
    }

    public function getFormattedPriceAttribute(): ?string
    {
        $priceFrom = $this->price_from;

        if ($priceFrom === null) {
            $priceFrom = $this->start_from_price;
        }

        if ($priceFrom === null) {
            return null;
        }

        $symbol = $this->currency?->symbol ?? '$';

        return $symbol . number_format((float) $priceFrom, 2);
    }

    public function getPriceRangeTextAttribute(): ?string
    {
        $symbol = $this->currency?->symbol ?? '$';
        $priceFrom = (float) ($this->price_from ?? $this->start_from_price ?? 0);
        $priceTo = (float) ($this->price_to ?? 0);

        if ($priceFrom <= 0 && $priceTo <= 0) {
            return null;
        }

        if ($priceTo > $priceFrom && $priceFrom > 0) {
            return $symbol . number_format($priceFrom, 2) . ' to ' . $symbol . number_format($priceTo, 2);
        }

        return $symbol . number_format(max($priceFrom, $priceTo), 2);
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->featured_image)) {
            $path = ltrim((string) $this->featured_image, '/');
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            if (file_exists(public_path($path))) {
                return asset($path);
            }
            if (file_exists(public_path('storage/' . $path))) {
                return asset('storage/' . $path);
            }
        }

        $gallery = $this->gallery_images;
        if (!empty($gallery)) {
            $decoded = is_array($gallery) ? $gallery : json_decode((string) $gallery, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $p = is_array($item) ? ($item['path'] ?? $item['url'] ?? null) : $item;
                    if ($p) {
                        $p = ltrim((string) $p, '/');
                        if (file_exists(public_path($p))) {
                            return asset($p);
                        }
                        if (file_exists(public_path('storage/' . $p))) {
                            return asset('storage/' . $p);
                        }
                    }
                }
            }
        }

        if ($this->package_type === 'nile_cruise') {
            if ($this->nileCruiseCategory?->image_url) {
                return $this->nileCruiseCategory->image_url;
            }
            if ($this->nileCruiseType?->image_url) {
                return $this->nileCruiseType->image_url;
            }
            return asset('website/images/nile-cruises/luxor-aswan.jpg');
        }

        return asset('website/photos/home2.webp');
    }

    public function getGroupPricingTiersAttribute(): array
    {
        $raw = $this->attributes['group_pricing_tiers'] ?? null;
        if (!empty($raw)) {
            $decoded = is_array($raw) ? $raw : json_decode($raw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return array_map(function ($item) {
                    return [
                        'id' => $item['id'] ?? ($item['persons_count'] ?? 1) . '_persons',
                        'title' => $item['title'] ?? ($item['label'] ?? __('Group Tier')),
                        'persons_count' => (int) ($item['persons_count'] ?? $item['min'] ?? 1),
                        'min' => (int) ($item['min'] ?? $item['persons_count'] ?? 1),
                        'max' => isset($item['max']) ? (int) $item['max'] : null,
                        'persons_label' => $item['persons_label'] ?? ($item['title'] ?? ''),
                        'price_per_person' => (float) ($item['price_per_person'] ?? $item['price'] ?? 0),
                        'badge' => $item['badge'] ?? $item['badge_label'] ?? null,
                        'is_popular' => !empty($item['is_popular']) || ($item['badge'] ?? '') === __('♥ Most Popular'),
                        'is_best_value' => !empty($item['is_best_value']) || ($item['badge'] ?? '') === __('🏆 Best Value'),
                        'is_featured' => !empty($item['is_featured']),
                        'is_variable' => !empty($item['is_variable']),
                    ];
                }, $decoded);
            }
        }

        // Never invent a public price. Packages without configured pricing must
        // remain enquiry-only instead of silently becoming bookable for $150.
        $priceFrom = (float) ($this->price_from ?: ($this->start_from_price ?: ($this->adult_price ?: 0)));

        $p1 = $this->price_1_person !== null ? (float) $this->price_1_person : round($priceFrom * 1.3, 2);
        $p2 = $this->price_2_persons !== null ? (float) $this->price_2_persons : round($priceFrom, 2);
        $p3 = $this->price_3_persons !== null ? (float) $this->price_3_persons : round($priceFrom * 0.95, 2);
        $p4 = $this->price_4_persons !== null ? (float) $this->price_4_persons : round($priceFrom * 0.90, 2);
        $p5 = $this->price_5_persons !== null ? (float) $this->price_5_persons : round($priceFrom * 0.86, 2);
        $p6 = $this->price_6_plus_persons !== null ? (float) $this->price_6_plus_persons : round($priceFrom * 0.83, 2);

        return [
            [
                'id' => '1_person',
                'title' => __('Solo Traveler'),
                'persons_count' => 1,
                'min' => 1,
                'max' => 1,
                'persons_label' => __('1 Person'),
                'price_per_person' => $p1,
                'badge' => null,
                'is_popular' => false,
                'is_best_value' => false,
                'is_featured' => false,
                'is_variable' => false,
            ],
            [
                'id' => '2_persons',
                'title' => __("Couple's Journey"),
                'persons_count' => 2,
                'min' => 2,
                'max' => 2,
                'persons_label' => __('2 Persons'),
                'price_per_person' => $p2,
                'badge' => __('♥ Most Popular'),
                'is_popular' => true,
                'is_best_value' => false,
                'is_featured' => true,
                'is_variable' => false,
            ],
            [
                'id' => '3_persons',
                'title' => __('Small Group'),
                'persons_count' => 3,
                'min' => 3,
                'max' => 3,
                'persons_label' => __('3 Persons'),
                'price_per_person' => $p3,
                'badge' => null,
                'is_popular' => false,
                'is_best_value' => false,
                'is_featured' => false,
                'is_variable' => false,
            ],
            [
                'id' => '4_persons',
                'title' => __('Family Adventure'),
                'persons_count' => 4,
                'min' => 4,
                'max' => 4,
                'persons_label' => __('4 Persons'),
                'price_per_person' => $p4,
                'badge' => null,
                'is_popular' => false,
                'is_best_value' => false,
                'is_featured' => false,
                'is_variable' => false,
            ],
            [
                'id' => '5_persons',
                'title' => __('Extended Group'),
                'persons_count' => 5,
                'min' => 5,
                'max' => 5,
                'persons_label' => __('5 Persons'),
                'price_per_person' => $p5,
                'badge' => null,
                'is_popular' => false,
                'is_best_value' => false,
                'is_featured' => false,
                'is_variable' => false,
            ],
            [
                'id' => '6_plus_persons',
                'title' => __('Large Group'),
                'persons_count' => 6,
                'min' => 6,
                'max' => 99,
                'persons_label' => __('6+ Persons'),
                'price_per_person' => $p6,
                'badge' => __('🏆 Best Value'),
                'is_popular' => false,
                'is_best_value' => true,
                'is_featured' => true,
                'is_variable' => true,
            ],
        ];
    }
}
