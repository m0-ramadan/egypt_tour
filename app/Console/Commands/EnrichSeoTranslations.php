<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Attraction;
use App\Models\City;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EnrichSeoTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:enrich-translations {--force : Overwrite existing non-empty SEO fields}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrich missing SEO titles and SEO descriptions across all active site languages (en, ar, fr, de)';

    protected array $activeLocales = ['en', 'ar', 'fr', 'de'];

    /**
     * Execute the console command.
     */
    public function handle(TranslationService $translationService): int
    {
        $this->info('Starting SEO Title & SEO Description enrichment...');

        $force = (bool) $this->option('force');

        $models = [
            'Packages' => Package::query()->where('is_active', true)->get(),
            'Package Categories' => PackageCategory::query()->where('is_active', true)->get(),
            'Cities' => City::query()->where('is_active', true)->get(),
            'Attractions' => Attraction::query()->where('is_active', true)->get(),
            'Articles' => Article::query()->where('is_active', true)->get(),
        ];

        foreach ($models as $name => $records) {
            $this->info("Processing {$name} (" . count($records) . " records)...");
            $updatedCount = 0;

            foreach ($records as $record) {
                $seoTitle = is_array($record->seo_title) ? $record->seo_title : [];
                $seoDescription = is_array($record->seo_description) ? $record->seo_description : [];

                $modified = false;

                foreach ($this->activeLocales as $locale) {
                    // 1. Process SEO Title
                    if ($force || empty($seoTitle[$locale])) {
                        $baseTitle = $this->getLocalizedField($record, ['title', 'name'], $locale);
                        if (blank($baseTitle)) {
                            $sourceTitle = $this->getAnyLocalizedField($record, ['title', 'name']);
                            if (!blank($sourceTitle)) {
                                $translations = $translationService->translateTextToAllLanguages($sourceTitle);
                                $baseTitle = $translations[$locale] ?? $sourceTitle;
                            }
                        }
                        if (!blank($baseTitle)) {
                            $seoTitle[$locale] = trim(Str::limit(strip_tags($baseTitle), 60));
                            $modified = true;
                        }
                    }

                    // 2. Process SEO Description
                    if ($force || empty($seoDescription[$locale])) {
                        $baseDesc = $this->getLocalizedField($record, ['short_description', 'subtitle', 'description'], $locale);
                        if (blank($baseDesc)) {
                            $sourceDesc = $this->getAnyLocalizedField($record, ['short_description', 'subtitle', 'description']);
                            if (!blank($sourceDesc)) {
                                $translations = $translationService->translateTextToAllLanguages($sourceDesc);
                                $baseDesc = $translations[$locale] ?? $sourceDesc;
                            }
                        }
                        if (!blank($baseDesc)) {
                            $seoDescription[$locale] = trim(Str::limit(preg_replace('/\s+/', ' ', strip_tags($baseDesc)), 160));
                            $modified = true;
                        }
                    }
                }

                if ($modified) {
                    $record->timestamps = false;
                    $record->update([
                        'seo_title' => $seoTitle,
                        'seo_description' => $seoDescription,
                    ]);
                    $updatedCount++;
                }
            }

            $this->info("Updated {$updatedCount} {$name}.");
        }

        Cache::flush();
        $this->info('SEO Title & Description enrichment completed successfully.');

        return Command::SUCCESS;
    }

    private function getLocalizedField($record, array $fields, string $locale): ?string
    {
        foreach ($fields as $field) {
            if (!isset($record->{$field})) {
                continue;
            }
            $val = $record->{$field};
            if (is_array($val) && !empty($val[$locale])) {
                return (string) $val[$locale];
            }
        }

        return null;
    }

    private function getAnyLocalizedField($record, array $fields): ?string
    {
        foreach ($fields as $field) {
            if (!isset($record->{$field})) {
                continue;
            }
            $val = $record->{$field};
            if (is_array($val)) {
                foreach (['en', 'ar', 'fr', 'de'] as $loc) {
                    if (!empty($val[$loc])) {
                        return (string) $val[$loc];
                    }
                }
            } elseif (is_string($val) && !blank($val)) {
                return $val;
            }
        }

        return null;
    }
}
