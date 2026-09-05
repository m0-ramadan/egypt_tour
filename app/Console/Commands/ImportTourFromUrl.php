<?php

namespace App\Console\Commands;

use App\Services\ExternalTours\ExternalTourImportService;
use Illuminate\Console\Command;

class ImportTourFromUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tour:import-url
                            {url : The external tour URL to import}
                            {--rewrite : Rewrite marketing copy using AI}
                            {--download-images : Download and store tour images locally}
                            {--update : Update existing package if already imported}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an external tour page by URL and create/update a complete TravelNest Package';

    /**
     * Execute the console command.
     */
    public function handle(ExternalTourImportService $importService): int
    {
        $url = trim((string) $this->argument('url'));
        $rewrite = (bool) $this->option('rewrite');
        $downloadImages = (bool) $this->option('download-images');
        $update = (bool) $this->option('update');

        $this->info('Importing external tour...');

        try {
            $options = [
                'rewrite' => $rewrite,
                'download_images' => $downloadImages,
                'update' => $update,
            ];

            $result = $importService->import($url, $options);
            $package = $result['package'];
            $stats = $result['stats'];
            $warnings = $result['warnings'];

            $this->line('Source downloaded.');
            $this->line('Page parsed.');
            $this->line("Tour type: " . ($stats['tour_type'] ?? 'travel_package'));
            $this->line("Cities: " . ($stats['cities'] ?? ''));
            $this->line("Pricing levels: " . ($stats['pricing_levels_count'] ?? 0));
            $this->line("Images discovered: " . ($stats['images_discovered_count'] ?? 0));

            if ($result['is_update']) {
                $this->info('Package updated successfully.');
            } else {
                $this->info('Package created successfully.');
            }

            $this->line("Package ID: {$package->id}");

            if (!empty($warnings)) {
                $this->warn('Warnings:');
                foreach ($warnings as $warning) {
                    $this->line("- {$warning}");
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Tour import failed:\n{$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
