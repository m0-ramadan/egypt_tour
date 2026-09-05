<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('destinations') || !Schema::hasTable('cities') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            UPDATE cities
            INNER JOIN destinations ON cities.id = destinations.city_id
            SET
                cities.short_description = COALESCE(cities.short_description, destinations.short_description),
                cities.description = COALESCE(cities.description, destinations.description),
                cities.hero_image = COALESCE(cities.hero_image, destinations.hero_image),
                cities.featured_image = COALESCE(cities.featured_image, destinations.featured_image),
                cities.latitude = COALESCE(cities.latitude, destinations.latitude),
                cities.longitude = COALESCE(cities.longitude, destinations.longitude),
                cities.is_featured = COALESCE(cities.is_featured, destinations.is_featured),
                cities.is_active = COALESCE(cities.is_active, destinations.is_active),
                cities.seo_title = COALESCE(cities.seo_title, destinations.seo_title),
                cities.seo_description = COALESCE(cities.seo_description, destinations.seo_description),
                cities.schema_json = COALESCE(cities.schema_json, destinations.schema_json)
        ");
    }

    public function down(): void
    {
        //
    }
};
