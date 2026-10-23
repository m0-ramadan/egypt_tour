<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Attraction;
use App\Models\City;
use App\Models\Page;
use App\Models\Package;
use App\Support\LocaleNormalizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Resolves legacy / alternative website URLs and renders the matching (or
 * closest) real page in place.
 *
 * Historically this controller answered every unknown URL with a 301 redirect
 * to a category page or the homepage, which hid genuinely missing pages from
 * search engines and still produced "not found" reports. It now always
 * responds with HTTP 200 at the requested URL and exposes the modern URL via
 * rel=canonical, so the page stays accessible without any redirect.
 */
class LegacyRedirectController extends Controller
{
    /**
     * URL prefixes that used to serve a package on the previous site.
     */
    private const PACKAGE_PREFIXES = [
        'tour-packages', 'tour-package', 'package', 'packages',
        'trip', 'trips', 'tour', 'tours',
        'day-tour', 'day-tours',
        'cruise', 'cruises', 'nile-cruise', 'nile-cruises',
        'egypt-tours', 'luxury-egypt-tours',
    ];

    public function handle(Request $request, ?string $path = null)
    {
        if ($request->attributes->get('legacy.resolved')) {
            // A resolved page itself asked for the legacy handler: stop here.
            return $this->renderHome();
        }

        $request->attributes->set('legacy.resolved', true);

        [$locale, $uri] = $this->splitLocale((string) ($path ?? $request->getPathInfo()));

        if ($locale !== null) {
            app()->setLocale($locale);
        }

        return $this->resolve($request, trim($uri, '/'));
    }

    private function resolve(Request $request, string $uri)
    {
        if ($uri === '') {
            return $this->renderHome();
        }

        $segments = array_values(array_filter(explode('/', $uri), static fn (string $part): bool => $part !== ''));
        $decoded = array_map(static fn (string $part): string => rawurldecode($part), $segments);
        $first = Str::lower($decoded[0]);
        $last = (string) end($decoded);

        // German "Über" (about) page.
        if (in_array($first, ['über', 'uber', 'ueber'], true)) {
            return $this->renderPageBySlug('about-etrotours') ?? $this->renderHome();
        }

        $static = $this->resolveStatic($request, Str::lower($uri));
        if ($static !== null) {
            return $static;
        }

        // WordPress-style dated permalinks: /2023/09/11/giza-plateau/
        if (preg_match('#^\d{4}/\d{1,2}/\d{1,2}/(.+)$#', $uri, $matches) === 1) {
            return $this->renderArticleBySlug(rawurldecode($matches[1])) ?? $this->renderBlogIndex($request);
        }

        if (in_array($first, ['blog', 'blogs', 'stories', 'news'], true)) {
            $article = count($segments) >= 2 ? $this->findArticle($last) : null;

            return $article ? $this->renderArticle($article) : $this->renderBlogIndex($request);
        }

        if (in_array($first, ['tags', 'tag'], true)) {
            // Tags were dropped in the current data model: the closest page is
            // the blog listing.
            return $this->renderBlogIndex($request);
        }

        if (in_array($first, ['blog-category', 'blog-categories', 'category', 'categories'], true)) {
            $category = $this->findArticleCategory($last);

            return $category ? $this->renderBlogCategory($category) : $this->renderBlogIndex($request);
        }

        if (in_array($first, ['service-category', 'service-categories', 'package-category', 'package-categories'], true)) {
            return $this->renderTravelPackages($request);
        }

        if (in_array($first, ['know-category', 'know-categories', 'knowledge-category'], true)) {
            return $this->renderBlogIndex($request);
        }

        if ($first === 'services') {
            return $this->renderTravelPackages($request);
        }

        if ($first === 'countries') {
            return $this->renderDestinationsIndex($request);
        }

        if ($first === 'policies') {
            return $this->resolvePolicy($uri);
        }

        if (in_array($first, ['accommodation_categories', 'accommodation-categories'], true)) {
            return $this->renderHome();
        }

        if (in_array($first, ['sights', 'sight', 'attractions', 'attraction', 'monuments', 'landmarks'], true) && count($segments) >= 2) {
            return $this->resolveAttraction($request, $last);
        }

        if (in_array($first, self::PACKAGE_PREFIXES, true) && count($segments) >= 2) {
            return $this->resolvePackage($request, $first, $last);
        }

        if (in_array($first, ['cities', 'city', 'destinations', 'destination'], true) && count($segments) >= 2) {
            return $this->resolveCity($request, $decoded[1] ?? $last) ?? $this->renderDestinationsIndex($request);
        }

        return $this->resolveGeneric($request, $last);
    }

