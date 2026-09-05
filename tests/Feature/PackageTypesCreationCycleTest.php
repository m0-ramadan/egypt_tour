<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\NileCruiseCategory;
use App\Models\NileCruiseType;
use App\Models\Package;
use App\Models\PackageCategory;
use Database\Seeders\NileCruiseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTypesCreationCycleTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Country $country;
    protected City $city;
    protected Currency $currency;
    protected PackageCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NileCruiseSeeder::class);

        $this->admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'slug' => 'egypt',
            'code' => 'EG',
        ]);

        $this->city = City::create([
            'country_id' => $this->country->id,
            'name' => ['en' => 'Cairo', 'ar' => 'القاهرة'],
            'slug' => 'cairo',
        ]);

        $this->currency = Currency::create([
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'is_default' => true,
        ]);

        $this->category = PackageCategory::create([
            'name' => ['en' => 'General Tours', 'ar' => 'رحلات عامة'],
            'slug' => 'general-tours',
            'category_type' => 'travel_package',
        ]);
    }

    protected function defaultAgeRules(): array
    {
        return [
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
        ];
    }

    public function test_can_create_and_update_day_tour(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.packages.store'), array_merge($this->defaultAgeRules(), [
            'title' => 'Pyramids Day Trip',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'tour_type' => 'private',
            'duration_type' => 'hours',
            'duration_hours' => 6,
            'adult_price' => 80,
            'itinerary' => [
                [
                    'day_number' => 1,
                    'title' => 'Giza Pyramids & Sphinx',
                    'duration' => '6 hours',
                    'start_time' => '08:00',
                    'end_time' => '14:00',
                    'description' => 'Visit Giza Plateau and Great Sphinx.',
                    'meals_breakfast' => '1',
                    'meals_lunch' => '1',
                    'meals_dinner' => '0',
                ]
            ],
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.packages.index'));

        $package = Package::where('package_type', 'day_tour')->firstOrFail();
        $this->assertEquals('day_tour', $package->package_type);
        $this->assertEquals('hours', $package->duration_type);
        $this->assertEquals(6, $package->duration_hours);
        $this->assertCount(1, $package->itineraries);

        $itinerary = $package->itineraries->first();
        $this->assertEquals('08:00', $itinerary->start_time);
        $this->assertEquals('14:00', $itinerary->end_time);
    }

    public function test_can_create_and_update_travel_package(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.packages.store'), array_merge($this->defaultAgeRules(), [
            'title' => '7-Day Grand Egypt Package',
            'package_type' => 'travel_package',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'tour_type' => 'group',
            'duration_type' => 'days',
            'duration_days' => 7,
            'duration_nights' => 6,
            'adult_price' => 1200,
            'itinerary' => [
                [
                    'day_number' => 1,
                    'title' => 'Arrival in Cairo',
                    'duration' => 'Day 1',
                    'description' => 'Airport meet and greet.',
                    'meals_breakfast' => '0',
                    'meals_lunch' => '0',
                    'meals_dinner' => '1',
                    'overnight_location' => 'Cairo',
                    'accommodation' => '5-star hotel in Cairo',
                    'transport_notes' => 'Private luxury transfer from airport',
                ],
                [
                    'day_number' => 2,
                    'title' => 'Pyramids and Egyptian Museum',
                    'duration' => 'Day 2',
                    'description' => 'Full day sightseeing in Cairo.',
                    'meals_breakfast' => '1',
                    'meals_lunch' => '1',
                    'meals_dinner' => '1',
                    'overnight_location' => 'Cairo',
                    'accommodation' => '5-star hotel in Cairo',
                    'transport_notes' => 'Private AC bus',
                ]
            ],
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.packages.index'));

        $package = Package::where('package_type', 'travel_package')->firstOrFail();
        $this->assertEquals('travel_package', $package->package_type);
        $this->assertEquals(7, $package->duration_days);
        $this->assertCount(2, $package->itineraries);

        $day1 = $package->itineraries->where('day_number', 1)->first();
        $this->assertEquals('Cairo', $day1->overnight_location);
        $this->assertEquals('5-star hotel in Cairo', $day1->accommodation);
        $this->assertTrue((bool)$day1->meals_dinner);
    }

    public function test_can_create_and_update_nile_cruise(): void
    {
        $luxorType = NileCruiseType::where('slug', 'luxor-aswan-nile-cruises')->firstOrFail();
        $deluxeCategory = NileCruiseCategory::where('slug', 'deluxe-nile-cruises')->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.packages.store'), array_merge($this->defaultAgeRules(), [
            'title' => 'Luxor to Aswan 4 Nights Cruise',
            'package_type' => 'nile_cruise',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'tour_type' => 'private',
            'nile_cruise_type_id' => $luxorType->id,
            'nile_cruise_category_id' => $deluxeCategory->id,
            'duration_type' => 'days',
            'duration_days' => 5,
            'duration_nights' => 4,
            'adult_price' => 650,
            'itinerary' => [
                [
                    'day_number' => 1,
                    'title' => 'Embarkation in Luxor',
                    'duration' => 'Day 1',
                    'description' => 'Check-in on Nile Cruise ship and lunch on board.',
                    'meals_breakfast' => '1',
                    'meals_lunch' => '1',
                    'meals_dinner' => '1',
                ]
            ],
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.packages.index'));

        $package = Package::where('package_type', 'nile_cruise')->firstOrFail();
        $this->assertEquals('nile_cruise', $package->package_type);
        $this->assertEquals($luxorType->id, $package->nile_cruise_type_id);
        $this->assertEquals($deluxeCategory->id, $package->nile_cruise_category_id);
        $this->assertCount(1, $package->itineraries);
    }

    public function test_multi_select_meals_persisted_correctly(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.packages.store'), array_merge($this->defaultAgeRules(), [
            'title' => 'Multi Meal Test Package',
            'package_type' => 'travel_package',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'tour_type' => 'group',
            'duration_type' => 'days',
            'duration_days' => 3,
            'duration_nights' => 2,
            'adult_price' => 500,
            'itinerary' => [
                [
                    'day_number' => 1,
                    'title' => 'Day 1 - Breakfast & Dinner Only',
                    'description' => 'First day details.',
                    'meals' => ['breakfast', 'dinner'],
                ],
                [
                    'day_number' => 2,
                    'title' => 'Day 2 - Full Board',
                    'description' => 'Second day details.',
                    'meals' => ['breakfast', 'lunch', 'dinner'],
                ]
            ],
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.packages.index'));

        $package = Package::where('slug', 'multi-meal-test-package')->firstOrFail();
        $day1 = $package->itineraries->where('day_number', 1)->first();
        $day2 = $package->itineraries->where('day_number', 2)->first();

        $this->assertEquals(['breakfast', 'dinner'], $day1->meals_list);
        $this->assertTrue($day1->meals_breakfast);
        $this->assertFalse($day1->meals_lunch);
        $this->assertTrue($day1->meals_dinner);

        $this->assertEquals(['breakfast', 'lunch', 'dinner'], $day2->meals_list);
        $this->assertTrue($day2->meals_breakfast);
        $this->assertTrue($day2->meals_lunch);
        $this->assertTrue($day2->meals_dinner);
    }
}
