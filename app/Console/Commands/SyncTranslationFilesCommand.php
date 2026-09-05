<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Services\JsonTranslationFileService;
use App\Support\LocaleNormalizer;
use Illuminate\Console\Command;

class SyncTranslationFilesCommand extends Command
{
    protected $signature = 'translations:sync {locales?* : Locale codes to synchronize} {--force : Regenerate existing values}';

    protected $description = 'Synchronize JSON translation files outside the web request lifecycle';

    public function handle(JsonTranslationFileService $files, LocaleNormalizer $normalizer): int
    {
        $locales = (array) $this->argument('locales');

        if ($locales === []) {
            try {
                $locales = Language::query()->where('is_active', true)->pluck('code')->all();
            } catch (\Throwable) {
                $locales = (array) config('translation.supported_locales', ['en', 'ar']);
            }
        }

        $locales = $normalizer->normalizeList($locales);
        $failed = [];

        foreach ($locales as $locale) {
            if ($files->ensureLocaleFile($locale, (bool) $this->option('force'))) {
                $this->info("Synchronized lang/{$locale}.json");
            } else {
                $failed[] = $locale;
                $this->error("Could not write lang/{$locale}.json; check ownership and permissions.");
            }
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
