<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeduplicatePackageCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds to merge duplicate categories into primary categories.
     */
    public function run(): void
    {
        $merges = [
            3  => [12],     // holiday-packages <- egypt-holiday-packages
            4  => [13],     // christmas-holidays <- egypt-christmas-holidays
            5  => [14],     // classic-packages <- classic-egypt-tours
            6  => [15],     // short-breaks-packages <- egypt-short-breaks-packages
            47 => [7, 16],  // nile-cruises <- nile-cruise, egypt-nile-cruise
            17 => [8],      // standard-nile-cruise <- standard-nile-cruise-packages
            18 => [9],      // egypt-deluxe-nile-cruise <- deluxe-nile-cruise-packages
            19 => [10],     // egypt-luxury-nile-cruise <- luxury-nile-cruise-packages
            20 => [26],     // private-egypt-tours <- private-egypt-tours-2
            21 => [25],     // family-egypt-tours <- family-egypt-tours-2
            22 => [24],     // egypt-vacation-packages <- egypt-vacation-packages-2
            27 => [39],     // cairo-tours <- cairo-tours-2
            28 => [40],     // luxor-tours <- luxor-tours-2
            29 => [42],     // hurghada-tours <- hurghada-tours-2
            30 => [41],     // aswan-tours <- aswan-tours-2
            31 => [43],     // sharm-el-sheikh-tours <- sharm-el-sheikh-tours-2
            32 => [44],     // marsa-alam-tours <- marsa-alam-tours-2
        ];

        DB::transaction(function () use ($merges) {
            foreach ($merges as $primaryId => $dupIds) {
                $primary = PackageCategory::find($primaryId);
                if (!$primary) {
                    $this->command->warn("Primary category ID {$primaryId} not found, skipping.");
                    continue;
                }

                // 1. Re-assign packages
                $transferredPackages = Package::whereIn('category_id', $dupIds)->update([
                    'category_id' => $primaryId,
                ]);

                // 2. Re-assign child categories
                PackageCategory::whereIn('parent_id', $dupIds)->update([
                    'parent_id' => $primaryId,
                ]);

                // 3. Re-assign FAQs if table exists
                if (Schema::hasTable('faqs') && Schema::hasColumn('faqs', 'category_id')) {
                    DB::table('faqs')->whereIn('category_id', $dupIds)->update([
                        'category_id' => $primaryId,
                    ]);
                }

                // 4. Delete duplicates
                $deletedCount = PackageCategory::whereIn('id', $dupIds)->delete();

                $this->command->info("Merged duplicate categories " . implode(', ', $dupIds) . " into {$primary->slug} (ID: {$primaryId}). Transferred {$transferredPackages} packages. Deleted {$deletedCount} category records.");
            }
        });

        Cache::forget('active_package_categories');
        Cache::forget('website_categories');
        Cache::flush();

        $this->command->info('Package Category deduplication completed successfully.');
    }
}
