<?php

namespace App\Http\Controllers\Website;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TourController extends BaseWebsiteController
{
    public function index(Request $request)
    {
        $tours = Package::query()
            ->with(['currency', 'category', 'primaryCountry'])
            ->where('is_active', true)
            ->whereIn('package_type', ['day_tour', 'shore_excursion'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . $request->q . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('subtitle', 'like', $search)
                        ->orWhere('short_description', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->paginate(12)
            ->withQueryString();

        return view('website.pages.tours.index', compact('tours'));
    }

    public function show(string $slug)
    {
        return app(TripController::class)->show($slug);
    }

    public function legacyShow(string $country, string $slug)
    {
        return $this->show($slug);
    }

    public function offers()
    {
        $offers = Package::query()
            ->with(['currency', 'primaryCountry', 'highlights', 'tags', 'prices'])
            ->where('is_active', true)
            ->whereNotNull('offer_price')
            ->where('offer_price', '>', 0)
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        $offers->getCollection()->transform(function (Package $package) {
            $basePrice = $package->start_from_price;

            if ($basePrice === null && $package->relationLoaded('prices')) {
                $basePrice = $package->prices->first()?->amount;
            }

            $offerPrice = $package->offer_price;
            $symbol = $package->currency?->symbol ?: '$';

            $highlights = $package->relationLoaded('highlights')
                ? $this->localizedTagNames($package->highlights, 2)
                : [];

            if (empty($highlights) && $package->relationLoaded('tags')) {
                $highlights = $this->localizedTagNames($package->tags, 2);
            }

            return [
                'title' => $this->translated($package->getRawOriginal('title') ?? $package->title),
                'description' => $this->shortText(
                    $package->getRawOriginal('short_description') ?: $package->getRawOriginal('description'),
                    190
                ),
                'image' => $this->imageUrl($package->featured_image, 'website/photos/home2.webp'),
                'url' => $this->packageRoute($package),
                'country' => $this->localizedModelText($package->primaryCountry, 'name') ?: __('Offer'),
                'duration' => $this->packageDuration($package),
                'tour_type' => $this->packageTourTypeLabel($package),
                'offer_price' => $symbol . number_format((float) $offerPrice, 0),
                'regular_price' => $basePrice && (float) $basePrice > (float) $offerPrice
                    ? $symbol . number_format((float) $basePrice, 0)
                    : null,
                'savings_percent' => $basePrice && (float) $basePrice > (float) $offerPrice
                    ? (int) round((((float) $basePrice - (float) $offerPrice) / (float) $basePrice) * 100)
                    : null,
                'tags' => $highlights,
            ];
        });

        return view('website.pages.offers', compact('offers'));
    }
}
