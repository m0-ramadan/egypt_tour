<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ai_translation_caches')) {
            Schema::create('ai_translation_caches', function (Blueprint $table) {
                $table->id();
                $table->string('source_hash', 64)->index();
                $table->string('source_language', 10)->index();
                $table->string('target_language', 10)->index();
                $table->text('source_text');
                $table->longText('translated_text');
                $table->string('provider', 50)->default('gemini');
                $table->string('model', 50)->default('gemini-2.5-flash');
                $table->timestamps();

                $table->unique(['source_hash', 'source_language', 'target_language'], 'ai_trans_cache_unique');
            });
        }

        if (!Schema::hasTable('ai_translation_usages')) {
            Schema::create('ai_translation_usages', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 50);
                $table->string('model', 50);
                $table->string('entity_type', 100)->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('field', 100)->nullable();
                $table->string('source_language', 10);
                $table->string('target_language', 10);
                $table->integer('prompt_tokens')->nullable();
                $table->integer('output_tokens')->nullable();
                $table->integer('total_tokens')->nullable();
                $table->string('status', 20)->default('success');
                $table->integer('duration_ms')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_translation_usages');
        Schema::dropIfExists('ai_translation_caches');
    }
};
