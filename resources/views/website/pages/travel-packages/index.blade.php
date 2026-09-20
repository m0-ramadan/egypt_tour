@extends('website.layouts.master')

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords', 'Egypt Vacation Packages, Egypt Tours 2026, 7 Days Egypt Tour, 10 Days Egypt Vacation, Luxury Egypt
    Tours, Egypt Holidays, Nile Cruise Packages')
@section('image', $heroImage)

@section('css')
    <style>
        .page-breadcrumb {
            background: var(--etp-surface-muted, #f8fafc);
            border-bottom: 1px solid rgba(243, 107, 10, 0.16);
            padding: 16px 0;
        }

        .page-breadcrumb .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
        }

        .page-breadcrumb .breadcrumb-item,
        .page-breadcrumb .breadcrumb-item a {
            color: #061B3E;
            font-size: 0.95rem;
            text-decoration: none;
        }

        .page-breadcrumb .breadcrumb-item.active {
            color: #F36B0A;
            font-weight: 700;
        }

        .packages-hero {
            position: relative;
            min-height: 490px;
            display: flex;
            align-items: center;
            margin-top: -85px;
            padding: 150px 0 90px;
            color: #fff !important;
            background: linear-gradient(rgba(16, 33, 63, 0.78), rgba(22, 60, 103, 0.68)),
                url('{{ $heroImage }}') center/cover no-repeat;
            overflow: hidden;
        }

        .packages-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(243, 107, 10, 0.18), transparent 35%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.08), transparent 30%);
        }

        .packages-hero .container,
        .packages-section .container {
            position: relative;
            z-index: 1;
        }

        .hero-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            font-weight: 600;
            margin-bottom: 22px;
            color: #ffd27d;
        }

        .hero-title-main {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5.5vw, 4.2rem);
            line-height: 1.12;
            margin-bottom: 18px;
            color: #fff !important;
        }

        .hero-subtitle-lead {
            max-width: 820px;
            margin: 0 auto 34px;
            font-size: 1.12rem;
            line-height: 1.85;
            color: rgba(255, 255, 255, 0.92);
        }

        .hero-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            max-width: 760px;
            margin: 0 auto;
        }

        .hero-stat-card {
            padding: 20px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(14px);
            text-align: center;
        }

        .hero-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffd27d;
            margin-bottom: 4px;
        }

        .hero-stat-desc {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
        }

        /* Package Duration Cards */
        .package-duration-card {
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.08);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .package-duration-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.14);
        }

        .package-img-wrap {
            position: relative;
            height: 270px;
            overflow: hidden;
        }

        .package-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .package-duration-card:hover .package-img-wrap img {
            transform: scale(1.06);
        }

        .package-badge-tag {
            position: absolute;
            top: 18px;
            left: 18px;
            background: rgba(16, 33, 63, 0.85);
            color: #ffd27d;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            backdrop-filter: blur(8px);
        }

        .package-card-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .package-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #061B3E;
            margin-bottom: 12px;
            line-height: 1.28;
        }

        .package-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .package-card-title a:hover {
            color: #F36B0A;
        }

        .package-card-desc {
            color: #5b6b7c;
            font-size: 0.96rem;
            line-height: 1.7;
            margin-bottom: 22px;
            flex-grow: 1;
        }

        .package-explore-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 13px 24px;
            border-radius: 16px;
            background: linear-gradient(135deg, #061B3E, #0B2554);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(16, 33, 63, 0.18);
        }

        .package-explore-btn:hover {
            background: linear-gradient(135deg, #0B2554, #103570);
            color: #fff;
            box-shadow: 0 12px 28px rgba(16, 33, 63, 0.28);
        }

        /* Feature Boxes */
        .feature-box {
            background: #fff;
            padding: 32px 24px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: rgba(6, 27, 62, 0.08);
            color: #0B2554;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        /* FAQs Accordion */
        .faq-accordion .accordion-item {
            border: 1px solid rgba(16, 33, 63, 0.08);
            border-radius: 16px !important;
            margin-bottom: 14px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            background: #fff;
        }

        .faq-accordion .accordion-button {
            padding: 20px 24px;
            font-size: 1.06rem;
            font-weight: 600;
            color: #061B3E;
            background: #fff;
            box-shadow: none;
        }

        .faq-accordion .accordion-button:not(.collapsed) {
            color: #F36B0A;
            background: #fdfaf6;
        }

        .faq-accordion .accordion-button::after {
            filter: grayscale(1);
        }

        .faq-accordion .accordion-body {
            padding: 20px 24px 24px;
            color: #5b6b7c;
            font-size: 0.98rem;
            line-height: 1.8;
            background: #fdfaf6;
        }

        /* CTA */
        .cta-box-modern {
            background: linear-gradient(135deg, #061B3E 0%, #0B2554 100%);
            border-radius: 28px;
            padding: 60px 48px;
            color: #fff;
            box-shadow: 0 20px 50px rgba(16, 33, 63, 0.25);
            position: relative;
            overflow: hidden;
        }

        .cta-box-modern::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 210, 125, 0.2), transparent 45%);
        }

        .cta-box-modern .cta-btn-gold {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            border-radius: 999px;
            background: linear-gradient(135deg, #F36B0A, #FF8A33);
            color: #0f172a;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(197, 149, 91, 0.35);
        }

        .cta-box-modern .cta-btn-gold:hover {
            background: linear-gradient(135deg, #FF8A33, #f4c36a);
            color: #0f172a;
            transform: translateY(-2px);
        }

        .cta-box-modern .cta-btn-glass {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-weight: 600;
            text-decoration: none;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .cta-box-modern .cta-btn-glass:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        /* Dark Mode */
        html[data-theme='dark'] .page-breadcrumb {
            background: #0b1120 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        html[data-theme='dark'] .page-breadcrumb .breadcrumb-item a {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .package-duration-card {
            background: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.4) !important;
        }

        html[data-theme='dark'] .package-card-title {
            color: #f8fafc !important;
        }

        html[data-theme='dark'] .package-card-desc {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .package-explore-btn {
            background: linear-gradient(135deg, #F36B0A, #FF8A33) !important;
            color: #0f172a !important;
        }

        html[data-theme='dark'] .feature-box {
            background: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        html[data-theme='dark'] .feature-box h4 {
            color: #f4c36a !important;
        }

        html[data-theme='dark'] .feature-box p {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .faq-accordion .accordion-item {
            background: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        html[data-theme='dark'] .faq-accordion .accordion-button {
            background: #111827 !important;
            color: #f8fafc !important;
        }

        html[data-theme='dark'] .faq-accordion .accordion-button:not(.collapsed) {
            color: #f4c36a !important;
            background: #1e293b !important;
        }

        html[data-theme='dark'] .faq-accordion .accordion-body {
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }

        @media (max-width: 768px) {
            .hero-stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .cta-box-modern {
                padding: 40px 24px;
                text-align: center;
            }
        }
    </style>
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
    <section class="packages-hero">
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
                                    onerror="this.onerror=null;this.src='{{ asset('website/photos/Dest/Egypt.jpg') }}';">
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
                    {{ __('Egypt Tours Packages FAQs') }}
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
