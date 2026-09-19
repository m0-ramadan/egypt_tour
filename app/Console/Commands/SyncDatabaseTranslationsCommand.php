<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Attraction;
use App\Models\City;
use App\Models\Itinerary;
use App\Models\NileCruiseCategory;
use App\Models\NileCruiseType;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageHighlight;
use App\Models\PackageInclusion;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncDatabaseTranslationsCommand extends Command
{
    protected $signature = 'translations:sync-all
                            {--target=all : Specific target entity (package, itinerary, attraction, city, category, article, page, all)}
                            {--force : Re-translate fields even if target language already exists}';

    protected $description = 'Synchronize and translate all database text fields into English, French, and German';

    protected array $targetLocales = ['en', 'fr', 'de'];

    public function handle(): int
    {
        $target = $this->option('target');
        $force = (bool) $this->option('force');

        $this->info("Starting full database translation sync into: en, fr, de...");

        if (in_array($target, ['all', 'package'], true)) {
            $this->translatePackages($force);
            $this->translateItineraries($force);
            $this->translatePackageHighlights($force);
            $this->translatePackageInclusions($force);
        }

        if (in_array($target, ['all', 'category'], true)) {
            $this->translateCategories($force);
            $this->translateNileCruiseCategories($force);
            $this->translateNileCruiseTypes($force);
        }

        if (in_array($target, ['all', 'attraction'], true)) {
            $this->translateAttractions($force);
            $this->translatePackageAttractions($force);
        }

        if (in_array($target, ['all', 'city'], true)) {
            $this->translateCities($force);
        }

        if (in_array($target, ['all', 'article'], true)) {
            $this->translateArticles($force);
            $this->translateArticleCategories($force);
        }

        if (in_array($target, ['all', 'page'], true)) {
            $this->translatePages($force);
        }

        $this->info("Database translation sync completed successfully!");
        return Command::SUCCESS;
    }

    protected function translatePackages(bool $force): void
    {
        $fields = [
            'title',
            'subtitle',
            'short_description',
            'description',
            'route_text',
            'location_summary',
            'destinations_text',
            'duration_text',
            'cancellation_policy',
            'terms_conditions',
            'pricing_information',
            'children_policy',
            'pickup_policy',
            'seo_title',
            'seo_description'
        ];

        $packages = Package::all();
        $this->info("Translating Packages (" . $packages->count() . ")...");

        foreach ($packages as $package) {
            $updated = false;

            foreach ($fields as $field) {
                if ($this->processFieldTranslations($package, $field, $force)) {
                    $updated = true;
                }
            }

            if ($updated) {
                $package->save();
            }
        }
    }

    protected function translateItineraries(bool $force): void
    {
        $fields = ['title', 'description'];
        $items = Itinerary::all();
        $this->info("Translating Itineraries (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translatePackageHighlights(bool $force): void
    {
        $items = PackageHighlight::all();
        $this->info("Translating PackageHighlights (" . $items->count() . ")...");

        foreach ($items as $item) {
            if ($this->processFieldTranslations($item, 'title', $force)) {
                $item->save();
            }
        }
    }

    protected function translatePackageInclusions(bool $force): void
    {
        $items = PackageInclusion::all();
        $this->info("Translating PackageInclusions (" . $items->count() . ")...");

        foreach ($items as $item) {
            if ($this->processFieldTranslations($item, 'title', $force)) {
                $item->save();
            }
        }
    }

    protected function translateCategories(bool $force): void
    {
        $fields = ['name', 'description'];
        $items = PackageCategory::all();
        $this->info("Translating PackageCategories (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translateNileCruiseCategories(bool $force): void
    {
        $fields = ['name', 'description'];
        $items = NileCruiseCategory::all();
        $this->info("Translating NileCruiseCategories (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translateNileCruiseTypes(bool $force): void
    {
        $fields = ['name', 'description'];
        $items = NileCruiseType::all();
        $this->info("Translating NileCruiseTypes (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translateAttractions(bool $force): void
    {
        $fields = ['name', 'short_description', 'description', 'seo_title', 'seo_description'];
        $items = Attraction::all();
        $this->info("Translating Attractions (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translatePackageAttractions(bool $force): void
    {
        $items = DB::table('package_attractions')->get();
        $this->info("Translating PackageAttractions (" . $items->count() . ")...");

        foreach ($items as $item) {
            $titleData = $this->ensureArray($item->title);
            $teaserData = $this->ensureArray($item->teaser);
            $changed = false;

            $titleData = $this->translateDataArray($titleData, $changed, $force);
            $teaserData = $this->translateDataArray($teaserData, $changed, $force);

            if ($changed) {
                DB::table('package_attractions')->where('id', $item->id)->update([
                    'title' => json_encode($titleData, JSON_UNESCAPED_UNICODE),
                    'teaser' => json_encode($teaserData, JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    protected function translateCities(bool $force): void
    {
        $fields = ['name', 'short_description', 'description', 'seo_title', 'seo_description'];
        $items = City::all();
        $this->info("Translating Cities (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translateArticles(bool $force): void
    {
        $fields = ['title', 'summary', 'content', 'excerpt', 'seo_title', 'seo_description'];
        $items = Article::all();
        $this->info("Translating Articles (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function translateArticleCategories(bool $force): void
    {
        $items = ArticleCategory::all();
        $this->info("Translating ArticleCategories (" . $items->count() . ")...");

        foreach ($items as $item) {
            if ($this->processFieldTranslations($item, 'name', $force)) {
                $item->save();
            }
        }
    }

    protected function translatePages(bool $force): void
    {
        $fields = ['title', 'body', 'seo_title', 'seo_description'];
        $items = Page::all();
        $this->info("Translating Pages (" . $items->count() . ")...");

        foreach ($items as $item) {
            $updated = false;
            foreach ($fields as $field) {
                if ($this->processFieldTranslations($item, $field, $force)) {
                    $updated = true;
                }
            }
            if ($updated) {
                $item->save();
            }
        }
    }

    protected function processFieldTranslations(object $model, string $field, bool $force): bool
    {
        $raw = $model->getRawOriginal($field) ?? $model->{$field} ?? null;
        $data = $this->ensureArray($raw);
        $changed = false;

        $data = $this->translateDataArray($data, $changed, $force);

        if ($changed) {
            $model->{$field} = $data;
        }

        return $changed;
    }

    protected function translateDataArray(array $data, bool &$changed, bool $force): array
    {
        $sourceText = $data['en'] ?? $data['ar'] ?? (count($data) ? reset($data) : null);
        if (empty($sourceText) || !is_string($sourceText)) {
            return $data;
        }

        foreach ($this->targetLocales as $loc) {
            $currentVal = $data[$loc] ?? null;
            // Translate if forcing, missing, or if value is identical to English source text (and target is not en)
            $needsTranslation = $force || empty($currentVal) || ($loc !== 'en' && $currentVal === $sourceText);

            if ($needsTranslation) {
                if ($loc === 'en' && !empty($data['en']) && !$force) {
                    continue;
                }
                $translated = $this->requestTranslation((string) $sourceText, $loc);
                if (!empty($translated)) {
                    $data[$loc] = $translated;
                    $changed = true;
                }
            }
        }

        return $data;
    }

    protected function ensureArray(mixed $val): array
    {
        if (is_array($val)) {
            return $val;
        }

        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            if (!empty(trim($val))) {
                return ['en' => trim($val)];
            }
        }

        return [];
    }

    protected function requestTranslation(string $text, string $targetLang): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // If HTML text, handle strip/clean or translate plain text parts
        try {
            $response = Http::timeout(10)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'auto',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $translated = '';
                if (isset($json[0]) && is_array($json[0])) {
                    foreach ($json[0] as $segment) {
                        $translated .= $segment[0] ?? '';
                    }
                }
                return trim($translated) ?: $text;
            }
        } catch (\Throwable $e) {
            // Log or fallback
        }

        return $text;
    }
}
