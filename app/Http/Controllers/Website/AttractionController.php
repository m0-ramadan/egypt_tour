<?php

namespace App\Http\Controllers\Website;

use App\Models\Attraction;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttractionController extends BaseWebsiteController
{
    public function show(Request $request, string $slug): View
    {
        $attraction = Attraction::query()
            ->with(['city.country'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $packages = Package::query()
            ->with(['currency', 'primaryCountry', 'highlights', 'tags', 'cruise', 'category'])
            ->where('is_active', true)
            ->where(function ($query) use ($attraction) {
                $query->whereHas('packageAttractions', function ($q) use ($attraction) {
                    $q->where('attraction_id', $attraction->id);
                });
                if ($attraction->city_id) {
                    $query->orWhereHas('destination', function ($q) use ($attraction) {
                        $q->where('city_id', $attraction->city_id);
                    });
                }
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->latest('id')
            ->paginate(6)
            ->withQueryString();

        $packages->getCollection()->transform(
            fn (Package $package) => $this->packageListingCard($package)
        );

        $relatedAttractions = Attraction::query()
            ->where('is_active', true)
            ->where('city_id', $attraction->city_id)
            ->where('id', '!=', $attraction->id)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $heroImage = $this->imageUrl(
            $attraction->image ?: $attraction->city?->hero_image ?: $attraction->city?->featured_image,
            asset('website/photos/home2.webp')
        );

        $shortDescription = trim(strip_tags(
            $attraction->display_short_description ?: $attraction->display_description
        ));

        $descriptionHtml = $this->cleanHtml($attraction->display_description ?: '');
        $overviewText = Str::limit(
            trim(strip_tags($descriptionHtml ?: $attraction->display_short_description)),
            450
        );

        $pageTitle = $attraction->getTranslation('seo_title')
            ?: $attraction->display_name . ($attraction->city ? ' - ' . $attraction->city->display_name : '');

        $pageDescription = $attraction->getTranslation('seo_description')
            ?: Str::limit(
                $shortDescription !== ''
                    ? $shortDescription
                    : __('Discover :name in :city, opening hours, map location, and top tours visiting this highlight.', [
                        'name' => $attraction->display_name,
                        'city' => $attraction->city?->display_name ?: __('our destinations'),
                    ]),
                170
            );

        $openingHours = $this->translated(
            $attraction->getRawOriginal('opening_hours') ?? $attraction->opening_hours
        );

        return view('website.pages.attractions.show', compact(
            'attraction',
            'packages',
            'relatedAttractions',
            'heroImage',
            'pageTitle',
            'pageDescription',
            'shortDescription',
            'descriptionHtml',
            'overviewText',
            'openingHours'
        ));
    }
}
