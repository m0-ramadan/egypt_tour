<?php

namespace App\Http\Controllers\Website;

use App\Models\Attraction;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttractionsIndexController extends BaseWebsiteController
{
    public function index(Request $request): View
    {
        // All active cities with their attractions count
        $cities = City::query()
            ->where('is_active', true)
            ->withCount(['attractions' => fn($q) => $q->where('is_active', true)])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($city) {
                return [
                    'id'               => $city->id,
                    'name'             => $city->display_name,
                    'slug'             => $city->slug,
                    'image'            => $this->imageUrl(
                        $city->featured_image ?: $city->hero_image,
                        asset('website/photos/home2.webp')
                    ),
                    'attractions_count' => $city->attractions_count,
                    'description'      => \Illuminate\Support\Str::limit(
                        strip_tags($city->display_short_description ?: $city->display_description),
                        120
                    ),
                    'url' => route('website.attractions.by-city', $city->slug),
                ];
            });

        // Featured attractions across all cities
        $featuredAttractions = Attraction::query()
            ->where('is_active', true)
            ->with('city')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->take(12)
            ->get()
            ->map(function ($attraction) {
                return [
                    'id'          => $attraction->id,
                    'name'        => $attraction->display_name,
                    'slug'        => $attraction->slug,
                    'city'        => $attraction->city?->display_name,
                    'image'       => $this->imageUrl(
                        $attraction->image ?: $attraction->city?->featured_image,
                        asset('website/photos/home2.webp')
                    ),
                    'description' => \Illuminate\Support\Str::limit(
                        strip_tags($attraction->display_short_description ?: $attraction->display_description),
                        100
                    ),
                    'url' => route('website.attractions.show', $attraction->slug),
                ];
            });

        return view('website.pages.attractions.index', compact('cities', 'featuredAttractions'));
    }

    public function byCity(Request $request, string $citySlug): View
    {
        $city = City::where('slug', $citySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $attractions = Attraction::query()
            ->where('is_active', true)
            ->where('city_id', $city->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        $attractions->getCollection()->transform(function ($attraction) {
            return [
                'id'          => $attraction->id,
                'name'        => $attraction->display_name,
                'slug'        => $attraction->slug,
                'image'       => $this->imageUrl(
                    $attraction->image,
                    asset('website/photos/home2.webp')
                ),
                'description' => \Illuminate\Support\Str::limit(
                    strip_tags($attraction->display_short_description ?: $attraction->display_description),
                    120
                ),
                'url' => route('website.attractions.show', $attraction->slug),
            ];
        });

        $heroImage = $this->imageUrl(
            $city->hero_image ?: $city->featured_image,
            asset('website/photos/home2.webp')
        );

        return view('website.pages.attractions.by-city', compact('city', 'attractions', 'heroImage'));
    }
}
