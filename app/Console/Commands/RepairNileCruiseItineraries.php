<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Services\ExternalTours\ExternalTourImportService;
use Illuminate\Console\Command;

class RepairNileCruiseItineraries extends Command
{
    protected $signature = 'tour:repair-cruise-itineraries {--package= : Repair only this package ID}';

    protected $description = 'Reimport separate itinerary programs for previously imported Nile cruises without changing prices or images';

    public function handle(ExternalTourImportService $importer): int
    {
        $failures = 0;
        $packages = Package::where('package_type', 'nile_cruise')
            ->where('source_type', 'external_url')
            ->when($this->option('package'), fn ($query, $id) => $query->whereKey($id));

        foreach ($packages->lazyById() as $package) {
            // Older imports store the slug and URL hash, not the URL itself.
            $url = 'https://www.luxorandaswan.com/Egypt/cruise/' . $package->source_remote_slug;
            try {
                if (sha1(strtolower(rtrim($url, '/'))) !== $package->source_remote_id) {
                    throw new \RuntimeException('Cannot reconstruct the source URL; reimport its original URL with --update.');
                }
                $repaired = $importer->repairCruiseItinerary($package, $url);
                $this->info("Package {$package->id}: " . ($repaired ? 'itinerary programs repaired.' : 'no separate programs found; unchanged.'));
            } catch (\Throwable $e) {
                $failures++;
                $this->error("Package {$package->id}: {$e->getMessage()}");
            }
        }

        return $failures ? self::FAILURE : self::SUCCESS;
    }
}
