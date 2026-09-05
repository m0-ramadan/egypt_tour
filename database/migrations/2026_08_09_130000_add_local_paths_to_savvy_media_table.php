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
        Schema::table('savvy_media', function (Blueprint $table) {
            $table->string('local_path')->nullable()->after('thumbnail_url');
            $table->string('local_thumbnail_path')->nullable()->after('local_path');
            $table->boolean('is_downloaded')->default(false)->after('local_thumbnail_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savvy_media', function (Blueprint $table) {
            $table->dropColumn(['local_path', 'local_thumbnail_path', 'is_downloaded']);
        });
    }
};
