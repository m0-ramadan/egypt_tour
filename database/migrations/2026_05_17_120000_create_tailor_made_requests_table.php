<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tailor_made_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('country_of_residence', 120);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('trip_duration')->nullable();
            $table->string('accommodation_preference', 50)->nullable();
            $table->unsignedInteger('adults')->default(2);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('infants')->default(0);
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->string('budget_max', 50)->nullable();
            $table->string('occasion', 100)->nullable();
            $table->json('interests')->nullable();
            $table->text('dietary_requirements')->nullable();
            $table->text('mobility_requirements')->nullable();
            $table->longText('special_requests');
            $table->string('source')->nullable();
            $table->enum('status', ['new', 'contacted', 'quoted', 'won', 'lost', 'spam'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tailor_made_requests');
    }
};
