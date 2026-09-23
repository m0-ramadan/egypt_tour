<?php

use App\Http\Controllers\Website\AttractionController;
use App\Http\Controllers\Website\AttractionsIndexController;
use App\Http\Controllers\Website\BlogController;
use App\Http\Controllers\Website\CheckoutController;
use App\Http\Controllers\Website\ContactController;
use App\Http\Controllers\Website\DayTourController;
use App\Http\Controllers\Website\DestinationController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\InquiryController;
use App\Http\Controllers\Website\NewsletterController;
use App\Http\Controllers\Website\NileCruiseController;
use App\Http\Controllers\Website\PackageController;
use App\Http\Controllers\Website\PageController;
use App\Http\Controllers\Website\SearchController;
use App\Http\Controllers\Website\SitemapController;
use App\Http\Controllers\Website\TailorMadeController;
use App\Http\Controllers\Website\TourController;
use App\Http\Controllers\Website\TravelPackageController;
use App\Http\Controllers\Website\TripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website Routes
|--------------------------------------------------------------------------
| Clean routes without duplication
| Includes legacy SEO support + aliases
*/
// use Spatie\Sitemap\SitemapGenerator;

// SitemapGenerator::create('https://egypttourpro.com')->writeToFile(public_path('sitemap.xml'));

