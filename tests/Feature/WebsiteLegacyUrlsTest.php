<?php

namespace Tests\Feature;

use App\Models\Attraction;
use App\Models\Country;
use App\Models\Page;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebsiteLegacyUrlsTest extends TestCase
{
    use RefreshDatabase;

    private ?Country $country = null;

    private function country(): Country
    {
        return $this->country ??= Country::query()->create([
            'code' => 'EG',
            'name' => ['en' => 'Egypt'],
            'slug' => 'egypt',
            'is_active' => true,
        ]);
    }

    private function package(array $attributes = []): Package
    {
        return Package::query()->create(array_merge([
            'primary_country_id' => $this->country()->id,
            'package_type' => 'nile_cruise',
            'slug' => 'legacy-nile-cruise',
            'title' => ['en' => 'Legacy Nile Cruise'],
            'is_active' => true,
        ], $attributes));
    }

    private function mapLegacyId(string $sourceTable, int $sourceId, Package $package): void
    {
        DB::table('legacy_import_maps')->insert([
            'source_table' => $sourceTable,
            'source_id' => $sourceId,
            'target_table' => 'packages',
            'target_id' => $package->id,
            'created_at' => now(),
        ]);
    }

    public function test_legacy_numeric_package_url_renders_in_place_without_redirect(): void
    {
        $package = $this->package();
        $this->mapLegacyId('packages', 42, $package);

        $response = $this->get('/de/tour-packages/42');

        $response->assertOk();
        $response->assertSee('lang="de"', false);
        $response->assertSee(route('website.nile_cruises.show', $package->slug), false);
    }

    public function test_legacy_cruise_url_uses_the_legacy_package_mapping(): void
    {
        $package = $this->package(['slug' => 'legacy-cruise-27']);
        $this->mapLegacyId('packages', 27, $package);

        $this->get('/fr/cruise/27')
            ->assertOk()
            ->assertSee('lang="fr"', false)
            ->assertSee(route('website.nile_cruises.show', $package->slug), false);
    }

    public function test_legacy_tours_prefix_maps_old_tour_ids_to_day_tours(): void
    {
        $package = $this->package([
            'package_type' => 'day_tour',
            'slug' => 'legacy-day-tour-82',
        ]);
        $this->mapLegacyId('tours', 82, $package);

        $this->get('/de/tours/82')
            ->assertOk()
            ->assertSee(route('website.day_tours.show', $package->slug), false);
    }

    public function test_sights_url_renders_attraction_page_at_the_same_url(): void
    {
        $attraction = Attraction::query()->create([
            'slug' => 'memphis',
            'name' => ['en' => 'Memphis'],
            'is_active' => true,
        ]);

        $this->get('/sights/memphis')
            ->assertOk()
            ->assertSee(route('website.attractions.show', $attraction->slug), false);
    }

    public function test_tags_and_blog_categories_fall_back_to_the_blog_listing_without_redirecting(): void
    {
        $this->get('/tags/El-Gouna-excursions')
            ->assertOk()
            ->assertSee(route('website.blogs.index'), false);

        $this->get('/de/blog-category/Aegypten-Reisepakete')
            ->assertOk()
            ->assertSee('lang="de"', false);
    }

    public function test_dated_wordpress_permalinks_render_the_blog_listing_in_place(): void
    {
        $this->get('/2023/09/11/giza-plateau/')
            ->assertOk()
            ->assertSee(route('website.blogs.index'), false);
    }

    public function test_german_about_url_renders_the_about_page(): void
    {
        Page::query()->create([
            'slug' => 'about-etrotours',
            'title' => ['en' => 'About Egypt Tour Pro', 'de' => 'Über Egypt Tour Pro'],
            'body' => ['en' => 'About body', 'de' => 'Über uns'],
            'is_active' => true,
        ]);

        $this->get('/%C3%9Cber')
            ->assertOk()
            ->assertSee(route('website.pages.show', 'about-etrotours'), false);
    }

    public function test_locale_prefix_is_stripped_without_a_redirect(): void
    {
        $this->get('/fr/tour-packages')
            ->assertOk()
            ->assertSee('lang="fr"', false);
    }

    public function test_unknown_urls_keep_responding_with_a_page_instead_of_a_404(): void
    {
        $this->get('/this-page-never-existed-anywhere')
            ->assertOk();
    }

    public function test_old_blog_slug_renders_the_closest_article(): void
    {
        \App\Models\Article::query()->create([
            'slug' => 'best-time-to-visit-egypt-2027-weather-guide',
            'title' => ['en' => 'Best Time to Visit Egypt'],
            'content' => ['en' => 'Content'],
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/best-time-to-visit-egypt')
            ->assertOk()
            ->assertSee(route('website.blogs.show', 'best-time-to-visit-egypt-2027-weather-guide'), false);
    }

    public function test_legacy_city_url_renders_the_destination_page_in_place(): void
    {
        \App\Models\City::query()->create([
            'country_id' => $this->country()->id,
            'slug' => 'marsa-alam',
            'name' => ['en' => 'Marsa Alam', 'de' => 'Marsa Alam'],
            'is_active' => true,
        ]);

        $this->get('/cities/marsa-alam')
            ->assertOk()
            ->assertSee(route('website.destinations.show', 'marsa-alam'), false);
    }

    public function test_service_category_and_cruise_prefixes_render_without_redirecting(): void
    {
        $this->get('/service-category/custom-egypt-vacations')->assertOk();
        $this->get('/cruise/5-day-luxury-nile-cruise-from-luxor')->assertOk();
    }

    public function test_why_us_alias_renders_the_why_page_instead_of_redirecting(): void
    {
        Page::query()->create([
            'slug' => 'why-etrotours',
            'title' => ['en' => 'Why Egypt Tour Pro'],
            'body' => ['en' => 'Why body'],
            'is_active' => true,
        ]);

        $this->get('/why-us')
            ->assertOk()
            ->assertSee(route('website.pages.show', 'why-etrotours'), false);
    }

    public function test_mirror_routes_and_trailing_slashes_render_without_redirecting(): void
    {
        foreach (['/blogs', '/travel-packages', '/tour-packages/'] as $url) {
            $this->get($url)->assertOk();
        }

        // Trailing-slash requests expose the slash-less canonical URL.
        $this->get('/tour-packages/')
            ->assertSee(rtrim(route('website.travel_packages.index'), '/') . '"', false);
    }

    public function test_day_tours_and_trips_render_on_their_requested_urls(): void
    {
        $dayTour = $this->package([
            'package_type' => 'day_tour',
            'slug' => 'legacy-day-tour',
        ]);

        $this->get('/tours/legacy-day-tour')
            ->assertOk()
            ->assertSee(route('website.day_tours.show', $dayTour->slug), false);

        $travelPackage = $this->package([
            'package_type' => 'travel_package',
            'slug' => 'legacy-travel-package',
        ]);

        $this->get('/trips/legacy-travel-package')
            ->assertOk()
            ->assertSee(route('website.tour_packages.show', $travelPackage->slug), false);
    }

    public function test_localized_policy_and_category_urls_render_in_place(): void
    {
        Page::query()->create([
            'slug' => 'privacy-policy',
            'title' => ['en' => 'Privacy Policy', 'fr' => 'Politique de confidentialité'],
            'body' => ['en' => 'Body', 'fr' => 'Contenu'],
            'is_active' => true,
        ]);

        $this->get('/fr/policies/politique-de-confidentialit%C3%A9')
            ->assertOk()
            ->assertSee(route('website.pages.show', 'privacy-policy'), false);

        $this->get('/fr/know-category/2')->assertOk();
        $this->get('/de/services/Tagesausfl%C3%BCge-und-Exkursionen-in-%C3%84gypten')->assertOk();
    }
}
