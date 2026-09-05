<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_categories')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement(
                'ALTER TABLE package_categories MODIFY name TEXT NOT NULL, MODIFY seo_title TEXT NULL'
            ),
            'pgsql' => DB::statement(
                'ALTER TABLE package_categories ALTER COLUMN name TYPE TEXT, ALTER COLUMN seo_title TYPE TEXT'
            ),
            // SQLite text affinity does not enforce VARCHAR length limits.
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('package_categories')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement(
                'ALTER TABLE package_categories MODIFY name VARCHAR(190) NOT NULL, MODIFY seo_title VARCHAR(255) NULL'
            ),
            'pgsql' => DB::statement(
                'ALTER TABLE package_categories ALTER COLUMN name TYPE VARCHAR(190), ALTER COLUMN seo_title TYPE VARCHAR(255)'
            ),
            default => null,
        };
    }
};
