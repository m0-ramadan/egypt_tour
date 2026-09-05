<?php

namespace App\Http\Controllers\Website;

use App\Models\NileCruiseCategory;
use App\Models\NileCruiseType;
use App\Models\Package;
use Illuminate\Http\Request;

class NileCruiseController extends BaseWebsiteController
{
    /**
     * Display the main Egypt Nile Cruises landing page.
     */
    public function index(Request $request)
    {
        $types = NileCruiseType::query()
            ->where('is_active', true)
            ->withCount(['packages' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $totalPackages = Package::query()
            ->where('is_active', true)
            ->where('package_type', 'nile_cruise')
            ->count();

        $pageContent = [
            'badge' => __('Nile River Cruises'),
            'title' => __('Egypt Nile Cruises & River Voyages'),
            'subtitle' => __('Sail through 5,000 years of Egyptian history aboard luxury 5-star Nile cruise ships, traditional Dahabiya sailboats, and Lake Nasser vessels.'),
            'overview_title' => __('Unforgettable Nile Cruise Journeys'),
            'overview_text' => __('Discover the ultimate Egypt Nile Cruise experience connecting Luxor, Aswan, and Abu Simbel. Explore iconic ancient temples, royal tombs in the Valley of the Kings, and timeless river vistas with personalized 5-star hospitality.'),
        ];

        return view('website.pages.nile-cruises.index', compact('types', 'totalPackages', 'pageContent'));
    }

    /**
     * Display the Luxor and Aswan Nile Cruises landing page with categories.
     */
    public function showLuxorAswan(Request $request)
    {
        $type = NileCruiseType::query()
            ->where('slug', 'luxor-aswan-nile-cruises')
            ->where('is_active', true)
            ->firstOrFail();

        $categories = NileCruiseCategory::query()
            ->where('nile_cruise_type_id', $type->id)
            ->where('is_active', true)
            ->withCount(['packages' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $totalPackages = Package::query()
            ->where('is_active', true)
            ->where(function ($q) use ($type) {
                $q->where('nile_cruise_type_id', $type->id)
                  ->orWhere(fn($sub) => $sub->where('package_type', 'nile_cruise')->whereNull('nile_cruise_type_id'));
            })
            ->count();

        $featuredPackagesQuery = Package::query()
            ->with(['currency', 'category', 'primaryCountry'])
            ->where('is_active', true)
            ->where(function ($q) use ($type) {
                $q->where('nile_cruise_type_id', $type->id)
                  ->orWhere(fn($sub) => $sub->where('package_type', 'nile_cruise')->whereNull('nile_cruise_type_id'));
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->take(6)
            ->get();

        $featuredPackages = $featuredPackagesQuery->map(fn($pkg) => $this->formatPackage($pkg));

        $pageContent = [
            'badge' => __('Luxor & Aswan'),
            'title' => $type->display_name ?: __('Luxor & Aswan Nile Cruises'),
            'subtitle' => $type->display_short_description ?: __('Sail the classic route between Luxor and Aswan with handpicked 5-star Nile cruise ships.'),
            'overview_title' => __('Choose Your Nile Cruise Category'),
            'overview_text' => $type->display_description ?: __('From budget-friendly 5-star standard cruise ships to royal ultra-luxury vessels, select your preferred comfort tier for an extraordinary Nile river adventure.'),
        ];

        return view('website.pages.nile-cruises.luxor-aswan', compact(
            'type',
            'categories',
            'totalPackages',
            'featuredPackages',
            'pageContent'
        ));
    }

    /**
     * Display a cruise listing page filtered by cruise type (e.g. Dahabiya or Lake Nasser).
     */
    public function showType(Request $request, string $typeSlug)
    {
        $type = NileCruiseType::query()
            ->where('slug', $typeSlug)
            ->where('is_active', true)
            ->firstOrFail();

        $search = trim((string) $request->input('q', ''));

        $query = Package::query()
            ->with(['currency', 'category', 'primaryCountry', 'nileCruiseCategory'])
            ->where('is_active', true)
            ->where(function ($q) use ($type) {
                $q->where('nile_cruise_type_id', $type->id)
                  ->orWhere(function ($sub) use ($type) {
                      $sub->where('package_type', 'nile_cruise');
                      if ($type->slug === 'dahabiya-nile-cruise') {
                          $sub->where(function ($w) {
                              $w->where('title', 'like', '%dahabiya%')
                                ->orWhere('title', 'like', '%dahabeya%')
                                ->orWhere('title', 'like', '%دهبية%')
                                ->orWhere('title', 'like', '%دهبيه%')
                                ->orWhere('title', 'like', '%داهابيا%')
                                ->orWhere('slug', 'like', '%dahabiya%')
                                ->orWhere('slug', 'like', '%dahabeya%');
                          });
                      } elseif ($type->slug === 'lake-nasser-cruise') {
                          $sub->where(function ($w) {
                              $w->where('title', 'like', '%lake nasser%')
                                ->orWhere('title', 'like', '%nasser%')
                                ->orWhere('title', 'like', '%ناصر%')
                                ->orWhere('title', 'like', '%بحيرة ناصر%')
                                ->orWhere('title', 'like', '%abu simbel%')
                                ->orWhere('title', 'like', '%أبو سمبل%')
                                ->orWhere('slug', 'like', '%nasser%')
                                ->orWhere('slug', 'like', '%lake-nasser%')
                                ->orWhere('slug', 'like', '%abu-simbel%');
                          });
                      }
                  });
            })
            ->when($search !== '', function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('subtitle', 'like', $term)
                        ->orWhere('short_description', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC');

        $paginated = $query->paginate(12)->withQueryString();
        $packages = $paginated->through(fn($pkg) => $this->formatPackage($pkg));

        $stats = [
            'count' => $paginated->total(),
            'categories' => 1,
            'featured' => Package::query()
                ->where('is_active', true)
                ->where(function ($q) use ($type) {
                    $q->where('nile_cruise_type_id', $type->id)
                      ->orWhere(function ($sub) use ($type) {
                          $sub->where('package_type', 'nile_cruise');
                          if ($type->slug === 'dahabiya-nile-cruise') {
                              $sub->where(fn($w) => $w->where('title', 'like', '%dahabiya%')->orWhere('title', 'like', '%دهبية%')->orWhere('slug', 'like', '%dahabiya%'));
                          } elseif ($type->slug === 'lake-nasser-cruise') {
                              $sub->where(fn($w) => $w->where('title', 'like', '%lake nasser%')->orWhere('title', 'like', '%ناصر%')->orWhere('slug', 'like', '%nasser%'));
                          }
                      });
                })
                ->where('is_featured', true)
                ->count(),
        ];

        $pageContent = [
            'badge' => __('Nile Cruise'),
            'title' => $type->display_name,
            'subtitle' => $type->display_short_description,
            'overview_title' => $type->display_name,
            'overview_text' => $type->display_description,
            'empty_title' => __('No Cruises Found'),
            'empty_text' => __('We currently have no available cruises matching this category. Contact our travel planners for custom bookings!'),
        ];

        return view('website.pages.nile-cruises.listing', compact(
            'type',
            'packages',
            'paginated',
            'stats',
            'search',
            'pageContent'
        ));
    }

    /**
     * Display a cruise listing page filtered by Luxor & Aswan Category (Standard, Deluxe, Ultra Deluxe, Luxury).
     */
    public function showLuxorAswanCategory(Request $request, string $categorySlug)
    {
        $type = NileCruiseType::query()
            ->where('slug', 'luxor-aswan-nile-cruises')
            ->where('is_active', true)
            ->firstOrFail();

        $category = NileCruiseCategory::query()
            ->where('nile_cruise_type_id', $type->id)
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $search = trim((string) $request->input('q', ''));

        $query = Package::query()
            ->with(['currency', 'category', 'primaryCountry', 'nileCruiseType'])
            ->where('is_active', true)
            ->where('nile_cruise_category_id', $category->id)
            ->where(function ($q) use ($type) {
                $q->where('nile_cruise_type_id', $type->id)
                  ->orWhereNull('nile_cruise_type_id');
            })
            ->when($search !== '', function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('subtitle', 'like', $term)
                        ->orWhere('short_description', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('sort_order IS NULL, sort_order ASC');

        $paginated = $query->paginate(12)->withQueryString();
        $packages = $paginated->through(fn($pkg) => $this->formatPackage($pkg));

        $stats = [
            'count' => $paginated->total(),
            'categories' => 1,
            'featured' => Package::where('is_active', true)->where('nile_cruise_category_id', $category->id)->where('is_featured', true)->count(),
        ];

        $pageContent = [
            'badge' => $type->display_name,
            'title' => $category->display_name,
            'subtitle' => $category->display_short_description,
            'overview_title' => $category->display_name,
            'overview_text' => $category->display_description ?: $category->display_short_description,
            'empty_title' => __('No Cruises Found in this Category'),
            'empty_text' => __('There are currently no packages assigned to this category. Contact us to customize a trip for you!'),
        ];

        return view('website.pages.nile-cruises.listing', compact(
            'type',
            'category',
            'packages',
            'paginated',
            'stats',
            'search',
            'pageContent'
        ));
    }

    /**
     * Format package model into standard view array.
     */
    protected function formatPackage(Package $package): array
    {
        $package->loadMissing(['currency', 'highlights', 'tags', 'prices']);
        $card = $this->packageCard($package);
        $card['button_text'] = __('Explore Journey');
        return $card;
    }
}
