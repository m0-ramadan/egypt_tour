<?php

namespace App\Http\Controllers\Website;

use App\Models\Article;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\WebsiteDestinationService;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class HomeController extends BaseWebsiteController
{
    public function index(WebsiteDestinationService $websiteDestinationService)
    {
        $agent = new Agent();
        $agent->setUserAgent((string) request()->userAgent());

        // Build a genuinely smaller response for phones. Keeping the device
        // profile in the cache key prevents a desktop response leaking into
        // the mobile cache (and vice versa).
        $isMobileHome = $agent->isMobile() && !$agent->isTablet();
        $profile = $isMobileHome ? 'mobile' : 'desktop';
        $packageLimit = $isMobileHome ? 3 : 6;
        $destinationLimit = $isMobileHome ? 3 : 6;
        $testimonialLimit = $isMobileHome ? 2 : 6;

        $cacheVersion = (int) Cache::get('website.home.version', 1);
        $cacheKey = 'website.home.v3.' . app()->getLocale() . '.' . $profile . '.' . $cacheVersion;

        $data = Cache::remember($cacheKey, now()->addHour(), function () use (
            $websiteDestinationService,
            $packageLimit,
            $destinationLimit,
            $testimonialLimit
        ) {
            $packages = Package::query()
                ->with(['currency', 'highlights', 'tags', 'prices'])
                ->where('is_active', true)
                ->displayOrder()
                ->limit($packageLimit)
                ->get();

            $featuredPackages = $packages->map(fn(Package $package) => $this->packageCard($package));
            $destinations = $websiteDestinationService->homeDestinations($destinationLimit);

            $latestArticles = Article::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->orderByDesc('is_featured')
                ->latest('published_at')
                ->latest('id')
                ->limit(3)
                ->get()
                ->map(function (Article $article) {
                    return [
                        'title' => $this->translated($article->getRawOriginal('title') ?? $article->title),
                        'excerpt' => $this->shortText($article->getRawOriginal('excerpt') ?: $article->getRawOriginal('content'), 170),
                        'image' => $this->imageUrl('storage/' . $article->featured_image, 'website/photos/home2.webp'),
                        'url' => route('website.blogs.show', $article->slug),
                        'date' => optional($article->published_at ?: $article->created_at)->format('M d, Y'),
                    ];
                });

            $testimonials = Testimonial::query()
                ->where(function ($query) {
                    $query->where('is_active', true)->orWhereNull('is_active');
                })
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->latest('id')
                ->limit($testimonialLimit)
                ->get()
                ->map(function (Testimonial $testimonial) {
                    $name = $testimonial->customer_name ?: 'Guest';

                    return [
                        'name' => $name,
                        'initials' => $testimonial->customer_initials ?: collect(explode(' ', $name))->map(fn($part) => mb_substr($part, 0, 1))->take(2)->implode(''),
                        'rating' => (int) ($testimonial->rating ?: 5),
                        'content' => $this->shortText($testimonial->getRawOriginal('content') ?? $testimonial->content, 260),
                        'is_verified' => (bool) $testimonial->is_verified,
                        'source_url' => $testimonial->source_url,
                        'avatar' => $testimonial->avatar ? $this->imageUrl($testimonial->avatar) : null,
                    ];
                });

            $customHeroHome = Setting::query()->where('key', 'hero_image_home')->value('value');

            return compact('featuredPackages', 'destinations', 'latestArticles', 'testimonials', 'customHeroHome');
        });

        $data['isMobileHome'] = $isMobileHome;
        $data['showHomeNewsletter'] = !$isMobileHome;

        return view('website.pages.home', $data);
    }
}