    /**
     * Exact matches for well-known legacy paths.
     */
    private function resolveStatic(Request $request, string $uri)
    {
        switch ($uri) {
            case 'about':
            case 'about-us':
            case 'about-egypt-tour-pro':
            case 'who-we-are':
                return $this->renderPageBySlug('about-etrotours') ?? $this->renderHome();

            case 'contact':
            case 'contact-us':
                view()->share('legacyCanonical', route('website.contact.index'));

                return app(ContactController::class)->index();

            case 'services':
            case 'travel-packages':
            case 'travel-pakages':
            case 'egypt/travel-packages':
            case 'egypt/travel-pakages':
                return $this->renderTravelPackages($request);

            case 'blogs':
            case 'blog':
                return $this->renderBlogIndex($request);

            case 'stories':
            case 'dyks':
                return $this->renderBlogIndex($request);

            case 'attractions':
            case 'sights':
            case 'monuments':
                return $this->renderAttractionsIndex($request);

            case 'cities':
            case 'destinations':
                return $this->renderDestinationsIndex($request);

            case 'accommodations':
            case 'review':
            case 'reviews':
            case 'testimonial':
            case 'testimonials':
                return $this->renderHome();

            case 'home':
                return $this->renderHome();

            case 'faqs':
            case 'faq':
                return $this->renderPageBySlug('why-etrotours') ?? $this->renderHome();

            case 'why-us':
            case 'why-choose-us':
            case 'why-egypt-tour-pro':
            case 'why-luxor-and-aswan-travel':
                return $this->renderPageBySlug('why-etrotours') ?? $this->renderHome();

            case 'tailor_made/tours':
            case 'tailor-made-tours':
                view()->share('legacyCanonical', route('website.tailor_made.index'));

                return app(TailorMadeController::class)->index();

            case 'policies/cookies':
            case 'policies/cookie-policy':
            case 'policies/privacy-policy':
                return $this->renderPageBySlug('privacy-policy') ?? $this->renderHome();

            case 'policies/terms-&-conditions':
            case 'policies/terms-and-conditions':
                return $this->renderPageBySlug('terms-and-conditions') ?? $this->renderHome();
        }

        return null;
    }

    /**
     * Translated policy URLs (e.g. /fr/policies/politique-de-confidentialité).
     */
    private function resolvePolicy(string $uri)
    {
        $uri = Str::lower($uri);

        if (Str::contains($uri, ['confidential', 'privacy', 'cookie', 'datenschutz'])) {
            return $this->renderPageBySlug('privacy-policy') ?? $this->renderHome();
        }

        if (Str::contains($uri, ['condition', 'terms', 'agb'])) {
            return $this->renderPageBySlug('terms-and-conditions') ?? $this->renderHome();
        }

        return $this->renderHome();
    }

    private function resolvePackage(Request $request, string $prefix, string $key)
    {
        $package = ctype_digit($key)
            ? $this->findLegacyPackage((int) $key, $this->legacySourceFor($prefix))
            : $this->findPackageBySlug($key);

        return $package ? $this->renderPackage($package) : $this->renderPackageListing($request, $prefix);
    }

    private function resolveAttraction(Request $request, string $key)
    {
        $attraction = $this->findAttraction($key);

        return $attraction ? $this->renderAttraction($request, $attraction) : $this->renderAttractionsIndex($request);
    }

    private function resolveCity(Request $request, string $key)
    {
        $city = $this->findCity($key);

        if (! $city) {
            return null;
        }

        view()->share('legacyCanonical', route('website.destinations.show', $city->slug));

        return app(DestinationController::class)->show($request, $city->slug);
    }

    private function resolveGeneric(Request $request, string $key)
    {
        if ($key === '') {
            return $this->renderHome();
        }

        $package = $this->findPackageBySlug($key);
        if ($package) {
            return $this->renderPackage($package);
        }

        $article = $this->findArticle($key);
        if ($article) {
            return $this->renderArticle($article);
        }

        $page = $this->findPage($key);
        if ($page) {
            return $this->renderPage($page);
        }

        $attraction = $this->findAttraction($key);
        if ($attraction) {
            return $this->renderAttraction($request, $attraction);
        }

        $city = $this->findCity($key);
        if ($city) {
            return $this->resolveCity($request, $city->slug);
        }

        return $this->renderHome();
    }

    /*
    |--------------------------------------------------------------------------
    | Renderers — every one responds with HTTP 200 at the requested URL.
    |--------------------------------------------------------------------------
    */

    private function renderHome()
    {
        view()->share('legacyCanonical', route('website.home'));

        return app()->call([app(HomeController::class), 'index']);
    }

