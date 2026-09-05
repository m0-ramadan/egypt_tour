<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_cities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('stop_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('nights')->nullable();

            $table->timestamps();
        });

        if (Schema::hasTable('package_destinations') && Schema::hasTable('destinations') && DB::getDriverName() === 'mysql') {
            DB::statement("
                INSERT INTO package_cities (package_id, city_id, stop_order, is_primary, nights, created_at, updated_at)
                SELECT
                    pd.package_id,
                    d.city_id,
                    pd.stop_order,
                    pd.is_primary,
                    pd.nights,
                    NOW(),
                    NOW()
                FROM package_destinations pd
                INNER JOIN destinations d ON pd.destination_id = d.id
                WHERE d.city_id IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('package_cities');
    }
};
