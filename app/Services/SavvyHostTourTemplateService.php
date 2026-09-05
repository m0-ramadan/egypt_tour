<?php

namespace App\Services;

use App\Models\SavvyMedia;
use App\Models\SavvyTourTemplate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SavvyHostTourTemplateService
{
    public function __construct(
        protected SavvyHostClient $client
    ) {}

    /**
     * Parse single page JSON payload from SavvyHost Templates API.
     *
     * SavvyHost returns a paginated envelope:
     * response.data.data = actual tour template records array
     * response.data = paginator metadata + records
     *
     * @param array $body
     * @return array
     * @throws Exception
     */
    public function parseTemplatesPage(array $body): array
    {
        if (!is_array($body)) {
            throw new Exception('Invalid JSON response structure from SavvyHost Templates API.');
        }

        if (($body['success'] ?? null) === false) {
            throw new Exception('SavvyHost Templates API returned an unsuccessful response.');
        }

        $pagination = $body['data'] ?? null;
        if (!is_array($pagination)) {
            throw new Exception('Invalid SavvyHost templates pagination envelope.');
        }

        // Real production API shape: data.data[] contains template items array
        $items = $pagination['data'] ?? null;

        // Fallback for flat items array if data itself is a list of template objects
        if ($items === null && array_is_list($pagination) && isset($pagination[0]) && is_array($pagination[0])) {
            $items = $pagination;
        }

        if (!is_array($items)) {
            throw new Exception('Invalid SavvyHost templates items array.');
        }

        $currentPage = (int) (
            $pagination['current_page']
            ?? data_get($body, 'meta.current_page')
            ?? 1
        );

        $lastPage = (int) (
            $pagination['last_page']
            ?? data_get($body, 'meta.last_page')
            ?? 1
        );

        $total = (int) (
            $pagination['total']
            ?? data_get($body, 'meta.total')
            ?? count($items)
        );

        $perPage = (int) (
            $pagination['per_page']
            ?? data_get($body, 'meta.per_page')
            ?? 50
        );

        return [
            'items' => array_values($items),
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
        ];
    }

    /**
     * Validate whether a template payload contains a valid Tour Template structure.
     *
     * @param mixed $item
     * @return bool
     */
    public function isValidTemplatePayload(mixed $item): bool
    {
        if (empty($item) || !is_array($item)) {
            return false;
        }

        $id = $item['id'] ?? $item['remote_id'] ?? null;
        if ($id === null || !is_scalar($id)) {
            return false;
        }

        $idString = trim((string) $id);
        if (empty($idString)) {
            return false;
        }

        // Reject URL IDs or pagination query URLs
        if (filter_var($idString, FILTER_VALIDATE_URL) || str_starts_with($idString, 'http://') || str_starts_with($idString, 'https://')) {
            return false;
        }

        // Must have a valid slug
        $slug = $item['slug'] ?? $item['remote_slug'] ?? null;
        if (empty($slug) || !is_string($slug)) {
            return false;
        }

        // Must have a valid name (array or non-empty string)
        $name = $item['name'] ?? null;
        if (empty($name) || (!is_array($name) && !is_string($name))) {
            return false;
        }

        // Must have a valid tour_type
        $tourType = $item['tour_type'] ?? $item['remote_tour_type'] ?? null;
        if (empty($tourType) || !is_string($tourType)) {
            return false;
        }

        return true;
    }

    /**
     * Sync all AI Tour Templates from SavvyHost API.
     *
     * @param string $processUuid Process identifier for scoping Cache progress
     * @param int|string|null $adminId Admin ID for scoping Cache progress
     * @return array
     * @throws Exception
     */
    public function syncAll(string $processUuid = 'default', int|string|null $adminId = null): array
    {
        $cacheKey = $this->getProgressCacheKey($processUuid, $adminId);

        $currentPage = 1;
        $lastPage = 1;
        $apiTotal = 0;
        $receivedCount = 0;
        $validCount = 0;
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $syncedRemoteIds = [];
        $now = now();

        $this->updateProgress($cacheKey, [
            'status' => 'running',
            'processed' => 0,
            'total' => 0,
            'percentage' => 0,
            'current_page' => 1,
            'last_page' => 1,
            'current_template' => '',
            'message' => 'Connecting to SavvyHost server and fetching tour templates...',
            'errors' => 0,
        ]);

        do {
            try {
                $response = $this->client->get('/api/v1/dashboard/ai/templates', [
                    'per_page' => 50,
                    'page' => $currentPage,
                    'is_active' => true,
                ]);
            } catch (Exception $e) {
                Log::error('SavvyHost Templates API connection error', [
                    'page' => $currentPage,
                    'error' => $e->getMessage(),
                ]);
                $this->updateProgress($cacheKey, [
                    'status' => 'failed',
                    'message' => "Server connection error on page {$currentPage}: " . $e->getMessage(),
                ]);
                throw new Exception("Failed to connect to SavvyHost Templates API at page {$currentPage}: " . $e->getMessage());
            }

            if (!$response->successful()) {
                $statusCode = $response->status();
                Log::error('SavvyHost Templates API request failed', [
                    'status' => $statusCode,
                    'page' => $currentPage,
                    'body' => $response->body(),
                ]);

                if ($statusCode === 403) {
                    throw new Exception('SavvyHost API access forbidden (HTTP 403). Please check tenant subdomain configuration.');
                }

                throw new Exception("SavvyHost Templates API returned HTTP status {$statusCode} on page {$currentPage}.");
            }

            $pageData = $this->parseTemplatesPage($response->json());
            $items = $pageData['items'];
            $lastPage = $pageData['last_page'];
            $apiTotal = $pageData['total'];

            foreach ($items as $item) {
                $receivedCount++;

                if (!$this->isValidTemplatePayload($item)) {
                    $skippedCount++;
                    Log::warning('Skipped invalid Savvy tour template payload item', [
                        'page' => $currentPage,
                        'item_sample' => is_array($item) ? array_slice($item, 0, 3) : $item,
                    ]);
                    continue;
                }

                $validCount++;

                try {
                    $template = $this->syncTemplate($item, $now);
                    $processedCount++;
                    $syncedRemoteIds[] = (string) $template->remote_id;

                    $templateName = $template->display_name;
                    $percentage = $apiTotal > 0 ? min(100, round(($processedCount / $apiTotal) * 100, 1)) : 0;

                    $this->updateProgress($cacheKey, [
                        'status' => 'running',
                        'processed' => $processedCount,
                        'total' => $apiTotal,
                        'percentage' => $percentage,
                        'current_page' => $currentPage,
                        'last_page' => $lastPage,
                        'current_template' => $templateName,
                        'message' => "Syncing tours... ({$processedCount} of {$apiTotal})",
                        'errors' => $errorCount,
                    ]);
                } catch (Exception $e) {
                    $errorCount++;
                    Log::warning('Error syncing template record', [
                        'remote_id' => $item['id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $currentPage++;
        } while ($currentPage <= $lastPage);

        // Atomic missing records update only after full sync completes successfully (Rule #34 & #35)
        if (!empty($syncedRemoteIds)) {
            SavvyTourTemplate::query()
                ->whereNotIn('remote_id', array_unique($syncedRemoteIds))
                ->update(['missing_from_last_sync' => true]);

            SavvyTourTemplate::query()
                ->whereIn('remote_id', array_unique($syncedRemoteIds))
                ->update(['missing_from_last_sync' => false]);
        }

        $finalStatus = ($apiTotal > 0 && $processedCount !== $apiTotal) ? 'completed_with_warnings' : 'completed';
        $finalMessage = ($finalStatus === 'completed_with_warnings')
            ? "SavvyHost reported {$apiTotal} templates but {$processedCount} valid templates were processed."
            : 'Synchronization completed successfully!';

        $this->updateProgress($cacheKey, [
            'status' => $finalStatus,
            'processed' => $processedCount,
            'total' => $apiTotal,
            'percentage' => 100,
            'current_page' => $lastPage,
            'last_page' => $lastPage,
            'current_template' => '',
            'message' => $finalMessage,
            'errors' => $errorCount,
        ]);

        return [
            'status' => $finalStatus,
            'api_total' => $apiTotal,
            'received_count' => $receivedCount,
            'valid_count' => $validCount,
            'processed_count' => $processedCount,
            'skipped_count' => $skippedCount,
            'error_count' => $errorCount,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Upsert a single remote template payload into local DB.
     */
    public function syncTemplate(array $remoteData, ?Carbon $now = null): SavvyTourTemplate
    {
        $now = $now ?? now();
        $normalized = $this->normalizeRemoteTemplate($remoteData);

        $remoteId = (string) ($remoteData['id'] ?? $remoteData['remote_id'] ?? '');
        if (empty($remoteId)) {
            throw new Exception('Missing remote_id in template item payload.');
        }

        // Try matching preview media from local SavvyMedia
        $previewMediaId = $this->resolvePreviewMediaId($normalized);

        $template = SavvyTourTemplate::updateOrCreate(
            ['remote_id' => $remoteId],
            array_merge($normalized, [
                'last_synced_at' => $now,
                'missing_from_last_sync' => false,
                'preview_media_id' => $previewMediaId,
            ])
        );

        return $template;
    }

    /**
     * Normalize remote template API payload into model attribute array.
     */
    public function normalizeRemoteTemplate(array $item): array
    {
        $name = $item['name'] ?? null;
        if (is_string($name)) {
            $name = ['en' => $name, 'ar' => $name];
        }

        $description = $item['description'] ?? null;

        $remoteCreatedAt = null;
        if (!empty($item['created_at'])) {
            try {
                $remoteCreatedAt = Carbon::parse($item['created_at']);
            } catch (Exception $e) {
                $remoteCreatedAt = null;
            }
        }

        $remoteUpdatedAt = null;
        if (!empty($item['updated_at'])) {
            try {
                $remoteUpdatedAt = Carbon::parse($item['updated_at']);
            } catch (Exception $e) {
                $remoteUpdatedAt = null;
            }
        }

        return [
            'remote_slug' => $item['slug'] ?? $item['remote_slug'] ?? null,
            'name' => is_array($name) ? $name : null,
            'description' => is_array($description) ? json_encode($description, JSON_UNESCAPED_UNICODE) : (is_null($description) ? null : (string) $description),
            'remote_tour_type' => $item['tour_type'] ?? $item['remote_tour_type'] ?? null,
            'remote_category' => $item['category'] ?? $item['remote_category'] ?? null,
            'region' => $item['region'] ?? null,
            'destinations' => is_array($item['destinations'] ?? null) ? $item['destinations'] : [],
            'cities' => is_array($item['cities'] ?? null) ? $item['cities'] : [],
            'vessel_classes' => is_array($item['vessel_classes'] ?? null) ? $item['vessel_classes'] : [],
            'default_ship_slug' => $item['default_ship_slug'] ?? null,
            'duration_value' => isset($item['duration_value']) ? (int) $item['duration_value'] : (isset($item['duration']) ? (int) $item['duration'] : null),
            'duration_unit' => $item['duration_unit'] ?? null,
            'description_template' => $item['description_template'] ?? null,
            'highlights' => is_array($item['highlights'] ?? null) ? $item['highlights'] : [],
            'itinerary_outline' => is_array($item['itinerary_outline'] ?? null) ? $item['itinerary_outline'] : [],
            'includes' => is_array($item['includes'] ?? null) ? $item['includes'] : [],
            'excludes' => is_array($item['excludes'] ?? null) ? $item['excludes'] : [],
            'ai_prompt_template' => $item['ai_prompt_template'] ?? null,
            'ai_config' => is_array($item['ai_config'] ?? null) ? $item['ai_config'] : [],
            'customization_options' => is_array($item['customization_options'] ?? null) ? $item['customization_options'] : [],
            'suggested_min_price' => isset($item['suggested_min_price']) ? (float) $item['suggested_min_price'] : null,
            'suggested_max_price' => isset($item['suggested_max_price']) ? (float) $item['suggested_max_price'] : null,
            'price_currency' => $item['price_currency'] ?? 'USD',
            'min_participants' => isset($item['min_participants']) ? (int) $item['min_participants'] : null,
            'max_participants' => isset($item['max_participants']) ? (int) $item['max_participants'] : null,
            'difficulty_level' => $item['difficulty_level'] ?? null,
            'tags' => is_array($item['tags'] ?? null) ? $item['tags'] : [],
            'generation_count' => isset($item['generation_count']) ? (int) $item['generation_count'] : 0,
            'popularity_score' => isset($item['popularity_score']) ? (float) $item['popularity_score'] : 0.0,
            'remote_is_active' => isset($item['is_active']) ? (bool) $item['is_active'] : true,
            'remote_is_featured' => isset($item['is_featured']) ? (bool) $item['is_featured'] : false,
            'remote_sort_order' => isset($item['sort_order']) ? (int) $item['sort_order'] : 0,
            'allowed_plans' => is_array($item['allowed_plans'] ?? null) ? $item['allowed_plans'] : [],
            'remote_created_at' => $remoteCreatedAt,
            'remote_updated_at' => $remoteUpdatedAt,
            'raw_payload' => $item,
        ];
    }

    /**
     * Safely detect and clean corrupted template records from local database.
     *
     * @param bool $dryRun
     * @return array
     */
    public function repairCorruptedRecords(bool $dryRun = false): array
    {
        $query = SavvyTourTemplate::query()
            ->where(function ($q) {
                $q->where('remote_id', 'like', 'http://%')
                  ->orWhere('remote_id', 'like', 'https://%')
                  ->orWhere(function ($sub) {
                      $sub->whereNull('name')
                          ->whereNull('remote_slug')
                          ->whereNull('remote_tour_type');
                  });
            });

        $corruptedRecords = $query->get();
        $corruptedCount = $corruptedRecords->count();
        $protectedCount = 0;
        $deletedCount = 0;

        foreach ($corruptedRecords as $record) {
            if (!empty($record->imported_package_id)) {
                $protectedCount++;
                Log::warning('Corrupted Savvy template is linked to an imported package and requires manual review.', [
                    'template_id' => $record->id,
                    'remote_id' => $record->remote_id,
                    'imported_package_id' => $record->imported_package_id,
                ]);
                continue;
            }

            if (!$dryRun) {
                $record->delete();
                $deletedCount++;
            }
        }

        return [
            'corrupted_count' => $corruptedCount,
            'deleted_count' => $deletedCount,
            'protected_count' => $protectedCount,
            'dry_run' => $dryRun,
        ];
    }

    /**
     * Get Cache key for sync progress.
     */
    public function getProgressCacheKey(string $processUuid, int|string|null $adminId = null): string
    {
        $adminId = $adminId ?: (auth('admin')->id() ?? 'guest');
        return "savvy_tour_sync_progress:{$adminId}:{$processUuid}";
    }

    /**
     * Update progress in Cache.
     */
    protected function updateProgress(string $cacheKey, array $data): void
    {
        $existing = Cache::get($cacheKey, []);
        Cache::put($cacheKey, array_merge($existing, $data), 600);
    }

    /**
     * Resolve preview media ID from SavvyMedia.
     */
    protected function resolvePreviewMediaId(array $normalized): ?int
    {
        $cities = $normalized['cities'] ?? [];
        $citySlug = !empty($cities[0]) ? strtolower(trim($cities[0])) : null;

        if ($citySlug) {
            $media = SavvyMedia::query()
                ->where('city_slug', $citySlug)
                ->orderByDesc('is_downloaded')
                ->first();

            if ($media) {
                return $media->id;
            }
        }

        return SavvyMedia::query()->orderByDesc('is_downloaded')->first()?->id;
    }
}
