<?php

namespace App\Http\Controllers\Website;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends BaseWebsiteController
{
    public function show(string $slug): View|RedirectResponse
    {
        $page = Page::query()
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        if ($page->slug === 'contact-us') {
            return redirect()->route('website.contact.index');
        }

        return $this->renderStaticPage($page);
    }

    public function redirectLegacy(string $slug): RedirectResponse
    {
        return redirect()->route('website.pages.show', ['slug' => $slug], 301);
    }

    public function services()
    {
        return view('website.pages.services');
    }

    public function multiCountry(Request $request)
    {
        $featuredCategory = PackageCategory::query()
            ->where('is_active', true)
            ->where('category_type', 'nile_cruise')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->first();

        $packages = Package::query()
            ->with(['currency', 'highlights', 'tags', 'primaryCountry', 'category'])
            ->where('is_active', true)
            ->where('package_type', 'nile_cruise')
            ->when($request->filled('pricerange'), function ($query) use ($request) {
                match ((string) $request->pricerange) {
                    '1' => $query->where('start_from_price', '<', 1500),
                    '2' => $query->whereBetween('start_from_price', [1500, 2500]),
                    '3' => $query->where('start_from_price', '>', 2500),
                    default => null,
                };
            })
            ->when($request->filled('days'), function ($query) use ($request) {
                match ((string) $request->days) {
                    '1' => $query->where('duration_days', '<', 10),
                    '2' => $query->whereBetween('duration_days', [10, 20]),
                    '3' => $query->where('duration_days', '>', 20),
                    default => null,
                };
            })
            ->when($request->sort === 'price', fn ($query) => $query->orderBy('start_from_price'))
            ->when($request->sort === 'duration', fn ($query) => $query->orderBy('duration_days'))
            ->when(!in_array($request->sort, ['price', 'duration'], true), function ($query) {
                $query->orderByDesc('is_featured')
                    ->orderByRaw('sort_order IS NULL, sort_order ASC')
                    ->latest('published_at')
                    ->latest('id');
            })
            ->paginate(12)
            ->withQueryString();

        $packages->getCollection()->transform(fn (Package $package) => $this->mapMultiCountryCard($package));

        $heroImage = $this->resolveMultiCountryHeroImage(
            $featuredCategory?->image,
            $packages->first()['image'] ?? null
        );

        $pageContent = [
            'badge' => __('Luxury Nile Journeys'),
            'title' => __('Egypt Nile Cruise'),
            'subtitle' => __('Sail through the heart of Egypt with elegant Nile cruise experiences between Luxor and Aswan.'),
            'overview_title' => __('Curated Nile cruise itineraries across timeless Egyptian landmarks'),
            'overview_text' => $featuredCategory
                ? $this->translated($featuredCategory->getRawOriginal('description') ?? $featuredCategory->description)
                : __('Browse handpicked Nile cruise journeys designed around comfort, culture, scenic sailing, and unforgettable moments along the Nile.'),
            'description' => __('Explore thoughtfully selected Nile cruises featuring ancient temples, riverfront sunsets, and seamless travel between Egypt’s most iconic cities.'),
            'stats_count_label' => __('Cruises'),
            'stats_categories_label' => __('Sailing Options'),
            'stats_featured_label' => __('Featured Sailings'),
            'results_title' => __('Matching Nile Cruises'),
            'results_count_label' => __('Cruises'),
            'cta_label' => __('View Cruise'),
            'empty_title' => __('No Nile cruises found'),
            'empty_text' => __('Please change the filters or add Nile cruise packages from the admin panel.'),
            'breadcrumb_title' => __('Egypt Nile Cruise'),
        ];

        $stats = [
            'count' => $packages->total(),
            'featured' => Package::query()
                ->where('is_active', true)
                ->where('package_type', 'nile_cruise')
                ->where('is_featured', true)
                ->count(),
            'categories' => PackageCategory::query()
                ->where('is_active', true)
                ->where('category_type', 'nile_cruise')
                ->count(),
        ];

        return view('website.pages.multi-country', compact('packages', 'heroImage', 'pageContent', 'stats'));
    }

    private function mapMultiCountryCard(Package $package): array
    {
        $highlights = $this->localizedTagNames($package->highlights, 3);

        if (empty($highlights)) {
            $highlights = $this->localizedTagNames($package->tags, 3);
        }

        return [
            'title' => $this->translated($package->getRawOriginal('title') ?? $package->title),
            'url' => $this->packageRoute($package),
            'image' => $this->resolvePackageImage($package),
            'price' => $this->packagePrice($package),
            'duration' => $this->packageDuration($package),
            'tour_type' => $this->packageTourTypeLabel($package),
            'description' => $this->shortText(
                $package->getRawOriginal('short_description') ?: $package->getRawOriginal('description'),
                220
            ),
            'tags' => $highlights,
            'country' => $this->localizedModelText($package->primaryCountry, 'name'),
            'badge' => $package->is_featured ? __('Featured') : null,
        ];
    }

    private function resolvePackageImage(Package $package): string
    {
        $galleryImage = null;

        if (is_array($package->gallery_images) && isset($package->gallery_images[0])) {
            $galleryImage = is_array($package->gallery_images[0])
                ? ($package->gallery_images[0]['path'] ?? $package->gallery_images[0]['url'] ?? null)
                : $package->gallery_images[0];
        }

        return $this->imageUrl($package->featured_image ?: $galleryImage, 'website/photos/ship-7.jpg');
    }

    private function resolveMultiCountryHeroImage(?string $categoryImage, ?string $packageImage): string
    {
        if ($categoryImage) {
            return $this->imageUrl($categoryImage, 'website/photos/ship-7.jpg');
        }

        if ($packageImage) {
            return $packageImage;
        }

        return asset('website/photos/ship-7.jpg');
    }

    private function renderStaticPage(Page $page): View
    {
        $pageTitle = $page->display_title ?: Str::headline(str_replace('-', ' ', $page->slug));
        $seoTitle = $page->display_seo_title ?: $pageTitle . ' - Etro Tours';
        $pageBody = $page->display_body;
        $pageExcerpt = $page->display_seo_description ?: $this->plainText($pageBody, 200);
        $heroImage = $this->imageUrl($page->featured_image, 'website/photos/home2.webp');

        return view('website.pages.static-pages.index', compact(
            'page',
            'pageTitle',
            'seoTitle',
            'pageBody',
            'pageExcerpt',
            'heroImage'
        ));
    }
}
