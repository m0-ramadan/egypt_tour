<?php

namespace App\Console\Commands;

use App\Models\SavvyTourTemplate;
use App\Services\SavvyHostTourTemplateService;
use Exception;
use Illuminate\Console\Command;

class SavvyToursRepairCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savvy:tours:repair {--dry-run : Inspect corrupted records without deleting or syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and clean corrupted SavvyHost Tour Template DB records and execute a full re-sync';

    /**
     * Execute the console command.
     */
    public function handle(SavvyHostTourTemplateService $service): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Starting SavvyHost Tour Templates database repair inspection...');

        // 1. Detect and repair corrupted records
        $repairResult = $service->repairCorruptedRecords($isDryRun);

        $this->line("Detected corrupted rows: <comment>{$repairResult['corrupted_count']}</comment>");
        $this->line("Protected imported rows: <comment>{$repairResult['protected_count']}</comment>");

        if ($isDryRun) {
            $this->warn("Dry-run mode active. No records deleted. Found {$repairResult['corrupted_count']} corrupted rows that would be cleaned.");
            return self::SUCCESS;
        }

        $this->info("Deleted corrupted rows: <comment>{$repairResult['deleted_count']}</comment>");

        // 2. Execute full re-sync
        $this->info("\nStarting full re-sync from SavvyHost API...");

        try {
            $syncResult = $service->syncAll('artisan-repair', 1);

            $this->newLine();
            $this->info("Remote total: <comment>{$syncResult['api_total']}</comment>");
            $this->info("Received: <comment>{$syncResult['received_count']}</comment>");
            $this->info("Valid: <comment>{$syncResult['valid_count']}</comment>");
            $this->info("Saved: <comment>{$syncResult['processed_count']}</comment>");
            $this->info("Skipped: <comment>{$syncResult['skipped_count']}</comment>");
            $this->info("Errors: <comment>{$syncResult['error_count']}</comment>");

            // 3. Database Integrity Checks
            $invalidUrlCount = SavvyTourTemplate::where('remote_id', 'like', 'http%')->count();
            $incompleteCount = SavvyTourTemplate::whereNull('name')->whereNull('remote_slug')->count();
            $finalCount = SavvyTourTemplate::count();

            $this->newLine();
            $this->line("Final DB Template Count: <comment>{$finalCount}</comment>");
            $this->line("Invalid URL remote IDs: <comment>{$invalidUrlCount}</comment>");
            $this->line("Incomplete template records: <comment>{$incompleteCount}</comment>");

            if ($invalidUrlCount === 0 && $incompleteCount === 0) {
                $this->info("\nRepair completed successfully. All database records are clean and verified!");
                return self::SUCCESS;
            }

            $this->warn("\nRepair finished with warnings: Some records failed integrity checks.");
            return self::FAILURE;
        } catch (Exception $e) {
            $this->error("\nRepair failed during API synchronization: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
