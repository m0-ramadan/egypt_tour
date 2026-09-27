<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'article_type')) {
            try {
                DB::statement("ALTER TABLE articles MODIFY COLUMN article_type VARCHAR(100) NULL DEFAULT 'blog'");
            } catch (\Throwable $e) {
                Schema::table('articles', function (Blueprint $table) {
                    $table->string('article_type', 100)->nullable()->default('blog')->change();
                });
            }
        }
    }

    public function down(): void
    {
        // No revert needed
    }
};
