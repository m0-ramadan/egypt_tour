<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        DB::transaction(function (): void {
            foreach (['english' => 'en', 'arabic' => 'ar'] as $legacy => $canonical) {
                $legacyRow = DB::table('languages')->whereRaw('LOWER(code) = ?', [$legacy])->first();
                if (!$legacyRow) {
                    continue;
                }

                $canonicalRow = DB::table('languages')->whereRaw('LOWER(code) = ?', [$canonical])->first();
                if ($canonicalRow && $canonicalRow->id !== $legacyRow->id) {
                    DB::table('languages')->where('id', $canonicalRow->id)->update([
                        'is_active' => (bool) $canonicalRow->is_active || (bool) $legacyRow->is_active,
                        'is_default' => (bool) $canonicalRow->is_default || (bool) $legacyRow->is_default,
                        'updated_at' => now(),
                    ]);
                    DB::table('languages')->where('id', $legacyRow->id)->delete();
                } else {
                    DB::table('languages')->where('id', $legacyRow->id)->update([
                        'code' => $canonical,
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        Cache::forget('active_languages');
        Cache::forget('supported_locales');
    }

    public function down(): void
    {
        // Canonical locale codes are intentionally retained.
    }
};
