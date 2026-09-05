<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class NormalizeCategoryOrders extends Command
{
    protected $signature = 'categories:normalize-orders';
    protected $description = 'Fix categories order: remove zeros and duplicates and make it sequential';

    public function handle()
    {
        $updated = Category::normalizeOrders();
        $this->info("Updated {$updated} categories.");
        return 0;
    }
}