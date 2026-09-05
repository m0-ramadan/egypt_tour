<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->integer('day_number');
            $table->string('title', 255);
            $table->longText('description')->nullable();
            $table->boolean('meals_breakfast')->default(false);
            $table->boolean('meals_lunch')->default(false);
            $table->boolean('meals_dinner')->default(false);
            $table->string('overnight_location')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['package_id', 'day_number']);
        });

        Schema::create('package_inclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['included', 'excluded', 'optional'])->default('included');
            $table->string('title')->nullable();
            $table->text('description');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 190)->unique();
            $table->string('name', 190);
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('opening_hours')->nullable();
            $table->string('map_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attraction_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attraction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attraction_id', 'package_id']);
        });

        Schema::create('cruises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('ship_name');
            $table->string('cruise_class')->nullable();
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->string('sailing_days')->nullable();
            $table->unsignedTinyInteger('star_rating')->nullable();
            $table->unsignedInteger('cabin_count')->nullable();
            $table->json('onboard_features')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cruises');
        Schema::dropIfExists('attraction_package');
        Schema::dropIfExists('attractions');
        Schema::dropIfExists('package_inclusions');
        Schema::dropIfExists('itineraries');
    }
};
