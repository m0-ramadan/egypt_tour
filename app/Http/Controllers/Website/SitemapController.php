<?php

namespace App\Http\Controllers\Website;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\City;
use App\Models\Package;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends BaseWebsiteController
{
    public function index(): Response
    {
        $urls = $this->staticUrls()
            ->merge($this->nileCruiseUrls())
            ->merge($this->pageUrls())
            ->merge($this->destinationUrls())
            ->merge($this->blogCategoryUrls())
            ->merge($this->articleUrls())
            ->merge($this->packageUrls())
            ->unique('loc')
            ->values();

        return response()
            ->view('website.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function nileCruiseUrls(): Collection
    {
        return collect([
            $this->makeUrl(route('website.nile_cruises.index'), null, 'daily', '0.9'),
            $this->makeUrl(route('website.nile_cruises.luxor_aswan'), null, 'daily', '0.9'),
            $this->makeUrl(route('website.nile_cruises.luxor_aswan.category', 'standard-nile-cruises'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.nile_cruises.luxor_aswan.category', 'deluxe-nile-cruises'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.nile_cruises.luxor_aswan.category', 'ultra-deluxe-nile-cruises'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.nile_cruises.luxor_aswan.category', 'luxury-nile-cruises'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.nile_cruises.type', 'dahabiya-nile-cruise'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.nile_cruises.type', 'lake-nasser-cruise'), null, 'weekly', '0.8'),
        ]);
    }

    private function staticUrls(): Collection
    {
        return collect([
            $this->makeUrl(route('website.home'), null, 'daily', '1.0'),
            $this->makeUrl(route('website.offers'), null, 'daily', '0.9'),
            $this->makeUrl(route('website.multi_country'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.services'), null, 'monthly', '0.6'),
            $this->makeUrl(route('website.contact.index'), null, 'monthly', '0.6'),
            $this->makeUrl(route('website.destinations.index'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.blogs.index'), null, 'weekly', '0.8'),
            $this->makeUrl(route('website.trips'), null, 'daily', '0.9'),
            $this->makeUrl(route('website.tours.all'), null, 'daily', '0.9'),
            $this->makeUrl(route('website.tailor_made.index'), null, 'monthly', '0.7'),
        ]);
    }

    private function pageUrls(): Collection
    {
        $reservedSlugs = [
            'blogs',
            'contact-us',
            'destinations',
            'latest-offers',
            'multi-country',
            'multi-country-tours',
            'search',
            'services',
            'tailor-made',
            'tours',
            'trips',
        ];

        return Page::query()
            ->publiclyVisible()
            ->whereNotIn('slug', $reservedSlugs)
            ->orderBy('slug')
            ->get(['slug', 'published_at', 'updated_at'])
            ->map(fn (Page $page) => $this->makeUrl(
                route('website.pages.show', ['slug' => $page->slug]),
                $page->updated_at ?? $page->published_at,
                'monthly',
                '0.6'
            ));
    }

    private function destinationUrls(): Collection
    {
        return City::query()
            ->where('is_active', true)
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->map(fn (City $city) => $this->makeUrl(
                route('website.destinations.show', ['slug' => $city->slug]),
                $city->updated_at,
                'weekly',
                '0.7'
            ));
    }

    private function blogCategoryUrls(): Collection
    {
        return ArticleCategory::query()
            ->active()
            ->whereHas('articles', fn ($query) => $query->active()->published())
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->map(fn (ArticleCategory $category) => $this->makeUrl(
                route('website.blogs.category', ['slug' => $category->slug]),
                $category->updated_at,
                'weekly',
                '0.7'
            ));
    }

    private function articleUrls(): Collection
    {
        return Article::query()
            ->active()
            ->published()
            ->orderBy('slug')
            ->get(['slug', 'published_at', 'updated_at'])
            ->map(fn (Article $article) => $this->makeUrl(
                route('website.blogs.show', ['slug' => $article->slug]),
                $article->updated_at ?? $article->published_at,
                'weekly',
                '0.7'
            ));
    }

    private function packageUrls(): Collection
    {
        return Package::query()
            ->active()
            ->orderBy('slug')
            ->get(['slug', 'package_type', 'published_at', 'updated_at'])
            ->map(fn (Package $package) => $this->makeUrl(
                $this->packageRoute($package),
                $package->updated_at ?? $package->published_at,
                'weekly',
                '0.8'
            ));
    }

    private function makeUrl(string $loc, mixed $lastmod = null, ?string $changefreq = null, ?string $priority = null): array
    {
        return array_filter([
            'loc' => $loc,
            'lastmod' => $lastmod?->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ], fn ($value) => $value !== null);
    }
}
