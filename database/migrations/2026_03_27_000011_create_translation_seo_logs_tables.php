<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type', 120);
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 10);
            $table->string('field', 120);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(
                ['translatable_type', 'translatable_id', 'locale', 'field'],
                'uq_translations_lookup'
            );
            $table->index(['translatable_type', 'translatable_id', 'locale'], 'idx_translations_model_locale');
        });

        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('model_type', 120);
            $table->unsignedBigInteger('model_id');
            $table->string('locale', 10)->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('schema_json')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'locale'], 'idx_seo_meta_lookup');
        });

        Schema::create('seo_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_path', 255)->unique();
            $table->string('new_path', 255);
            $table->smallInteger('http_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 120);
            $table->string('loggable_type', 120)->nullable();
            $table->unsignedBigInteger('loggable_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['loggable_type', 'loggable_id'], 'idx_activity_logs_model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('seo_redirects');
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('translations');
    }
};
