<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPackageUploadProgressTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private PackageCategory $category;
    private Country $country;
    private City $city;
    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'slug' => 'egypt',
            'code' => 'EG',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->country->id,
            'name' => ['en' => 'Cairo', 'ar' => 'القاهرة'],
            'slug' => 'cairo',
            'is_active' => true,
        ]);

        $this->category = PackageCategory::create([
            'name' => ['en' => 'Day Tours', 'ar' => 'جولات اليوم الواحد'],
            'slug' => 'day-tours',
            'category_type' => 'day_tour',
            'is_active' => true,
        ]);

        $this->currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 1.0,
            'is_active' => true,
        ]);
    }

    public function test_package_store_via_ajax_returns_json_with_redirect(): void
    {
        $file = UploadedFile::fake()->image('featured.jpg', 600, 400);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('admin.packages.store'), [
                'title' => 'Ajax Day Tour in Cairo',
                'package_type' => 'day_tour',
                'category_id' => $this->category->id,
                'destination_id' => $this->city->id,
                'primary_country_id' => $this->country->id,
                'currency_id' => $this->currency->id,
                'duration_type' => 'hours',
                'duration_hours' => 5,
                'adult_price' => 100,
                'adult_min_age' => 12,
                'child_min_age' => 2,
                'child_max_age' => 11,
                'infant_min_age' => 0,
                'infant_max_age' => 1,
                'featured_image' => $file,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'redirect' => route('admin.packages.index'),
        ]);

        $this->assertDatabaseHas('packages', [
            'package_type' => 'day_tour',
            'duration_hours' => 5,
        ]);
    }

    public function test_package_update_via_ajax_preserves_existing_gallery_and_returns_json(): void
    {
        $package = Package::create([
            'title' => ['en' => 'Existing Cairo Trip', 'ar' => 'رحلة القاهرة الحالية'],
            'slug' => 'existing-cairo-trip',
            'package_type' => 'day_tour',
            'category_id' => $this->category->id,
            'destination_id' => $this->city->id,
            'primary_country_id' => $this->country->id,
            'currency_id' => $this->currency->id,
            'duration_type' => 'hours',
            'duration_hours' => 4,
            'adult_price' => 80,
            'adult_min_age' => 12,
            'child_min_age' => 2,
            'child_max_age' => 11,
            'infant_min_age' => 0,
            'infant_max_age' => 1,
            'gallery_images' => [
                'storage/packages/gallery/old1.webp',
                'storage/packages/gallery/old2.webp',
            ],
            'is_active' => true,
        ]);

        $newImage = UploadedFile::fake()->image('new-gallery.jpg', 600, 400);

        $response = $this->actingAs($this->admin, 'admin')
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->put(route('admin.packages.update', $package), [
                'title' => 'Updated Cairo Trip',
                'package_type' => 'day_tour',
                'category_id' => $this->category->id,
                'destination_id' => $this->city->id,
                'primary_country_id' => $this->country->id,
                'currency_id' => $this->currency->id,
                'duration_type' => 'hours',
                'duration_hours' => 4,
                'adult_price' => 80,
                'adult_min_age' => 12,
                'child_min_age' => 2,
                'child_max_age' => 11,
                'infant_min_age' => 0,
                'infant_max_age' => 1,
                'gallery_images' => [$newImage],
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'redirect' => route('admin.packages.index'),
        ]);

        $package->refresh();

        // Must have preserved old 2 gallery images and added the 1 new one (total 3)
        $this->assertCount(3, $package->gallery_images);
        $this->assertContains('storage/packages/gallery/old1.webp', $package->gallery_images);
        $this->assertContains('storage/packages/gallery/old2.webp', $package->gallery_images);
    }
}

