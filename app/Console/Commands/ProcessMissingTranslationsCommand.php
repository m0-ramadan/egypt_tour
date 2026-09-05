<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Services\Translation\AiTranslationService;
use App\Services\Translation\Schemas\PackageTranslationSchema;
use Illuminate\Console\Command;

class ProcessMissingTranslationsCommand extends Command
{
    protected $signature = 'translations:process-missing 
                            {--entity=package : Entity type to translate (default: package)}
                            {--id= : Specific entity ID}
                            {--language= : Target language code}
                            {--dry-run : Preview translation units without making API calls}';

    protected $description = 'Process missing translations using Gemini 2.5 Flash and DeepSeek fallback';

    public function handle(AiTranslationService $service, PackageTranslationSchema $schema): int
    {
        $entityType = $this->option('entity');
        $id = $this->option('id');
        $targetLangOption = $this->option('language');
        $isDryRun = (bool) $this->option('dry-run');

        if ($entityType !== 'package') {
            $this->error("Currently supported entity type is 'package'.");
            return 1;
        }

        $query = Package::query();
        if ($id) {
            $query->where('id', $id);
        }

        $packages = $query->get();

        if ($packages->isEmpty()) {
            $this->info("No packages found matching criteria.");
            return 0;
        }

        $activeLangs = $service->getActiveLanguages();
        $this->info("Active languages: " . implode(', ', $activeLangs));

        foreach ($packages as $package) {
            $this->line("--------------------------------------------------");
            $this->info("Processing Package #{$package->id}: " . ($package->title['en'] ?? $package->title['ar'] ?? 'Untitled'));

            $sourceLang = $service->detectSourceLanguage($package);
            $targetLangs = $targetLangOption ? [strtolower($targetLangOption)] : array_values(array_filter($activeLangs, fn($l) => strtolower($l) !== strtolower($sourceLang)));

            if ($isDryRun) {
                $units = $schema->extractUnits($package, $sourceLang, $targetLangs, true);
                $this->warn("[DRY RUN] Found " . count($units) . " missing translation units:");

                $tableData = array_map(function ($unit) {
                    return [
                        'Entity' => $unit->entityType,
                        'ID' => $unit->entityId,
                        'Field' => $unit->field,
                        'Source' => $unit->sourceLanguage,
                        'Target' => $unit->targetLanguage,
                        'Type' => $unit->structuredType,
                        'Chars' => mb_strlen($unit->sourceText),
                    ];
                }, $units);

                $this->table(['Entity', 'ID', 'Field', 'Source', 'Target', 'Type', 'Chars'], $tableData);
            } else {
                $this->info("Translating missing content...");
                $summary = $service->translatePackage($package, $sourceLang, true);

                $this->info("Completed. Total: {$summary['total_units']}, Success: {$summary['success_count']}, Fallback: {$summary['fallback_count']}, Cached: {$summary['cached_count']}, Failed: {$summary['failed_count']}");
            }
        }

        return 0;
    }
}