    private function renderPackage(Package $package)
    {
        view()->share('legacyCanonical', $this->packageCanonical($package));

        return app(TripController::class)->show($package->slug);
    }

    private function renderPackageListing(Request $request, string $prefix)
    {
        if (in_array($prefix, ['tours', 'tour', 'day-tour', 'day-tours'], true)) {
            view()->share('legacyCanonical', route('website.day_tours.index'));

            return app(DayTourController::class)->index($request);
        }

        if (in_array($prefix, ['cruise', 'cruises', 'nile-cruise', 'nile-cruises'], true)) {
            view()->share('legacyCanonical', route('website.nile_cruises.index'));

            return app(NileCruiseController::class)->index($request);
        }

        return $this->renderTravelPackages($request);
    }

    private function renderTravelPackages(Request $request)
    {
        view()->share('legacyCanonical', route('website.travel_packages.index'));

        return app(PackageController::class)->index($request);
    }

    private function renderBlogIndex(Request $request)
    {
        view()->share('legacyCanonical', route('website.blogs.index'));

        return app(BlogController::class)->index($request);
    }

    private function renderBlogCategory(ArticleCategory $category)
    {
        view()->share('legacyCanonical', route('website.blogs.category', $category->slug));

        return app(BlogController::class)->category($category->slug);
    }

    private function renderArticle(Article $article)
    {
        view()->share('legacyCanonical', route('website.blogs.show', $article->slug));

        return app(BlogController::class)->show($article->slug);
    }

    private function renderAttraction(Request $request, Attraction $attraction)
    {
        view()->share('legacyCanonical', route('website.attractions.show', $attraction->slug));

        return app(AttractionController::class)->show($request, $attraction->slug);
    }

    private function renderAttractionsIndex(Request $request)
    {
        view()->share('legacyCanonical', route('website.attractions.index'));

        return app(AttractionsIndexController::class)->index($request);
    }

    private function renderDestinationsIndex(Request $request)
    {
        view()->share('legacyCanonical', route('website.destinations.index'));

        return app(DestinationController::class)->index($request);
    }

    private function renderPage(Page $page)
    {
        view()->share('legacyCanonical', route('website.pages.show', $page->slug));

        return app(PageController::class)->renderPage($page);
    }

    private function renderPageBySlug(string $slug)
    {
        $page = $this->findPage($slug);

        return $page ? $this->renderPage($page) : null;
    }

