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
        Schema::create('package_attractions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('package_id')
                ->constrained('packages')
                ->onDelete('cascade');

            $table->foreignId('attraction_id')
                ->constrained('attractions')
                ->onDelete('cascade');

            // Translatable JSON fields (as used with your HasTranslatableAttributes trait)
            $table->json('title')->nullable();
            $table->json('teaser')->nullable();

            // Other fields
            $table->string('image')->nullable();   // path or URL to the image
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Optional: Add indexes for better performance
            $table->index(['package_id', 'sort_order']);
            $table->index('attraction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_attractions');
    }
};
