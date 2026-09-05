<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'host')) {
                $table->string('host')->nullable()->after('ip');
            }
            if (!Schema::hasColumn('visitors', 'method')) {
                $table->string('method', 10)->nullable()->after('host');
            }
            if (!Schema::hasColumn('visitors', 'path')) {
                $table->string('path')->nullable()->after('method');
            }
            if (!Schema::hasColumn('visitors', 'full_url')) {
                $table->text('full_url')->nullable()->after('path');
            }
            if (!Schema::hasColumn('visitors', 'referer')) {
                $table->text('referer')->nullable()->after('full_url');
            }
            if (!Schema::hasColumn('visitors', 'browser')) {
                $table->string('browser', 100)->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('visitors', 'browser_version')) {
                $table->string('browser_version', 50)->nullable()->after('browser');
            }
            if (!Schema::hasColumn('visitors', 'platform')) {
                $table->string('platform', 100)->nullable()->after('browser_version');
            }
            if (!Schema::hasColumn('visitors', 'device')) {
                $table->string('device', 100)->nullable()->after('platform');
            }
            if (!Schema::hasColumn('visitors', 'is_mobile')) {
                $table->boolean('is_mobile')->default(false)->after('device');
            }
            if (!Schema::hasColumn('visitors', 'is_tablet')) {
                $table->boolean('is_tablet')->default(false)->after('is_mobile');
            }
            if (!Schema::hasColumn('visitors', 'is_desktop')) {
                $table->boolean('is_desktop')->default(true)->after('is_tablet');
            }
            if (!Schema::hasColumn('visitors', 'is_bot')) {
                $table->boolean('is_bot')->default(false)->after('is_desktop');
            }
            if (!Schema::hasColumn('visitors', 'country_iso')) {
                $table->string('country_iso', 10)->nullable()->after('country');
            }
            if (!Schema::hasColumn('visitors', 'region')) {
                $table->string('region', 100)->nullable()->after('country_iso');
            }
            if (!Schema::hasColumn('visitors', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('city');
            }
            if (!Schema::hasColumn('visitors', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('visitors', 'timezone')) {
                $table->string('timezone', 100)->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('visitors', 'headers')) {
                $table->longText('headers')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('visitors', 'query')) {
                $table->text('query')->nullable()->after('headers');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $columns = [
                'host', 'method', 'path', 'full_url', 'referer',
                'browser', 'browser_version', 'platform', 'device',
                'is_mobile', 'is_tablet', 'is_desktop', 'is_bot',
                'country_iso', 'region', 'latitude', 'longitude',
                'timezone', 'headers', 'query'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('visitors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
