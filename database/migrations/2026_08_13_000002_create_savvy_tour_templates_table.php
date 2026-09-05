<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savvy_tour_templates', function (Blueprint $table) {
            $table->id();
            $table->string('remote_id', 100)->unique();
            $table->string('remote_slug', 190)->nullable()->index();
            $table->json('name')->nullable();
            $table->longText('description')->nullable();
            $table->string('remote_tour_type', 50)->nullable()->index();
            $table->string('remote_category', 100)->nullable()->index();
            $table->string('region', 100)->nullable()->index();
            $table->json('destinations')->nullable();
            $table->json('cities')->nullable();
            $table->json('vessel_classes')->nullable();
            $table->string('default_ship_slug', 100)->nullable();
            $table->integer('duration_value')->nullable();
            $table->string('duration_unit', 20)->nullable();
            $table->longText('description_template')->nullable();
            $table->json('highlights')->nullable();
            $table->json('itinerary_outline')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->longText('ai_prompt_template')->nullable();
            $table->json('ai_config')->nullable();
            $table->json('customization_options')->nullable();
            $table->decimal('suggested_min_price', 12, 2)->nullable();
            $table->decimal('suggested_max_price', 12, 2)->nullable();
            $table->string('price_currency', 10)->nullable();
            $table->integer('min_participants')->nullable();
            $table->integer('max_participants')->nullable();
            $table->string('difficulty_level', 30)->nullable();
            $table->json('tags')->nullable();
            $table->integer('generation_count')->default(0);
            $table->decimal('popularity_score', 8, 2)->default(0);
            $table->boolean('remote_is_active')->default(true)->index();
            $table->boolean('remote_is_featured')->default(false);
            $table->integer('remote_sort_order')->default(0);
            $table->json('allowed_plans')->nullable();
            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('remote_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->longText('raw_payload')->nullable();
            $table->foreignId('preview_media_id')->nullable()->constrained('savvy_media')->nullOnDelete();
            $table->foreignId('imported_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('import_status', 40)->default('not_imported')->index();
            $table->timestamp('imported_at')->nullable();
            $table->text('last_import_error')->nullable();
            $table->boolean('missing_from_last_sync')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savvy_tour_templates');
    }
};
