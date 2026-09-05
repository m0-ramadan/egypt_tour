<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inquiries') && Schema::hasColumn('inquiries', 'inquiry_type')) {
            try {
                DB::statement("ALTER TABLE inquiries MODIFY COLUMN inquiry_type VARCHAR(50) NULL DEFAULT 'contact'");
            } catch (\Throwable $e) {
                // Fallback using Schema if DB statement fails
                Schema::table('inquiries', function (Blueprint $table) {
                    $table->string('inquiry_type', 50)->nullable()->default('contact')->change();
                });
            }
        }
    }

    public function down(): void
    {
        // No revert needed
    }
};
