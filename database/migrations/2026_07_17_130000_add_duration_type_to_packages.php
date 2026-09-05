<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('packages', 'duration_type')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->string('duration_type', 20)->default('days')->after('description');
            });
        }

        DB::table('packages')
            ->whereNotNull('duration_hours')
            ->where('duration_hours', '>', 0)
            ->where(function ($query) {
                $query->whereNull('duration_days')->orWhere('duration_days', 0);
            })
            ->update(['duration_type' => 'hours']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('packages', 'duration_type')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('duration_type');
            });
        }
    }
};
