<?php

namespace Database\Seeders;

use App\Services\ExternalTours\ExternalTourImportService;
use Illuminate\Database\Seeder;
use RuntimeException;

class ImportTourFromUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ExternalTourImportService $importService): void
    {
        $url = env('TOUR_IMPORT_URL');

        if (empty($url)) {
            throw new RuntimeException('TOUR_IMPORT_URL environment variable is not set. Please provide a tour URL in your environment.');
        }

        $rewrite = filter_var(env('TOUR_IMPORT_REWRITE_CONTENT', true), FILTER_VALIDATE_BOOLEAN);
        $downloadImages = filter_var(env('TOUR_IMPORT_DOWNLOAD_IMAGES', true), FILTER_VALIDATE_BOOLEAN);
        $update = filter_var(env('TOUR_IMPORT_UPDATE_EXISTING', false), FILTER_VALIDATE_BOOLEAN);

        $this->command?->info("Importing tour from environment URL: {$url}");

        $result = $importService->import((string) $url, [
            'rewrite' => $rewrite,
            'download_images' => $downloadImages,
            'update' => $update,
        ]);

        $package = $result['package'];
        $this->command?->info("Package #{$package->id} successfully imported/synced.");

        if (!empty($result['warnings'])) {
            foreach ($result['warnings'] as $warning) {
                $this->command?->warn("- {$warning}");
            }
        }
    }
}
