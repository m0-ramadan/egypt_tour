@extends('website.layouts.master')

@section('title', __('Home - Egypt Tour Pro'))
@section('description',
    __('Luxury Egypt tours, Nile cruises, private day trips, and tailor-made travel experiences curated by Egypt Tour Pro across Cairo, Luxor, Aswan, and beyond.'))
@section('keywords',
    'Egypt Tour Pro, luxury Egypt tours, Nile cruises, Egypt holidays, Cairo tours, Luxor tours, Aswan tours, tailor made travel')
@section('image', asset('website/logo/egypt-tour-pro-charcoal.webp'))
@section('preferred_theme', 'light')
@section('body_class', 'home-reference-page')
@section('home_style_bundle', '1')

@php
    $isRtl = app()->getLocale() === 'ar';
    $defaultHeroBg =
        $isMobileHome ?? false
            ? asset('website/photos/optimized/home-hero-mobile-brand-744.avif')
            : asset('website/photos/optimized/home-pyramids-desktop-1280.webp');
    $heroHomeBg = $customHeroHome ? asset($customHeroHome) : $defaultHeroBg;
    $heroWidth = $isMobileHome ?? false ? 744 : 1280;
    $heroHeight = $isMobileHome ?? false ? 1000 : 720;
@endphp

@section('lcp_preload')
    @if ($customHeroHome)
        <link rel="preload" as="image" href="{{ $heroHomeBg }}" fetchpriority="high">
    @elseif ($isMobileHome ?? false)
        <link rel="preload" as="image" type="image/avif"
            href="{{ asset('website/photos/optimized/home-hero-mobile-brand-744.avif') }}"
            fetchpriority="high">
    @else
        <link rel="preload" as="image" type="image/webp"
            href="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }}"
            imagesrcset="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }} 1280w, {{ asset('website/photos/optimized/home-pyramids-desktop-1677.webp') }} 1677w"
            imagesizes="100vw" media="(min-width: 768px)" fetchpriority="high">
        <link rel="preload" as="image" type="image/avif"
            href="{{ asset('website/photos/optimized/home-hero-mobile-brand-744.avif') }}"
            media="(max-width: 767px)" fetchpriority="high">
    @endif
@endsection

