<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class DisableEmptyCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
      protected $signature = 'disable:empty-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
 public function handle()
{
    $count = Category::disableEmptyCategories();
    $this->info("Disabled {$count} empty categories.");
    return 0;
}
}
