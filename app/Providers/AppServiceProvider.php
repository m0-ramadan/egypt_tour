<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Attraction;
use App\Models\City;
use App\Models\Country;
use App\Models\Package;
use App\Models\Testimonial;
use App\Services\WebsiteDestinationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WebsiteDestinationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.bootstrap-5');

        View::composer(['website.layouts.header', 'website.layouts.footer'], function ($view) {
            $view->with('navigationDestinations', app(WebsiteDestinationService::class)->homeDestinations(24));
        });

        $invalidateWebsiteCache = static function (): void {
            Cache::forever('website.home.version', (int) Cache::get('website.home.version', 1) + 1);
        };

        foreach ([Article::class, Attraction::class, City::class, Country::class, Package::class, Testimonial::class] as $model) {
            $model::saved($invalidateWebsiteCache);
            $model::deleted($invalidateWebsiteCache);
        }
    }
}
