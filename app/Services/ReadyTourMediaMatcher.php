<?php

namespace App\Services;

use App\Models\Package;
use App\Models\SavvyMedia;
use App\Models\SavvyTourTemplate;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReadyTourMediaMatcher
{
    /**
     * Match best SavvyMedia records for a given tour template.
     * Scoring:
     * Exact city_slug                +50
     * Destination match             +40
     * Exact category                +30
     * sub_category                  +25
     * country/region                +20
     * Tag exact match               +15 each
     * Title keyword match           +10
     * Alt text keyword match        +5
     * is_global fallback             +1
     *
     * @param SavvyTourTemplate $template
     * @param int $limit Max gallery images to return
     * @return Collection
     */
    public function matchMediaForTemplate(SavvyTourTemplate $template, int $limit = 7): Collection
    {
        $allMedia = SavvyMedia::query()
            ->where('type', 'image')
            ->get();

        if ($allMedia->isEmpty()) {
            return collect();
        }

        $templateCities = array_map('strtolower', (array) ($template->cities ?? []));
        $templateDestinations = array_map('strtolower', (array) ($template->destinations ?? []));
        $templateCategory = strtolower((string) $template->remote_category);
        $templateRegion = strtolower((string) $template->region);
        $templateTags = array_map('strtolower', (array) ($template->tags ?? []));
        $templateTitle = strtolower($template->display_name);

        $scored = $allMedia->map(function (SavvyMedia $media) use (
            $templateCities,
            $templateDestinations,
            $templateCategory,
            $templateRegion,
            $templateTags,
            $templateTitle
        ) {
            $score = 0;

            // Prefer locally downloaded images
            if ($media->is_downloaded && $media->local_path) {
                $score += 100;
            }

            // City match
            if ($media->city_slug && in_array(strtolower($media->city_slug), $templateCities, true)) {
                $score += 50;
            }

            // Destination match
            if ($media->city_slug && in_array(strtolower($media->city_slug), $templateDestinations, true)) {
                $score += 40;
            }

            // Category match
            if ($media->category && strtolower($media->category) === $templateCategory) {
                $score += 30;
            }

            // Subcategory match
            if ($media->sub_category && strtolower($media->sub_category) === $templateCategory) {
                $score += 25;
            }

            // Country/region match
            if ($media->country_slug && (strtolower($media->country_slug) === $templateRegion || strtolower($media->country_slug) === 'egypt')) {
                $score += 20;
            }

            // Tag matches
            $mediaTags = array_map('strtolower', (array) ($media->tags ?? []));
            foreach ($templateTags as $tag) {
                if (in_array($tag, $mediaTags, true)) {
                    $score += 15;
                }
            }

            // Title match
            if ($media->title && str_contains($templateTitle, strtolower($media->title))) {
                $score += 10;
            }

            // Alt text match
            if ($media->alt_text && str_contains($templateTitle, strtolower($media->alt_text))) {
                $score += 5;
            }

            // Global fallback
            if ($media->is_global) {
                $score += 1;
            }

            return [
                'media' => $media,
                'score' => $score,
            ];
        });

        // Sort descending by score
        $sorted = $scored->sortByDesc('score')->map(fn ($item) => $item['media'])->values();

        return $sorted->take($limit);
    }

    /**
     * Copy matched media files into local package directory and assign featured & gallery images.
     * Rule #29: Copy from storage/app/public/savvy_media/ to storage/app/public/packages/imported/{package_id}/
     *
     * @param Package $package
     * @param Collection $matchedMedia
     * @return array ['featured_image' => string, 'gallery_images' => array]
     */
    public function copyAndAssignMedia(Package $package, Collection $matchedMedia): array
    {
        if ($matchedMedia->isEmpty()) {
            return [
                'featured_image' => null,
                'gallery_images' => [],
            ];
        }

        $packageDir = "packages/imported/{$package->id}";
        Storage::disk('public')->makeDirectory($packageDir);

        $featuredPath = null;
        $galleryPaths = [];

        foreach ($matchedMedia as $index => $media) {
            $sourcePath = $media->local_path;
            if (!$sourcePath || !Storage::disk('public')->exists($sourcePath)) {
                // If not downloaded locally yet, try fallback display URL download
                $sourcePath = $this->ensureLocalCopyOfMedia($media);
            }

            if (!$sourcePath || !Storage::disk('public')->exists($sourcePath)) {
                continue;
            }

            $ext = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'webp';
            $filename = ($index === 0) ? "featured.{$ext}" : "gallery-{$index}.{$ext}";
            $targetPath = "{$packageDir}/{$filename}";

            try {
                Storage::disk('public')->copy($sourcePath, $targetPath);
                $publicUrlPath = "storage/{$targetPath}";

                if ($index === 0) {
                    $featuredPath = $publicUrlPath;
                } else {
                    $galleryPaths[] = $publicUrlPath;
                }
            } catch (Exception $e) {
                Log::warning("Failed copying media UUID {$media->uuid} for Package #{$package->id}: " . $e->getMessage());
            }
        }

        // If gallery is empty but featured exists, duplicate featured for fallback gallery if needed
        if ($featuredPath && empty($galleryPaths)) {
            $galleryPaths[] = $featuredPath;
        }

        return [
            'featured_image' => $featuredPath,
            'gallery_images' => array_values(array_unique($galleryPaths)),
        ];
    }

    /**
     * Download media locally on demand if not present.
     */
    protected function ensureLocalCopyOfMedia(SavvyMedia $media): ?string
    {
        /** @var SavvyHostMediaService $savvyMediaService */
        $savvyMediaService = app(SavvyHostMediaService::class);
        $downloaded = $savvyMediaService->downloadMediaFiles($media);

        return $downloaded ? $media->local_path : null;
    }
}
