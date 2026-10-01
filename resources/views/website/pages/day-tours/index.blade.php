@extends('website.layouts.master')

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords', 'Egypt Day Tours, Cairo Excursions, Luxor Day Tours, Aswan Day Tours, Hurghada Excursions, Sharm El
    Sheikh Tours, Marsa Alam Tours, Dahab Tours')
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/day-tours-index.css')
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
                        {{ __('Day Tours') }}
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="day-tour-hero">
        <div class="container text-center">
            <div class="hero-badge-pill">
                <i class="la la-compass"></i> {{ $pageContent['badge'] }}
            </div>
            <h1 class="hero-title-main">{{ $pageContent['title'] }}</h1>
            <p class="hero-subtitle-lead">{{ $pageContent['subtitle'] }}</p>

            <div class="hero-stats-grid">
                <div class="hero-stat-card">
                    <div class="hero-stat-value">7</div>
                    <p class="hero-stat-desc">{{ __('Iconic Destinations') }}</p>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-value">100%</div>
                    <p class="hero-stat-desc">{{ __('Private & Tailored') }}</p>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-value">{{ $totalDayTours > 0 ? $totalDayTours . '+' : '25+' }}</div>
                    <p class="hero-stat-desc">{{ __('Available Excursions') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview Section -->
    <section class="day-tour-section py-5 my-4">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 860px;">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                    style="background: rgba(243, 107, 10, 0.15); color: #F36B0A; font-weight: 700; font-size: 0.85rem;">
                    <i class="la la-gem"></i> {{ __('Curated Excursions') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">
                    {{ $pageContent['overview_title'] }}
                </h2>
                <p class="text-muted lead fs-6" style="line-height: 1.8;">
                    {{ $pageContent['overview_text'] }}
                </p>
            </div>

            <!-- Destination Cards Grid (7 Cities) -->
            <div class="row g-4">
                @foreach ($destinationCards as $card)
                    <div class="col-lg-4 col-md-6">
                        <div class="excursion-card">
                            <div class="excursion-img-wrap">
                                <img src="{{ asset($card['image']) }}" alt="{{ $card['title'] }}" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                <div class="excursion-badge">
                                    <i class="la la-map-marker"></i> {{ $card['badge'] }}
                                </div>
                            </div>
                            <div class="excursion-body">
                                <h3 class="excursion-title">
                                    <a href="{{ $card['url'] }}">{{ $card['title'] }}</a>
                                </h3>
                                <p class="excursion-desc">{{ $card['desc'] }}</p>

                                <div class="mt-auto">
                                    <a href="{{ $card['url'] }}" class="excursion-btn">
                                        <span>{{ __('Explore Tours') }}</span>
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
                    <i class="la la-star"></i> {{ __('Unmatched Quality') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">
                    {{ __('Why Travel with Egypt Tour Pro?') }}
                </h2>
                <p class="text-muted fs-6">
                    {{ __('Experience seamless day excursions with local certified Egyptologists and guaranteed 5-star service.') }}
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
                    <i class="la la-question-circle"></i> {{ __('Help & Insights') }}
                </div>
                <h2 class="h1 fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">
                    {{ __('Egypt Day Tours FAQs') }}
                </h2>
                <p class="text-muted fs-6">
                    {{ __('Frequently asked questions to help you prepare for memorable day trips across Egypt.') }}</p>
            </div>

            <div class="accordion faq-accordion" id="dayToursFaqAccordion">
                @foreach ($faqs as $index => $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFaq{{ $index }}">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseFaq{{ $index }}"
                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-controls="collapseFaq{{ $index }}">
                                <i class="la la-comment-alt me-2 text-warning"></i>
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="collapseFaq{{ $index }}"
                            class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                            aria-labelledby="headingFaq{{ $index }}" data-bs-parent="#dayToursFaqAccordion">
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
                            <i class="la la-magic"></i> {{ __('Tailor-Made Day Excursions') }}
                        </div>
                        <h2 class="h1 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">
                            {{ __('Ready to Plan Your Perfect Day Tour?') }}
                        </h2>
                        <p class="fs-6 mb-0 text-white-50" style="max-width: 600px; line-height: 1.8;">
                            {{ __('Tell us your dream destinations and preferred travel dates, and our travel specialists will craft an exclusive, personalized day itinerary just for you.') }}
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end d-flex flex-wrap gap-3 justify-content-lg-end">
                        <a href="{{ route('website.tailor_made.index') }}" class="cta-btn-gold">
                            <i class="la la-route"></i>
                            <span>{{ __('Build My Tour') }}</span>
                        </a>
                        <a href="{{ route('website.contact.index') }}" class="cta-btn-glass">
                            <i class="la la-envelope"></i>
                            <span>{{ __('Contact Us') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
