<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('packages')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->text('title')->change();
            $table->text('subtitle')->nullable()->change();
            $table->text('schedule_text')->nullable()->change();
            $table->text('pickup_location')->nullable()->change();
            $table->text('dropoff_location')->nullable()->change();
            $table->text('destinations_text')->nullable()->change();
            $table->text('location_summary')->nullable()->change();
            $table->text('seo_title')->nullable()->change();
            $table->text('breadcrumb_title')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Deliberately non-destructive: multilingual JSON can exceed VARCHAR limits.
    }
};
