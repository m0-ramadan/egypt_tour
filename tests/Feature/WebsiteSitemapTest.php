<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\City;
use App\Models\Country;
use App\Models\Package;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_website_only_sitemap(): void
    {
        $country = Country::query()->create([
            'code' => 'EG',
            'name' => ['en' => 'Egypt'],
            'slug' => 'egypt',
            'is_active' => true,
        ]);

        City::query()->create([
            'country_id' => $country->id,
            'name' => ['en' => 'Cairo'],
            'slug' => 'cairo',
            'is_active' => true,
        ]);

        Package::query()->create([
            'primary_country_id' => $country->id,
            'package_type' => 'travel_package',
            'slug' => 'classic-egypt',
            'title' => ['en' => 'Classic Egypt'],
            'is_active' => true,
        ]);

        Package::query()->create([
            'primary_country_id' => $country->id,
            'package_type' => 'day_tour',
            'slug' => 'luxor-day-tour',
            'title' => ['en' => 'Luxor Day Tour'],
            'is_active' => true,
        ]);

        Page::query()->create([
            'slug' => 'about-etrotours',
            'title' => ['en' => 'About Etro Tours'],
            'body' => ['en' => '<p>About us</p>'],
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        Page::query()->create([
            'slug' => 'future-page',
            'title' => ['en' => 'Future Page'],
            'body' => ['en' => '<p>Coming soon</p>'],
            'is_active' => true,
            'published_at' => now()->addDay(),
        ]);

        $category = ArticleCategory::query()->create([
            'name' => ['en' => 'Travel Guides'],
            'slug' => 'travel-guides',
            'is_active' => true,
        ]);

        Article::query()->create([
            'category_id' => $category->id,
            'slug' => 'best-of-cairo',
            'title' => ['en' => 'Best of Cairo'],
            'excerpt' => ['en' => 'A quick guide'],
            'content' => ['en' => '<p>Explore Cairo.</p>'],
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        Article::query()->create([
            'category_id' => $category->id,
            'slug' => 'coming-soon',
            'title' => ['en' => 'Coming Soon'],
            'excerpt' => ['en' => 'Soon'],
            'content' => ['en' => '<p>Not yet published.</p>'],
            'is_active' => true,
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(url('/'), false);
        $response->assertSee(url('/about-etrotours'), false);
        $response->assertSee(url('/destinations/cairo'), false);
        $response->assertSee(url('/blog/travel-guides'), false);
        $response->assertSee(url('/blogs/best-of-cairo'), false);
        $response->assertSee(url('/trips/classic-egypt'), false);
        $response->assertSee(url('/tours/luxor-day-tour'), false);
        $response->assertDontSee(url('/future-page'), false);
        $response->assertDontSee(url('/blogs/coming-soon'), false);
        $response->assertDontSee(url('/lang/en'), false);
        $response->assertDontSee(url('/search'), false);
        $response->assertDontSee(url('/package/classic-egypt'), false);
        $response->assertDontSee(url('/egypt/package/classic-egypt.html'), false);
        $response->assertDontSee(url('/multi-country-tours'), false);
    }
}
