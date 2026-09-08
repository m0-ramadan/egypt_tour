<?php

namespace Tests\Feature;

use App\Models\Attraction;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\TourPackageAccommodation;
use App\Models\TourPackagePriceItem;
use App\Models\TourPackageSeason;
use App\Services\DeepSeekService;
use App\Services\ExternalTours\ExternalTourContentRewriter;
use App\Services\ExternalTours\ExternalTourImageDownloader;
use App\Services\ExternalTours\ExternalTourImportService;
use App\Services\ExternalTours\LuxorAndAswanTourPageParser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class ExternalTourImportTest extends TestCase
{
    use DatabaseTransactions;

    protected string $sampleUrl = 'https://www.luxorandaswan.com/Egypt/package/7-Day-Cairo-Alexandria-and-Nile-Cruise-Tour-Package-by-Flight';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Setup base country, currency and test cities if not already in DB
        $country = Country::firstOrCreate(
            ['code' => 'EG'],
            ['name' => ['en' => 'Egypt', 'ar' => 'مصر'], 'slug' => 'egypt', 'is_active' => true]
        );

        Currency::firstOrCreate(
            ['code' => 'USD'],
            ['name' => ['en' => 'US Dollar', 'ar' => 'دولار أمريكي'], 'symbol' => '$', 'is_default' => true, 'is_active' => true]
        );

        foreach (['Cairo', 'Aswan', 'Luxor', 'Alexandria'] as $cityName) {
            City::firstOrCreate(
                ['slug' => strtolower($cityName)],
                ['country_id' => $country->id, 'name' => ['en' => $cityName, 'ar' => ''], 'is_active' => true]
            );
        }

        PackageCategory::firstOrCreate(
            ['slug' => 'tour-packages'],
            ['name' => ['en' => 'Tour Packages', 'ar' => 'باقات سياحية'], 'category_type' => 'travel_package', 'is_active' => true]
        );

        PackageCategory::firstOrCreate(
            ['slug' => 'nile cruise'],
            ['name' => ['en' => 'Nile trip', 'ar' => 'رحلة نيلية'], 'category_type' => 'nile_cruise', 'is_active' => true]
        );
    }

    /**
     * Comprehensive HTML fixture matching the Luxor and Aswan tour page structure.
     */
    protected function getSampleTourHtml(int $marketingCruiseNights = 3): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight - Luxor and Aswan Travel</title>
    <meta name="description" content="Discover the beauty of Cairo, Alexandria and a magnificent Nile Cruise with flights included.">
    <meta property="og:title" content="7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight">
    <meta property="og:description" content="Discover the beauty of Cairo, Alexandria and a magnificent Nile Cruise with flights included.">
    <meta property="og:image" content="https://www.luxorandaswan.com/images/cairo-pyramids-main.jpg">
