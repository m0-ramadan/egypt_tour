<?php

namespace App\Jobs;

use App\Models\Package;
use App\Services\Translation\AiTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateContentUnitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 10;

    public function __construct(
        public int $packageId,
        public ?string $sourceLang = null,
        public bool $missingOnly = true
    ) {}

    public function handle(AiTranslationService $translationService): void
    {
        $package = Package::find($this->packageId);

        if (!$package) {
            Log::warning("TranslateContentUnitJob: Package #{$this->packageId} not found.");
            return;
        }

        Log::info("Processing background translation job for Package #{$package->id}");
        $summary = $translationService->translatePackage($package, $this->sourceLang, $this->missingOnly);
        Log::info("Finished background translation for Package #{$package->id}: " . json_encode($summary));
    }
}
