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
use App\Services\ReadyTourTaxonomyMapper;
use Database\Seeders\NileCruiseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NileCruiseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NileCruiseSeeder::class);
    }

    public function test_nile_cruise_seeder_creates_types_and_categories(): void
    {
        $this->assertDatabaseHas('nile_cruise_types', ['slug' => 'luxor-aswan-nile-cruises']);
        $this->assertDatabaseHas('nile_cruise_types', ['slug' => 'dahabiya-nile-cruise']);
        $this->assertDatabaseHas('nile_cruise_types', ['slug' => 'lake-nasser-cruise']);

        $luxorType = NileCruiseType::where('slug', 'luxor-aswan-nile-cruises')->first();
        $this->assertNotNull($luxorType);
        $this->assertCount(4, $luxorType->categories);
    }

    public function test_package_model_relationships_and_scopes_work(): void
    {
        $luxorType = NileCruiseType::where('slug', 'luxor-aswan-nile-cruises')->first();
        $deluxeCategory = NileCruiseCategory::where('slug', 'deluxe-nile-cruises')->first();

        $package = Package::create([
            'title' => ['en' => 'Luxor to Aswan Cruise', 'ar' => 'رحلة نيلية من الأقصر إلى أسوان'],
            'slug' => 'luxor-aswan-cruise-test',
            'package_type' => 'nile_cruise',
            'nile_cruise_type_id' => $luxorType->id,
            'nile_cruise_category_id' => $deluxeCategory->id,
            'tour_type' => 'private',
            'duration_type' => 'days',
            'duration_days' => 4,
            'adult_price' => 400,
        ]);

        $this->assertEquals('nile_cruise', $package->package_type);
        $this->assertEquals($luxorType->id, $package->nileCruiseType->id);
        $this->assertEquals($deluxeCategory->id, $package->nileCruiseCategory->id);

        $this->assertCount(1, Package::nileCruises()->get());
        $this->assertCount(1, Package::forNileCruiseType($luxorType->id)->get());
        $this->assertCount(1, Package::forNileCruiseCategory($deluxeCategory->id)->get());
    }

    public function test_admin_can_create_nile_cruise_package_with_valid_classification(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = PackageCategory::create([
            'name' => 'Nile Cruises Category',
            'slug' => 'nile-cruises-cat',
            'category_type' => 'nile_cruise',
        ]);

        $country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'slug' => 'egypt',
            'code' => 'EG',
        ]);

        $city = City::create([
            'country_id' => $country->id,
            'name' => ['en' => 'Luxor', 'ar' => 'الأقصر'],
            'slug' => 'luxor',
        ]);

        $currency = Currency::create([
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'is_default' => true,
        ]);

        $luxorType = NileCruiseType::where('slug', 'luxor-aswan-nile-cruises')->first();
        $deluxeCategory = NileCruiseCategory::where('slug', 'deluxe-nile-cruises')->first();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.packages.store'), [
            'title' => 'Luxor to Aswan 5-Day Cruise',
            'package_type' => 'nile_cruise',
            'category_id' => $category->id,
            'destination_id' => $city->id,
            'primary_country_id' => $country->id,
            'currency_id' => $currency->id,
            'tour_type' => 'private',
            'nile_cruise_type_id' => $luxorType->id,
            'nile_cruise_category_id' => $deluxeCategory->id,
            'adult_price' => 500,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'duration_type' => 'days',
            'duration_days' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('packages', [
            'package_type' => 'nile_cruise',
            'nile_cruise_type_id' => $luxorType->id,
            'nile_cruise_category_id' => $deluxeCategory->id,
        ]);
    }

    public function test_admin_package_creation_fails_when_nile_cruise_type_is_missing(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = PackageCategory::create([
            'name' => 'Nile Cruises Category 2',
            'slug' => 'nile-cruises-cat-2',
            'category_type' => 'nile_cruise',
        ]);

        $country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'slug' => 'egypt-2',
            'code' => 'E2',
        ]);

        $city = City::create([
            'country_id' => $country->id,
            'name' => ['en' => 'Aswan', 'ar' => 'أسوان'],
            'slug' => 'aswan',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.packages.store'), [
            'title' => 'Invalid Nile Cruise',
            'package_type' => 'nile_cruise',
            'category_id' => $category->id,
            'destination_id' => $city->id,
        ]);

        $response->assertSessionHasErrors(['nile_cruise_type_id']);
    }

    public function test_ready_tour_taxonomy_mapper_resolves_nile_cruise_type_and_category(): void
    {
        $mapper = new ReadyTourTaxonomyMapper();

        $deluxeRes = $mapper->resolveNileCruiseTaxonomy('Deluxe Nile Cruise', 'Luxor Aswan Deluxe Tour');
        $this->assertNotNull($deluxeRes['nile_cruise_type_id']);
        $this->assertNotNull($deluxeRes['nile_cruise_category_id']);

        $dahabiyaRes = $mapper->resolveNileCruiseTaxonomy('Dahabiya Cruise', 'Private Dahabiya Sailing');
        $this->assertNotNull($dahabiyaRes['nile_cruise_type_id']);
        $this->assertNull($dahabiyaRes['nile_cruise_category_id']);
    }

    public function test_public_nile_cruise_landing_pages_are_accessible(): void
    {
        $this->get(route('website.nile_cruises.index'))
            ->assertStatus(200)
            ->assertSee('Egypt Nile Cruises');

        $this->get(route('website.nile_cruises.luxor_aswan'))
            ->assertStatus(200)
            ->assertSee('Luxor and Aswan Nile Cruises');

        $this->get(route('website.nile_cruises.luxor_aswan.category', 'standard-nile-cruises'))
            ->assertStatus(200)
            ->assertSee('Standard Nile Cruises');

        $this->get(route('website.nile_cruises.type', 'dahabiya-nile-cruise'))
            ->assertStatus(200)
            ->assertSee('Dahabiya Nile Cruise');

        $this->get(route('website.nile_cruises.type', 'lake-nasser-cruise'))
            ->assertStatus(200)
            ->assertSee('Lake Nasser Cruise');
    }

    public function test_sitemap_includes_nile_cruise_urls(): void
    {
        $response = $this->get(route('website.sitemap'));
        $response->assertStatus(200);
        $response->assertSee('/nile-cruises');
        $response->assertSee('/nile-cruises/luxor-aswan-nile-cruises');
        $response->assertSee('dahabiya-nile-cruise');
    }
}
