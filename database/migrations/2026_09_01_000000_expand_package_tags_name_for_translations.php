<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('package_tags') || !Schema::hasColumn('package_tags', 'name')) {
            return;
        }

        if (Schema::hasIndex('package_tags', 'package_tags_name_unique')) {
            Schema::table('package_tags', function (Blueprint $table) {
                $table->dropUnique('package_tags_name_unique');
            });
        }

        Schema::table('package_tags', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    public function down(): void
    {
        // Deliberately non-destructive: translated names may exceed the old VARCHAR(100).
    }
};
