<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('legacy_import_maps')) {
            Schema::create('legacy_import_maps', function (Blueprint $table) {
                $table->id();
                $table->string('source_table')->index();
                $table->unsignedBigInteger('source_id')->index();
                $table->string('target_table')->index();
                $table->unsignedBigInteger('target_id')->index();
                $table->json('legacy_summary')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['source_table', 'source_id', 'target_table']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_import_maps');
    }
};