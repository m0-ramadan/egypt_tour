<?php

namespace App\Console\Commands;

use Database\Seeders\DeepSeekTranslationSeeder;
use Illuminate\Console\Command;

class TranslateContentWithDeepSeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:translate-deepseek
                            {--dry-run : Preview missing translations without modifying database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and translate all packages and articles into all active dashboard languages using DeepSeek AI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No database records will be modified.');
        }

        $seeder = new DeepSeekTranslationSeeder($dryRun);
        $seeder->setCommand($this);
        $seeder->run();

        return Command::SUCCESS;
    }
}
