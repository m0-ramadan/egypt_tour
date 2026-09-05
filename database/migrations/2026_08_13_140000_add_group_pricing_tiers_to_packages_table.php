<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('packages', 'group_pricing_tiers')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->json('group_pricing_tiers')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('packages', 'group_pricing_tiers')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('group_pricing_tiers');
            });
        }
    }
};
