<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->json('short_description')->nullable()->after('name');
            $table->json('description')->nullable()->after('short_description');

            $table->string('hero_image')->nullable()->after('description');
            $table->string('featured_image')->nullable()->after('hero_image');

            $table->decimal('latitude', 10, 7)->nullable()->after('featured_image');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // $table->boolean('is_featured')->default(false)->after('longitude');
            // $table->boolean('is_active')->default(true)->after('is_featured');

            $table->json('seo_title')->nullable()->after('is_active');
            $table->json('seo_description')->nullable()->after('seo_title');
            $table->json('schema_json')->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'description',
                'hero_image',
                'featured_image',
                'latitude',
                'longitude',
                'is_featured',
                'is_active',
                'seo_title',
                'seo_description',
                'schema_json',
            ]);
        });
    }
};
