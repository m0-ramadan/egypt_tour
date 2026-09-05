<?php

namespace App\Services;

use App\Models\City;
use App\Models\Package;
use App\Models\PackageHighlight;
use App\Models\PackageInclusion;
use App\Models\PackagePrice;
use App\Models\PackageTag;
use App\Models\SavvyTourTemplate;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReadyTourImportService
{
    public function __construct(
        protected ReadyTourTaxonomyMapper $taxonomyMapper,
        protected ReadyTourMediaMatcher $mediaMatcher,
        protected PackageAiService $packageAiService,
        protected TranslationService $translationService
    ) {}

    /**
     * Import a single SavvyTourTemplate into a native Package record.
     *
     * @param SavvyTourTemplate $template
     * @param string $processUuid
     * @param int|string|null $adminId
     * @return array
     */
    public function importTemplate(SavvyTourTemplate $template, string $processUuid = 'single', int|string|null $adminId = null): array
    {
        $cacheKey = $this->getProgressCacheKey($processUuid, $adminId);

        // 1. Duplicate check (Rule #42)
        $existingPackage = Package::where('source_type', 'savvy_template')
            ->where('source_remote_id', $template->remote_id)
            ->first();

        if ($existingPackage) {
            $template->update([
                'imported_package_id' => $existingPackage->id,
                'import_status' => 'imported',
                'imported_at' => $template->imported_at ?? now(),
            ]);

            return [
                'status' => 'already_imported',
                'package_id' => $existingPackage->id,
                'package' => $existingPackage,
                'message' => 'Tour already imported previously.',
                'warnings' => [],
            ];
        }

        $warnings = [];

        try {
            $this->updateProgress($cacheKey, 5, 'Starting import process');

            // 2. Validate template
            $this->updateProgress($cacheKey, 10, 'Reading tour template data');
            $this->validateTemplate($template);

            // 3. Resolve taxonomy (Type & Category)
            $this->updateProgress($cacheKey, 20, 'Matching package type and category');
            $packageType = $this->taxonomyMapper->mapPackageType($template->remote_tour_type);
            $localTourType = $this->taxonomyMapper->mapLocalTourType($template->remote_tour_type);
            $category = $this->taxonomyMapper->resolveCategory($template->remote_category, $packageType, $warnings);

            // 4. Resolve Cities & Primary Destination
            $this->updateProgress($cacheKey, 30, 'Resolving cities and destinations');
            $resolvedCities = $this->taxonomyMapper->resolveCities((array) ($template->cities ?? []), $warnings);
            $primaryCity = $resolvedCities->first()['city'] ?? null;
            $primaryDestination = $this->taxonomyMapper->resolvePrimaryDestination($primaryCity);
            $primaryCountryId = $primaryCity?->country_id;

            // 5. Prepare basic fields (Duration, Currency, Prices, Difficulty)
            $this->updateProgress($cacheKey, 40, 'Preparing basic tour fields');
            $durationData = $this->taxonomyMapper->mapDuration($template->duration_value, $template->duration_unit);
            $currency = $this->taxonomyMapper->resolveCurrency($template->price_currency);
            $difficulty = $this->taxonomyMapper->mapDifficulty($template->difficulty_level);

            // 6. Enrich missing content via AI (Rule #31 & #32) - HTTP outside DB transaction
            $this->updateProgress($cacheKey, 55, 'Enriching missing content via AI');
            $enrichedData = $this->enrichMissingContent($template, $primaryCity?->display_name, $category?->display_name, $warnings);

            // 7. Match images from SavvyMedia (Rule #27 & #28)
            $this->updateProgress($cacheKey, 70, 'Matching media images');
            $matchedMedia = $this->mediaMatcher->matchMediaForTemplate($template);

            // 8. DB Transaction - Create package and relations (Rule #38)
            $this->updateProgress($cacheKey, 90, 'Creating database package and relations');
            $package = DB::transaction(function () use (
                $template,
                $packageType,
                $localTourType,
                $category,
                $primaryDestination,
                $primaryCountryId,
                $durationData,
                $currency,
                $difficulty,
                $enrichedData,
                $resolvedCities
            ) {
                return $this->createPackageWithRelations(
                    $template,
                    $packageType,
                    $localTourType,
                    $category,
                    $primaryDestination,
                    $primaryCountryId,
                    $durationData,
                    $currency,
                    $difficulty,
                    $enrichedData,
                    $resolvedCities
                );
            });

            // 9. Copy media files locally to Package folder (Rule #29)
            $this->updateProgress($cacheKey, 95, 'Copying media files locally');
            $mediaPaths = $this->mediaMatcher->copyAndAssignMedia($package, $matchedMedia);

            $package->update([
                'featured_image' => $mediaPaths['featured_image'],
                'gallery_images' => $mediaPaths['gallery_images'],
            ]);

            // 10. Finalize import status
            $importStatus = !empty($warnings) ? 'imported_with_warnings' : 'imported';
            $template->update([
                'imported_package_id' => $package->id,
                'import_status' => $importStatus,
                'imported_at' => now(),
                'last_import_error' => !empty($warnings) ? implode(' | ', $warnings) : null,
            ]);

            $this->updateProgress($cacheKey, 100, 'Tour added successfully');

            return [
                'status' => 'success',
                'package_id' => $package->id,
                'package' => $package->fresh(['category', 'cities', 'facilities', 'inclusions', 'prices']),
                'message' => 'Tour added successfully.',
                'warnings' => $warnings,
            ];
        } catch (Exception $e) {
            Log::error("Failed to import ReadyTour template #{$template->remote_id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $template->update([
                'import_status' => 'failed',
                'last_import_error' => $e->getMessage(),
            ]);

            $this->updateProgress($cacheKey, 100, 'Import failed: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Import multiple selected template records with progress tracking.
     */
    public function importMultiple(array $templateIds, string $processUuid = 'bulk', int|string|null $adminId = null): array
    {
        $cacheKey = $this->getProgressCacheKey($processUuid, $adminId);

        $templates = SavvyTourTemplate::whereIn('id', $templateIds)->get();
        $total = $templates->count();
        $completed = 0;
        $successCount = 0;
        $failedCount = 0;
        $warningsCount = 0;
        $importedPackages = [];

        $this->updateProgress($cacheKey, 0, "Importing {$total} tours...", [
            'total' => $total,
            'completed' => 0,
        ]);

        foreach ($templates as $template) {
            $title = $template->display_name;

            try {
                $result = $this->importTemplate($template, $processUuid . '_sub', $adminId);
                $completed++;
                $successCount++;

                if (!empty($result['warnings'])) {
                    $warningsCount++;
                }

                if (!empty($result['package'])) {
                    $importedPackages[] = $result['package'];
                }
            } catch (Exception $e) {
                $completed++;
                $failedCount++;
                Log::warning("Bulk import failed for template #{$template->remote_id}: " . $e->getMessage());
            }

            $percentage = $total > 0 ? min(100, round(($completed / $total) * 100, 1)) : 100;
            $this->updateProgress($cacheKey, $percentage, "Adding {$completed} of {$total}: {$title}", [
                'total' => $total,
                'completed' => $completed,
                'success' => $successCount,
                'failed' => $failedCount,
            ]);
        }

        $this->updateProgress($cacheKey, 100, 'Bulk import completed', [
            'total' => $total,
            'completed' => $completed,
            'success' => $successCount,
            'failed' => $failedCount,
        ]);

        return [
            'total' => $total,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'warnings_count' => $warningsCount,
            'packages' => $importedPackages,
        ];
    }

    /**
     * Validate template before import.
     */
    protected function validateTemplate(SavvyTourTemplate $template): void
    {
        if (empty($template->remote_id)) {
            throw new Exception('Remote tour template ID is missing.');
        }
    }

    /**
     * Enrich missing text fields via PackageAiService without altering original factual data (Rules #31, #32, #34).
     */
    protected function enrichMissingContent(SavvyTourTemplate $template, ?string $cityName, ?string $categoryName, array &$warnings): array
    {
        $hasDescription = !empty($template->description) || !empty($template->description_template);
        $hasItinerary = !empty($template->itinerary_outline);

        // Standard default translations
        $nameAr = $template->name['ar'] ?? $template->name['en'] ?? 'Ready Tour';
        $nameEn = $template->name['en'] ?? $template->name['ar'] ?? 'Ready Tour';

        $title = ['en' => $nameEn, 'ar' => $nameAr];
        $subtitle = ['en' => "Experience {$nameEn}", 'ar' => "استمتع بـ {$nameAr}"];
        $shortDesc = ['en' => Str::limit(strip_tags((string) $template->description_template), 250) ?: "Discover {$nameEn}", 'ar' => "اكتشف {$nameAr}"];
        $desc = ['en' => (string) ($template->description_template ?: $nameEn), 'ar' => (string) ($template->description_template ?: $nameAr)];

        $seoTitle = ['en' => "{$nameEn} - Book Now", 'ar' => "{$nameAr} - احجز الآن"];
        $seoDesc = ['en' => Str::limit(strip_tags($shortDesc['en']), 160), 'ar' => Str::limit(strip_tags($shortDesc['ar']), 160)];
        $breadcrumb = ['en' => $nameEn, 'ar' => $nameAr];

        // If description is missing, attempt AI completion safely
        if (!$hasDescription) {
            try {
                $aiResult = $this->packageAiService->generate([
                    'prompt' => "Generate description and itinerary for tour: {$nameEn} in {$cityName}",
                    'destination_name' => $cityName,
                    'category_name' => $categoryName,
                    'duration_days' => $template->duration_value,
                ]);

                if (is_array($aiResult)) {
                    if (!empty($aiResult['subtitle'])) {
                        $subtitle = $aiResult['subtitle'];
                    }
                    if (!empty($aiResult['short_description'])) {
                        $shortDesc = $aiResult['short_description'];
                    }
                    if (!empty($aiResult['description'])) {
                        $desc = $aiResult['description'];
                    }
                    if (!empty($aiResult['seo_title'])) {
                        $seoTitle = $aiResult['seo_title'];
                    }
                    if (!empty($aiResult['seo_description'])) {
                        $seoDesc = $aiResult['seo_description'];
                    }
                    if (!empty($aiResult['breadcrumb_title'])) {
                        $breadcrumb = $aiResult['breadcrumb_title'];
                    }
                }
            } catch (Exception $e) {
                $warnings[] = 'AI content enrichment failed, falling back to original texts.';
            }
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'short_description' => $shortDesc,
            'description' => $desc,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDesc,
            'breadcrumb_title' => $breadcrumb,
        ];
    }

    /**
     * DB creation of Package and related models.
     */
    protected function createPackageWithRelations(
        SavvyTourTemplate $template,
        string $packageType,
        string $localTourType,
        $category,
        $primaryDestination,
        ?int $primaryCountryId,
        array $durationData,
        $currency,
        ?string $difficulty,
        array $enrichedData,
        $resolvedCities
    ): Package {
        // Generate unique slug
        $baseSlug = Str::slug($template->remote_slug ?: $enrichedData['title']['en'] ?: 'tour-' . $template->remote_id);
        $slug = $baseSlug;
        $counter = 1;

        while (Package::where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }

        $minPrice = (float) ($template->suggested_min_price ?? 0);
        $maxPrice = (float) ($template->suggested_max_price ?? $minPrice);

        // 1. Create Package record
        $package = Package::create([
            'category_id' => $category?->id,
            'destination_id' => $primaryDestination?->id,
            'primary_country_id' => $primaryCountryId,
            'package_type' => $packageType,
            'tour_type' => $localTourType,
            'slug' => $slug,
            'title' => $enrichedData['title'],
            'subtitle' => $enrichedData['subtitle'],
            'short_description' => $enrichedData['short_description'],
            'description' => $enrichedData['description'],
            'duration_type' => $durationData['duration_type'],
            'duration_days' => $durationData['duration_days'],
            'duration_hours' => $durationData['duration_hours'],
            'duration_nights' => $durationData['duration_nights'],
            'duration_text' => $durationData['duration_text'],
            'start_from_price' => $minPrice,
            'price_from' => $minPrice,
            'price_to' => $maxPrice,
            'adult_price' => $minPrice,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'currency_id' => $currency?->id,
            'difficulty_level' => $difficulty,
            'min_participants' => $template->min_participants ?: 1,
            'max_participants' => $template->max_participants,
            'is_active' => true,
            'is_featured' => (bool) $template->remote_is_featured,
            'is_best_seller' => false,
            'is_ultra_luxury' => false,
            'seo_title' => $enrichedData['seo_title'],
            'seo_description' => $enrichedData['seo_description'],
            'breadcrumb_title' => $enrichedData['breadcrumb_title'],
            'source_type' => 'savvy_template',
            'source_remote_id' => $template->remote_id,
            'source_remote_slug' => $template->remote_slug,
            'source_synced_at' => now(),
            'created_by' => auth('admin')->id(),
        ]);

        // 2. Sync Cities pivot (Rule #17)
        $syncCities = [];
        foreach ($resolvedCities as $item) {
            $syncCities[$item['city']->id] = [
                'stop_order' => $item['stop_order'],
                'is_primary' => $item['is_primary'],
                'nights' => $item['nights'],
            ];
        }
        $package->cities()->sync($syncCities);

        // 3. Sync Highlights (Rule #24)
        $highlights = (array) ($template->highlights ?? []);
        foreach ($highlights as $index => $highlightItem) {
            $highlightText = is_array($highlightItem) ? ($highlightItem['text'] ?? $highlightItem['title'] ?? reset($highlightItem)) : (string) $highlightItem;
            if (trim($highlightText) === '') {
                continue;
            }

            $package->highlights()->create([
                'title' => trim($highlightText),
                'description' => '',
                'sort_order' => $index,
            ]);
        }

        // 4. Sync Inclusions / Exclusions (Rule #25)
        $includes = (array) ($template->includes ?? []);
        foreach ($includes as $index => $item) {
            $text = is_array($item) ? ($item['item'] ?? $item['title'] ?? reset($item)) : (string) $item;
            if (trim($text) === '') {
                continue;
            }

            $package->inclusions()->create([
                'title' => trim($text),
                'content' => trim($text),
                'description' => trim($text),
                'type' => 'included',
                'item_type' => 'included',
                'sort_order' => $index,
            ]);
        }

        $excludes = (array) ($template->excludes ?? []);
        foreach ($excludes as $index => $item) {
            $text = is_array($item) ? ($item['item'] ?? $item['title'] ?? reset($item)) : (string) $item;
            if (trim($text) === '') {
                continue;
            }

            $package->inclusions()->create([
                'title' => trim($text),
                'content' => trim($text),
                'description' => trim($text),
                'type' => 'excluded',
                'item_type' => 'excluded',
                'sort_order' => $index,
            ]);
        }

        // 5. Sync Itinerary
        $itineraries = (array) ($template->itinerary_outline ?? []);
        foreach ($itineraries as $index => $day) {
            if (!is_array($day)) {
                continue;
            }

            $dayTitle = $day['title'] ?? "Day " . ($index + 1);
            $dayDesc = $day['description'] ?? $day['activities'] ?? '';
            if (is_array($dayDesc)) {
                $dayDesc = implode("\n", $dayDesc);
            }

            $package->itineraries()->create([
                'day_number' => $day['day'] ?? ($index + 1),
                'title' => ['en' => (string) $dayTitle, 'ar' => (string) $dayTitle],
                'description' => ['en' => (string) $dayDesc, 'ar' => (string) $dayDesc],
                'sort_order' => $index,
            ]);
        }

        // 6. Sync Prices (Rule #21)
        if ($minPrice > 0) {
            $package->prices()->create([
                'label' => ['en' => 'Suggested Price', 'ar' => 'السعر المقترح'],
                'price_type' => 'from',
                'amount' => $minPrice,
                'currency_id' => $currency?->id,
            ]);
        }

        // 7. Sync Tags (Rule #26)
        $tags = (array) ($template->tags ?? []);
        $tagIds = [];
        foreach ($tags as $tagName) {
            $normSlug = Str::slug($tagName);
            if (empty($normSlug)) {
                continue;
            }

            $tag = PackageTag::firstOrCreate(
                ['slug' => $normSlug],
                ['name' => trim($tagName)]
            );
            $tagIds[] = $tag->id;
        }
        $package->tags()->sync($tagIds);

        return $package;
    }

    /**
     * Get Cache key for progress tracking.
     */
    public function getProgressCacheKey(string $processUuid, int|string|null $adminId = null): string
    {
        $adminId = $adminId ?: (auth('admin')->id() ?? 'guest');
        return "savvy_tour_import_progress:{$adminId}:{$processUuid}";
    }

    /**
     * Update import progress in Cache.
     */
    protected function updateProgress(string $cacheKey, float|int $percentage, string $message, array $extra = []): void
    {
        Cache::put($cacheKey, array_merge([
            'status' => ($percentage >= 100) ? 'completed' : 'running',
            'percentage' => $percentage,
            'message' => $message,
        ], $extra), 600);
    }
}
