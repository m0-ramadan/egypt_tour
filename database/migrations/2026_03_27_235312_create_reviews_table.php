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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();

            $table->decimal('rating', 2, 1);
            $table->string('title')->nullable();
            $table->text('content')->nullable();

            $table->text('pros')->nullable();
            $table->text('cons')->nullable();

            $table->date('travel_date')->nullable();
            $table->json('images')->nullable();

            $table->boolean('is_approved')->default(false);
            $table->integer('helpful_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
