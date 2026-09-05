<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nile_cruise_details')) {
            Schema::create('nile_cruise_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->unique()->constrained('packages')->cascadeOnDelete();
                $table->unsignedInteger('decks')->nullable();
                $table->unsignedInteger('sun_beds')->nullable();
                $table->unsignedInteger('sun_deck_pergolas')->nullable();
                $table->string('tour_style')->nullable();
                $table->string('route_summary')->nullable();
                $table->boolean('all_inclusive')->default(false);
                $table->json('what_to_bring')->nullable();
                $table->json('on_tour_languages')->nullable();
                $table->string('timezone')->nullable();
                $table->text('pickup_notes')->nullable();
                $table->text('dropoff_notes')->nullable();
                $table->string('fact_sheet_path')->nullable();
                $table->longText('additional_notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('nile_cruise_schedules')) {
            Schema::create('nile_cruise_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('departure_day')->nullable();
                $table->foreignId('departure_city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->foreignId('arrival_city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->string('direction')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['package_id', 'is_active', 'sort_order'], 'idx_nc_schedule_package_active_sort');
            });
        }

        if (!Schema::hasTable('nile_cruise_cabins')) {
            Schema::create('nile_cruise_cabins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('name');
                $table->unsignedInteger('quantity')->nullable();
                $table->string('bed_type')->nullable();
                $table->decimal('size_sqm', 8, 2)->nullable();
                $table->unsignedInteger('max_adults')->nullable();
                $table->unsignedInteger('max_children')->nullable();
                $table->boolean('has_private_bathroom')->default(false);
                $table->boolean('has_private_terrace')->default(false);
                $table->json('amenities')->nullable();
                $table->longText('description')->nullable();
                $table->string('featured_image')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['package_id', 'sort_order'], 'idx_nc_cabin_package_sort');
            });
        }

        if (!Schema::hasTable('nile_cruise_durations')) {
            Schema::create('nile_cruise_durations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->string('title');
                $table->unsignedInteger('days');
                $table->unsignedInteger('nights')->nullable();
                $table->string('direction')->nullable();
                $table->foreignId('departure_city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->foreignId('arrival_city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->string('departure_day')->nullable();
                $table->decimal('start_from_price', 12, 2)->nullable();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['package_id', 'is_active', 'sort_order'], 'idx_nc_duration_package_active_sort');
            });
        }

        if (!Schema::hasTable('nile_cruise_itinerary_days')) {
            Schema::create('nile_cruise_itinerary_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nile_cruise_duration_id')->constrained('nile_cruise_durations')->cascadeOnDelete();
                $table->unsignedInteger('day_number');
                $table->json('title')->nullable();
                $table->json('description')->nullable();
                $table->json('meals')->nullable();
                $table->json('overnight')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['nile_cruise_duration_id', 'day_number'], 'uniq_nc_duration_day');
            });
        }

        if (!Schema::hasTable('nile_cruise_itinerary_activities')) {
            Schema::create('nile_cruise_itinerary_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nile_cruise_itinerary_day_id');
                $table->foreign('nile_cruise_itinerary_day_id', 'fk_nc_activity_day')
                    ->references('id')->on('nile_cruise_itinerary_days')->cascadeOnDelete();
                $table->foreignId('attraction_id')->nullable()->constrained('attractions')->nullOnDelete();
                $table->json('title')->nullable();
                $table->json('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['nile_cruise_itinerary_day_id', 'sort_order'], 'idx_nc_day_activity_sort');
            });
        }

        if (!Schema::hasTable('nile_cruise_season_prices')) {
            Schema::create('nile_cruise_season_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
                $table->foreignId('nile_cruise_duration_id')->constrained('nile_cruise_durations')->cascadeOnDelete();
                $table->json('season_name')->nullable();
                $table->date('date_from')->nullable();
                $table->date('date_to')->nullable();
                $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
                $table->json('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['package_id', 'nile_cruise_duration_id', 'is_active'], 'idx_nc_season_package_duration');
            });
        }

        if (!Schema::hasTable('nile_cruise_season_price_items')) {
            Schema::create('nile_cruise_season_price_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('nile_cruise_season_price_id');
                $table->foreign('nile_cruise_season_price_id', 'fk_nc_price_item_season')
                    ->references('id')->on('nile_cruise_season_prices')->cascadeOnDelete();
                $table->foreignId('nile_cruise_cabin_id')->nullable()->constrained('nile_cruise_cabins')->nullOnDelete();
                $table->string('occupancy_type')->nullable();
                $table->json('label')->nullable();
                $table->decimal('price', 12, 2);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['nile_cruise_season_price_id', 'sort_order'], 'idx_nc_season_item_sort');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nile_cruise_season_price_items');
        Schema::dropIfExists('nile_cruise_season_prices');
        Schema::dropIfExists('nile_cruise_itinerary_activities');
        Schema::dropIfExists('nile_cruise_itinerary_days');
        Schema::dropIfExists('nile_cruise_durations');
        Schema::dropIfExists('nile_cruise_cabins');
        Schema::dropIfExists('nile_cruise_schedules');
        Schema::dropIfExists('nile_cruise_details');
    }
};
