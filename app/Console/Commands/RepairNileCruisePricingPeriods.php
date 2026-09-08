<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Services\ExternalTours\ExternalTourImportService;
use Illuminate\Console\Command;

class RepairNileCruisePricingPeriods extends Command
{
    protected $signature = 'tour:repair-cruise-pricing-periods {--package= : Repair only this package ID}';

    protected $description = 'Restore missing source season labels for imported Nile cruise prices';

    public function handle(ExternalTourImportService $importer): int
    {
        $failures = 0;
        $packages = Package::where('package_type', 'nile_cruise')->where('source_type', 'external_url')
            ->whereHas('tourPackageAccommodations.seasons', fn ($q) => $q->whereNull('period'))
            ->when($this->option('package'), fn ($q, $id) => $q->whereKey($id));
        foreach ($packages->lazyById() as $package) {
            $url = 'https://www.luxorandaswan.com/Egypt/cruise/' . $package->source_remote_slug;
            if (sha1(strtolower($url)) !== $package->source_remote_id) {
                $this->line("Package {$package->id}: skipped (different source path).");
                continue;
            }
            try {
                $count = $importer->repairCruisePricingPeriods($package, $url);
                $this->info("Package {$package->id}: {$count} season labels restored.");
            } catch (\Throwable $e) {
                $failures++;
                $this->error("Package {$package->id}: {$e->getMessage()}");
            }
        }

        return $failures ? self::FAILURE : self::SUCCESS;
    }
}
