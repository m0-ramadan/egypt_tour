<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        DB::table('languages')->update(['is_default' => false]);

        DB::table('languages')
            ->whereRaw('LOWER(code) = ?', ['en'])
            ->update([
                'is_default' => true,
                'is_active' => true,
            ]);
    }

    public function down(): void
    {
        // The previous default is environment-specific and cannot be restored safely.
    }
};
