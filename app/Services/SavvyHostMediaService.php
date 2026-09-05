<?php

namespace App\Services;

use App\Models\SavvyMedia;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SavvyHostMediaService
{
    public function __construct(
        protected SavvyHostClient $client
    ) {}

    /**
     * Synchronize all media records from SavvyHost Media API and download images locally.
     *
     * @param bool $downloadFiles Whether to download image files to local storage
     * @return array
     * @throws Exception
     */
    public function syncAllMedia(bool $downloadFiles = true): array
    {
        $currentPage = 1;
        $lastPage = 1;
        $totalProcessed = 0;
        $totalDownloaded = 0;
        $apiTotal = 0;
        $now = now();

        Cache::put('savvy_media_sync_progress', [
            'status' => 'running',
            'processed' => 0,
            'downloaded' => 0,
            'total' => 0,
            'percentage' => 0,
            'current_page' => 1,
            'last_page' => 1,
            'message' => 'Connecting to server and fetching media list...',
        ], 300);

        do {
            try {
                $response = $this->client->get('/api/v1/dashboard/media', [
                    'storage_type' => 'all',
                    'sort_field' => 'created_at',
                    'sort_direction' => 'desc',
                    'page' => $currentPage,
                    'per_page' => 50,
                ]);
            } catch (Exception $e) {
                Log::error('SavvyHost Media API connection error', [
                    'page' => $currentPage,
                    'error' => $e->getMessage(),
                ]);
                throw new Exception("Failed to connect to SavvyHost Media API at page {$currentPage}: " . $e->getMessage());
            }

            if (!$response->successful()) {
                $statusCode = $response->status();
                Log::error('SavvyHost Media API request failed', [
                    'status' => $statusCode,
                    'page' => $currentPage,
                    'body' => $response->body(),
                ]);

                if ($statusCode === 403) {
                    throw new Exception('SavvyHost Media API access forbidden (HTTP 403). Please check tenant subdomain configuration.');
                }

                throw new Exception("SavvyHost Media API returned HTTP status {$statusCode} on page {$currentPage}.");
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            $meta = $json['meta'] ?? [];

            if (is_array($data) && (isset($data['uuid']) || isset($data['url']))) {
                $data = [$data];
            }

            $lastPage = (int) ($meta['last_page'] ?? 1);
            $apiTotal = (int) ($meta['total'] ?? count((array) $data));

            if (empty($data) || !is_array($data)) {
                break;
            }

            foreach ($data as $item) {
                if (!is_array($item) || empty($item['uuid'])) {
                    continue;
                }

                $remoteCreatedAt = null;
                if (!empty($item['created_at'])) {
                    try {
                        $remoteCreatedAt = Carbon::parse($item['created_at']);
                    } catch (Exception $e) {
                        $remoteCreatedAt = null;
                    }
                }

                $mediaRecord = SavvyMedia::updateOrCreate(
                    ['uuid' => (string) $item['uuid']],
                    [
                        'remote_id' => isset($item['id']) ? (int) $item['id'] : null,
                        'storage_type' => $item['storage_type'] ?? null,
                        'filename' => $item['filename'] ?? null,
                        'original_filename' => $item['original_filename'] ?? null,
                        'mime_type' => $item['mime_type'] ?? null,
                        'size_bytes' => isset($item['size_bytes']) ? (int) $item['size_bytes'] : null,
                        'size_human' => $item['size_human'] ?? null,
                        'url' => $item['url'] ?? null,
                        'webp_url' => $item['webp_url'] ?? null,
                        'thumbnail_url' => $item['thumbnail_url'] ?? null,
                        'thumbnails' => is_array($item['thumbnails'] ?? null) ? $item['thumbnails'] : null,
                        'category' => $item['category'] ?? null,
                        'tags' => is_array($item['tags'] ?? null) ? $item['tags'] : [],
                        'country_slug' => $item['country_slug'] ?? null,
                        'city_slug' => $item['city_slug'] ?? null,
                        'sub_category' => $item['sub_category'] ?? null,
                        'alt_text' => $item['alt_text'] ?? null,
                        'title' => $item['title'] ?? null,
                        'description' => $item['description'] ?? null,
                        'type' => $item['type'] ?? 'image',
                        'is_global' => isset($item['is_global']) ? (bool) $item['is_global'] : false,
                        'is_public' => isset($item['is_public']) ? (bool) $item['is_public'] : false,
                        'remote_created_at' => $remoteCreatedAt,
                        'last_synced_at' => $now,
                    ]
                );

                if ($downloadFiles) {
                    $downloaded = $this->downloadMediaFiles($mediaRecord);
                    if ($downloaded) {
                        $totalDownloaded++;
                    }
                }

                $totalProcessed++;

                $percentage = $apiTotal > 0 ? min(100, round(($totalProcessed / $apiTotal) * 100, 1)) : 0;
                Cache::put('savvy_media_sync_progress', [
                    'status' => 'running',
                    'processed' => $totalProcessed,
                    'downloaded' => $totalDownloaded,
                    'total' => $apiTotal,
                    'percentage' => $percentage,
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'message' => "جاري تنزيل ومعالجة الصور... (الصفحة {$currentPage} من {$lastPage})",
                ], 300);
            }

            $currentPage++;
        } while ($currentPage <= $lastPage);

        Cache::put('savvy_media_sync_progress', [
            'status' => 'completed',
            'processed' => $totalProcessed,
            'downloaded' => $totalDownloaded,
            'total' => $apiTotal,
            'percentage' => 100,
            'current_page' => $lastPage,
            'last_page' => $lastPage,
            'message' => 'تم اكتكال الجلب والتنزيل بنجاح!',
        ], 300);

        return [
            'total_processed' => $totalProcessed,
            'total_downloaded' => $totalDownloaded,
            'total_pages' => $lastPage,
            'api_total' => $apiTotal,
        ];
    }

    /**
     * Download media files (main image and thumbnail) locally into public storage.
     *
     * @param SavvyMedia $media
     * @return bool
     */
    public function downloadMediaFiles(SavvyMedia $media): bool
    {
        $downloadUrl = $media->webp_url ?: $media->url;
        if (empty($downloadUrl)) {
            return false;
        }

        $downloadedMain = false;

        try {
            // Check main image
            if (!$media->local_path || !Storage::disk('public')->exists($media->local_path)) {
                $ext = pathinfo(parse_url($downloadUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (empty($ext) || strlen($ext) > 5) {
                    $ext = 'jpg';
                }

                $cleanFilename = $media->uuid . '.' . strtolower($ext);
                $localPath = 'savvy_media/' . $cleanFilename;

                $response = Http::timeout(30)->get($downloadUrl);
                if ($response->successful()) {
                    Storage::disk('public')->put($localPath, $response->body());
                    $media->local_path = $localPath;
                    $downloadedMain = true;
                }
            } else {
                $downloadedMain = true;
            }

            // Check thumbnail image if present
            if ($media->thumbnail_url && (!$media->local_thumbnail_path || !Storage::disk('public')->exists($media->local_thumbnail_path))) {
                $thumbExt = pathinfo(parse_url($media->thumbnail_url, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (empty($thumbExt) || strlen($thumbExt) > 5) {
                    $thumbExt = 'jpg';
                }

                $thumbFilename = 'thumbs/' . $media->uuid . '_thumb.' . strtolower($thumbExt);
                $localThumbPath = 'savvy_media/' . $thumbFilename;

                $thumbResponse = Http::timeout(20)->get($media->thumbnail_url);
                if ($thumbResponse->successful()) {
                    Storage::disk('public')->put($localThumbPath, $thumbResponse->body());
                    $media->local_thumbnail_path = $localThumbPath;
                }
            }

            if ($downloadedMain) {
                $media->is_downloaded = true;
                $media->save();
            }

            return $downloadedMain;
        } catch (Exception $e) {
            Log::warning("Failed to download local image for SavvyMedia UUID {$media->uuid}: " . $e->getMessage());
            return false;
        }
    }
}
