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
        Schema::create('savvy_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('remote_id')->nullable()->index();
            $table->string('uuid')->unique();
            $table->string('storage_type')->nullable();
            $table->string('filename')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('size_human')->nullable();
            $table->text('url')->nullable();
            $table->text('webp_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->json('thumbnails')->nullable();
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();
            $table->string('country_slug')->nullable()->index();
            $table->string('city_slug')->nullable()->index();
            $table->string('sub_category')->nullable()->index();
            $table->text('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_global')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamp('remote_created_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savvy_media');
    }
};
