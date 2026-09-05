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
        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();

            $table->string('label')->nullable(); // Standard / Deluxe
            $table->string('season_name')->nullable();
            $table->string('price_type')->nullable(); // per_person / group

            $table->string('room_type')->nullable(); // single / double / triple

            $table->integer('pax_min')->nullable();
            $table->integer('pax_max')->nullable();

            $table->integer('group_size_min')->nullable();
            $table->integer('group_size_max')->nullable();

            $table->decimal('amount', 10, 2);
            $table->foreignId('currency_id')->nullable()->constrained();

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_id');
    }
};
