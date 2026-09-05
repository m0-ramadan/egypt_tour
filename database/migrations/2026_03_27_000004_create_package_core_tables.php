<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('package_categories')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 190)->unique();
            $table->string('name', 190);
            $table->text('description')->nullable();
            $table->enum('category_type', [
                'travel_package',
                'nile_cruise',
                'day_tour',
                'shore_excursion',
                'deal',
                'multi_country',
                'custom',
            ])->default('travel_package');
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->integer('min_days')->nullable();
            $table->integer('max_days')->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('symbol', 10)->nullable();
            $table->string('name', 100);
            $table->decimal('rate_to_default', 18, 8)->default(1);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('package_categories')->nullOnDelete();
            $table->foreignId('primary_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->enum('package_type', [
                'travel_package',
                'nile_cruise',
                'day_tour',
                'shore_excursion',
                'deal',
                'multi_country',
                'custom',
            ])->default('travel_package');
            $table->string('slug', 220)->unique();
            $table->string('title', 255);
            $table->string('subtitle')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('duration_nights')->nullable();
            $table->decimal('start_from_price', 12, 2)->nullable();
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->string('schedule_text')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->string('destinations_text')->nullable();
            $table->string('location_summary')->nullable();
            $table->enum('tour_type', ['private', 'group', 'shared', 'custom'])->default('private');
            $table->enum('difficulty_level', ['easy', 'moderate', 'hard'])->nullable();
            $table->enum('booking_mode', ['request', 'instant'])->default('request');
            $table->decimal('rating_avg', 4, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('min_participants')->nullable();
            $table->unsignedInteger('max_participants')->nullable();
            $table->unsignedInteger('booking_lead_days')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->longText('terms_conditions')->nullable();
            $table->json('faq_json')->nullable();
            $table->string('video_url')->nullable();
            $table->json('gallery_images')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_ultra_luxury')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('breadcrumb_title')->nullable();
            $table->string('canonical_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('package_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 130)->unique();
            $table->timestamps();
        });

        Schema::create('package_tag_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('package_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['package_id', 'tag_id']);
        });

        Schema::create('package_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->integer('stop_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->integer('nights')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'destination_id', 'stop_order']);
        });

        Schema::create('package_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_highlights');
        Schema::dropIfExists('package_destinations');
        Schema::dropIfExists('package_tag_items');
        Schema::dropIfExists('package_tags');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('package_categories');
    }
};
