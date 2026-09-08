<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteHomeCategoryShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_three_tour_categories_in_english(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('website.day_tours.index'), false);
        $response->assertSee(route('website.travel_packages.index'), false);
        $response->assertSee(route('website.nile_cruises.index'), false);
        $response->assertSee('Egypt Day Tours', false);
        $response->assertSee('Egypt Tour Packages', false);
        $response->assertSee('Egypt Nile Cruise', false);
        $response->assertSee('Explore Day Tours', false);
        $response->assertSee('Explore Tour Packages', false);
        $response->assertSee('Explore Nile Cruises', false);
        $response->assertSee('website/photos/experiences/day-tours.jpg', false);
        $response->assertSee('website/photos/experiences/travel-packages.jpg', false);
        $response->assertSee('website/photos/experiences/nile-cruises.jpg', false);
    }

    public function test_homepage_displays_three_tour_categories_in_arabic(): void
    {
        $this->withoutExceptionHandling();
        app()->setLocale('ar');
        session(['locale' => 'ar']);
        $response = $this->withSession(['locale' => 'ar'])->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('website.day_tours.index'), false);
        $response->assertSee(route('website.travel_packages.index'), false);
        $response->assertSee(route('website.nile_cruises.index'), false);
        $response->assertSee('جولات اليوم الواحد في مصر', false);
        $response->assertSee('باقات السفر في مصر', false);
        $response->assertSee('رحلات النايل كروز في مصر', false);
        $response->assertSee('استكشف رحلات اليوم الواحد', false);
        $response->assertSee('استكشف الباقات السياحية', false);
        $response->assertSee('استكشف رحلات الكروز النيلية', false);
        $response->assertSee('website/photos/experiences/day-tours.jpg', false);
        $response->assertSee('website/photos/experiences/travel-packages.jpg', false);
        $response->assertSee('website/photos/experiences/nile-cruises.jpg', false);
    }
}