    private function renderArticleBySlug(string $slug)
    {
        $article = $this->findArticle($slug);

        return $article ? $this->renderArticle($article) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Lookups
    |--------------------------------------------------------------------------
    */

    private function findLegacyPackage(int $id, string $sourceTable): ?Package
    {
        $sources = [$sourceTable, $sourceTable === 'tours' ? 'packages' : 'tours'];

        foreach ($sources as $source) {
            $targetId = DB::table('legacy_import_maps')
                ->where('source_table', $source)
                ->where('source_id', $id)
                ->where('target_table', 'packages')
                ->value('target_id');

            if (! $targetId) {
                continue;
            }

            $package = Package::query()
                ->where('is_active', true)
                ->whereKey((int) $targetId)
                ->first();

            if ($package) {
                return $package;
            }
        }

        return Package::query()->where('is_active', true)->whereKey($id)->first();
    }

    private function legacySourceFor(string $prefix): string
    {
        return in_array($prefix, ['tours', 'tour', 'day-tour', 'day-tours'], true) ? 'tours' : 'packages';
    }

    private function findPackageBySlug(string $key): ?Package
    {
        $slug = Str::slug($key) ?: $key;

        $package = Package::query()
            ->where('is_active', true)
            ->where(function ($query) use ($key, $slug): void {
                $query->where('slug', $slug)
                    ->orWhere('slug', Str::lower($key))
                    ->orWhere('slug', rawurldecode($key));
            })
            ->first();

        return $package ?: $this->findPackageByFuzzySlug($key);
    }

    private function findPackageByFuzzySlug(string $key): ?Package
    {
        $needleSlug = Str::slug($key);
        $tokens = $this->fuzzyTokens($key);

        if (count($tokens) < 2) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;
        $bestLengthDelta = PHP_INT_MAX;

        foreach (Package::query()->where('is_active', true)->get(['id', 'slug', 'package_type']) as $candidate) {
            $score = $this->tokenScore($tokens, $candidate->slug);
            $lengthDelta = abs(strlen($candidate->slug) - strlen($needleSlug));

            // Prefer more matching words, then the slug closest in length.
            if ($score > $bestScore || ($score === $bestScore && $lengthDelta < $bestLengthDelta)) {
                $bestScore = $score;
                $bestLengthDelta = $lengthDelta;
                $best = $candidate;
            }
        }

        return $bestScore >= 0.6 ? $best : null;
    }

    /**
     * Significant words of a legacy slug (numeric tokens are kept).
     *
     * @return array<int, string>
     */
    private function fuzzyTokens(string $key): array
    {
        return array_values(array_filter(
            explode('-', Str::slug($key)),
            static fn (string $token): bool => ($token !== '' && ctype_digit($token)) || (strlen($token) > 2 && ! in_array($token, [
                'the', 'and', 'with', 'from', 'for', 'into', 'tour', 'tours', 'trip', 'trips',
                'package', 'packages', 'day', 'days', 'night', 'nights', 'your', 'our',
            ], true))
        ));
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function tokenScore(array $tokens, string $slug): float
    {
        if ($tokens === []) {
            return 0.0;
        }

        $matched = 0;
        foreach ($tokens as $token) {
            $hit = ctype_digit($token)
                ? preg_match('/(?:^|-)' . preg_quote($token, '/') . '(?:-|$)/', $slug) === 1
                : str_contains($slug, $token);

            if ($hit) {
                $matched++;
            }
        }

        return $matched / count($tokens);
    }

    private function findArticle(string $key): ?Article
    {
        $slug = Str::slug($key) ?: $key;

        $article = Article::query()
            ->active()
            ->published()
            ->where(fn ($query) => $query->where('slug', $slug)->orWhere('slug', Str::lower($key)))
            ->first();

        if ($article) {
            return $article;
        }

        $tokens = $this->fuzzyTokens($key);
        if (count($tokens) < 2) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;
        $bestLengthDelta = PHP_INT_MAX;

        foreach (Article::query()->active()->published()->get(['id', 'slug']) as $candidate) {
            $score = $this->tokenScore($tokens, $candidate->slug);
            $lengthDelta = abs(strlen($candidate->slug) - strlen($slug));

            if ($score > $bestScore || ($score === $bestScore && $lengthDelta < $bestLengthDelta)) {
                $bestScore = $score;
                $bestLengthDelta = $lengthDelta;
                $best = $candidate;
            }
        }

        return $bestScore >= 0.6 ? $best : null;
    }

    private function findArticleCategory(string $key): ?ArticleCategory
    {
        $slug = Str::slug($key) ?: $key;

        return ArticleCategory::query()
            ->where(fn ($query) => $query->where('slug', $slug)->orWhere('slug', Str::lower($key)))
            ->first();
    }

    private function findPage(string $key): ?Page
    {
        $slug = Str::slug($key) ?: $key;

        return Page::query()
            ->publiclyVisible()
            ->where(fn ($query) => $query->where('slug', $slug)->orWhere('slug', Str::lower($key)))
            ->first();
    }

    private function findAttraction(string $key): ?Attraction
    {
        $slug = Str::slug($key) ?: $key;

        $attraction = Attraction::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('slug', $slug)->orWhere('slug', Str::lower($key)))
            ->first();

        if ($attraction) {
            return $attraction;
        }

        $needle = Str::slug($key);
        if ($needle === '') {
            return null;
        }

        return Attraction::query()
            ->where('is_active', true)
            ->where('slug', 'like', '%' . $needle . '%')
            ->first();
    }

    private function findCity(string $key): ?City
    {
        $slug = Str::slug($key) ?: $key;

        $city = City::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('slug', $slug)->orWhere('slug', Str::lower($key)))
            ->first();

        if ($city) {
            return $city;
        }

        // Legacy URLs occasionally use the translated city name (e.g. /de/cities/Kairo).
        $needle = Str::lower(rawurldecode($key));

        foreach (City::query()->where('is_active', true)->get(['id', 'slug', 'name']) as $candidate) {
            foreach ((array) $candidate->name as $value) {
                if (Str::lower(trim((string) $value)) === $needle) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function packageCanonical(Package $package): string
    {
        return match ((string) $package->package_type) {
            'day_tour', 'shore_excursion' => route('website.day_tours.show', $package->slug),
            'nile_cruise' => route('website.nile_cruises.show', $package->slug),
            'travel_package' => route('website.tour_packages.show', $package->slug),
            default => route('website.trips.show', $package->slug),
        };
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function splitLocale(string $uri): array
    {
        $uri = ltrim($uri, '/');

        if ($uri === '') {
            return [null, ''];
        }

        $segments = explode('/', $uri);
        $first = Str::lower(rawurldecode($segments[0]));

        if (! in_array($first, app(LocaleNormalizer::class)->supported(), true)) {
            return [null, $uri];
        }

        array_shift($segments);

        return [$first, implode('/', $segments)];
    }
}
