<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Attraction;
use App\Models\City;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegacyRedirectController extends Controller
{
    public function handle(Request $request, ?string $path = null)
    {
        $uri = ltrim($request->getPathInfo(), '/');

        if (empty($uri)) {
            return redirect()->route('website.home', [], 301);
        }

        // 1. Extract prefix language if present (e.g. /de/..., /fr/...)
        $languagePrefix = null;
        $segments = explode('/', $uri);
        if (in_array(Str::lower($segments[0]), ['de', 'fr', 'es', 'it', 'ar', 'ru', 'ch'], true)) {
            $languagePrefix = array_shift($segments);
            $uri = implode('/', $segments);
        }

        if (empty($uri)) {
            return redirect()->route('website.home', [], 301);
        }

        // 2. Direct static mappings
        $staticMap = [
            'about' => route('website.pages.show', 'about-etrotours'),
            'contact' => route('website.contact.index'),
            'services' => route('website.services'),
            'stories' => route('website.blogs.index'),
            'attractions' => route('website.destinations.index'),
            'sights' => route('website.destinations.index'),
            'cities' => route('website.destinations.index'),
            'accommodations' => route('website.home'),
            'testimonials' => route('website.home'),
            'faqs' => route('website.pages.show', 'why-etrotours'),
            'dyks' => route('website.blogs.index'),
            'tailor_made/tours' => route('website.tailor_made.index'),
            'policies/Cookies' => route('website.pages.show', 'privacy-policy'),
            'policies/Privacy-Policy' => route('website.pages.show', 'privacy-policy'),
            'policies/Terms-&-Conditions' => route('website.pages.show', 'terms-and-conditions'),
        ];

        if (isset($staticMap[$uri])) {
            return redirect($staticMap[$uri], 301);
        }

        // 3. Category / Listing routes
        if (Str::startsWith($uri, ['tour-packages', 'cruise', 'tours', 'service-category', 'accommodation_categories'])) {
            // Check if exact package exists matching second segment
            if (count($segments) >= 2) {
                $rawSlug = urldecode(end($segments));
                $cleanSlug = Str::slug($rawSlug);

                $pkg = Package::where('slug', $cleanSlug)->orWhere('slug', Str::lower($rawSlug))->first();
                if ($pkg) {
                    $route = match ($pkg->package_type) {
                        'day_tour', 'shore_excursion' => route('website.day_tours.show', $pkg->slug),
                        'travel_package' => route('website.tour_packages.show', $pkg->slug) . '/',
                        'nile_cruise' => route('website.nile_cruises.show', $pkg->slug),
                        default => route('website.trips.show', $pkg->slug),
                    };
                    return redirect($route, 301);
                }
            }

            if (Str::startsWith($uri, 'cruise')) {
                return redirect()->route('website.nile_cruises.index', [], 301);
            }

            if (Str::startsWith($uri, 'tours')) {
                return redirect()->route('website.day_tours.index', [], 301);
            }

            return redirect()->route('website.travel_packages.index', [], 301);
        }

        // 4. Cities routes (e.g. cities/luxor, cities/cairo/tours)
        if (Str::startsWith($uri, 'cities/')) {
            $cityName = urldecode($segments[1] ?? '');
            $cleanCitySlug = Str::slug($cityName);

            $city = City::where('slug', $cleanCitySlug)->orWhere('slug', Str::lower($cityName))->first();
            if ($city) {
                return redirect()->route('website.destinations.show', $city->slug, 301);
            }

            return redirect()->route('website.destinations.index', [], 301);
        }

        // 5. Blog routes (e.g. blog/..., blog-category/..., tags/...)
        if (Str::startsWith($uri, ['blog/', 'blog-category/', 'tags/'])) {
            $rawSlug = urldecode(end($segments));
            $cleanSlug = Str::slug($rawSlug);

            $article = Article::where('slug', $cleanSlug)->orWhere('slug', Str::lower($rawSlug))->first();
            if ($article) {
                return redirect()->route('website.blogs.show', $article->slug, 301);
            }

            $cat = ArticleCategory::where('slug', $cleanSlug)->orWhere('slug', Str::lower($rawSlug))->first();
            if ($cat) {
                return redirect()->route('website.blogs.category', $cat->slug, 301);
            }

            return redirect()->route('website.blogs.index', [], 301);
        }

        // 6. Attractions / Sights routes (e.g. sights/luxor-temple, attractions/temples-of-egypt)
        if (Str::startsWith($uri, ['attractions/', 'sights/'])) {
            $rawSlug = urldecode(end($segments));
            $cleanSlug = Str::slug($rawSlug);

            $attraction = Attraction::where('slug', $cleanSlug)->orWhere('slug', Str::lower($rawSlug))->first();
            if ($attraction) {
                return redirect()->route('website.attractions.show', $attraction->slug, 301);
            }

            return redirect()->route('website.destinations.index', [], 301);
        }

        // 7. Generic fallback attempt matching Package, Article, or Destination
        $lastSegment = urldecode(end($segments));
        $cleanSlug = Str::slug($lastSegment);

        $pkg = Package::where('slug', $cleanSlug)->first();
        if ($pkg) {
            $route = match ($pkg->package_type) {
                'day_tour', 'shore_excursion' => route('website.day_tours.show', $pkg->slug),
                'travel_package' => route('website.tour_packages.show', $pkg->slug) . '/',
                'nile_cruise' => route('website.nile_cruises.show', $pkg->slug),
                default => route('website.trips.show', $pkg->slug),
            };
            return redirect($route, 301);
        }

        $article = Article::where('slug', $cleanSlug)->first();
        if ($article) {
            return redirect()->route('website.blogs.show', $article->slug, 301);
        }

        // Final fallback: 301 Redirect to Homepage so indexed links never 404
        return redirect()->route('website.home', [], 301);
    }
}
