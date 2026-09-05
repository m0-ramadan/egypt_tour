<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\NileCruiseType;
use App\Models\Package;
use App\Models\PackageCategory;
use Database\Seeders\NileCruiseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NileCruiseExtendedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NileCruiseSeeder::class);
    }

    private function fixtures(): array
    {
        $admin = Admin::create(['name'=>'Admin','email'=>'nc@example.com','password'=>bcrypt('password')]);
        $country = Country::create(['name'=>['en'=>'Egypt','ar'=>'مصر'],'slug'=>'egypt-nc','code'=>'NC']);
        $luxor = City::create(['country_id'=>$country->id,'name'=>['en'=>'Luxor','ar'=>'الأقصر'],'slug'=>'luxor-nc']);
        $aswan = City::create(['country_id'=>$country->id,'name'=>['en'=>'Aswan','ar'=>'أسوان'],'slug'=>'aswan-nc']);
        $currency = Currency::create(['code'=>'USD','symbol'=>'$','name'=>'US Dollar','is_default'=>true]);
        $category = PackageCategory::create(['name'=>'Cruises','slug'=>'cruises-nc','category_type'=>'nile_cruise']);
        $dahabiya = NileCruiseType::where('slug','dahabiya-nile-cruise')->firstOrFail();
        return compact('admin','country','luxor','aswan','currency','category','dahabiya');
    }

    public function test_nile_cruise_extended_content_is_saved_without_changing_package_architecture(): void
    {
        $f = $this->fixtures();
        $response = $this->actingAs($f['admin'], 'admin')->post(route('admin.packages.store'), [
            'title' => 'Princess Farida Luxury Dahabiya Nile Cruise',
            'package_type' => 'nile_cruise',
            'nile_cruise_type_id' => $f['dahabiya']->id,
            'category_id' => $f['category']->id,
            'destination_id' => $f['luxor']->id,
            'primary_country_id' => $f['country']->id,
            'currency_id' => $f['currency']->id,
            'tour_type' => 'group',
            'duration_type' => 'days',
            'duration_days' => 4,
            'adult_price' => 1790,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'nile_cruise' => [
                '_present' => 1,
                'decks' => 2,
                'operating_days' => ['Monday','Friday'],
                'promotional_videos' => "https://example.com/video-one\nhttps://example.com/video-two",
                'deposit_policy' => 'required',
                'deposit_type' => 'percent',
                'deposit_value' => 50,
                'focus_keyword' => 'Luxury Dahabiya Nile Cruise',
                'meta_keywords' => 'Dahabiya, Nile Cruise, Luxor, Aswan',
                'og_title' => 'Princess Farida Luxury Dahabiya Nile Cruise',
                'og_description' => 'Sail between Luxor and Aswan aboard Princess Farida.',
                'twitter_card' => 'summary_large_image',
                'twitter_title' => 'Princess Farida Dahabiya',
                'twitter_description' => 'Luxury small-group Nile sailing.',
                'robots_index' => 1,
                'robots_follow' => 1,
                'tour_style' => 'Small Group Tour',
                'facility_titles' => ['Lounge', 'Dining Room', 'Sun Deck'],
                'route_summary' => 'Luxor / Edfu / Kom Ombo / Aswan',
                'route_city_ids' => [$f['luxor']->id, $f['aswan']->id],
                'addons' => [
                    ['name'=>'Balloon Ride','description'=>'Optional sunrise balloon flight','price'=>120,'currency_id'=>$f['currency']->id,'is_active'=>1],
                ],
                'schedules' => [
                    ['departure_day'=>'Monday','departure_city_id'=>$f['luxor']->id,'arrival_city_id'=>$f['aswan']->id,'direction'=>'Luxor → Aswan','is_active'=>1],
                    ['departure_day'=>'Friday','departure_city_id'=>$f['aswan']->id,'arrival_city_id'=>$f['luxor']->id,'direction'=>'Aswan → Luxor','is_active'=>1],
                ],
                'cabins' => [
                    ['client_key'=>'royal','name'=>'Royal Suite','quantity'=>2,'bed_type'=>'King Size Bed','size_sqm'=>35,'has_private_bathroom'=>1,'has_private_terrace'=>1,'amenities'=>"TV\nMini Bar"],
                ],
                'durations' => [[
                    'title'=>'3 Nights / 4 Days','days'=>4,'nights'=>3,'direction'=>'Aswan → Luxor','departure_day'=>'Friday','currency_id'=>$f['currency']->id,'is_default'=>1,'is_active'=>1,
                    'itinerary'=>[[
                        'day_number'=>1,'title'=>'Aswan & Kom Ombo','description'=>'Board the Dahabiya and sail north.','meals'=>"Lunch\nDinner",'overnight'=>'El Beshier',
                        'activities'=>[['title'=>'Temple of Philae','description'=>'Guided temple visit']],
                    ]],
                    'seasons'=>[[
                        'season_name'=>'Summer','date_from'=>'2026-05-01','date_to'=>'2026-08-31','currency_id'=>$f['currency']->id,'is_active'=>1,
                        'items'=>[
                            ['occupancy_type'=>'double','label'=>'Double Price','price'=>1790],
                            ['cabin_key'=>'royal','occupancy_type'=>'suite','label'=>'Royal Suite Price','price'=>5780],
                        ],
                    ]],
                ]],
            ],
        ]);

        $response->assertRedirect();
        $package = Package::where('slug', 'princess-farida-luxury-dahabiya-nile-cruise')->firstOrFail();
        $this->assertSame('nile_cruise', $package->package_type);
        $this->assertDatabaseHas('nile_cruise_details', [
            'package_id'=>$package->id,
            'decks'=>2,
            'focus_keyword'=>'Luxury Dahabiya Nile Cruise',
            'og_title'=>'Princess Farida Luxury Dahabiya Nile Cruise',
            'twitter_card'=>'summary_large_image',
        ]);
        $this->assertDatabaseCount('nile_cruise_schedules', 2);
        $this->assertDatabaseHas('nile_cruise_addons', ['package_id'=>$package->id,'name'=>'Balloon Ride','price'=>120]);
        $this->assertDatabaseHas('package_facilities', ['package_id'=>$package->id,'title'=>'Lounge']);
        $this->assertDatabaseHas('nile_cruise_cabins', ['package_id'=>$package->id,'name'=>'Royal Suite']);
        $this->assertDatabaseHas('nile_cruise_durations', ['package_id'=>$package->id,'days'=>4,'nights'=>3]);
        $this->assertDatabaseHas('nile_cruise_itinerary_days', ['day_number'=>1]);
        $this->assertDatabaseHas('nile_cruise_season_price_items', ['price'=>1790]);
        $freshPackage = $package->fresh();
        $this->assertEquals(1790.0, (float) $freshPackage->price_from);
        $this->assertSame(4, $freshPackage->duration_days);
        $this->assertStringContainsString('3 Nights / 4 Days', (string) $freshPackage->duration_text);
    }

    public function test_non_nile_package_does_not_create_nile_cruise_records(): void
    {
        $f = $this->fixtures();
        $this->actingAs($f['admin'], 'admin')->post(route('admin.packages.store'), [
            'title'=>'Normal Travel Package','package_type'=>'travel_package','category_id'=>$f['category']->id,
            'destination_id'=>$f['luxor']->id,'primary_country_id'=>$f['country']->id,'currency_id'=>$f['currency']->id,
            'tour_type'=>'private','duration_type'=>'days','duration_days'=>3,'adult_price'=>200,
            'adult_min_age'=>12,'child_min_age'=>2,'child_max_age'=>11,'infant_min_age'=>0,'infant_max_age'=>1,
            'nile_cruise' => [
                'decks' => 99,
                'facility_titles' => ['Lounge'],
                'durations' => [['title'=>'Should Never Save','days'=>8]],
            ],
        ])->assertRedirect();

        $this->assertDatabaseCount('nile_cruise_details', 0);
        $this->assertDatabaseCount('nile_cruise_durations', 0);
        $this->assertDatabaseCount('nile_cruise_cabins', 0);
        $this->assertDatabaseCount('nile_cruise_addons', 0);
    }
}
