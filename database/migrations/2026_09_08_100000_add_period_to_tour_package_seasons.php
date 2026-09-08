<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_package_seasons', function (Blueprint $table) {
            $table->string('period')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tour_package_seasons', fn (Blueprint $table) => $table->dropColumn('period'));
    }
};
