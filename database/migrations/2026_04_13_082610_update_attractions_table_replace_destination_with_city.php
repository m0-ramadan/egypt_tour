<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attractions', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('destination_id')->constrained()->nullOnDelete();

            $table->decimal('latitude', 10, 7)->nullable()->after('map_url');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            $table->json('seo_title')->nullable()->after('sort_order');
            $table->json('seo_description')->nullable()->after('seo_title');
        });

        if (Schema::hasTable('destinations') && DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE attractions
                INNER JOIN destinations ON attractions.destination_id = destinations.id
                SET attractions.city_id = destinations.city_id
            ");
        }

        if (DB::getDriverName() === 'mysql') {
            Schema::table('attractions', function (Blueprint $table) {
                $table->dropForeign(['destination_id']);
                $table->dropColumn('destination_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('attractions', function (Blueprint $table) {
            $table->foreignId('destination_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
        });

        Schema::table('attractions', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn([
                'city_id',
                'latitude',
                'longitude',
                'seo_title',
                'seo_description',
            ]);
        });
    }
};
