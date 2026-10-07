@extends('website.layouts.master')

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords',
    'Egypt Vacation Packages, Egypt Tours 2026, 7 Days Egypt Tour, 10 Days Egypt Vacation, Luxury Egypt
    Tours, Egypt Holidays, Nile Cruise Packages')
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/travel-packages-index.css')
@endsection

@section('content')
    <!-- Breadcrumb -->
    <section class="page-breadcrumb">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.destinations.index') }}">{{ __('Egypt') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('Travel Packages') }}
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="packages-hero" style="--hero-bg: url('{{ $heroImage }}');">
        <div class="container text-center">
            <div class="hero-badge-pill">
                <i class="la la-suitcase"></i> {{ $pageContent['badge'] }}
            </div>
            <h1 class="hero-title-main">{{ $pageContent['title'] }}</h1>
            <p class="hero-subtitle-lead">{{ $pageContent['subtitle'] }}</p>

            <div class="hero-stats-grid">
                <div class="hero-stat-card">
                    <div class="hero-stat-value">15+</div>
                    <p class="hero-stat-desc">{{ __('Duration Options') }}</p>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-value">5-Star</div>
                    <p class="hero-stat-desc">{{ __('Luxury Comfort') }}</p>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-value">{{ $totalPackages > 0 ? $totalPackages . '+' : '20+' }}</div>
                    <p class="hero-stat-desc">{{ __('Vacation Journeys') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview Section -->
    <section class="packages-section py-5 my-4">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 880px;">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                    style="background: rgba(243, 107, 10, 0.15); color: #F36B0A; font-weight: 700; font-size: 0.85rem;">
                    <i class="la la-gem"></i> {{ __('Curated Vacation Itineraries') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">
                    {{ $pageContent['overview_title'] }}
                </h2>
                <p class="text-muted lead fs-6" style="line-height: 1.8;">
                    {{ $pageContent['overview_text'] }}
                </p>
            </div>

            <!-- Package Cards Grid -->
            <div class="row g-4">
                @foreach ($packageCards as $card)
                    <div class="col-lg-4 col-md-6">
                        <div class="package-duration-card">
                            <div class="package-img-wrap">
                                <img src="{{ asset($card['image']) }}" alt="{{ $card['title'] }}" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ asset('website/photos/Dest/Egypt.webp') }}';">
                                <div class="package-badge-tag">
                                    <i class="la la-clock"></i> {{ $card['badge'] }}
                                </div>
                            </div>
                            <div class="package-card-body">
                                <h3 class="package-card-title">
                                    <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                                </h3>
                                <p class="package-card-desc">{{ $card['desc'] }}</p>

                                <div class="mt-auto">
                                    <a href="{{ $card['url'] }}" class="package-explore-btn">
                                        <span>{{ __('Explore Packages') }}</span>
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Explore Egypt Tours by Travel Style Section -->
    <section class="py-5 my-4 bg-light">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 880px;">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                    style="background: rgba(243, 107, 10, 0.15); color: #F36B0A; font-weight: 700; font-size: 0.85rem;">
                    <i class="la la-compass"></i> {{ __('Travel Styles') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">
                    {{ __('Explore Egypt Tours by Travel Style') }}
                </h2>
                <p class="text-muted lead fs-6" style="line-height: 1.8;">
                    {{ __('Find the ideal travel style for your journey, from luxury vacations and private tours to family getaways.') }}
                </p>
            </div>

            <div class="row g-4">
                @foreach ($featuredCategories as $card)
                    <div class="col-lg-3 col-md-6">
                        <div class="package-duration-card">
                            <div class="package-img-wrap">
                                <img src="{{ asset($card['image']) }}" alt="{{ $card['title'] }}" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ asset('website/photos/Dest/Egypt.webp') }}';">
                                <div class="package-badge-tag">
                                    <i class="la la-star"></i> {{ $card['badge'] }}
                                </div>
                            </div>
                            <div class="package-card-body">
                                <h3 class="package-card-title">
                                    <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                                </h3>
                                <p class="package-card-desc">{{ $card['desc'] }}</p>

                                <div class="mt-auto">
                                    <a href="{{ $card['url'] }}" class="package-explore-btn">
                                        <span>{{ __('Explore') }}</span>
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Why Travel With Us Section -->
    <section class="py-5 bg-light">
        <div class="container py-3">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                    style="background: rgba(6, 27, 62, 0.08); color: #0B2554; font-weight: 700; font-size: 0.85rem;">
                    <i class="la la-star"></i> {{ __('Unrivaled Hospitality') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">
                    {{ __('Why Travel with Egypt Tour Pro?') }}
                </h2>
                <p class="text-muted fs-6">
                    {{ __('Explore ancient wonders in timeless luxury with tailor-made care and 5-star standard quality.') }}
                </p>
            </div>

            <div class="row g-4">
                @foreach ($features as $feature)
                    <div class="col-lg-3 col-md-6">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="{{ $feature['icon'] }}"></i>
                            </div>
                            <h4 class="fw-bold mb-2 fs-5">{{ $feature['title'] }}</h4>
                            <p class="text-muted fs-6 mb-0">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQs Section -->
    <section class="py-5 my-3">
        <div class="container" style="max-width: 920px;">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                    style="background: rgba(243, 107, 10, 0.15); color: #F36B0A; font-weight: 700; font-size: 0.85rem;">
                    <i class="la la-question-circle"></i> {{ __('Traveler Questions') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">
                    {{ __('Egypt Tour Packages FAQs') }}
                </h2>
                <p class="text-muted fs-6">
                    {{ __('Answers to common questions when planning your Egypt vacation package.') }}</p>
            </div>

            <div class="accordion faq-accordion" id="packagesFaqAccordion">
                @foreach ($faqs as $index => $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingPkgFaq{{ $index }}">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapsePkgFaq{{ $index }}"
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-controls="collapsePkgFaq{{ $index }}">
                                <i class="la la-comment-alt me-2 text-warning"></i>
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="collapsePkgFaq{{ $index }}"
                            class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                            aria-labelledby="headingPkgFaq{{ $index }}" data-bs-parent="#packagesFaqAccordion">
                            <div class="accordion-body">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 mb-5">
        <div class="container">
            <div class="cta-box-modern">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                            style="background: rgba(255, 255, 255, 0.15); color: #ffd27d; font-weight: 700; font-size: 0.85rem;">
                            <i class="la la-gem"></i> {{ __('Customized Vacations') }}
                        </div>
                        <h2 class="h1 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">
                            {{ __('Ready to Design Your Dream Vacation?') }}
                        </h2>
                        <p class="fs-6 mb-0 text-white-50" style="max-width: 600px; line-height: 1.8;">
                            {{ __('Whether you want a quick city break or a multi-week grand tour across Egypt, our travel designers will tailor every detail to your preferences.') }}
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end d-flex flex-wrap gap-3 justify-content-lg-end">
                        <a href="{{ route('website.tailor_made.index') }}" class="cta-btn-gold">
                            <i class="la la-route"></i>
                            <span>{{ __('Plan My Vacation') }}</span>
                        </a>
                        <a href="{{ route('website.contact.index') }}" class="cta-btn-glass">
                            <i class="la la-envelope"></i>
                            <span>{{ __('Talk to an Expert') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
