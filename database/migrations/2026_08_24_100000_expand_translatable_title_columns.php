<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $alterations = [
                'package_highlights' => 'MODIFY title TEXT NULL, MODIFY description TEXT NULL',
                'package_inclusions' => 'MODIFY title TEXT NULL, MODIFY description TEXT NULL',
                'itineraries' => 'MODIFY title TEXT NULL',
                'nile_cruise_cabins' => 'MODIFY name TEXT NULL',
                'nile_cruise_addons' => 'MODIFY name TEXT NULL',
                'package_addons' => 'MODIFY title TEXT NULL',
                'package_facilities' => 'MODIFY title TEXT NULL',
                'tour_package_accommodations' => 'MODIFY name TEXT NULL',
                'nile_cruise_durations' => 'MODIFY title TEXT NULL',
            ];

            foreach ($alterations as $table => $clause) {
                if (Schema::hasTable($table)) {
                    DB::statement("ALTER TABLE {$table} {$clause}");
                }
            }
        } elseif ($driver === 'pgsql') {
            $alterations = [
                'package_highlights' => ['title TYPE TEXT', 'description TYPE TEXT'],
                'package_inclusions' => ['title TYPE TEXT', 'description TYPE TEXT'],
                'itineraries' => ['title TYPE TEXT'],
                'nile_cruise_cabins' => ['name TYPE TEXT'],
                'nile_cruise_addons' => ['name TYPE TEXT'],
                'package_addons' => ['title TYPE TEXT'],
                'package_facilities' => ['title TYPE TEXT'],
                'tour_package_accommodations' => ['name TYPE TEXT'],
                'nile_cruise_durations' => ['title TYPE TEXT'],
            ];

            foreach ($alterations as $table => $clauses) {
                if (Schema::hasTable($table)) {
                    $clauseStr = implode(', ', array_map(fn($c) => "ALTER COLUMN {$c}", $clauses));
                    DB::statement("ALTER TABLE {$table} {$clauseStr}");
                }
            }
        } else {
            Schema::table('package_highlights', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
                $table->text('description')->nullable()->change();
            });
            Schema::table('package_inclusions', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
                $table->text('description')->nullable()->change();
            });
            Schema::table('itineraries', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
            Schema::table('nile_cruise_cabins', function (Blueprint $table) {
                $table->text('name')->nullable()->change();
            });
            Schema::table('nile_cruise_addons', function (Blueprint $table) {
                $table->text('name')->nullable()->change();
            });
            Schema::table('package_addons', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
            Schema::table('package_facilities', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
            Schema::table('tour_package_accommodations', function (Blueprint $table) {
                $table->text('name')->nullable()->change();
            });
            Schema::table('nile_cruise_durations', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive down - columns remain TEXT
    }
};

