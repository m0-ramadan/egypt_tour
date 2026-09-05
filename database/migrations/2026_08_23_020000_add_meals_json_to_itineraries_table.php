<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('itineraries')) {
            Schema::table('itineraries', function (Blueprint $table) {
                if (!Schema::hasColumn('itineraries', 'meals')) {
                    $table->json('meals')->nullable()->after('description');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('itineraries')) {
            Schema::table('itineraries', function (Blueprint $table) {
                if (Schema::hasColumn('itineraries', 'meals')) {
                    $table->dropColumn('meals');
                }
            });
        }
    }
};
