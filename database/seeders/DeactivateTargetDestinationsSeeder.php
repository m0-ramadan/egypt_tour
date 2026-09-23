<?php

namespace Database\Seeders;

use App\Models\Attraction;
use App\Models\City;
use App\Models\PackageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DeactivateTargetDestinationsSeeder extends Seeder
{
    public function run(): void
    {
        // Target city slugs
        $targetCitySlugs = ['dahab', 'fayoum', 'faiyum', 'abu-simbel'];

        $cities = City::whereIn('slug', $targetCitySlugs)
            ->orWhere('name->en', 'like', '%dahab%')
            ->orWhere('name->en', 'like', '%fayoum%')
            ->orWhere('name->en', 'like', '%simbel%')
            ->get();

        foreach ($cities as $city) {
            $city->update(['is_active' => false]);
            // Also deactivate attractions belonging to this city
            Attraction::where('city_id', $city->id)->update(['is_active' => false]);
        }

        // Also check if any PackageCategory represents Dahab/Fayoum/Abu Simbel
        PackageCategory::whereIn('slug', $targetCitySlugs)
            ->orWhere('name->en', 'like', '%dahab%')
            ->orWhere('name->en', 'like', '%fayoum%')
            ->orWhere('name->en', 'like', '%simbel%')
            ->update(['is_active' => false]);

        Cache::forget('active_cities');
        Cache::forget('website_destinations');
        Cache::forget('supported_locales');
    }
}
