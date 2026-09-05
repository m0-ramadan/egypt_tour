<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getSchemaBuilder()->hasTable('languages')) {
            DB::table('languages')
                ->where('code', 'english')
                ->orWhere('id', 1)
                ->update([
                    'code' => 'en',
                    'name' => 'English',
                    'native_name' => 'English',
                    'is_default' => true,
                    'is_active' => true,
                ]);

            DB::table('languages')
                ->where('code', 'It')
                ->update(['code' => 'it']);
        }

        Cache::forget('active_languages');
        Cache::forget('supported_locales');
    }

    public function down(): void
    {
    }
};
