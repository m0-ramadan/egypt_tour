<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('adult_price', 10, 2)->nullable()->default(0)->change();
            $table->decimal('child_price', 10, 2)->nullable()->default(0)->change();
            $table->decimal('infant_price', 10, 2)->nullable()->default(0)->change();
            $table->decimal('price_from', 10, 2)->nullable()->default(0)->change();
            $table->decimal('price_to', 10, 2)->nullable()->default(0)->change();
            $table->string('tour_type', 50)->nullable()->change();
            $table->string('booking_mode', 50)->nullable()->change();
            $table->string('difficulty_level', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('adult_price', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('child_price', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('infant_price', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('price_from', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('price_to', 10, 2)->nullable(false)->default(0)->change();
            $table->string('tour_type', 50)->nullable(false)->default('private')->change();
            $table->string('booking_mode', 50)->nullable(false)->default('request')->change();
            $table->string('difficulty_level', 50)->nullable()->change();
        });
    }
};
