<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteHomeCategoryShowcaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::forget('supported_locales');
        \App\Models\Language::updateOrCreate(['code' => 'ar'], ['name' => 'Arabic', 'is_active' => true]);
        $this->seed(\Database\Seeders\PackageCategoriesSeeder::class);
    }

    public function test_homepage_displays_three_tour_categories_in_english(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('website.day_tours.index'), false);
        $response->assertSee(route('website.travel_packages.index'), false);
        $response->assertSee(route('website.nile_cruises.index'), false);
        $response->assertSee('Egypt day tours', false);
        $response->assertSee('Egypt tour packages', false);
        $response->assertSee('Nile Cruises', false);
        $response->assertSee('Explore Day Tours', false);
        $response->assertSee('Explore Tour Packages', false);
        $response->assertSee('Explore Nile Cruises', false);
        $response->assertSee('day-tours', false);
        $response->assertSee('travel-packages', false);
        $response->assertSee('nile-cruises', false);
    }

    public function test_homepage_displays_three_tour_categories_in_arabic(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->withSession(['locale' => 'ar'])->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('website.day_tours.index'), false);
        $response->assertSee(route('website.travel_packages.index'), false);
        $response->assertSee(route('website.nile_cruises.index'), false);
        $response->assertSee('جولات اليوم الواحد في مصر', false);
        $response->assertSee('باقات السفر في مصر', false);
        $response->assertSee('رحلات نيلية', false);
        $response->assertSee('استكشف رحلات اليوم الواحد', false);
        $response->assertSee('استكشف الباقات السياحية', false);
        $response->assertSee('استكشف رحلات الكروز النيلية', false);
        $response->assertSee('day-tours', false);
        $response->assertSee('travel-packages', false);
        $response->assertSee('nile-cruises', false);
    }
}
