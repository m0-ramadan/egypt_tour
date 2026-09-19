@extends('website.layouts.master')

@section('title', __('Home - Egypt Tour Pro'))
@section('description',
    __('Luxury Egypt tours, Nile cruises, private day trips, and tailor-made travel experiences
    curated by Egypt Tour Pro across Cairo, Luxor, Aswan, and beyond.'))
@section('keywords',
    'Egypt Tour Pro, luxury Egypt tours, Nile cruises, Egypt holidays, Cairo tours, Luxor tours, Aswan
    tours, tailor made travel')
@section('image', asset('website/logo/egypt-tour-pro-charcoal.png'))
@section('preferred_theme', 'light')
@section('body_class', 'home-reference-page')

@section('lcp_preload')
    <link rel="preload" as="image" type="image/webp"
        href="{{ asset('website/photos/optimized/home-pyramids-mobile-744.webp') }}" media="(max-width: 767px)"
        fetchpriority="high">
    <link rel="preload" as="image" type="image/webp"
        href="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }}"
        imagesrcset="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }} 1280w, {{ asset('website/photos/optimized/home-pyramids-desktop-1677.webp') }} 1677w"
        imagesizes="100vw" media="(min-width: 768px)" fetchpriority="high">
@endsection

@php($isRtl = app()->getLocale() === 'ar')

@section('css')
    @vite('resources/css/website-home.css')
@endsection

@section('content')
    <div class="tour-page">

        <section class="showcase-hero" id="home">
            <picture class="showcase-hero__media" aria-hidden="true">
                <source media="(max-width: 767px)" type="image/webp"
                    srcset="{{ asset('website/photos/optimized/home-pyramids-mobile-744.webp') }}">
                <source type="image/webp"
                    srcset="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }} 1280w, {{ asset('website/photos/optimized/home-pyramids-desktop-1677.webp') }} 1677w"
                    sizes="100vw">
                <img src="{{ asset('website/photos/optimized/home-pyramids-desktop-1280.webp') }}" alt=""
                    width="1677" height="938" fetchpriority="high" loading="eager" decoding="async">
            </picture>
            <div class="showcase-hero__flight" aria-hidden="true">
                <span class="showcase-hero__flight-path"></span>
                <i class="la la-plane"></i>
            </div>
            <div class="showcase-hero__signature" aria-hidden="true">
                <span>Egypt</span>
                <small>{{ __('More Than a Destination') }}</small>
            </div>

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
                            <a href="#featured-packages" class="showcase-hero__primary-btn">
                                <i class="la la-long-arrow-right"></i>
                                {{ __('Browse Tours') }}
                            </a>
                            <a href="#showcase-tour" class="showcase-hero__video-btn">
                                <i class="la la-play-circle"></i>
                                {{ __('Watch Video') }}
                            </a>
                        </div>

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
                    </div>

                    <div class="showcase-hero__visuals" id="showcase-tour">
                        <a href="{{ route('website.nile_cruises.index') }}" class="showcase-tour-card">
                            <div class="showcase-tour-card__image">
                                <img src="{{ asset('website/photos/optimized/home-nile-cruise-900.webp') }}"
                                    alt="{{ __('Luxury Nile cruise at sunset') }}" width="900" height="473"
                                    loading="eager" decoding="sync">
                                <span class="showcase-tour-card__category">
                                    <i class="la la-map-marker"></i>{{ __('Nile Cruise') }}
                                </span>
                            </div>
                            <div class="showcase-tour-card__body">
                                <div class="showcase-tour-card__heading">
                                    <h2>{{ __('Nile Cruise Experience') }}</h2>
                                    <span class="showcase-tour-card__rating">
                                        <strong aria-label="{{ __('Five stars') }}">★★★★★</strong>
                                        <small>{{ __('(128 reviews)') }}</small>
                                    </span>
                                </div>
                                <p>{{ __('Sail the timeless Nile, visit iconic temples, and enjoy unforgettable views.') }}
                                <div class="showcase-tour-card__meta">
                                    <span><i class="la la-clock"></i>{{ __('4 Days') }}</span>
                                    <span><i class="la la-user-friends"></i>{{ __('From $499') }}</span>
                                    <b><i class="la la-long-arrow-right"></i></b>
                                </div>
                            </div>
                        </a>

                        <div class="showcase-hero__destinations">
                            <a href="{{ route('website.nile_cruises.index') }}" class="showcase-destination-card">
                                <img src="{{ asset('website/admin/uploads/1603406020abu-simbel.jpg') }}"
                                    alt="{{ __('Abu Simbel') }}" width="1000" height="300" loading="eager">
                                <span><i class="la la-map-marker"></i>{{ __('Abu Simbel') }}</span>
                            </a>
                            <a href="{{ route('website.nile_cruises.index') }}" class="showcase-destination-card">
                                <img src="{{ asset('website/images/day-tours/aswan-destination.jpg') }}"
                                    alt="{{ __('Aswan') }}" width="1264" height="848" loading="eager">
                                <span><i class="la la-map-marker"></i>{{ __('Aswan') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <svg class="showcase-hero__wave" viewBox="0 0 2170 82" preserveAspectRatio="none" aria-hidden="true">
                <path class="showcase-hero__wave-fill"
                    d="M0,18 C205,88 455,40 720,56 C1120,82 1540,88 2170,8 L2170,85 L0,85 Z"></path>
                <path class="showcase-hero__wave-line" d="M0,18 C205,88 455,40 720,56 C1120,82 1540,88 2170,8"></path>
            </svg>
        </section>

        <section class="trust-section">
            <div class="container">
                <div class="trust-box">
                    <div class="trust-content">
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-trophy"></i></div>
                            <h2 class="trust-title">{{ __('Award-Winning Service') }}</h2>
                            <p class="trust-description">
                                {{ __('Recognized excellence & top guest reviews.') }}
                            </p>
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-certificate"></i></div>
                            <h2 class="trust-title">{{ __('Licensed & Certified') }}</h2>
                            <p class="trust-description">
                                {{ __('Officially licensed tourism professionals.') }}
                            </p>
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-clock"></i></div>
                            <h2 class="trust-title">{{ __('24/7 Travel Support') }}</h2>
                            <p class="trust-description">
                                {{ __('24/7 personal support across Egypt.') }}
                            </p>
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                        <article class="trust-item reveal-up">
                            <div class="trust-icon"><i class="la la-lock"></i></div>
                            <h2 class="trust-title">{{ __('Secure Payment') }}</h2>
                            <p class="trust-description">
                                {{ __('Protected by 3D Secure & encryption.') }}
                            </p>
                            <span class="trust-line" aria-hidden="true"></span>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        {{-- <section class="section-pad light-section">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-tripadvisor"></i>
                        {{ __('Trusted Excellence') }}
                    </div>
                    <h2 class="section-title">{{ __('TripAdvisor Hall of Fame') }}</h2>
                    <p class="section-subtitle">
                        {{ __('Consistently recognized for excellence in travel experiences and unforgettable journeys across Egypt.') }}
                    </p>
                </div>

                <div class="tripadvisor-row">
                    @foreach (['Travellers-Choice-2019-.png', 'Travellers-Choice-2020.png', 'Travellers-Choice-2021.png', 'Travellers-Choice-2025.png', 'Travellers-Choice-2022.png', 'Travellers-Choice-2023.png', 'Travellers-Choice-2024-.png'] as $award)
                        <div class="certificate-card reveal-up">
                            <picture>
                                <source type="image/avif"
                                    srcset="{{ asset('website/photos/optimized/' . pathinfo($award, PATHINFO_FILENAME) . '.avif') }}">
                                <img loading="lazy" decoding="async"
                                    src="{{ asset('website/photos/optimized/' . pathinfo($award, PATHINFO_FILENAME) . '.webp') }}"
                                    alt="{{ __('TripAdvisor Award') }}" class="certificate-img" width="176"
                                    height="176">
                            </picture>
                        </div>
                    @endforeach
                </div>
            </div>
        </section> --}}
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

                            <a href="{{ route('website.day_tours.index') }}" aria-label="{{ __('Egypt Day Tours') }}">
                                <img src="{{ asset('website/photos/experiences/day-tours.jpg') }}"
                                    alt="{{ __('Egypt Day Tours') }}" width="800" height="500" loading="lazy"
                                    decoding="async"
                                    onerror="this.onerror=null;this.src='{{ asset('website/images/day-tours/cairo-day-tours.jpg') }}';">
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
                                {{ __('Discover Egypt\'s iconic landmarks and ancient marvels on private guided day trips. From the Giza Pyramids to Luxor\'s temples and Red Sea shores, experience unforgettable day adventures.') }}
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

                            <a href="{{ route('website.travel_packages.index') }}"
                                aria-label="{{ __('Egypt Tour Packages') }}">
                                <img src="{{ asset('website/photos/experiences/travel-packages.jpg') }}"
                                    alt="{{ __('Egypt Tour Packages') }}" width="800" height="500" loading="lazy"
                                    decoding="async"
                                    onerror="this.onerror=null;this.src='{{ asset('website/images/travel-packages/7-days-egypt-vacation.jpg') }}';">
                            </a>
                        </div>

                        <div class="card-body">
                            <h3 class="deal-title">
                                <a
                                    href="{{ route('website.travel_packages.index') }}">{{ __('Egypt Tour Packages') }}</a>
                            </h3>

                            <div class="deal-meta">
                                <span><i class="la la-calendar"></i>{{ __('Multi-Day Journeys') }}</span>
                                <span><i class="la la-hotel"></i>{{ __('5-Star & Luxury Stays') }}</span>
                                <span><i class="la la-sliders-h"></i>{{ __('Customizable Itineraries') }}</span>
                            </div>

                            <p class="deal-description">
                                {{ __('Multi-day curated journeys combining ancient wonders, luxury hotel stays, desert adventures, and bespoke cultural itineraries with seamless transfers and dedicated support.') }}
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

                            <a href="{{ route('website.nile_cruises.index') }}"
                                aria-label="{{ __('Egypt Nile Cruise') }}">
                                <img src="{{ asset('website/photos/experiences/nile-cruises.jpg') }}"
                                    alt="{{ __('Egypt Nile Cruise') }}" width="800" height="500" loading="lazy"
                                    decoding="async"
                                    onerror="this.onerror=null;this.src='{{ asset('website/images/nile-cruises/luxor-aswan.jpg') }}';">
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
                                {{ __('Sail timeless waters between Luxor and Aswan aboard five-star ships, boutique Dahabiyas, and Lake Nasser floating palaces with world-class dining and guided temple visits.') }}
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
        <section class="section-pad">
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
                        @foreach ($featuredPackages->take(3) as $package)
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

                    <button class="gold-btn" data-bs-toggle="modal" data-bs-target="#quoteModal">
                        <i class="la la-paper-plane"></i>
                        {{ __('Get Custom Quote') }}
                    </button>
                </div>
            </div>
        </section>

        <section class="section-pad light-section">
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
                                    <img src="{{ asset('website/logo/egypt-tour-pro-light.png') }}" alt="Egypt Tour Pro"
                                        loading="lazy">
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

        <section class="section-pad">
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
                                    <img src="{{ asset('website/logo/egypt-tour-pro-light.png') }}" alt="Egypt Tour Pro"
                                        loading="lazy">
                                </div>
                                <a href="{{ $article['url'] }}">
                                    <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" width="800"
                                        height="500" loading="lazy" decoding="async">
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

        <section class="section-pad light-section">
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

                <div class="testimonials-grid">
                    @forelse ($testimonials as $testimonial)
                        <div class="testimonial-card reveal-up">
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

                <div class="text-center mt-5 reveal-up">
                    <a href="#" target="_blank" class="gold-btn">
                        <i class="la la-external-link"></i>
                        {{ __('Read All Reviews on TripAdvisor') }}
                    </a>
                </div>
            </div>
        </section>

        <section class="section-pad cream-section">
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


                                <div class="col-md-4">
                                    <input class="form-control" type="date" name="travel_date">
                                </div>

                                <div class="col-md-4">
                                    <input class="form-control" type="number" min="1" name="adults"
                                        placeholder="{{ __('Adults') }}">
                                </div>

                                <div class="col-md-4">
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

@section('js')
    <script>
        function initReveal() {
            const revealItems = document.querySelectorAll('.reveal-up');

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.05,
                    rootMargin: '0px 0px 50px 0px'
                });

                revealItems.forEach(function(item, index) {
                    item.style.transitionDelay = (index % 4) * 60 + 'ms';
                    observer.observe(item);
                });
            } else {
                revealItems.forEach(function(item) {
                    item.classList.add('is-visible');
                });
            }

            setTimeout(function() {
                document.querySelectorAll('.reveal-up:not(.is-visible)').forEach(function(item) {
                    item.classList.add('is-visible');
                });
            }, 400);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initReveal);
        } else {
            initReveal();
        }
    </script>
@endsection
