<?php

namespace App\Services\ExternalTours;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Only download and reuse third-party images when the operator
// has the legal right or permission to reuse them.
class ExternalTourImageDownloader
{
    /**
     * Keywords that flag an image URL as non-tour asset (logos, icons, trackers, etc.).
     */
    protected const EXCLUDED_KEYWORDS = [
        'logo',
        'favicon',
        'sprite',
        'icon',
        'tripadvisor',
        'facebook',
        'instagram',
        'youtube',
        'twitter',
        'whatsapp',
        'payment',
        'visa',
        'mastercard',
        'flag',
        'avatar',
        'tracking',
    ];

    /**
     * Supported image MIME types mapped to file extensions.
     */
    protected const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    /**
     * Download candidate images and persist them into the configured storage disk.
     *
     * @param int $packageId
     * @param array<string> $imageUrls
     * @param array<string, mixed> $options
     * @return array{
     *     featured_image: ?string,
     *     gallery_images: array<string>,
     *     warnings: array<string>
     * }
     */
    public function download(int $packageId, array $imageUrls, array $options = []): array
    {
        $disk = (string) ($options['disk'] ?? config('tour_import.image_disk', 'public'));
        $baseDir = rtrim((string) ($options['directory'] ?? config('tour_import.image_directory', 'packages/imported')), '/');
        $maxImages = (int) ($options['max_images'] ?? config('tour_import.max_images', 10));
        $maxBytes = (int) ($options['max_image_bytes'] ?? config('tour_import.max_image_bytes', 5 * 1024 * 1024));
        $timeout = (int) ($options['timeout'] ?? config('tour_import.timeout', 30));
        $connectTimeout = (int) ($options['connect_timeout'] ?? config('tour_import.connect_timeout', 10));
        $userAgent = (string) ($options['user_agent'] ?? config('tour_import.user_agent', 'TravelNest Tour Importer/1.0'));

        $packageDir = "{$baseDir}/{$packageId}";
        $savedPaths = [];
        $warnings = [];

        $filteredUrls = $this->filterCandidateUrls($imageUrls);

        foreach ($filteredUrls as $url) {
            if (count($savedPaths) >= $maxImages) {
                break;
            }

            try {
                if (!$this->isSafeRemoteUrl($url)) {
                    $warnings[] = "Skipped insecure or non-public image URL: {$url}";
                    continue;
                }

                $response = Http::timeout($timeout)
                    ->connectTimeout($connectTimeout)
                    ->withUserAgent($userAgent)
                    ->get($url);

                if (!$response->successful()) {
                    $warnings[] = "Failed downloading image (HTTP {$response->status()}): {$url}";
                    continue;
                }

                $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0] ?? ''));
                if (!str_starts_with($contentType, 'image/') || !isset(self::MIME_EXTENSIONS[$contentType])) {
                    $warnings[] = "Skipped image with unsupported Content-Type [{$contentType}]: {$url}";
                    continue;
                }

                $body = $response->body();
                $size = strlen($body);

                if ($size === 0 || $size > $maxBytes) {
                    $warnings[] = "Skipped image exceeding max allowed size ({$size} bytes): {$url}";
                    continue;
                }

                $extension = self::MIME_EXTENSIONS[$contentType];
                $hash = substr(sha1($url), 0, 8);
                $index = count($savedPaths);

                $filename = $index === 0
                    ? "featured-{$hash}.{$extension}"
                    : "gallery-{$index}-{$hash}.{$extension}";

                $relativePath = "{$packageDir}/{$filename}";

                Storage::disk($disk)->put($relativePath, $body);
                $savedPaths[] = $relativePath;
            } catch (\Throwable $e) {
                Log::warning('External tour image download failed', [
                    'package_id' => $packageId,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
                $warnings[] = "Exception downloading image {$url}: {$e->getMessage()}";
            }
        }

        return [
            'featured_image' => $savedPaths[0] ?? null,
            'gallery_images' => array_slice($savedPaths, 1),
            'warnings' => $warnings,
        ];
    }

    /**
     * Filter out candidate URLs that contain blacklisted keywords, data URIs, or SVG files.
     *
     * @param array<string> $urls
     * @return array<string>
     */
    public function filterCandidateUrls(array $urls): array
    {
        $uniqueUrls = array_values(array_unique(array_filter($urls)));
        $filtered = [];

        foreach ($uniqueUrls as $url) {
            $lower = strtolower($url);

            if (str_starts_with($lower, 'data:')) {
                continue;
            }

            if (str_ends_with(parse_url($lower, PHP_URL_PATH) ?? '', '.svg')) {
                continue;
            }

            $matchedExclusion = false;
            foreach (self::EXCLUDED_KEYWORDS as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $matchedExclusion = true;
                    break;
                }
            }

            if ($matchedExclusion) {
                continue;
            }

            $filtered[] = $url;
        }

        return $filtered;
    }

    /**
     * Ensure remote URL uses HTTP/HTTPS and resolves to a routable, non-private IP.
     */
    protected function isSafeRemoteUrl(string $url): bool
    {
        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = $parsed['host'] ?? '';

        if (!in_array($scheme, ['http', 'https'], true) || empty($host)) {
            return false;
        }

        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Validate public, non-private and non-reserved IP
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
