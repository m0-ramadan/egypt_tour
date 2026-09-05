<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'source_type')) {
                $table->string('source_type', 50)->nullable()->after('updated_by');
            }
            if (!Schema::hasColumn('packages', 'source_remote_id')) {
                $table->string('source_remote_id', 100)->nullable()->after('source_type');
            }
            if (!Schema::hasColumn('packages', 'source_remote_slug')) {
                $table->string('source_remote_slug', 190)->nullable()->after('source_remote_id');
            }
            if (!Schema::hasColumn('packages', 'source_synced_at')) {
                $table->timestamp('source_synced_at')->nullable()->after('source_remote_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'source_remote_id',
                'source_remote_slug',
                'source_synced_at',
            ]);
        });
    }
};