Route::name('website.')->group(function () {
    Route::get('/lang/{locale}', function (Illuminate\Http\Request $request, $locale) {
        $normalizer = app(\App\Support\LocaleNormalizer::class);
        $locale = $normalizer->normalize((string) $locale);

        $supportedLocales = \Illuminate\Support\Facades\Cache::remember('supported_locales', 3600, function () {
            return \App\Models\Language::where('is_active', true)->pluck('code')->toArray();
        });

        $supportedLocales = $normalizer->normalizeList(array_merge(
            $supportedLocales,
            (array) config('translation.supported_locales', ['en', 'ar'])
        ));

        if (in_array($locale, $supportedLocales, true)) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->back();
    })->name('lang.switch');

    /*
    |--------------------------------------------------------------------------
    | Offers
    |--------------------------------------------------------------------------
    */
    Route::get('/latest-offers', [TourController::class, 'offers'])->name('offers');

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

    Route::get('/', [HomeController::class, 'index'])->name('home');

    /*
    |--------------------------------------------------------------------------
    | Nile Cruises
    |--------------------------------------------------------------------------
    */
    Route::prefix('nile-cruises')->name('nile_cruises.')->controller(NileCruiseController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/luxor-aswan-nile-cruises', 'showLuxorAswan')->name('luxor_aswan');
        Route::get('/luxor-aswan-nile-cruises/{categorySlug}', 'showLuxorAswanCategory')->name('luxor_aswan.category');
        Route::get('/{typeSlug}', 'showType')->where('typeSlug', 'dahabiya-nile-cruise|lake-nasser-cruise')->name('type');
    });

    Route::get('/nile-cruises/{slug}', [PackageController::class, 'showNileCruise'])
        ->name('nile_cruises.show');

    /*
    |--------------------------------------------------------------------------
    | Day Tours
    |--------------------------------------------------------------------------
    */
    Route::get('/day-tours', [DayTourController::class, 'index'])->name('day_tours.index');
    Route::get('/day-tours/{destination}-day-tours', [PackageController::class, 'dayTourDestination'])
        ->where('destination', 'cairo|luxor|aswan|hurghada|sharm-el-sheikh|marsa-alam|dahab')
        ->name('day_tours.destination');
    Route::get('/day-tours/{slug}', [PackageController::class, 'showDayTour'])->name('day_tours.show');
    Route::get('/Egypt/day-tours', [DayTourController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Travel Packages
    |--------------------------------------------------------------------------
    */
    Route::get('/tour-packages', [PackageController::class, 'index'])->name('travel_packages.index');
    Route::permanentRedirect('/travel-packages', '/tour-packages');
    Route::permanentRedirect('/Egypt/travel-packages', '/tour-packages');
    Route::permanentRedirect('/Egypt/travel-pakages', '/tour-packages');

    /*
    |--------------------------------------------------------------------------
    | Static Pages
    |--------------------------------------------------------------------------
    */
    Route::controller(PageController::class)->group(function () {
        Route::get('/multi-country', 'multiCountry')
            ->name('multi_country');

        Route::get('/multi-country-tours', 'multiCountry')
            ->name('multi_country_tours');

        Route::get('/services', 'services')
            ->name('services');

        Route::get('/why-us', function () {
            return redirect()->route('website.pages.show', ['slug' => 'why-egypt-tour-pro'], 301);
        });

        Route::get('/why-luxor-and-aswan-travel', function () {
            return redirect()->route('website.pages.show', ['slug' => 'why-egypt-tour-pro'], 301);
        });
    });

    Route::controller(ContactController::class)->group(function () {
        Route::get('/contact-us', 'index')
            ->name('contact.index');

        Route::post('/contact-us', 'store')
            ->name('contact.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Destinations
    |--------------------------------------------------------------------------
    */
    Route::prefix('destinations')->name('destinations.')->group(function () {

        Route::get('/', [DestinationController::class, 'index'])->name('index');

        Route::get('/{slug}', [DestinationController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Attractions
    |--------------------------------------------------------------------------
    */
    Route::prefix('attractions')->name('attractions.')->group(function () {
        Route::get('/', [AttractionsIndexController::class, 'index'])->name('index');
        Route::get('/city/{city}', [AttractionsIndexController::class, 'byCity'])->name('by-city');
        Route::get('/{slug}', [AttractionController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Alias Support
    |--------------------------------------------------------------------------
    | Fix old blade calls:
    | route('website.destinations')
    | route('website.blogs')
    | route('website.trips')
    | route('website.tours')
    */
    // Route::get('/destinations', [DestinationController::class, 'index'])
    //     ->name('destinations');

    /*
    |--------------------------------------------------------------------------
    | Blogs
    |--------------------------------------------------------------------------
    */
    Route::prefix('blog')->name('blogs.')->group(function () {

        Route::get('/', [BlogController::class, 'index'])
            ->name('index');

        Route::get('/category/{slug}', [BlogController::class, 'category'])
            ->name('category');

        Route::get('/{slug}', [BlogController::class, 'show'])
            ->name('show');

        Route::get('/{categorySlug}/{slug}', [BlogController::class, 'show'])
            ->name('show.legacy');
    });

    Route::redirect('/blogs', '/blog', 301);
    // Route::get('/blogs', [BlogController::class, 'index'])
    //     ->name('blogs');

    /*
    |--------------------------------------------------------------------------
    | Trips
    |--------------------------------------------------------------------------
    */
    Route::get('/tour-packages/{days}-day-egypt-tours', [PackageController::class, 'duration'])
        ->whereNumber('days')
        ->name('tour_packages.duration');

    Route::get('/luxury-egypt-tours', [PackageController::class, 'category'])
        ->defaults('category', 'egypt-luxury-tours')
        ->name('luxury_egypt_tours');

    Route::get('/egypt-luxury-tours', [PackageController::class, 'category'])
        ->defaults('category', 'egypt-luxury-tours');

    Route::get('/tour-packages/{category}', [PackageController::class, 'category'])
        ->where('category', 'egypt-vacation-packages|private-egypt-tours|egypt-luxury-tours|luxury-egypt-tours|family-egypt-tours')
        ->name('tour_packages.category');

    Route::get('/tour-packages/{slug}', [PackageController::class, 'showTravelPackage'])
        ->name('tour_packages.show');

    Route::prefix('trips')->name('trips.')->group(function () {

        Route::get('/', [PackageController::class, 'index'])
            ->name('all');

        Route::get('/{slug}', [PackageController::class, 'show'])
            ->name('show');
    });

    Route::get('/trips', [PackageController::class, 'index'])
        ->name('trips');

    /*
    |--------------------------------------------------------------------------
    | Tours
    |--------------------------------------------------------------------------
    */
    Route::prefix('tours')->name('tours.')->group(function () {

        Route::get('/', [PackageController::class, 'tours'])
            ->name('all');

        Route::get('/offers', [TourController::class, 'offers'])
            ->name('show.offers');

        Route::get('/{slug}', [TourController::class, 'show'])
            ->name('show');
    });

    // Route::get('/tours', [TourController::class, 'index'])
    //     ->name('tours');

    /*
    |--------------------------------------------------------------------------
    | Package Short URLs
    |--------------------------------------------------------------------------
    */
    Route::get('/package/{slug}/checkout', [CheckoutController::class, 'show'])
        ->name('checkout.show');

    Route::post('/package/{slug}/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('checkout.store');

    Route::get('/checkout/paypal/capture', [CheckoutController::class, 'capturePayPal'])
        ->name('checkout.paypal.capture');

    Route::get('/checkout/payment/{paymentReference}', [CheckoutController::class, 'status'])
        ->middleware('signed')
        ->name('checkout.status');

    Route::get('/package/{slug}', [TripController::class, 'show'])
        ->name('packages.show.simple');

    Route::get('/{country}/package/{slug}', [TripController::class, 'show'])
        ->name('packages.show');

    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */
    Route::post('/newsletter', [NewsletterController::class, 'store'])
        ->name('newsletter.store');

    Route::post('/inquiries', [InquiryController::class, 'store'])
        ->name('inquiries.store');

    Route::post('/enquiry-confirmation', [InquiryController::class, 'store'])
        ->name('enquiries.store');

    /*
    |--------------------------------------------------------------------------
    | Legacy SEO URLs
    |--------------------------------------------------------------------------
    */
    Route::get('/{country}/package/{slug}.html', [TripController::class, 'legacyShow'])
        ->name('legacy.package.show');

    Route::get('/{country}/cruise/{slug}.html', [TourController::class, 'legacyShow'])
        ->name('legacy.cruise.show');

    Route::get('/{slug}.html', [DestinationController::class, 'legacyShow'])
        ->name('legacy.destination.show');

    Route::get('/tailor-made', [TailorMadeController::class, 'index'])
        ->name('tailor_made.index');

    Route::post('/tailor-made', [TailorMadeController::class, 'store'])
        ->name('tailor_made.store');

    Route::get('search/suggestions', [SearchController::class, 'suggestions'])
        ->name('search.suggestions');

    Route::get('search', [SearchController::class, 'index'])
        ->name('search.index');

    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', '[A-Za-z0-9\-]+')
        ->name('pages.show');

    /*
    |--------------------------------------------------------------------------
    | Fallback Legacy URLs Handler (SEO Preservation 301 Redirects)
    |--------------------------------------------------------------------------
    */
    Route::fallback([\App\Http\Controllers\Website\LegacyRedirectController::class, 'handle'])
        ->name('legacy.fallback');
});


Route::get('/payment/paymob/return', [\App\Http\Controllers\Api\PaymobPaymentController::class, 'returnFromCheckout'])
    ->name('paymob.return');
