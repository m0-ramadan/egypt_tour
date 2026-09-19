<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\ExternalTours\ExternalTourImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncEgyptTourProCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tours:sync-egypttourpro
                            {--dry-run : Preview matching, category relations, and actions without making live DB updates}
                            {--update : Update existing packages if matched}
                            {--download-images : Download and store tour images locally}
                            {--categories : Create missing categories and scrape category pages}
                            {--only-missing : Import only URLs that do not currently exist}
                            {--force-images : Force redownload images even if already present}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform an idempotent UPSERT / Sync of 80 specified tours from EgyptTourPro into TravelNest';

    /**
     * Target 80 URLs categorized by parent type and subcategories.
     */
    protected array $targetUrls = [
        'day_tours' => [
            'https://egypttourpro.com/tours/Giza-Pyramids-Saqqara-%26-Memphis-Day-Tour-Private-Cairo-Excursion',
            'https://egypttourpro.com/tours/day-trip-to-alexandria-from-cairo',
            'https://egypttourpro.com/tours/cairo-half-day-tour-to-pyramids-of-giza-and-the-sphinx',
            'https://egypttourpro.com/tours/from-cairo-private-luxor-day-tour-with-guide-and-flights',
            'https://egypttourpro.com/tours/day-tour-to-luxor-from-cairo-by-plane',
            'https://egypttourpro.com/tours/Cairo-Day-Tour-to-Pyramids-of-Giza-%26-Grand-Egyptian-Museum-Private-Tour',
            'https://egypttourpro.com/tours/cairo-layover-tour-from-cairo-airport',
            'https://egypttourpro.com/tours/nmec-citadel-old-cairo-tour',
            'https://egypttourpro.com/tours/Luxor-West-bank-Private-Tour-Valley-of-kings-%26-hatshepsut-Temple',
            'https://egypttourpro.com/tours/explore-luxor-in-2-days-temples-tombs-lunch-included',
            'https://egypttourpro.com/tours/private-day-trip-to-edfu-kom-ombo-temples-from-luxor',
            'https://egypttourpro.com/tours/Luxor-Hot-Air-Balloon-Ride',
            'https://egypttourpro.com/tours/One-Package-Luxor-Hot-Air-Balloon-Flight-%26-West-Bank-Tours',
            'https://egypttourpro.com/tours/East-and-West-Banks-of-Luxor-Private-Tour',
            'https://egypttourpro.com/tours/Dendera-and-Abydos-temples-Tour-from-Luxor',
            'https://egypttourpro.com/tours/Private-Felucca-Boat-Ride-on-The-Nile-In-Luxor',
            'https://egypttourpro.com/tours/Sound-and-Light-Show-at-Karnak-Temple-in-Luxor',
            'https://egypttourpro.com/tours/from-aswan-abu-simbel-private-day-trip-by-car-or-bus',
            'https://egypttourpro.com/tours/aswan-day-tour-philae-temple-unfinished-obelisk-high-dam',
            'https://egypttourpro.com/tours/private-day-trip-to-abu-simbel-from-aswan',
            'https://egypttourpro.com/tours/Nubian-Village-Tour-in-Aswan-by-Motorboat',
            'https://egypttourpro.com/tours/Philae-Temple-Sound-and-Light-Show-Ticket-%26-Transfers',
            'https://egypttourpro.com/tours/private-aswan-to-luxor-transfer-with-edfu-%26-kom-ombo-temple-tours',
            'https://egypttourpro.com/tours/Hurghada-Hot-Air-Balloon-Ride',
            'https://egypttourpro.com/tours/hurghada-desert-safari-by-quad-bike-with-bedouin-dinner-and-show',
            'https://egypttourpro.com/tours/Giftun-Island-Snorkeling-Trip-from-Hurghada',
            'https://egypttourpro.com/tours/Hurghada-Scuba-Diving-Trip',
            'https://egypttourpro.com/tours/Orange-Bay-Island-Snorkeling-Trip-from-Hurghada',
            'https://egypttourpro.com/tours/Semi-Submarine-Boat-Tour-in-Hurghada',
            'https://egypttourpro.com/tours/privat-luxor-day-trip-from-hurghada',
            'https://egypttourpro.com/tours/hurghada-to-cairo-private-day-trip-by-flight',
            'https://egypttourpro.com/tours/Private-Day-Trip-to-Cairo-from-Hurghada-by-Car',
            'https://egypttourpro.com/tours/quad-bike-safari-desert-tour-in-hurghada',
            'https://egypttourpro.com/tours/day-trip-to-abu-simbel-from-luxor-private-excursion',
        ],
        'tour_packages' => [
            'https://egypttourpro.com/tour-packages/6-Day-Cairo-Alexandria-&-Luxor-Tour',
            'https://egypttourpro.com/tour-packages/9-days-egypt-tour-cairo-luxor-aswan-nile-cruise-guided-tours',
            'https://egypttourpro.com/tour-packages/Classic-Packages',
            'https://egypttourpro.com/tour-packages/Cairo-Luxor-Aswan-Hurghada-10-Days',
            'https://egypttourpro.com/tour-packages/Best-of-Egypt-7-Days-Cairo-Luxor-Aswan-Nile-Cruise',
            'https://egypttourpro.com/tour-packages/Cairo-%26-Nile-Cruise-by-Flight-8-Days',
            'https://egypttourpro.com/tour-packages/Pyramids-Nile-Cruise-%26-Red-Sea-12-Days',
            'https://egypttourpro.com/tour-packages/Grand-Egypt-Tour-14-Days-Cairo-Alexandria-Nile-Cruise-Hurghada',
            'https://egypttourpro.com/tour-packages/Egypt-Highlights-5-Days-Cairo-%26-Luxor',
            'https://egypttourpro.com/tour-packages/Luxury-Egypt-Tour-8-Days-Cairo-%26-Nile-Cruise',
            'https://egypttourpro.com/tour-packages/Cairo-%26-Alexandria-4-Days-Short-Break',
            'https://egypttourpro.com/tour-packages/Egypt-Budget-Tour-7-Days-Cairo-Luxor-%26-Aswan',
            'https://egypttourpro.com/tour-packages/Cairo-Luxor-Aswan-%26-Abu-Simbel-8-Days',
            'https://egypttourpro.com/tour-packages/Family-Egypt-Holiday-10-Days-Cairo-Nile-Cruise-Hurghada',
            'https://egypttourpro.com/tour-packages/Honeymoon-in-Egypt-9-Days-Cairo-Nile-Cruise-El-Gouna',
            'https://egypttourpro.com/tour-packages/Egypt-Easter-Tour-8-Days-Cairo-%26-Nile-Cruise',
            'https://egypttourpro.com/tour-packages/Christmas-%26-New-Year-in-Egypt-10-Days',
            'https://egypttourpro.com/tour-packages/Cairo-Alexandria-Siwa-Oasis-7-Days',
            'https://egypttourpro.com/tour-packages/White-Desert-Oasis-%26-Pyramids-5-Days',
            'https://egypttourpro.com/tour-packages/Cairo-Mt-Sinai-%26-Sharm-El-Sheikh-7-Days',
            'https://egypttourpro.com/tour-packages/Egypt-Express-Tour-4-Days-Cairo-%26-Pyramids',
            'https://egypttourpro.com/tour-packages/Cairo-%26-Luxor-Quick-Break-5-Days',
            'https://egypttourpro.com/tour-packages/Egypt-Heritage-%26-Culture-11-Days',
            'https://egypttourpro.com/tour-packages/Luxury-Nile-Cruise-%26-Cairo-Stay-7-Days',
            'https://egypttourpro.com/tour-packages/Cairo-Alexandria-Luxor-Aswan-Hurghada-13-Days',
            'https://egypttourpro.com/tour-packages/Classic-Egypt-Vacation-8-Days-Cairo-%26-Nile-Cruise',
            'https://egypttourpro.com/tour-packages/Splendors-of-Egypt-10-Days-Cairo-Nile-Cruise-Alexandria',
            'https://egypttourpro.com/tour-packages/Egypt-Oases-%26-Sahara-Adventure-8-Days',
            'https://egypttourpro.com/tour-packages/Cairo-Aswan-Luxor-by-Sleeper-Train-7-Days',
            'https://egypttourpro.com/tour-packages/Cairo-Sharm-El-Sheikh-Holiday-6-Days',
            'https://egypttourpro.com/tour-packages/Ultimate-Egypt-Explorer-15-Days',
        ],
        'nile_cruises' => [
            'https://egypttourpro.com/cruise/Deluxe-Nile-Cruise-Packages',
            'https://egypttourpro.com/cruise/From-Aswan-4-Days-5-Star-Nile-Cruise-with-Guided-Tours',
            'https://egypttourpro.com/cruise/From-Luxor-5-Days-5-Star-Nile-Cruise-to-Aswan',
            'https://egypttourpro.com/cruise/7-Nights-Luxor-to-Luxor-Nile-Cruise-Full-Itinerary',
            'https://egypttourpro.com/cruise/5-Star-Luxury-Nile-Cruise-Aswan-to-Luxor-3-Nights',
            'https://egypttourpro.com/cruise/5-Star-Deluxe-Nile-Cruise-Luxor-to-Aswan-4-Nights',
            'https://egypttourpro.com/cruise/Dahabiya-Nile-Cruise-5-Days-Esna-to-Aswan',
            'https://egypttourpro.com/cruise/Luxury-Dahabiya-Nile-Cruise-4-Days-Aswan-to-Esna',
            'https://egypttourpro.com/cruise/Lake-Nasser-Cruise-4-Days-Abu-Simbel-to-Aswan',
            'https://egypttourpro.com/cruise/Lake-Nasser-Cruise-5-Days-Aswan-to-Abu-Simbel',
            'https://egypttourpro.com/cruise/Oberoi-Philae-Nile-Cruise-4-Nights-Aswan-to-Luxor',
            'https://egypttourpro.com/cruise/Sonesta-St-George-Nile-Cruise-5-Days-Luxor-to-Aswan',
            'https://egypttourpro.com/cruise/Movenpick-Royal-Lily-Nile-Cruise-4-Days-Aswan-to-Luxor',
            'https://egypttourpro.com/cruise/Steigenberger-Minerva-Nile-Cruise-5-Days-Luxor-to-Aswan',
            'https://egypttourpro.com/cruise/MS-Amoura-Dahabiya-Nile-Cruise-5-Days',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(ExternalTourImportService $importService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $update = (bool) $this->option('update');
        $downloadImages = (bool) $this->option('download-images');
        $onlyMissing = (bool) $this->option('only-missing');
        $ensureCategories = (bool) $this->option('categories');

        $this->info('=== Starting EgyptTourPro Sync Process ===');
        if ($dryRun) {
            $this->warn('[DRY RUN MODE ENABLED - No changes will be saved to DB]');
        }

        if ($ensureCategories && !$dryRun) {
            $this->info('Phase 1: Ensuring parent & subcategory structure in DB...');
            $this->ensureCategoryStructure();
        }

        $allUrls = [];
        foreach ($this->targetUrls as $type => $urls) {
            foreach ($urls as $url) {
                $allUrls[] = [
                    'url' => trim($url),
                    'expected_type' => $type,
                ];
            }
        }

        $total = count($allUrls);
        $this->info("Phase 2: Processing {$total} target tour URLs...");

        $stats = [
            'total' => $total,
            'matched_existing' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'warnings_count' => 0,
        ];

        $reportDetails = [];
        $failedUrls = [];

        foreach ($allUrls as $index => $item) {
            $url = $item['url'];
            $num = $index + 1;
            $this->line("\n[{$num}/{$total}] Processing: {$url}");

            // Match checking
            $slug = basename(parse_url($url, PHP_URL_PATH) ?? '');
            $normalizedUrl = strtolower(rtrim($url, '/'));
            $sourceId = sha1($normalizedUrl);

            $existingPackage = Package::where('source_type', 'external_url')
                ->where('source_remote_id', $sourceId)
                ->orWhere('source_remote_slug', $slug)
                ->orWhere('slug', Str::slug($slug))
                ->first();

            if ($existingPackage) {
                $stats['matched_existing']++;
                $this->line("  -> Matched existing Package ID: {$existingPackage->id} (Slug: {$existingPackage->slug})");
            } else {
                $this->line("  -> No existing package matched; will create new record.");
            }

            if ($onlyMissing && $existingPackage) {
                $this->info("  -> Skipped (Only-missing flag active).");
                $stats['skipped']++;
                continue;
            }

            if ($dryRun) {
                $status = $existingPackage ? 'WOULD UPDATE' : 'WOULD CREATE';
                $this->info("  -> Dry Run Result: {$status}");
                if ($existingPackage) {
                    $stats['updated']++;
                } else {
                    $stats['created']++;
                }
                $reportDetails[] = [
                    'url' => $url,
                    'status' => $status,
                    'package_id' => $existingPackage?->id ?? 'NEW',
                ];
                continue;
            }

            // Live execution
            try {
                $options = [
                    'rewrite' => false, // Keep factual copy clean & accurate
                    'download_images' => $downloadImages,
                    'update' => $update || (bool) $existingPackage,
                ];

                $result = $importService->import($url, $options);
                $package = $result['package'];
                $isUpdate = $result['is_update'];
                $warnings = $result['warnings'];

                if ($isUpdate) {
                    $stats['updated']++;
                    $this->info("  -> Updated Package ID: {$package->id}");
                } else {
                    $stats['created']++;
                    $this->info("  -> Created Package ID: {$package->id}");
                }

                if (!empty($warnings)) {
                    $stats['warnings_count'] += count($warnings);
                    foreach ($warnings as $w) {
                        $this->warn("     Warning: {$w}");
                    }
                }

                $reportDetails[] = [
                    'url' => $url,
                    'status' => $isUpdate ? 'UPDATED' : 'CREATED',
                    'package_id' => $package->id,
                ];
            } catch (\Throwable $e) {
                $stats['failed']++;
                $this->error("  -> FAILED: {$e->getMessage()}");
                $failedUrls[] = [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ];
                Log::error("Tour sync failed for URL [{$url}]: " . $e->getMessage());
            }
        }

        $this->printReport($stats, $reportDetails, $failedUrls, $dryRun);

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Ensure core PackageCategory hierarchy exists in DB.
     */
    protected function ensureCategoryStructure(): void
    {
        $categories = [
            [
                'slug' => 'day-tours',
                'name' => ['en' => 'Day Tours', 'ar' => 'رحلات اليوم الواحد'],
                'category_type' => 'day_tour',
                'is_active' => true,
            ],
            [
                'slug' => 'tour-packages',
                'name' => ['en' => 'Tour Packages', 'ar' => 'برامج سياحية'],
                'category_type' => 'travel_package',
                'is_active' => true,
            ],
            [
                'slug' => 'nile-cruises',
                'name' => ['en' => 'Nile Cruises', 'ar' => 'رحلات نيلية'],
                'category_type' => 'nile_cruise',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            PackageCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }

    /**
     * Display final detailed report.
     */
    protected function printReport(array $stats, array $details, array $failures, bool $dryRun): void
    {
        $this->info("\n==============================================");
        $this->info("          EGYPTTOURPRO SYNC REPORT            ");
        $this->info("==============================================");
        $this->line("Execution Mode   : " . ($dryRun ? 'DRY RUN (Preview)' : 'LIVE EXECUTION'));
        $this->line("Total URLs       : {$stats['total']}");
        $this->line("Matched Existing : {$stats['matched_existing']}");
        $this->line("Created Packages : {$stats['created']}");
        $this->line("Updated Packages : {$stats['updated']}");
        $this->line("Skipped Packages : {$stats['skipped']}");
        $this->line("Failed Imports   : {$stats['failed']}");
        $this->line("Total Warnings   : {$stats['warnings_count']}");
        $this->info("==============================================");

        if (!empty($failures)) {
            $this->error("\nFailed URLs Summary:");
            foreach ($failures as $f) {
                $this->line("- URL  : {$f['url']}");
                $this->line("  Error: {$f['error']}");
            }
            file_put_contents(
                storage_path('logs/tour-import-errors.log'),
                date('[Y-m-d H:i:s] ') . json_encode($failures, JSON_PRETTY_PRINT) . "\n",
                FILE_APPEND
            );
            $this->warn("Failed URLs logged to storage/logs/tour-import-errors.log");
        }
    }
}
