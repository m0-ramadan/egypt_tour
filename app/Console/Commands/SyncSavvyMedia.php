<?php

namespace App\Console\Commands;

use App\Services\SavvyHostMediaService;
use Exception;
use Illuminate\Console\Command;

class SyncSavvyMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savvy:sync-media {--no-download : Skip downloading image files locally}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize media metadata from SavvyHost Media API into local database and download image files';

    /**
     * Execute the console command.
     */
    public function handle(SavvyHostMediaService $service): int
    {
        $this->info('Starting SavvyHost Media synchronization and download...');

        $download = !$this->option('no-download');

        try {
            $stats = $service->syncAllMedia($download);

            $this->info("Synchronization completed successfully!");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Records Processed', $stats['total_processed']],
                    ['Total Downloaded Locally', $stats['total_downloaded']],
                    ['Total Pages Fetched', $stats['total_pages']],
                    ['API Total Reported', $stats['api_total']],
                ]
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
