<?php

namespace Tests\Unit;

use App\Models\City;
use App\Models\Itinerary;
use App\Models\Package;
use App\Models\PackageAttraction;
use App\Models\PackageHighlight;
use Database\Seeders\CleanPackageHighlightsSeeder;
use Illuminate\Support\Collection;
use ReflectionMethod;
use Tests\TestCase;

class CleanPackageHighlightsSeederTest extends TestCase
{
    public function test_it_builds_unique_package_specific_highlights_and_drops_legacy_copy(): void
    {
        $package = new Package();

        $attraction = new PackageAttraction([
            'title' => ['en' => 'The Great Pyramids of Giza'],
            'teaser' => ['en' => '<p>See the pyramids and Sphinx.</p>'],
        ]);

        $package->setRelation('packageAttractions', new Collection([$attraction]));
        $package->setRelation('itineraries', new Collection([
            new Itinerary([
                'title' => ['en' => 'Explore Old Cairo'],
                'description' => ['en' => '<p>Visit the Citadel and historic mosques.</p>'],
            ]),
            new Itinerary(['title' => ['en' => 'The Great Pyramids of Giza']]),
        ]));
        $package->setRelation('cities', new Collection([
            new City(['name' => ['en' => 'Cairo']]),
        ]));
        $package->setRelation('highlights', new Collection([
            new PackageHighlight([
                'title' => ['en' => 'Experience the mysteries and treasures of Egypt in 11 nights trip.'],
            ]),
        ]));

        $method = new ReflectionMethod(CleanPackageHighlightsSeeder::class, 'highlightsFor');
        $result = $method->invoke(new CleanPackageHighlightsSeeder(), $package);

        $this->assertSame([
            'The Great Pyramids of Giza',
            'Explore Old Cairo',
            'Cairo',
        ], $result->pluck('title.en')->all());
        $this->assertSame('See the pyramids and Sphinx.', $result->first()['description']['en']);
    }

    public function test_it_drops_accommodation_highlights_from_day_tours_only(): void
    {
        $dayTour = $this->packageWithLegacyHighlight('day_tour', 'Hotel in Cairo for 2 nights');
        $travelPackage = $this->packageWithLegacyHighlight('travel_package', 'Hotel in Cairo for 2 nights');

        $method = new ReflectionMethod(CleanPackageHighlightsSeeder::class, 'highlightsFor');
        $seeder = new CleanPackageHighlightsSeeder();

        $this->assertSame([], $method->invoke($seeder, $dayTour)->all());
        $this->assertSame(
            ['Hotel in Cairo for 2 nights'],
            $method->invoke($seeder, $travelPackage)->pluck('title.en')->all()
        );
    }

    private function packageWithLegacyHighlight(string $type, string $highlight): Package
    {
        $package = new Package(['package_type' => $type]);
        $package->setRelation('packageAttractions', new Collection());
        $package->setRelation('itineraries', new Collection());
        $package->setRelation('cities', new Collection());
        $package->setRelation('highlights', new Collection([
            new PackageHighlight(['title' => ['en' => $highlight]]),
        ]));

        return $package;
    }
}