@section('content')
    <div class="tour-page">

        {{-- 1. Hero Section --}}
        <section class="showcase-hero" id="home" data-critical-fold>
            <picture class="showcase-hero__media">
                @if (!$customHeroHome)
                    @if ($isMobileHome ?? false)
                        <source type="image/avif" srcset="{{ asset('website/photos/optimized/home-hero-mobile-brand-744.avif') }}">
                    @else
                        <source media="(max-width: 767px)" type="image/avif"
                            srcset="{{ asset('website/photos/optimized/home-hero-mobile-brand-744.avif') }}">
                        <source type="image/webp"
                            srcset="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }} 1280w, {{ asset('website/photos/optimized/home-pyramids-desktop-1677.webp') }} 1677w"
                            sizes="100vw">
                    @endif
                @endif
                <img src="{{ $heroHomeBg }}" alt="{{ __('The Great Sphinx and Pyramids of Giza in Egypt') }}"
                    width="{{ $heroWidth }}" height="{{ $heroHeight }}" fetchpriority="high" loading="eager"
                    decoding="sync" elementtiming="home-hero-image">
            </picture>

            @unless ($isMobileHome ?? false)
                <div class="showcase-hero__flight" aria-hidden="true">
                    <i class="la la-plane"></i>
                </div>
                <div class="showcase-hero__signature" aria-hidden="true">
                    <span>Egypt</span>
                    <small>{{ __('More Than a Destination') }}</small>
                </div>
            @endunless

            <div class="container showcase-hero__container">
                <div class="showcase-hero__grid">
                    <div class="showcase-hero__content" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                        <div class="showcase-hero__eyebrow">
                            <i class="la la-landmark"></i>
                            <span>{{ __('Discover Timeless Wonders') }}</span>
                        </div>

                        <h1 class="showcase-hero__title">
                            {{ __('Explore Egypt') }}
                            <span>
                                {{ __('With') }} <em>{{ __('Egypt Tour Pro') }}</em>
                            </span>
                        </h1>

                        <p class="showcase-hero__subtitle">
                            {{ __('Discover breathtaking destinations, Nile cruises, cultural treasures, and unforgettable experiences across Egypt. Let us turn your travel dreams into reality.') }}
                        </p>

                        <div class="showcase-hero__actions">
                            <a href="#deals" class="showcase-hero__primary-btn">
                                <i class="la la-long-arrow-right"></i>
                                {{ __('Browse Tours') }}
                            </a>
                            <a href="{{ route('website.tailor_made.index') }}" class="showcase-hero__video-btn">
                                <i class="la la-route"></i>
                                {{ __('Plan My Trip') }}
                            </a>
                        </div>

                        @unless ($isMobileHome ?? false)
                            <div class="showcase-hero__features">
                                <div class="showcase-hero__feature">
                                    <i class="la la-map-marker"></i>
                                    <span>{{ __('Handpicked Destinations') }}</span>
                                </div>
                                <div class="showcase-hero__feature">
                                    <i class="la la-gem"></i>
                                    <span>{{ __('Best Price Guarantee') }}</span>
                                </div>
                                <div class="showcase-hero__feature">
                                    <i class="la la-users"></i>
                                    <span>{{ __('Local Experts & Support') }}</span>
                                </div>
                                <div class="showcase-hero__feature">
                                    <i class="la la-shield-alt"></i>
                                    <span>{{ __('Safe & Reliable Travel') }}</span>
                                </div>
                            </div>
                        @endunless
                    </div>
                </div>
            </div>

            <svg class="showcase-hero__wave" viewBox="0 0 2170 82" preserveAspectRatio="none" aria-hidden="true">
                <path class="showcase-hero__wave-fill" d="M0,8 L2170,8 L2170,85 L0,85 Z"></path>
                <path class="showcase-hero__wave-line" d="M0,8 L2170,8"></path>
            </svg>
        </section>

        {{-- 2. Trust Bar Section --}}
        <section class="trust-section" id="trust-bar">
            <div class="container">
                <div class="trust-box">
                    <div class="trust-content">
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-trophy"></i></div>
                            <h2 class="trust-title">{{ __('Award-Winning Service') }}</h2>
                            @unless ($isMobileHome ?? false)
                                <p class="trust-description">
                                    {{ __('Recognized excellence & top guest reviews.') }}
                                </p>
                            @endunless
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-certificate"></i></div>
                            <h2 class="trust-title">{{ __('Licensed & Certified') }}</h2>
                            @unless ($isMobileHome ?? false)
                                <p class="trust-description">
                                    {{ __('Officially licensed tourism professionals.') }}
                                </p>
                            @endunless
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-clock"></i></div>
                            <h2 class="trust-title">{{ __('24/7 Travel Support') }}</h2>
                            @unless ($isMobileHome ?? false)
                                <p class="trust-description">
                                    {{ __('24/7 personal support across Egypt.') }}
                                </p>
                            @endunless
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-lock"></i></div>
                            <h2 class="trust-title">{{ __('Secure Payment') }}</h2>
                            @unless ($isMobileHome ?? false)
                                <p class="trust-description">
                                    {{ __('Protected by 3D Secure & encryption.') }}
                                </p>
                            @endunless
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. Signature Egypt Experiences (Categories) --}}
        <section id="deals" class="section-pad cream-section">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-compass"></i>
                        {{ __('Tour Categories') }}
                    </div>
                    <h2 class="section-title">{{ __('Signature Egypt Experiences') }}</h2>
                    <p class="section-subtitle">
                        {{ __('Discover our premier journey categories, from iconic day excursions to comprehensive vacation packages and luxury Nile cruises.') }}
                    </p>
                </div>

                <div class="cards-grid">
                    {{-- Category 1: Day Tours --}}
                    <div class="deal-card reveal-up">
                        <div class="card-image">
                            <div class="badge-top">{{ __('Day Tours') }}</div>
                            <div class="destination-watermark-logo">
                                <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}" alt="Egypt Tour Pro"
                                    width="96" height="39" loading="lazy" decoding="async">
                            </div>

                            <a href="{{ route('website.day_tours.index') }}" aria-label="{{ __('Egypt Day Tours') }}">
                                @if ($isMobileHome ?? false)
                                    <img src="{{ asset('website/photos/experiences/day-tours-mobile.webp') }}"
                                        alt="{{ __('Egypt Day Tours') }}" width="360" height="240" loading="lazy"
                                        decoding="async">
                                @else
                                    <picture>
                                        <source media="(max-width: 767px)" type="image/webp"
                                            srcset="{{ asset('website/photos/experiences/day-tours-mobile.webp') }}">
                                        <source type="image/avif"
                                            srcset="{{ asset('website/photos/experiences/day-tours-480.avif') }} 480w, {{ asset('website/photos/experiences/day-tours-768.avif') }} 768w, {{ asset('website/photos/experiences/day-tours-1024.avif') }} 1024w"
                                            sizes="(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 33vw">
                                        <source type="image/webp"
                                            srcset="{{ asset('website/photos/experiences/day-tours-480.webp') }} 480w, {{ asset('website/photos/experiences/day-tours-768.webp') }} 768w, {{ asset('website/photos/experiences/day-tours-1024.webp') }} 1024w"
                                            sizes="(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 33vw">
                                        <img src="{{ asset('website/photos/experiences/day-tours.webp') }}"
                                            alt="{{ __('Egypt Day Tours') }}" width="800" height="500" loading="lazy"
                                            decoding="async"
                                            onerror="this.onerror=null;this.src='{{ asset('website/images/day-tours/cairo-day-tours.webp') }}';">
                                    </picture>
                                @endif
                            </a>
                        </div>

                        <div class="card-body">
                            <h3 class="deal-title">
                                <a href="{{ route('website.day_tours.index') }}">{{ __('Egypt Day Tours') }}</a>
                            </h3>

                            <div class="deal-meta">
                                <span><i class="la la-clock"></i>{{ __('Full & Half Day') }}</span>
                                <span><i class="la la-map-marker"></i>{{ __('Cairo, Luxor & Red Sea') }}</span>
                                <span><i class="la la-user-tie"></i>{{ __('Private Guided') }}</span>
                            </div>

                            <p class="deal-description">
                                {{ $categoryDescriptions['day_tours'] }}
                            </p>

                            <a href="{{ route('website.day_tours.index') }}" class="gold-btn deal-btn mt-auto">
                                {{ __('Explore Day Tours') }}
                                <i class="la la-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Category 2: Travel Packages --}}
                    <div class="deal-card reveal-up">
                        <div class="card-image">
                            <div class="badge-top">{{ __('Tour Packages') }}</div>
                            <div class="destination-watermark-logo">
                                <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}" alt="Egypt Tour Pro"
                                    width="96" height="39" loading="lazy" decoding="async">
                            </div>

                            <a href="{{ route('website.travel_packages.index') }}"
                                aria-label="{{ __('Egypt Tour Packages') }}">
                                @if ($isMobileHome ?? false)
                                    <img src="{{ asset('website/photos/experiences/travel-packages-mobile.webp') }}"
                                        alt="{{ __('Egypt Tour Packages') }}" width="360" height="240" loading="lazy"
                                        decoding="async">
                                @else
                                    <picture>
                                        <source media="(max-width: 767px)" type="image/webp"
                                            srcset="{{ asset('website/photos/experiences/travel-packages-mobile.webp') }}">
                                        <source type="image/avif"
                                            srcset="{{ asset('website/photos/experiences/travel-packages-480.avif') }} 480w, {{ asset('website/photos/experiences/travel-packages-768.avif') }} 768w, {{ asset('website/photos/experiences/travel-packages-1024.avif') }} 1024w"
                                            sizes="(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 33vw">
                                        <source type="image/webp"
                                            srcset="{{ asset('website/photos/experiences/travel-packages-480.webp') }} 480w, {{ asset('website/photos/experiences/travel-packages-768.webp') }} 768w, {{ asset('website/photos/experiences/travel-packages-1024.webp') }} 1024w"
                                            sizes="(max-width: 767px) 100vw, (max-width: 1200px) 50vw, 33vw">
                                        <img src="{{ asset('website/photos/experiences/travel-packages.webp') }}"
                                            alt="{{ __('Egypt Tour Packages') }}" width="800" height="500"
                                            loading="lazy" decoding="async"
                                            onerror="this.onerror=null;this.src='{{ asset('website/images/travel-packages/7-days-egypt-vacation.webp') }}';">
                                    </picture>
                                @endif
                            </a>
                        </div>

                        <div class="card-body">
                            <h3 class="deal-title">
                                <a href="{{ route('website.travel_packages.index') }}">{{ __('Egypt Tour Packages') }}</a>
                            </h3>

                            <div class="deal-meta">
                                <span><i class="la la-calendar"></i>{{ __('Multi-Day Journeys') }}</span>
                                <span><i class="la la-hotel"></i>{{ __('5-Star & Luxury Stays') }}</span>
                                <span><i class="la la-sliders-h"></i>{{ __('Customizable Itineraries') }}</span>
                            </div>

                            <p class="deal-description">
                                {{ $categoryDescriptions['travel_packages'] }}
                            </p>

                            <a href="{{ route('website.travel_packages.index') }}" class="gold-btn deal-btn mt-auto">
                                {{ __('Explore Tour Packages') }}
                                <i class="la la-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Category 3: Nile Cruises --}}
                    <div class="deal-card reveal-up">
                        <div class="card-image">
                            <div class="badge-top">{{ __('Nile Cruises') }}</div>
                            <div class="destination-watermark-logo">
                                <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}" alt="Egypt Tour Pro"
                                    width="96" height="39" loading="lazy" decoding="async">
                            </div>

                            <a href="{{ route('website.nile_cruises.index') }}"
                                aria-label="{{ __('Egypt Nile Cruise') }}">
                                @if ($isMobileHome ?? false)
                                    <img src="{{ asset('website/photos/experiences/nile-cruises-mobile.webp') }}"
                                        alt="{{ __('Egypt Nile Cruise') }}" width="360" height="240" loading="lazy"
                                        decoding="async">
                                @else
                                    <picture>
                                        <source media="(max-width: 767px)" type="image/webp"
                                            srcset="{{ asset('website/photos/experiences/nile-cruises-mobile.webp') }}">
                                        <source type="image/avif"
                                            srcset="{{ asset('website/photos/experiences/nile-cruises-420.avif') }} 420w, {{ asset('website/photos/experiences/nile-cruises-480.avif') }} 480w, {{ asset('website/photos/experiences/nile-cruises-672.avif') }} 672w, {{ asset('website/photos/experiences/nile-cruises-768-v2.avif') }} 768w, {{ asset('website/photos/experiences/nile-cruises-1024.avif') }} 1024w"
                                            sizes="(max-width: 575px) calc(100vw - 30px), (max-width: 991px) calc(50vw - 24px), 420px">
                                        <source type="image/webp"
                                            srcset="{{ asset('website/photos/experiences/nile-cruises-420.webp') }} 420w, {{ asset('website/photos/experiences/nile-cruises-480.webp') }} 480w, {{ asset('website/photos/experiences/nile-cruises-672.webp') }} 672w, {{ asset('website/photos/experiences/nile-cruises-768.webp') }} 768w, {{ asset('website/photos/experiences/nile-cruises-1024.webp') }} 1024w"
                                            sizes="(max-width: 575px) calc(100vw - 30px), (max-width: 991px) calc(50vw - 24px), 420px">
                                        <img src="{{ asset('website/photos/experiences/nile-cruises.webp') }}"
                                            alt="{{ __('Egypt Nile Cruise') }}" width="800" height="500"
                                            loading="lazy" decoding="async"
                                            onerror="this.onerror=null;this.src='{{ asset('website/images/nile-cruises/luxor-aswan.webp') }}';">
                                    </picture>
                                @endif
                            </a>
                        </div>

                        <div class="card-body">
                            <h3 class="deal-title">
                                <a href="{{ route('website.nile_cruises.index') }}">{{ __('Egypt Nile Cruise') }}</a>
                            </h3>

                            <div class="deal-meta">
                                <span><i class="la la-ship"></i>{{ __('Luxor & Aswan Sights') }}</span>
                                <span><i class="la la-moon"></i>{{ __('3 to 7 Night Cruises') }}</span>
                                <span><i class="la la-utensils"></i>{{ __('Full Board Dining') }}</span>
                            </div>

                            <p class="deal-description">
                                {{ $categoryDescriptions['nile_cruises'] }}
                            </p>

                            <a href="{{ route('website.nile_cruises.index') }}" class="gold-btn deal-btn mt-auto">
                                {{ __('Explore Nile Cruises') }}
                                <i class="la la-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. Why Choose Us Section --}}
        <section class="section-pad" id="why-choose-us">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-star"></i>
                        {{ __('Why Egypt Tour Pro') }}
                    </div>
                    <h2 class="section-title">{{ __('Travel Egypt With Confidence') }}</h2>
                    <p class="section-subtitle">
                        {{ __('A modern tourism experience combining expert planning, premium service, authentic culture, and smooth operations.') }}
                    </p>
                </div>

                <div class="features-grid">
                    <div class="feature-card reveal-up">
                        <div class="feature-icon"><i class="la la-user-graduate"></i></div>
                        <h3 class="feature-title">{{ __('Expert Egyptologists') }}</h3>
                        <p class="feature-description">
                            {{ __('Certified guides bring temples, tombs, museums, and ancient stories to life with rich knowledge.') }}
                        </p>
                    </div>

                    <div class="feature-card reveal-up">
                        <div class="feature-icon"><i class="la la-shield-alt"></i></div>
                        <h3 class="feature-title">{{ __('Safe Operations') }}</h3>
                        <p class="feature-description">
                            {{ __('Trusted transport, organized itineraries, and reliable local support for a comfortable journey.') }}
                        </p>
                    </div>

                    <div class="feature-card reveal-up">
                        <div class="feature-icon"><i class="la la-gem"></i></div>
                        <h3 class="feature-title">{{ __('Luxury Touch') }}</h3>
                        <p class="feature-description">
                            {{ __('Premium experiences, carefully selected services, and details designed for a refined holiday.') }}
                        </p>
                    </div>

                    <div class="feature-card reveal-up">
                        <div class="feature-icon"><i class="la la-headset"></i></div>
                        <h3 class="feature-title">{{ __('Tailor-Made Service') }}</h3>
                        <p class="feature-description">
                            {{ __('Every trip can be customized around your schedule, budget, interests, and travel style.') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. Featured Tours & Packages --}}
        @if ($featuredPackages->isNotEmpty())
            <section class="section-pad" id="featured-packages">
                <div class="container">
                    <div class="section-heading reveal-up">
                        <div class="section-kicker">
                            <i class="la la-suitcase"></i>
                            {{ __('Featured Tours') }}
                        </div>
                        <h2 class="section-title">{{ __('Most Popular Egypt Tours & Cruises') }}</h2>
                        <p class="section-subtitle">
                            {{ __('Discover our most requested journeys, from iconic landmarks to luxurious Nile adventures.') }}
                        </p>
                    </div>

                    <div class="cards-grid">
                        @foreach ($featuredPackages as $package)
                            <div class="deal-card reveal-up">
                                <div class="card-image">
                                    @if (!empty($package['is_ultra_luxury']))
                                        <div class="badge-top">{{ __('Ultra Luxury') }}</div>
                                    @elseif (!empty($package['is_best_seller']))
                                        <div class="badge-top">{{ __('Best Seller') }}</div>
                                    @elseif (!empty($package['is_featured']))
                                        <div class="badge-top">{{ __('Featured') }}</div>
                                    @endif

                                    @if (!empty($package['price']))
                                        <div class="deal-price">{{ $package['price'] }}</div>
                                    @endif

                                    <a href="{{ $package['url'] }}" aria-label="{{ $package['title'] }}">
                                        <img src="{{ $package['image'] }}" alt="{{ $package['title'] }}"
                                            width="800" height="500" loading="lazy" decoding="async">
                                    </a>
                                </div>

                                <div class="card-body">
                                    <h3 class="deal-title">
                                        <a href="{{ $package['url'] }}">{{ $package['title'] }}</a>
                                    </h3>

                                    <div class="deal-meta">
                                        @if (!empty($package['duration']))
                                            <span><i class="la la-clock"></i>{{ $package['duration'] }}</span>
                                        @endif
                                        @if (!empty($package['tour_type']))
                                            <span><i class="la la-users"></i>{{ $package['tour_type'] }}</span>
                                        @endif
                                        @if (!empty($package['route_text']))
                                            <span><i class="la la-map-marker"></i>{{ $package['route_text'] }}</span>
                                        @endif
                                    </div>

                                    @if (!empty($package['description']))
                                        <p class="deal-description">{{ $package['description'] }}</p>
                                    @endif

                                    @if (!empty($package['tags']))
                                        <div class="tag-list">
                                            @foreach ($package['tags'] as $tag)
                                                <span class="feature-tag">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <a href="{{ $package['url'] }}" class="gold-btn deal-btn mt-auto">
                                        {{ __('Explore Journey') }}
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-5 reveal-up">
                        <a href="{{ route('website.travel_packages.index') }}" class="gold-btn"
                            style="padding: 14px 32px; font-size: 1.05rem;">
                            <i class="la la-th-large"></i>
                            {{ __('View All Packages & Tours') }}
                        </a>
                    </div>
                </div>
            </section>
        @endif

        {{-- 6. Custom Itinerary Planner Banner --}}
        <section class="quote-section" id="quote">
            <div class="container">
                <div class="quote-card reveal-up">
                    <h2 class="quote-title">{{ __('Need Help Planning Your Trip?') }}</h2>
                    <p>
                        {{ __('Tell us your travel dates, interests, number of guests, and preferred style. Our travel experts will create a personalized Egypt experience for you.') }}
                    </p>

                    <div class="quote-features">
                        <div class="quote-feature">
                            <i class="la la-check-circle"></i>
                            <span>{{ __('Custom Itineraries') }}</span>
                        </div>
                        <div class="quote-feature">
                            <i class="la la-user-graduate"></i>
                            <span>{{ __('Expert Guides') }}</span>
                        </div>
                        <div class="quote-feature">
                            <i class="la la-headset"></i>
                            <span>{{ __('24/7 Support') }}</span>
                        </div>
                        <div class="quote-feature">
                            <i class="la la-dollar"></i>
                            <span>{{ __('Best Value') }}</span>
                        </div>
                    </div>

                    <button type="button" class="gold-btn" data-bs-toggle="modal" data-bs-target="#quoteModal">
                        <i class="la la-paper-plane"></i>
                        {{ __('Get Custom Quote') }}
                    </button>
                </div>
            </div>
        </section>

        {{-- 7. Destinations Section --}}
        <section class="section-pad light-section" id="destinations">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-map"></i>
                        {{ __('Destinations') }}
                    </div>
                    <h2 class="section-title">{{ __('Explore Extraordinary Places') }}</h2>
                    <p class="section-subtitle">
                        {{ __('From Cairo and Giza to Luxor, Aswan, the Red Sea, and hidden gems across Egypt.') }}
                    </p>
                </div>

                <div class="destinations-grid">
                    @forelse ($destinations as $destination)
                        <div class="destination-card reveal-up">
                            <div class="card-image">
                                <div class="badge-top">{{ $destination['country'] ?: __('Destination') }}</div>
                                <div class="destination-watermark-logo">
                                    <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}"
                                        alt="Egypt Tour Pro" width="96" height="39" loading="lazy"
                                        decoding="async">
                                </div>
                                <a href="{{ $destination['url'] }}">
                                    <img src="{{ $destination['image'] }}" alt="{{ $destination['title'] }}"
                                        width="800" height="500" loading="lazy" decoding="async">
                                </a>
                            </div>

                            <div class="card-body">
                                <h3 class="destination-title">
                                    <a href="{{ $destination['url'] }}">{{ $destination['title'] }}</a>
                                </h3>

                                <p class="destination-description">{{ $destination['description'] }}</p>

                                <div class="destination-meta">
                                    <span>
                                        <i class="la la-map-marker"></i>
                                        {{ $destination['sites_count'] }} {{ __('Sites') }}
                                    </span>
                                    <span>
                                        <i class="la la-suitcase"></i>
                                        {{ $destination['packages_count'] }} {{ __('Trips') }}
                                    </span>
                                </div>

                                <a href="{{ $destination['url'] }}" class="gold-btn destination-btn">
                                    {{ __('Discover') }}
                                    <i class="la la-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            {{ __('No active destinations found. Add active cities from the admin panel.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- 8. Testimonials Section --}}
        <section class="section-pad light-section" id="testimonials">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-comments"></i>
                        {{ __('Guest Reviews') }}
                    </div>
                    <h2 class="section-title">{{ __('Travelers Love Egypt Tour Pro') }}</h2>
                    <p class="section-subtitle">
                        {{ __('Real experiences from guests who discovered the magic of Egypt with our team.') }}
                    </p>
                </div>

                <div class="testimonials-slider-wrapper reveal-up">
                    <button type="button" class="testimonials-nav-btn prev-btn"
                        aria-label="{{ __('Previous Review') }}">
                        <i class="la la-angle-left"></i>
                    </button>

                    <div class="testimonials-slider" id="testimonialsSlider">
                        @forelse ($testimonials as $testimonial)
                            <div class="testimonial-card">
                                <div class="rating-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="la {{ $i <= $testimonial['rating'] ? 'la-star' : 'la-star-o' }}"></i>
                                    @endfor

                                    @if ($testimonial['is_verified'])
                                        <span class="verified-badge">{{ __('Verified') }}</span>
                                    @endif
                                </div>

                                <p class="testimonial-text">“{{ $testimonial['content'] }}”</p>

                                <div class="author-section">
                                    <div class="author-avatar">
                                        @if ($testimonial['avatar'])
                                            <img src="{{ $testimonial['avatar'] }}" alt="{{ $testimonial['name'] }}"
                                                width="80" height="80" loading="lazy" decoding="async">
                                        @else
                                            {{ $testimonial['initials'] }}
                                        @endif
                                    </div>

                                    <div>
                                        <h5 class="author-name">{{ $testimonial['name'] }}</h5>
                                        <p class="mb-0 text-muted">
                                            <i class="la la-check-circle"></i>
                                            {{ __('Guest Review') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                {{ __('No testimonials found. Add active testimonials from the admin panel.') }}
                            </div>
                        @endforelse
                    </div>

                    <button type="button" class="testimonials-nav-btn next-btn" aria-label="{{ __('Next Review') }}">
                        <i class="la la-angle-right"></i>
                    </button>
                </div>

                <div class="testimonials-dots" id="testimonialsDots"></div>

                <div class="text-center mt-4 reveal-up">
                    <a href="https://www.tripadvisor.com" target="_blank" rel="noopener noreferrer" class="gold-btn">
                        <i class="la la-external-link"></i>
                        {{ __('Read All Reviews on TripAdvisor') }}
                    </a>
                </div>
            </div>
        </section>

        {{-- 9. Latest Travel Stories (Desktop Only) --}}
        @if ($showHomeArticles)
            <section class="section-pad" id="travel-guides">
                <div class="container">
                    <div class="section-heading reveal-up">
                        <div class="section-kicker">
                            <i class="la la-newspaper"></i>
                            {{ __('Travel Guides') }}
                        </div>
                        <h2 class="section-title">{{ __('Latest Egypt Travel Stories') }}</h2>
                        <p class="section-subtitle">
                            {{ __('Useful tips, destination insights, and inspiring stories for planning your Egypt journey.') }}
                        </p>
                    </div>

                    <div class="articles-grid">
                        @forelse ($latestArticles as $article)
                            <div class="article-card reveal-up">
                                <div class="card-image">
                                    <div class="destination-watermark-logo">
                                        <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}"
                                            alt="Egypt Tour Pro" width="96" height="39" loading="lazy"
                                            decoding="async">
                                    </div>
                                    <a href="{{ $article['url'] }}">
                                        <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}"
                                            width="800" height="500" loading="lazy" decoding="async">
                                    </a>
                                </div>

                                <div class="card-body">
                                    <div class="article-date">
                                        <i class="la la-calendar"></i>
                                        {{ $article['date'] }}
                                    </div>

                                    <h3 class="article-title">
                                        <a href="{{ $article['url'] }}">{{ $article['title'] }}</a>
                                    </h3>

                                    <p class="article-excerpt">{{ $article['excerpt'] }}</p>

                                    <a href="{{ $article['url'] }}" class="gold-btn">
                                        {{ __('Read More') }}
                                        <span class="visually-hidden">: {{ $article['title'] }}</span>
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">{{ __('No active articles found.') }}</div>
                        @endforelse
                    </div>

                    <div class="text-center mt-5 reveal-up">
                        <a href="{{ route('website.blogs.index') }}" class="gold-btn btn-lg">
                            {{ __('View All Articles') }}
                            <i class="la la-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        {{-- 10. Newsletter Section (Desktop Only) --}}
        @if ($showHomeNewsletter)
            <section class="section-pad cream-section" id="newsletter">
                <div class="container">
                    <div class="newsletter-box reveal-up">
                        <div class="section-kicker">
                            <i class="la la-envelope"></i>
                            {{ __('Newsletter') }}
                        </div>

                        <h2 class="section-title">{{ __('Get Our Latest Travel Deals') }}</h2>

                        <p class="section-subtitle">
                            {{ __('Subscribe to receive updates, new packages, seasonal offers, and useful Egypt travel tips.') }}
                        </p>

                        <form action="{{ route('website.newsletter.store') }}" method="POST" class="newsletter-form">
                            @csrf
                            <input type="email" name="email" placeholder="{{ __('Enter your email address') }}"
                                required>
                            <button type="submit" class="gold-btn">
                                {{ __('Subscribe') }}
                                <i class="la la-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        {{-- 11. Custom Quote Modal --}}
        <div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Get Custom Quote') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('Close') }}"></button>
                    </div>

                    <form action="{{ route('website.inquiries.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="inquiry_type" value="custom_quote">

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input class="form-control" name="full_name" placeholder="{{ __('Full name') }}"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <input class="form-control" type="email" name="email"
                                        placeholder="{{ __('Email address') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <input class="form-control" type="tel" name="phone"
                                        placeholder="{{ __('Phone / WhatsApp') }}">
                                </div>

                                <div class="col-md-6">
                                    <input class="form-control" type="date" name="travel_date">
                                </div>

                                <div class="col-md-6">
                                    <input class="form-control" type="number" min="1" name="adults"
                                        placeholder="{{ __('Adults') }}">
                                </div>

                                <div class="col-md-6">
                                    <input class="form-control" type="number" min="0" name="children"
                                        placeholder="{{ __('Children') }}">
                                </div>

                                <div class="col-12">
                                    <textarea class="form-control" name="message" rows="4"
                                        placeholder="{{ __('Tell us about your preferred trip') }}"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                {{ __('Close') }}
                            </button>

                            <button type="submit" class="gold-btn">
                                {{ __('Send Request') }}
                                <i class="la la-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
