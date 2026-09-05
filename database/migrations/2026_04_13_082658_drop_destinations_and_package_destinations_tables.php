<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // لو attractions ما زال فيها destination_id
        if (Schema::hasTable('attractions') && Schema::hasColumn('attractions', 'destination_id')) {
            Schema::table('attractions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['destination_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        // لو package_destinations موجود
        if (Schema::hasTable('package_destinations')) {
            Schema::table('package_destinations', function (Blueprint $table) {
                try {
                    $table->dropForeign(['destination_id']);
                } catch (\Throwable $e) {
                }

                try {
                    $table->dropForeign(['package_id']);
                } catch (\Throwable $e) {
                }
            });

            Schema::dropIfExists('package_destinations');
        }

        // أي جداول أخرى مرتبطة بـ destinations
        $foreignKeys = DB::select("
            SELECT TABLE_NAME, CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = 'destinations'
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `{$fk->TABLE_NAME}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        Schema::dropIfExists('destinations');
    }

    public function down(): void
    {
        //
    }
};