</head>
<body>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/Egypt">Egypt</a></li>
            <li class="breadcrumb-item"><a href="/Egypt/packages">Best Egypt Vacation, Tours &amp; Travel Packages</a></li>
            <li class="breadcrumb-item"><a href="/Egypt/packages/7-days">7 Days Egypt Tour Packages</a></li>
            <li class="breadcrumb-item active">7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight</li>
        </ol>
    </nav>
    <section id="home" class="hero-section gx-lazy-bg" data-bg="https://www.luxorandaswan.com/images/hero-nile-cruise.jpg">
        <h1 class="tour-title">7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight</h1>
    </section>

    <section id="about" class="content-section">
        <h2 class="section-header">About 7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight</h2>
        <p class="section-subtitle">Discover the wonders of Egypt with our expertly crafted tour package.</p>
        <div class="about-content">
            <p>Live a beautiful adventure by exploring our Cairo, Nile Cruise, and Alexandria Package by Flight.</p>
            <div class="cruise-details">
                <div class="detail-item"><strong>Duration:</strong> 7 Days / 6 Nights</div>
                <div class="detail-item"><strong>Schedule:</strong> Every Day</div>
                <div class="detail-item"><strong>Destinations:</strong> Cairo / Aswan / Luxor / Alexandria</div>
                <div class="detail-item"><strong>Pickup Location:</strong> Cairo Airport or Hotel in Cairo</div>
                <div class="detail-item"><strong>Tour Type:</strong> Private</div>
            </div>
        </div>

        <div class="facilities-section">
            <h3 class="facilities-title">Why You'll Love This Trip</h3>
            <div class="styled-includes">
                <ul>
                    <li>Experience the mysteries of Egypt in 6 nights trip.</li>
                    <li>Embark on a leisurely paced {$marketingCruiseNights}-night cruise on the Nile.</li>
                    <li>Visit the Pyramids of Giza, Sphinx, and the Grand Egyptian Museum.</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="attractions">
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Giza Pyramids</h4>
            <img src="https://www.luxorandaswan.com/images/giza-pyramids.jpg" alt="Pyramids">
        </div>
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Grand Egyptian Museum</h4>
            <img src="https://www.luxorandaswan.com/images/gem.jpg" alt="GEM">
        </div>
        <!-- Ignore logos and social widgets -->
        <img src="https://www.luxorandaswan.com/images/tripadvisor-badge.png" alt="Tripadvisor">
        <img src="https://www.luxorandaswan.com/images/site-logo.png" alt="Logo">
    </section>

    <section id="itinerary" class="content-section">
        <div class="itinerary-section">
            <div class="day-card">
                <div class="day-header">
                    <div class="day-number">1</div>
                    <h3 class="day-title">Day 01: Arrival Cairo - Welcome to Egypt</h3>
                </div>
                <div class="day-content">
                    <p>Arrive at Cairo International Airport. Our representative will meet and assist you. Transfer to your hotel in Cairo. Overnight in Cairo.</p>
                    <div class="meals-included">
                        <div class="meals-list"><span class="meal-tag">No meals included</span></div>
                    </div>
                </div>
            </div>

            <div class="day-card">
                <div class="day-header">
                    <div class="day-number">2</div>
                    <h3 class="day-title">Day 02: Pyramids, Saqqara & Grand Egyptian Museum</h3>
                </div>
                <div class="day-content">
                    <p>Breakfast at hotel. Visit Giza Pyramids, the Great Sphinx and proceed to Saqqara. Later visit the Grand Egyptian Museum. Lunch included. Overnight in Cairo.</p>
                    <div class="meals-included">
                        <div class="meals-list"><span class="meal-tag">Breakfast</span> <span class="meal-tag">Lunch</span></div>
                    </div>
                </div>
            </div>

            <div class="day-card">
                <div class="day-header">
                    <div class="day-number">3</div>
                    <h3 class="day-title">Day 03: Flight to Aswan & Nile Cruise Embarkation</h3>
                </div>
                <div class="day-content">
                    <p>Breakfast in Cairo, transfer to airport to fly to Aswan. Embark on Nile Cruise. Visit Philae Temple and High Dam. Lunch and Dinner on board. Overnight in Aswan on Nile Cruise.</p>
                    <div class="meals-included">
                        <div class="meals-list"><span class="meal-tag">Breakfast</span> <span class="meal-tag">Lunch</span> <span class="meal-tag">Dinner</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="styled-includes">
            <ul>
                <li>Hotel in Cairo for 3 nights</li>
                <li>5* Nile Cruise for 3 nights</li>
                <li>Domestic flight tickets from Cairo to Aswan and Luxor to Cairo</li>
                <li>Private English speaking Egyptologist guide</li>
            </ul>
        </div>
        <div class="styled-excludes">
            <ul>
                <li>International airfare</li>
                <li>Egypt entry visa</li>
                <li>Tips and personal expenses</li>
            </ul>
        </div>
    </section>

    <section class="pricing-section-wrapper">
        <h3 class="section-header">Standard Accommodations</h3>
        <div class="pricing-section">
            <div class="pricing-card">
                <div class="pricing-header">
                    <div class="pricing-duration">May to August</div>
                    <div class="pricing-from">From: $1521</div>
                </div>
                <div class="room-pricing">
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Triple Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$1521</span></div>
                    </div>
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Double Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$1633</span></div>
                    </div>
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Single Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$2259</span></div>
                    </div>
                </div>
                <div class="tab-pane active" id="hotels-standard">
                    <div class="accommodation-item">
                        <span class="location">Cairo</span>
                        <p class="hotel-list">Steigenberger El Tahrir / Cairo Pyramids or similar</p>
                    </div>
                </div>
                <div class="tab-pane" id="cruises-standard">
                    <div class="accommodation-item cruise-item">
                        <h4 class="accommodation-name">MS Princess Sara</h4>
                    </div>
                </div>
            </div>

            <div class="pricing-card">
                <div class="pricing-header">
                    <div class="pricing-duration">September to April</div>
                    <div class="pricing-from">From: $1956</div>
                </div>
                <div class="room-pricing">
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Triple Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$1956</span></div>
                    </div>
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Double Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$2068</span></div>
                    </div>
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Single Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$2875</span></div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="section-header">Luxury Accommodations</h3>
        <div class="pricing-section">
            <div class="pricing-card">
                <div class="pricing-header">
                    <div class="pricing-duration">May to August</div>
                    <div class="pricing-from">From: $3800</div>
                </div>
                <div class="room-pricing">
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Double Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$3800</span></div>
                    </div>
                    <div class="room-price-row">
                        <div class="room-type">Per Person in Single Room</div>
                        <div class="room-price"><span class="currency">USD</span><span class="price">$6400</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="policies-section">
        <div class="policy-card">
            <h4 class="policy-title">Pricing Information</h4>
            <p>Prices are quoted in US Dollars per person except during peak holiday seasons.</p>
        </div>
        <div class="policy-card">
            <h4 class="policy-title">Children's Policy</h4>
            <p>0 - 1.99 years: Free of charge. 2 - 5.99 years: Pay 25% of tour price. 6 - 11.99 years: Pay 50%.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Fake HTTP requests for tour HTML and remote images.
     */
    protected function fakeHttpResponses(string $html): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());

        $dummyImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        Http::fake([
            'https://www.luxorandaswan.com/images/*' => Http::response($dummyImage, 200, ['Content-Type' => 'image/png']),
            'https://www.luxorandaswan.com/*' => Http::response($html, 200, ['Content-Type' => 'text/html']),
            'https://example.org/*' => Http::response('OK', 200),
            'http://127.0.0.1/*' => Http::response('Localhost', 200),
            'http://localhost/*' => Http::response('Localhost', 200),
        ]);
    }

    public function test_imports_tour_successfully_and_detects_correct_package_types(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, [
            'rewrite' => false,
            'download_images' => false,
        ]);

        $package = $result['package']->load('category');

        $this->assertInstanceOf(Package::class, $package);
        // Multi-city vacation with Nile cruise component is travel_package
        $this->assertSame('travel_package', $package->package_type);
        $this->assertSame('travel_package', $package->category?->category_type);
        $this->assertStringContainsString('Cairo', $package->getTranslation('title', 'en'));

        // Test the 3 allowed Nile Cruise categories:
        // 1. Lake Nasser Cruise
        $lakeNasserHtml = str_replace(
            ['7 Days Egypt Tour Packages', '7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight'],
            ['Lake Nasser Cruise', 'Movenpick Prince Abbas Lake Cruise'],
            $this->getSampleTourHtml(3)
        );
        $lakeNasserUrl = 'https://www.luxorandaswan.com/Egypt/cruise/Movenpick-Prince-Abbas-Lake-Cruise-';
        $this->fakeHttpResponses($lakeNasserHtml);
        $lakeResult = $service->import($lakeNasserUrl, ['rewrite' => false, 'download_images' => false]);
        $this->assertSame('nile_cruise', $lakeResult['package']->package_type);
        $this->assertSame('nile_cruise', $lakeResult['package']->category?->category_type);

        // 2. Dahabiya Nile Cruise
        $dahabiyaHtml = str_replace(
            ['7 Days Egypt Tour Packages', '7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight'],
            ['Dahabiya Nile Cruise', 'Princess Farida Luxury Dahabiya Nile Cruise'],
            $this->getSampleTourHtml(3)
        );
        $dahabiyaUrl = 'https://www.luxorandaswan.com/Egypt/cruise/princess-farida-luxury-dahabiya-nile-cruise';
        $this->fakeHttpResponses($dahabiyaHtml);
        $dahabiyaResult = $service->import($dahabiyaUrl, ['rewrite' => false, 'download_images' => false]);
        $this->assertSame('nile_cruise', $dahabiyaResult['package']->package_type);
        $this->assertSame('nile_cruise', $dahabiyaResult['package']->category?->category_type);

        // 3. Luxor and Aswan Nile Cruises
        $luxorAswanHtml = str_replace(
            ['7 Days Egypt Tour Packages', '7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight'],
            ['Luxor and Aswan Nile Cruises', 'MS Mayfair Nile Cruise'],
            $this->getSampleTourHtml(3)
        );
        $luxorAswanUrl = 'https://www.luxorandaswan.com/Egypt/cruise/MS-Mayfair-Nile-Cruise';
        $this->fakeHttpResponses($luxorAswanHtml);
        $luxorResult = $service->import($luxorAswanUrl, ['rewrite' => false, 'download_images' => false]);
        $this->assertSame('nile_cruise', $luxorResult['package']->package_type);
        $this->assertSame('nile_cruise', $luxorResult['package']->category?->category_type);
    }

    protected function getCruiseProgramsHtml(): string
    {
        $html = '<html><body><h1>Test Nile Cruise</h1><section id="itinerary"><div class="itinerary-section">';
        foreach ([4 => 'Wednesday', 5 => 'Saturday'] as $count => $departure) {
            $nights = $count - 1;
            $html .= "<h3 class=\"itinerary-program-title\">0{$count} Days / 0{$nights} Nights Program (Every {$departure})</h3>";
            for ($day = 1; $day <= $count; $day++) {
                $html .= "<div class=\"day-card\"><span class=\"day-number\">{$day}</span>"
                    . "<h3 class=\"day-title\">Day {$day}: {$departure} stop {$day}</h3>"
                    . "<div class=\"day-content\"><p>Explore Luxor on {$departure}.</p>"
                    . '<div class="meals-list">Breakfast Lunch</div></div></div>';
            }
        }

        return $html . '</div></section></body></html>';
    }

    public function test_cruise_programs_are_separate_and_updates_preserve_duration_prices(): void
    {
        $this->fakeHttpResponses(str_replace("04 Days / 03 Nights", "04 Days - 03 Nights", $this->getCruiseProgramsHtml()));
        $url = 'https://www.luxorandaswan.com/Egypt/cruise/test-programs';
        $service = app(ExternalTourImportService::class);
        $options = ['rewrite' => false, 'download_images' => false];
        $package = $service->import($url, $options)['package'];
        $durations = $package->nileCruiseDurations()->get();
        $this->assertCount(2, $durations);
        $this->assertSame(4, $package->duration_days);
        $this->assertSame(0, $package->itineraries()->count());
        foreach ($durations as $duration) {
            $this->assertSame(range(1, $duration->days), $duration->itineraryDays->pluck('day_number')->all());
            $this->assertSame(['breakfast', 'lunch'], $duration->itineraryDays->first()->meals);
        }
        $durations[0]->update(['start_from_price' => 1234]);
        $service->import($url, $options + ['update' => true]);
        $this->assertSame($durations->pluck('id')->all(), $package->nileCruiseDurations()->pluck('id')->all());
        $this->assertEquals(1234, $durations[0]->fresh()->start_from_price);
        $this->assertSame(4, $durations[0]->itineraryDays()->count());

        $view = view('website.pages.packages.partials.nile_cruise.itinerary', [
            'package' => $package->fresh(), 'title' => 'Test Nile Cruise',
        ])->render();
        $this->assertStringContainsString('Every Wednesday', $view);
        $this->assertStringContainsString('Every Saturday', $view);
        $this->assertStringNotContainsString('Day 6', $view);

        // Repair an old flattened import without touching commercial fields.
        $package->nileCruiseDurations()->delete();
        $package->itineraries()->create(['day_number' => 9, 'title' => ['en' => 'Flattened day'], 'sort_order' => 9]);
        $before = $package->fresh()->getAttributes();
        $this->artisan('tour:repair-cruise-itineraries', ['--package' => $package->id])->assertSuccessful();
        $this->assertSame($before, $package->fresh()->getAttributes());
        $this->assertSame(0, $package->itineraries()->count());
        $this->assertSame(2, $package->nileCruiseDurations()->count());

        // A partial source response must not remove existing days.
        $this->fakeHttpResponses(str_replace('<span class="day-number">5</span>', '<span class="day-number">6</span>', $this->getCruiseProgramsHtml()));
        $this->artisan('tour:repair-cruise-itineraries', ['--package' => $package->id])->assertFailed();
        $this->assertSame(5, $package->nileCruiseDurations()->where('days', 5)->first()->itineraryDays()->count());
    }

    public function test_same_length_cruise_programs_keep_both_directions(): void
    {
        $html = str_replace(
            ['04 Days / 03 Nights Program (Every Wednesday)', '05 Days / 04 Nights Program (Every Saturday)'],
            ['THREE-NIGHT CRUISE - ASWAN TO LUXOR', '4 Days - Luxor / Aswan'],
            $this->getCruiseProgramsHtml()
        );
        $html = preg_replace('/<div class="day-card"><span class="day-number">5<\/span>.*?<\/div><\/div><\/div>/s', '', $html);
        $this->fakeHttpResponses($html);
        $package = app(ExternalTourImportService::class)->import(
            'https://www.luxorandaswan.com/Egypt/cruise/two-directions',
            ['rewrite' => false, 'download_images' => false]
        )['package'];
        $this->assertSame(2, $package->nileCruiseDurations()->count());
        $this->assertSame([4, 4], $package->nileCruiseDurations->pluck('days')->all());
        $this->assertSame([3, 3], $package->nileCruiseDurations->pluck('nights')->all());
        $this->assertSame('Wednesday stop 1', $package->nileCruiseDurations[0]->itineraryDays[0]->display_title);
        $this->assertSame('Saturday stop 1', $package->nileCruiseDurations[1]->itineraryDays[0]->display_title);
    }

    public function test_cruise_pricing_preserves_periods_and_renders_separate_cabin_cards(): void
    {
        $cards = '';
        foreach (['May to August' => 640, 'September to April' => 780] as $period => $price) {
            $cards .= '<div class="pricing-card"><div class="pricing-duration">3 Nights 4 Days</div>'
                . "<div class=\"pricing-season\">{$period}</div>"
                . '<div class="room-price-row"><div class="room-type">Triple Cabin</div>'
                . "<span class=\"price\">{$price}</span></div></div>";
        }
        $html = str_replace('</body>', $cards . '</body>', $this->getCruiseProgramsHtml());
        $this->fakeHttpResponses($html);
        $url = 'https://www.luxorandaswan.com/Egypt/cruise/seasonal-pricing';
        $service = app(ExternalTourImportService::class);
        $package = $service->import($url, ['rewrite' => false, 'download_images' => false])['package'];
        $seasons = $package->tourPackageAccommodations->first()->seasons;
        $this->assertSame(['May to August', 'September to April'], $seasons->pluck('period')->all());
        $rendered = view('website.pages.packages.partials.nile_cruise.imported_pricing', [
            'package' => $package, 'currencySymbol' => '$',
        ])->render();
        $this->assertSame(2, substr_count($rendered, '<details class="nc-fare-card"'));
        $this->assertStringContainsString('May to August', $rendered);
        $this->assertStringContainsString('September to April', $rendered);
        $this->assertStringContainsString('Triple Cabin', $rendered);
        $this->assertStringContainsString('From: $640', $rendered);
        $this->assertStringContainsString('From: $780', $rendered);
        $this->assertStringNotContainsString('<table', $rendered);

        foreach ($seasons as $season) $season->update(['period' => null]);
        $seasons[1]->items->first()->update(['price' => 999]);
        $this->assertSame(1, $service->repairCruisePricingPeriods($package->fresh(), $url));
        $this->assertSame('May to August', $seasons[0]->fresh()->period);
        $this->assertNull($seasons[1]->fresh()->period);
        $this->assertEquals(999, $seasons[1]->items->first()->fresh()->price);
    }

    public function test_cruise_rewrite_matches_program_and_day_together(): void
    {
        $data = app(LuxorAndAswanTourPageParser::class)->parse($this->getCruiseProgramsHtml(), 'https://www.luxorandaswan.com/Egypt/cruise/test-programs');
        config(['services.deepseek.api_key' => 'test']);
        $ai = $this->createMock(DeepSeekService::class);
        $ai->method('askJson')->willReturn(['itinerary' => [
            ['program_id' => 1, 'day_number' => 1, 'title' => 'Aswan embarkation'],
            ['program_id' => 2, 'day_number' => 1, 'title' => 'Luxor embarkation'],
            ['day_number' => 2, 'title' => 'Ambiguous day must be ignored'],
        ]]);
        $result = (new ExternalTourContentRewriter($ai))->rewrite($data)['data'];
        $this->assertSame('Aswan embarkation', $result['itinerary'][0]['title']);
        $this->assertSame('Luxor embarkation', $result['itinerary'][4]['title']);
        $this->assertSame($data['itinerary'][1], $result['itinerary'][1]);
        $this->assertSame($data['itinerary'][5], $result['itinerary'][5]);
        $this->assertSame($data['itinerary'][4]['program'], $result['itinerary'][4]['program']);
    }

    public function test_cruise_import_assigns_type_and_category_and_repairs_on_update(): void
    {
        $type = \App\Models\NileCruiseType::firstOrCreate(
            ['slug' => 'luxor-aswan-nile-cruises'],
            ['name' => ['en' => 'Luxor and Aswan Nile Cruises'], 'is_active' => true]
        );
        $category = \App\Models\NileCruiseCategory::firstOrCreate(
            ['nile_cruise_type_id' => $type->id, 'slug' => 'standard-nile-cruises'],
            ['name' => ['en' => 'Standard Nile Cruises'], 'is_active' => true]
        );
        $html = str_replace(
            ['7 Days Egypt Tour Packages', '7 Day Cairo, Alexandria and Nile Cruise Tour Package by Flight'],
            ['Luxor and Aswan Nile Cruises</a></li><li>Standard Nile Cruises', 'Test Nile Cruise'],
            $this->getSampleTourHtml()
        );
        $this->fakeHttpResponses($html);
        $url = 'https://www.luxorandaswan.com/Egypt/cruise/test-nile-cruise';
        $service = app(ExternalTourImportService::class);
        $options = ['rewrite' => false, 'download_images' => false];
        $package = $service->import($url, $options)['package'];
        $this->assertEquals($type->id, $package->nile_cruise_type_id);
        $this->assertEquals($category->id, $package->nile_cruise_category_id);

        $package->update(['nile_cruise_type_id' => null, 'nile_cruise_category_id' => null]);
        $updated = $service->import($url, $options + ['update' => true])['package'];
        $this->assertSame($package->id, $updated->id);
        $this->assertEquals($type->id, $updated->nile_cruise_type_id);
        $this->assertEquals($category->id, $updated->nile_cruise_category_id);

        $view = app(\App\Http\Controllers\Website\NileCruiseController::class)
            ->showLuxorAswanCategory(\Illuminate\Http\Request::create('/'), $category->slug);
        $this->assertContains($package->id, $view->getData()['paginated']->pluck('id')->all());
    }

    public function test_cruise_taxonomy_uses_breadcrumb_tier_not_marketing_description(): void
    {
        $resolver = app(\App\Services\ExternalTours\ExternalNileCruiseTaxonomyResolver::class);
        $type = \App\Models\NileCruiseType::firstOrCreate(
            ['slug' => 'luxor-aswan-nile-cruises'],
            ['name' => ['en' => 'Luxor and Aswan Nile Cruises'], 'is_active' => true]
        );
        foreach (['standard', 'deluxe', 'ultra-deluxe', 'luxury'] as $tier) {
            $category = \App\Models\NileCruiseCategory::firstOrCreate(
                ['nile_cruise_type_id' => $type->id, 'slug' => $tier . '-nile-cruises'],
                ['name' => ['en' => $tier], 'is_active' => true]
            );
            $facts = [
                'package_type' => 'nile_cruise',
                'title' => 'Luxury Nile Cruise',
                'breadcrumbs' => ['Luxor and Aswan Nile Cruises', str_replace('-', ' ', $tier) . ' Nile Cruises']
            ];
            $resolved = $resolver->resolve($facts);
            $this->assertEquals($category->id, $resolved['nile_cruise_category_id']);
        }
        $unknown = $resolver->resolve([
            'package_type' => 'nile_cruise',
            'breadcrumbs' => ['Luxor and Aswan Nile Cruises'],
            'description' => 'Luxury cruise with standard cabins'
        ]);
        $this->assertNull($unknown['nile_cruise_category_id']);
        $this->assertNotEmpty($unknown['warnings']);
        $land = $resolver->resolve(['package_type' => 'travel_package', 'title' => 'Luxury Dahabiya and Cairo']);
        $this->assertNull($land['nile_cruise_type_id']);

        foreach (['dahabiya-nile-cruise' => 'Dahabiya Nile Cruise', 'lake-nasser-cruise' => 'Lake Nasser Cruise'] as $slug => $label) {
            $otherType = \App\Models\NileCruiseType::firstOrCreate(
                ['slug' => $slug],
                ['name' => ['en' => $label], 'is_active' => true]
            );
            $resolved = $resolver->resolve(['package_type' => 'nile_cruise', 'breadcrumbs' => [$label]]);
            $this->assertEquals($otherType->id, $resolved['nile_cruise_type_id']);
            $this->assertNull($resolved['nile_cruise_category_id']);
        }
    }

    public function test_parser_handles_missing_open_graph_metadata(): void
    {
        $html = preg_replace('/<meta property="og:[^>]+>/', '', $this->getSampleTourHtml());
        $parsed = app(LuxorAndAswanTourPageParser::class)->parse($html, $this->sampleUrl);
        $this->assertStringContainsString('Cairo', $parsed['title']);
        $this->assertNotEmpty($parsed['images']);
    }

    public function test_extracts_and_persists_correct_duration(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package'];

        $this->assertSame(7, $package->duration_days);
        $this->assertSame(6, $package->duration_nights);
        $this->assertSame('7 Days / 6 Nights', $package->duration_text);
    }

    public function test_extracts_and_associates_cities_with_pivot(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package']->load('cities');

        $cityNames = $package->cities->map(fn($c) => $c->getTranslation('name', 'en'))->toArray();

        $this->assertContains('Cairo', $cityNames);
        $this->assertContains('Aswan', $cityNames);
        $this->assertContains('Luxor', $cityNames);
        $this->assertContains('Alexandria', $cityNames);

        $cairo = $package->cities->firstWhere(fn($c) => $c->getTranslation('name', 'en') === 'Cairo');
        $this->assertNotNull($cairo);
        $this->assertTrue((bool) $cairo->pivot->is_primary);
        $this->assertSame(1, (int) $cairo->pivot->stop_order);
    }

    public function test_creates_daily_itinerary_with_meals_and_facts(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package']->load('itineraries');

        $this->assertCount(3, $package->itineraries);

        $day1 = $package->itineraries->firstWhere('day_number', 1);
        $this->assertNotNull($day1);
        $this->assertFalse((bool) $day1->meals_breakfast);

        $day2 = $package->itineraries->firstWhere('day_number', 2);
        $this->assertNotNull($day2);
        $this->assertTrue((bool) $day2->meals_breakfast);
        $this->assertTrue((bool) $day2->meals_lunch);

        $day3 = $package->itineraries->firstWhere('day_number', 3);
        $this->assertNotNull($day3);
        $this->assertTrue((bool) $day3->meals_breakfast);
        $this->assertTrue((bool) $day3->meals_lunch);
        $this->assertTrue((bool) $day3->meals_dinner);
    }

    public function test_creates_inclusions_and_exclusions_separately(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package']->load('inclusions');

        $included = $package->inclusions->where('type', 'included');
        $excluded = $package->inclusions->where('type', 'excluded');

        $this->assertGreaterThanOrEqual(1, $included->count());
        $this->assertGreaterThanOrEqual(1, $excluded->count());
    }

    public function test_calculates_min_max_prices_and_creates_accommodation_pricing_tree(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package'];

        $this->assertEquals(1521.00, (float) $package->start_from_price);
        $this->assertEquals(1521.00, (float) $package->price_from);
        $this->assertEquals(6400.00, (float) $package->price_to);
        $this->assertNull($package->compare_price);

        $accommodations = TourPackageAccommodation::where('package_id', $package->id)->get();
        $this->assertGreaterThanOrEqual(2, $accommodations->count());

        $standard = $accommodations->firstWhere('name', 'Standard');
        $this->assertNotNull($standard);

        $seasons = TourPackageSeason::where('accommodation_id', $standard->id)->get();
        $this->assertGreaterThanOrEqual(2, $seasons->count());

        $firstSeason = $seasons->first();
        $items = TourPackagePriceItem::where('season_id', $firstSeason->id)->get();
        $this->assertGreaterThanOrEqual(3, $items->count());
    }

    public function test_prevents_duplicate_imports_for_same_url(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);

        $first = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $second = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);

        $this->assertSame($first['package']->id, $second['package']->id);

        $count = Package::where('source_type', 'external_url')
            ->where('source_remote_id', sha1(strtolower(rtrim($this->sampleUrl, '/'))))
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_updates_existing_package_in_update_mode(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);

        $first = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $firstId = $first['package']->id;

        // Second import in update mode
        $updatedHtml = str_replace(
            'Live a beautiful adventure by exploring our Cairo',
            'UPDATED DESCRIPTION CONTENT FOR SECOND RUN',
            $this->getSampleTourHtml(3)
        );

        $this->fakeHttpResponses($updatedHtml);

        $second = $service->import($this->sampleUrl, [
            'rewrite' => false,
            'download_images' => false,
            'update' => true,
        ]);

        $this->assertSame($firstId, $second['package']->id);
        $this->assertTrue($second['is_update']);
        $this->assertStringContainsString('UPDATED DESCRIPTION CONTENT', $second['package']->getTranslation('description', 'en'));
    }

    public function test_ssrf_and_disallowed_host_protection(): void
    {
        $service = app(ExternalTourImportService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->import('https://example.org/tour');
    }

    public function test_ssrf_local_ip_protection(): void
    {
        $service = app(ExternalTourImportService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->import('http://127.0.0.1/test');
    }

    public function test_skips_image_downloads_when_disabled(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, [
            'rewrite' => false,
            'download_images' => false,
        ]);

        $package = $result['package'];
        $this->assertNull($package->featured_image);
        $this->assertEmpty($package->gallery_images ?? []);
    }

    public function test_handles_source_conflict_gracefully_with_warning(): void
    {
        // Marketing says 4 nights cruise, itinerary has 3 nights
        $conflictHtml = $this->getSampleTourHtml(4);
        $this->fakeHttpResponses($conflictHtml);

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);

        $this->assertInstanceOf(Package::class, $result['package']);

        $hasConflictWarning = false;
        foreach ($result['warnings'] as $warning) {
            if (str_contains($warning, 'Source conflict')) {
                $hasConflictWarning = true;
                break;
            }
        }

        $this->assertTrue($hasConflictWarning, 'Expected source conflict warning was not generated.');
    }

    public function test_falls_back_when_ai_rewrite_fails(): void
    {
        $this->fakeHttpResponses($this->getSampleTourHtml(3));

        // Mock DeepSeekService to return null or fail
        $mockDeepSeek = $this->createMock(DeepSeekService::class);
        $mockDeepSeek->method('askJson')->willReturn(null);

        $rewriter = new ExternalTourContentRewriter($mockDeepSeek);
        $service = new ExternalTourImportService(
            app(LuxorAndAswanTourPageParser::class),
            $rewriter,
            app(ExternalTourImageDownloader::class)
        );

        $result = $service->import($this->sampleUrl, ['rewrite' => true, 'download_images' => false]);

        $this->assertInstanceOf(Package::class, $result['package']);
        $this->assertNotEmpty($result['package']->getTranslation('title', 'en'));
    }

    public function test_parser_filters_unwanted_images_and_creates_stable_source_id(): void
    {
        $html = $this->getSampleTourHtml(3);
        $parser = new LuxorAndAswanTourPageParser();
        $downloader = new ExternalTourImageDownloader();

        $parsed = $parser->parse($html, $this->sampleUrl);

        $this->assertSame(sha1(strtolower(rtrim($this->sampleUrl, '/'))), $parsed['source_id']);
        $this->assertSame('7-Day-Cairo-Alexandria-and-Nile-Cruise-Tour-Package-by-Flight', $parsed['source_slug']);

        $filteredImages = $downloader->filterCandidateUrls($parsed['images']);

        foreach ($filteredImages as $img) {
            $this->assertStringNotContainsString('logo', strtolower($img));
            $this->assertStringNotContainsString('tripadvisor', strtolower($img));
            $this->assertStringNotContainsString('.svg', strtolower($img));
        }
    }

    public function test_auto_creates_missing_attractions_and_assigns_cities(): void
    {
        $customHtml = str_replace(
            '<div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Grand Egyptian Museum</h4>
            <img src="https://www.luxorandaswan.com/images/gem.jpg" alt="GEM">
        </div>',
            '<div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Hatshepsut Temple</h4>
        </div>
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Roman Amphitheatre in Alexandria</h4>
        </div>
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Khan El Khalili</h4>
        </div>',
            $this->getSampleTourHtml(3)
        );

        $this->fakeHttpResponses($customHtml);

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package']->load('packageAttractions.attraction.city');

        $this->assertGreaterThanOrEqual(3, $package->packageAttractions->count());

        // Verify Hatshepsut Temple was auto-created and mapped to Luxor
        $hatshepsut = Attraction::where('slug', 'hatshepsut-temple')->first();
        $this->assertNotNull($hatshepsut);
        $this->assertNotNull($hatshepsut->city_id);
        $this->assertEquals('luxor', $hatshepsut->city?->slug);

        // Verify Roman Amphitheatre was auto-created and mapped to Alexandria
        $amphitheatre = Attraction::where('slug', 'like', '%roman-amphitheatre%')->first();
        $this->assertNotNull($amphitheatre);
        $this->assertEquals('alexandria', $amphitheatre->city?->slug);

        // Verify Khan El Khalili was auto-created and mapped to Cairo
        $khan = Attraction::where('slug', 'khan-el-khalili')->first();
        $this->assertNotNull($khan);
        $this->assertEquals('cairo', $khan->city?->slug);
    }

    public function test_prevents_duplicate_attractions_in_places_you_will_visit(): void
    {
        // HTML containing duplicate and overlapping attraction mentions
        $duplicateHtml = str_replace(
            '<h4 class="attraction-luxury-title">Giza Pyramids</h4>',
            '<h4 class="attraction-luxury-title">Giza Pyramids Complex</h4>
        </div>
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Giza Pyramids</h4>
        </div>
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Grand Egyptian Museum (GEM)</h4>
        </div>
        <div class="attraction-luxury-card">
            <h4 class="attraction-luxury-title">Grand Egyptian Museum</h4>',
            $this->getSampleTourHtml(3)
        );

        $this->fakeHttpResponses($duplicateHtml);

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);
        $result = $service->import($this->sampleUrl, ['rewrite' => false, 'download_images' => false]);
        $package = $result['package']->load('packageAttractions.attraction');

        $attractionIds = $package->packageAttractions->pluck('attraction_id')->toArray();
        $this->assertSame(count($attractionIds), count(array_unique($attractionIds)), 'Package has duplicate attractions attached.');
    }

    /** @test */
    public function it_rejects_soft_404_pages_from_remote_source(): void
    {
        $notFoundHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>404 - Page Not Found | Luxor and Aswan Travel</title>
</head>
<body>
    <h1>404</h1>
    <p>The page you're looking for couldn't be found.</p>
</body>
</html>
HTML;

        Http::fake([
            'https://www.luxorandaswan.com/*' => Http::response($notFoundHtml, 200),
        ]);

        /** @var ExternalTourImportService $service */
        $service = app(ExternalTourImportService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("404 - Page Not Found");

        $service->import('https://www.luxorandaswan.com/Egypt/cruise/invalid-tour-slug', [
            'rewrite' => false,
            'download_images' => false,
        ]);
    }
}
