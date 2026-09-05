<?php

namespace App\Services\Translation;

use App\Services\Translation\DTOs\TranslationResult;
use App\Services\Translation\DTOs\TranslationUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationCacheService
{
    /**
     * Get cached translation if exists.
     */
    public function getCached(TranslationUnit $unit): ?string
    {
        $key = $this->cacheKey($unit);

        try {
            $cached = Cache::get($key);
            if (is_string($cached) && trim($cached) !== '') {
                return $cached;
            }
        } catch (\Throwable) {
            // Continue to the durable cache.
        }

        try {
            $cached = DB::table('ai_translation_caches')
                ->where('source_hash', $unit->contentHash)
                ->where('source_language', strtolower($unit->sourceLanguage))
                ->where('target_language', strtolower($unit->targetLanguage))
                ->value('translated_text');
        } catch (\Throwable) {
            return null;
        }

        if (is_string($cached) && trim($cached) !== '') {
            try {
                Cache::put($key, $cached, (int) config('translation.cache_ttl', 2592000));
            } catch (\Throwable) {
                // The database value remains usable.
            }
        }

        return $cached ?: null;
    }

    /**
     * Store translation in database cache.
     */
    public function storeCache(TranslationUnit $unit, TranslationResult $result): void
    {
        if (!$result->isSuccess || empty($result->translatedText)) {
            return;
        }

        try {
            Cache::put(
                $this->cacheKey($unit),
                $result->translatedText,
                (int) config('translation.cache_ttl', 2592000)
            );
        } catch (\Throwable) {
            // Continue with durable storage.
        }

        try {
            DB::table('ai_translation_caches')->updateOrInsert(
                [
                    'source_hash' => $unit->contentHash,
                    'source_language' => strtolower($unit->sourceLanguage),
                    'target_language' => strtolower($unit->targetLanguage),
                ],
                [
                    'source_text' => $unit->sourceText,
                    'translated_text' => $result->translatedText,
                    'provider' => $result->provider,
                    'model' => $result->model,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to store the durable AI translation cache.', ['exception' => $e::class]);
        }
    }

    /**
     * Log translation usage metadata for auditing and cost monitoring.
     */
    public function logUsage(TranslationUnit $unit, TranslationResult $result, string $status = 'success'): void
    {
        try {
            DB::table('ai_translation_usages')->insert([
                'provider' => $result->provider,
                'model' => $result->model,
                'entity_type' => $unit->entityType,
                'entity_id' => is_numeric($unit->entityId) ? (int) $unit->entityId : null,
                'field' => $unit->field,
                'source_language' => strtolower($unit->sourceLanguage),
                'target_language' => strtolower($unit->targetLanguage),
                'prompt_tokens' => $result->promptTokens,
                'output_tokens' => $result->outputTokens,
                'total_tokens' => $result->totalTokens,
                'status' => $status,
                'duration_ms' => $result->durationMs,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('Failed to store AI translation usage metadata.', ['exception' => $e::class]);
        }
    }

    private function cacheKey(TranslationUnit $unit): string
    {
        return sprintf(
            'translation:%s:%s:%s',
            strtolower($unit->sourceLanguage),
            strtolower($unit->targetLanguage),
            $unit->contentHash
        );
    }
}
