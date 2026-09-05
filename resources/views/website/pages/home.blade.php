@extends('website.layouts.master')

@section('title', __('Home - Etro Tours'))
@section('description',
    __('Luxury Egypt tours, Nile cruises, private day trips, and tailor-made travel experiences
    curated by Etro Tours across Cairo, Luxor, Aswan, and beyond.'))
@section('keywords',
    'Etro Tours, luxury Egypt tours, Nile cruises, Egypt holidays, Cairo tours, Luxor tours, Aswan
    tours, tailor made travel')
@section('image', asset('website/logo/logo-lat.png'))
@section('preferred_theme', 'light')
@section('body_class', 'home-reference-page')

@section('lcp_preload')
    <link rel="preload" as="image" type="image/avif" href="{{ asset('website/photos/optimized/hero-mobile-744.avif') }}"
        media="(max-width: 767px)" fetchpriority="high">
    <link rel="preload" as="image" type="image/avif" href="{{ asset('website/photos/optimized/hero-desktop-1280.avif') }}"
        imagesrcset="{{ asset('website/photos/optimized/hero-desktop-1280.avif') }} 1280w, {{ asset('website/photos/optimized/hero-desktop-1672.avif') }} 1672w"
        imagesizes="100vw" media="(min-width: 768px)" fetchpriority="high">
@endsection

@php($isRtl = app()->getLocale() === 'ar')

@section('css')
    @vite('resources/css/website-home.css')
@endsection

@section('content')
    <div class="tour-page">

        <section class="hero-section" id="home">
            <picture class="hero-media" aria-hidden="true">
                <source media="(max-width: 767px)" type="image/avif"
                    srcset="{{ asset('website/photos/optimized/hero-mobile-744.avif') }}">
                <source media="(max-width: 767px)" type="image/webp"
                    srcset="{{ asset('website/photos/optimized/hero-mobile-744.webp') }}">
                <source type="image/avif"
                    srcset="{{ asset('website/photos/optimized/hero-desktop-1280.avif') }} 1280w, {{ asset('website/photos/optimized/hero-desktop-1672.avif') }} 1672w"
                    sizes="100vw">
                <source type="image/webp"
                    srcset="{{ asset('website/photos/optimized/hero-desktop-1280.webp') }} 1280w, {{ asset('website/photos/optimized/hero-desktop-1672.webp') }} 1672w"
                    sizes="100vw">
                <img src="{{ asset('website/photos/optimized/hero-desktop-1280.webp') }}" alt="" width="1672"
                    height="941" fetchpriority="high" loading="eager" decoding="async">
            </picture>
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
                        style="text-align: {{ $isRtl ? 'right' : 'left' }} !important;">
                        <div class="hero-badge">
                            <i class="la la-map-marked"></i>
                            <span>{{ __('Luxury Egypt Travel Experiences') }}</span>
                        </div>

                        <h1 class="hero-title">
                            {{ __('Explore Egypt') }}
                            <span class="hero-title-line">
                                <span class="hero-title-prefix">{{ __('With') }}</span>
                                <span class="hero-title-gold">{{ __('Etro Tours') }}</span>
                            </span>
                        </h1>
                        <div class="hero-title-rule" aria-hidden="true"></div>

                        <p class="hero-subtitle">
                            {{ __('Discover timeless monuments, Nile cruises, desert escapes, and private journeys designed with comfort, style, and local expertise from arrival to departure.') }}
                        </p>

                        <div class="hero-actions">
                            <a href="#deals" class="gold-btn">
                                <i class="la la-compass"></i>
                                {{ __('Discover Experiences') }}
                            </a>
                            <a href="#quote" class="outline-btn">
                                <i class="la la-paper-plane"></i>
                                {{ __('Plan My Trip') }}
                            </a>
                        </div>

                        <div class="hero-stats">
                            <div class="hero-stat">
                                <strong>10+</strong>
                                <span>{{ __('Years Experience') }}</span>
                            </div>
                            <div class="hero-stat">
                                <strong>24/7</strong>
                                <span>{{ __('Local Support') }}</span>
                            </div>
                            <div class="hero-stat">
                                <strong>5★</strong>
                                <span>{{ __('Guest Reviews') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-floating-card">
                        <div class="hero-floating-card-inner">
                            <div class="premium-tour-badge">
                                <i class="la la-star"></i>
                                {{ __('Premium Tour') }}
                            </div>
                            <picture>
                                <source media="(max-width: 991px)" type="image/gif"
                                    srcset="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=">
                                <source type="image/avif"
                                    srcset="{{ asset('website/photos/optimized/hero-card-900.avif') }}">
                                <source type="image/webp"
                                    srcset="{{ asset('website/photos/optimized/hero-card-900.webp') }}">
                                <img src="{{ asset('website/photos/optimized/hero-card-900.webp') }}"
                                    alt="{{ __('Egypt Tour') }}" width="900" height="506" decoding="async">
                            </picture>
                            <div class="floating-info">
                                <h2>{{ __('Private Egypt Journey') }}</h2>
                                <p>{{ __('Premium tours, hand-picked guides, comfortable transfers, and carefully curated routes.') }}
                                </p>
                                <div class="mini-route">
                                    @if ($isRtl)
                                        <span><i class="la la-map-marker"></i>{{ __('Aswan') }}</span>
                                        <span><i class="la la-long-arrow-left"></i></span>
                                        <span><i class="la la-map-marker"></i>{{ __('Luxor') }}</span>
                                        <span><i class="la la-long-arrow-left"></i></span>
                                        <span><i class="la la-map-marker"></i> {{ __('Cairo') }}</span>
                                    @else
                                        <span><i class="la la-map-marker"></i> {{ __('Cairo') }}</span>
                                        <span><i class="la la-long-arrow-right"></i></span>
                                        <span><i class="la la-map-marker"></i>{{ __('Luxor') }}</span>
                                        <span><i class="la la-long-arrow-right"></i></span>
                                        <span><i class="la la-map-marker"></i>{{ __('Aswan') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

        <section class="section-pad light-section">
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
        </section>

        <section class="section-pad">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-star"></i>
                        {{ __('Why Etro Tours') }}
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

        <section id="deals" class="section-pad cream-section">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-suitcase"></i>
                        {{ __('Featured Tours') }}
                    </div>
                    <h2 class="section-title">{{ __('Signature Egypt Experiences') }}</h2>
                    <p class="section-subtitle">
                        {{ __('Discover our most requested journeys, from iconic landmarks to luxurious Nile adventures.') }}
                    </p>
                </div>

                <div class="cards-grid">
                    @forelse ($featuredPackages as $package)
                        <div class="deal-card reveal-up">
                            <div class="card-image">
                                @if ($package['is_ultra_luxury'])
                                    <div class="badge-top">{{ __('Ultra Luxury') }}</div>
                                @elseif ($package['is_best_seller'])
                                    <div class="badge-top">{{ __('Best Seller') }}</div>
                                @endif

                                <div class="deal-price">{{ $package['price'] }}</div>

                                <a href="{{ $package['url'] }}">
                                    <img src="{{ $package['image'] }}" alt="{{ $package['title'] }}" width="800"
                                        height="500" loading="lazy" decoding="async">
                                </a>
                            </div>

                            <div class="card-body">
                                <h3 class="deal-title">
                                    <a href="{{ $package['url'] }}">{{ $package['title'] }}</a>
                                </h3>

                                <div class="deal-meta">
                                    <span><i class="la la-clock"></i>{{ $package['duration'] }}</span>
                                    <span><i class="la la-users"></i>{{ $package['tour_type'] }}</span>
                                    @if ($package['route_text'])
                                        <span><i class="la la-map-marker"></i>{{ $package['route_text'] }}</span>
                                    @endif
                                </div>

                                <p class="deal-description">{{ $package['description'] }}</p>

                                @if (!empty($package['tags']))
                                    <div class="tag-list">
                                        @foreach ($package['tags'] as $tag)
                                            <span class="feature-tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <a href="{{ $package['url'] }}" class="gold-btn deal-btn">
                                    {{ __('Explore Journey') }}
                                    <i class="la la-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            {{ __('No featured packages found. Add active packages from the admin panel.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

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
            </div>
        </section>

        <section class="section-pad light-section">
            <div class="container">
                <div class="section-heading reveal-up">
                    <div class="section-kicker">
                        <i class="la la-comments"></i>
                        {{ __('Guest Reviews') }}
                    </div>
                    <h2 class="section-title">{{ __('Travelers Love Etro Tours') }}</h2>
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
                    <a href="https://www.tripadvisor.com/Attraction_Review-g294205-d19981172-Reviews-Etro_tours-Luxor_Nile_River_Valley.html"
                        target="_blank" class="gold-btn">
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
        document.addEventListener('DOMContentLoaded', function() {
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
                    threshold: 0.12
                });

                revealItems.forEach(function(item, index) {
                    item.style.transitionDelay = (index % 4) * 80 + 'ms';
                    observer.observe(item);
                });
            } else {
                revealItems.forEach(function(item) {
                    item.classList.add('is-visible');
                });
            }

        });
    </script>
@endsection
