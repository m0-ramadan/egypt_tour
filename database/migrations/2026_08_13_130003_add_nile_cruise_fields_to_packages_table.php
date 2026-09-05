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
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('nile_cruise_type_id')
                ->nullable()
                ->after('package_type')
                ->constrained('nile_cruise_types')
                ->onDelete('set null');

            $table->foreignId('nile_cruise_category_id')
                ->nullable()
                ->after('nile_cruise_type_id')
                ->constrained('nile_cruise_categories')
                ->onDelete('set null');

            $table->index(['package_type', 'nile_cruise_type_id', 'nile_cruise_category_id'], 'idx_packages_nile_cruise_type_cat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('idx_packages_nile_cruise_type_cat');
            $table->dropForeign(['nile_cruise_category_id']);
            $table->dropForeign(['nile_cruise_type_id']);
            $table->dropColumn(['nile_cruise_type_id', 'nile_cruise_category_id']);
        });
    }
};
