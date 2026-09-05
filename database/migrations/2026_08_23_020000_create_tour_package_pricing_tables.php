<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tour_package_accommodations')) {
            Schema::create('tour_package_accommodations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['package_id', 'is_active', 'sort_order'], 'idx_tp_acc_package_sort');
            });
        }

        if (!Schema::hasTable('tour_package_seasons')) {
            Schema::create('tour_package_seasons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->foreignId('accommodation_id')->constrained('tour_package_accommodations')->cascadeOnDelete();
                $table->json('name')->nullable();
                $table->date('date_from')->nullable();
                $table->date('date_to')->nullable();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['package_id', 'accommodation_id', 'is_active'], 'idx_tp_season_acc');
            });
        }

        if (!Schema::hasTable('tour_package_price_items')) {
            Schema::create('tour_package_price_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('season_id')->constrained('tour_package_seasons')->cascadeOnDelete();
                $table->string('occupancy_type')->nullable();
                $table->json('label')->nullable();
                $table->decimal('price', 12, 2);
                $table->string('price_unit', 50)->default('per_person');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['season_id', 'sort_order'], 'idx_tp_item_season');
            });
        }

        if (!Schema::hasTable('tour_package_hotels')) {
            Schema::create('tour_package_hotels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accommodation_id')->constrained('tour_package_accommodations')->cascadeOnDelete();
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->string('city_name')->nullable();
                $table->string('hotel_name');
                $table->unsignedInteger('star_rating')->nullable();
                $table->text('description')->nullable();
                $table->string('room_type')->nullable();
                $table->string('meal_plan')->nullable();
                $table->string('alternative_note')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['accommodation_id', 'sort_order'], 'idx_tp_hotel_acc');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_package_hotels');
        Schema::dropIfExists('tour_package_price_items');
        Schema::dropIfExists('tour_package_seasons');
        Schema::dropIfExists('tour_package_accommodations');
    }
};
