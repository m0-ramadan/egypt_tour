@extends('website.layouts.master')

@php
    $cityName = $attraction->city?->display_name ?: __('Destination');
    $countryName = $attraction->city?->country?->display_name ?: __('Egypt');
    $cityRoute = $attraction->city?->slug
        ? route('website.destinations.show', $attraction->city->slug)
        : route('website.destinations.index');
    $countryRoute = $attraction->city?->country?->slug
        ? route('website.destinations.index', ['country' => $attraction->city->country->slug])
        : route('website.destinations.index');

    $heroSubtitle = $shortDescription !== ''
        ? $shortDescription
        : __('Explore :name, one of the iconic places to visit in :city.', [
            'name' => $attraction->display_name,
            'city' => $cityName,
        ]);
@endphp

@section('title', $pageTitle . ' - Etro Tours')
@section('description', $pageDescription)
@section('keywords', trim(collect([$attraction->display_name, $cityName, $countryName, 'Attraction details', 'Etro Tours'])->filter()->implode(', '), ', '))
@section('image', $heroImage)

@section('css')
    <style>
        .attraction-breadcrumb {
            background: var(--pearl-luxury, #faf8f3);
            border-bottom: 1px solid rgba(197, 149, 91, 0.16);
            padding: 16px 0;
        }

        .attraction-breadcrumb .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
        }

        .attraction-breadcrumb .breadcrumb-item,
        .attraction-breadcrumb .breadcrumb-item a {
            color: #1c325c;
            font-size: 0.95rem;
            text-decoration: none;
        }

        .attraction-breadcrumb .breadcrumb-item.active {
            color: #9b6a2c;
            font-weight: 700;
        }

        .attraction-hero {
            position: relative;
            min-height: 480px;
            margin-top: -85px;
            padding: 145px 0 80px;
            color: #fff;
            background:
                linear-gradient(rgba(16, 33, 63, 0.72), rgba(18, 61, 102, 0.65)),
                url('{{ $heroImage }}') center/cover no-repeat;
            overflow: hidden;
        }

        .attraction-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 210, 125, 0.18), transparent 30%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.08), transparent 35%);
        }

        .attraction-hero .container,
        .attraction-overview .container,
        .attraction-journeys .container,
        .related-attractions .container,
        .attraction-cta .container {
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.24);
            backdrop-filter: blur(12px);
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
        }

        .hero-badge i {
            color: #ffd27d;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 5.5vw, 4.2rem);
            line-height: 1.08;
            margin-bottom: 18px;
            color: #fff;
        }

        .hero-subtitle {
            max-width: 780px;
            font-size: 1.1rem;
            line-height: 1.85;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 30px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .hero-btn,
        .hero-btn-outline,
        .cta-btn,
        .journey-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        }

        .hero-btn,
        .cta-btn,
        .journey-btn {
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            box-shadow: 0 12px 26px rgba(197, 149, 91, 0.22);
            min-height: 52px;
            padding: 0 24px;
            border-radius: 18px;
        }

        .hero-btn-outline {
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            min-height: 52px;
            padding: 0 24px;
            border-radius: 18px;
        }

        .hero-btn:hover,
        .cta-btn:hover,
        .journey-btn:hover {
            transform: translateY(-2px);
            color: #1c325c;
        }

        .hero-btn-outline:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            transform: translateY(-2px);
        }

        .attraction-overview {
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            padding: 72px 0 88px;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 36px;
            align-items: start;
        }

        .overview-panel,
        .sidebar-card,
        .journey-card,
        .attraction-card-item,
        .cta-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 18px 46px rgba(16, 33, 63, 0.08);
        }

        .overview-panel {
            padding: 38px;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: #9b6a2c;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .overview-panel h2,
        .section-heading h2,
        .cta-card h2 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .overview-panel h2 {
            font-size: clamp(2rem, 3.5vw, 2.6rem);
        }

        .overview-body {
            color: #4a5568;
            line-height: 1.95;
            font-size: 1.05rem;
        }

        .overview-body p {
            margin-bottom: 1.4rem;
        }

        .overview-body p:last-child {
            margin-bottom: 0;
        }

        .sidebar-card {
            padding: 30px;
            position: sticky;
            top: 100px;
        }

        .sidebar-card h3 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 1.45rem;
            margin-bottom: 20px;
        }

        .fact-list {
            display: grid;
            gap: 14px;
            margin-bottom: 24px;
        }

        .fact-item {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            padding: 14px 18px;
            border-radius: 18px;
            background: #f7fafc;
        }

        .fact-item span {
            color: #5b6776;
            font-size: 0.94rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .fact-item span i {
            color: #c5955b;
            font-size: 1.1rem;
        }

        .fact-item strong {
            color: #1c325c;
            font-weight: 700;
            text-align: right;
            font-size: 0.96rem;
        }

        .map-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 50px;
            border-radius: 16px;
            background: #f4f7fb;
            color: #1c325c;
            border: 1px solid rgba(26, 54, 93, 0.12);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .map-btn:hover {
            background: #1c325c;
            color: #fff;
            border-color: #1c325c;
        }

        .attraction-journeys {
            background: linear-gradient(180deg, #f7fafc 0%, #ffffff 100%);
            padding: 88px 0 92px;
        }

        .section-heading {
            max-width: 780px;
            margin-bottom: 36px;
        }

        .section-heading h2 {
            font-size: clamp(1.9rem, 3.8vw, 2.6rem);
        }

        .section-heading p {
            color: #5b6776;
            line-height: 1.85;
        }

        .results-grid {
            row-gap: 26px;
        }

        .journey-card {
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.32s ease, box-shadow 0.32s ease, border-color 0.32s ease;
        }

        .journey-card:hover {
            transform: translateY(-8px);
            border-color: rgba(197, 149, 91, 0.34);
            box-shadow: 0 24px 52px rgba(16, 33, 63, 0.14);
        }

        .journey-image {
            position: relative;
            height: 245px;
            overflow: hidden;
            background: #dbe6f2;
        }

        .journey-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .journey-card:hover .journey-image img {
            transform: scale(1.07);
        }

        .journey-type,
        .journey-badge,
        .journey-price {
            position: absolute;
            z-index: 2;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
        }

        .journey-type {
            top: 16px;
            left: 16px;
            color: #fff;
            background: rgba(16, 33, 63, 0.82);
        }

        .journey-badge {
            top: 16px;
            right: 16px;
            color: #1c325c;
            background: rgba(255, 210, 125, 0.95);
        }

        .journey-price {
            left: 16px;
            right: 16px;
            bottom: 16px;
            text-align: center;
            color: #1c325c;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 12px 28px rgba(16, 33, 63, 0.14);
        }

        .journey-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .journey-country {
            color: #9b6a2c;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 10px;
        }

        .journey-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            line-height: 1.35;
            margin-bottom: 14px;
        }

        .journey-title a {
            color: #1c325c;
            text-decoration: none;
        }

        .journey-title a:hover {
            color: #c5955b;
        }

        .journey-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .journey-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #f4f7fb;
            color: #425466;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .journey-meta i {
            color: #c5955b;
        }

        .journey-description {
            color: #5b6776;
            line-height: 1.8;
            font-size: 0.94rem;
            margin-bottom: 20px;
        }

        .journey-btn {
            margin-top: auto;
            width: 100%;
            border-radius: 18px;
            padding: 13px 18px;
        }

        .related-attractions {
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            padding: 80px 0 90px;
        }

        .attraction-card-item {
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .attraction-card-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 48px rgba(16, 33, 63, 0.12);
        }

        .attraction-card-img {
            height: 220px;
            overflow: hidden;
            background: #dbe6f2;
        }

        .attraction-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.45s ease;
        }

        .attraction-card-item:hover .attraction-card-img img {
            transform: scale(1.06);
        }

        .attraction-card-body {
            padding: 22px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .attraction-card-title {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .attraction-card-title a {
            color: #1c325c;
            text-decoration: none;
        }

        .attraction-card-title a:hover {
            color: #c5955b;
        }

        .attraction-card-desc {
            color: #5b6776;
            font-size: 0.92rem;
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .attraction-card-link {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #c5955b;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.92rem;
        }

        .attraction-card-link:hover {
            color: #1c325c;
        }

        .attraction-cta {
            background: linear-gradient(135deg, #0f2749 0%, #123d66 100%);
            padding: 88px 0 88px;
        }

        .cta-card {
            padding: 38px;
            background:
                radial-gradient(circle at top right, rgba(255, 210, 125, 0.14), transparent 30%),
                #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
            flex-wrap: wrap;
        }

        .cta-card p {
            color: #5b6776;
            line-height: 1.85;
        }

        .cta-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .cta-btn.secondary {
            background: #f8fbff;
            color: #1c325c;
            border: 1px solid rgba(26, 54, 93, 0.12);
            box-shadow: none;
        }

        /* Dark Theme Support */
        html[data-theme='dark'] .attraction-breadcrumb {
            background: #0b1220 !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
        }

        html[data-theme='dark'] .attraction-breadcrumb .breadcrumb-item,
        html[data-theme='dark'] .attraction-breadcrumb .breadcrumb-item a,
        html[data-theme='dark'] .overview-panel h2,
        html[data-theme='dark'] .sidebar-card h3,
        html[data-theme='dark'] .section-heading h2,
        html[data-theme='dark'] .journey-title a,
        html[data-theme='dark'] .attraction-card-title a,
        html[data-theme='dark'] .cta-card h2 {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .attraction-overview,
        html[data-theme='dark'] .attraction-journeys,
        html[data-theme='dark'] .related-attractions {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
        }

        html[data-theme='dark'] .overview-panel,
        html[data-theme='dark'] .sidebar-card,
        html[data-theme='dark'] .journey-card,
        html[data-theme='dark'] .attraction-card-item,
        html[data-theme='dark'] .cta-card {
            background: #111827 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        html[data-theme='dark'] .overview-body,
        html[data-theme='dark'] .section-heading p,
        html[data-theme='dark'] .journey-description,
        html[data-theme='dark'] .attraction-card-desc,
        html[data-theme='dark'] .cta-card p,
        html[data-theme='dark'] .fact-item span {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .fact-item,
        html[data-theme='dark'] .map-btn,
        html[data-theme='dark'] .journey-meta span,
        html[data-theme='dark'] .cta-btn.secondary {
            background: #172033 !important;
            color: var(--warm-gray) !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
        }

        html[data-theme='dark'] .fact-item strong,
        html[data-theme='dark'] .journey-price {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .journey-image,
        html[data-theme='dark'] .attraction-card-img {
            background: #0f172a !important;
        }

        @media (max-width: 991px) {
            .overview-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .sidebar-card {
                position: static;
            }
        }

        @media (max-width: 767px) {
            .attraction-hero {
                min-height: 420px;
                padding: 130px 0 65px;
            }

            .hero-actions,
            .cta-actions {
                flex-direction: column;
            }

            .hero-btn,
            .hero-btn-outline,
            .cta-btn {
                width: 100%;
            }

            .overview-panel,
            .sidebar-card,
            .cta-card {
                padding: 24px;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb -->
    <section class="attraction-breadcrumb">
        <div class="container">
            <nav aria-label="{{ __('Breadcrumb') }}">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.destinations.index') }}">{{ __('Destinations') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ $countryRoute }}">{{ $countryName }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ $cityRoute }}">{{ $cityName }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $attraction->display_name }}
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Hero Banner -->
    <section class="attraction-hero">
        <div class="container">
            <div class="attraction-hero-content">
                <div class="hero-badge">
                    <i class="la la-landmark"></i>
                    {{ $cityName }} — {{ $countryName }}
                </div>

                <h1 class="hero-title">{{ $attraction->display_name }}</h1>
                <p class="hero-subtitle">{{ $heroSubtitle }}</p>

                <div class="hero-actions">
                    @if ($packages->count())
                        <a href="#attraction-tours" class="hero-btn">
                            <i class="la la-suitcase"></i>
                            {{ __('Explore Tours Visiting Here') }}
                        </a>
                    @endif
                    <a href="{{ route('website.tailor_made.index') }}" class="hero-btn-outline">
                        <i class="la la-route"></i>
                        {{ __('Customize a Tour') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview & Quick Facts -->
    <section class="attraction-overview">
        <div class="container">
            <div class="overview-grid">
                <div class="overview-panel">
                    <div class="section-kicker">
                        <i class="la la-compass"></i>
                        {{ __('About this place') }}
                    </div>
                    <h2>{{ $attraction->display_name }}</h2>

                    @if ($descriptionHtml)
                        <div class="overview-body">{!! $descriptionHtml !!}</div>
                    @else
                        <div class="overview-body">
                            <p>{{ $overviewText ?: $heroSubtitle }}</p>
                        </div>
                    @endif
                </div>

                <div class="sidebar-card">
                    <h3>{{ __('Location & Info') }}</h3>

                    <div class="fact-list">
                        <div class="fact-item">
                            <span><i class="la la-map-marker"></i> {{ __('City') }}</span>
                            <strong>{{ $cityName }}</strong>
                        </div>

                        <div class="fact-item">
                            <span><i class="la la-globe"></i> {{ __('Country') }}</span>
                            <strong>{{ $countryName }}</strong>
                        </div>

                        @if ($openingHours)
                            <div class="fact-item">
                                <span><i class="la la-clock"></i> {{ __('Opening Hours') }}</span>
                                <strong>{{ $openingHours }}</strong>
                            </div>
                        @endif

                        @if ($attraction->latitude && $attraction->longitude)
                            <div class="fact-item">
                                <span><i class="la la-map-pin"></i> {{ __('Coordinates') }}</span>
                                <strong>{{ $attraction->latitude }}, {{ $attraction->longitude }}</strong>
                            </div>
                        @endif
                    </div>

                    @if ($attraction->map_url)
                        <a href="{{ $attraction->map_url }}" target="_blank" rel="noopener noreferrer" class="map-btn">
                            <i class="la la-map"></i>
                            {{ __('View on Google Maps') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Tours & Packages Visiting This Attraction -->
    @if ($packages->count())
        <section class="attraction-journeys" id="attraction-tours">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker">
                        <i class="la la-suitcase"></i>
                        {{ __('Featured Itineraries') }}
                    </div>
                    <h2>{{ __('Tours & Trips Visiting :name', ['name' => $attraction->display_name]) }}</h2>
                    <p>
                        {{ __('Discover handpicked itineraries that include :name as part of a complete luxury travel experience.', ['name' => $attraction->display_name]) }}
                    </p>
                </div>

                <div class="row results-grid">
                    @foreach ($packages as $package)
                        <div class="col-lg-4 col-md-6">
                            <article class="journey-card">
                                <div class="journey-image">
                                    <div class="journey-type">{{ $package['type_label'] }}</div>

                                    @if ($package['badge'])
                                        <div class="journey-badge">{{ $package['badge'] }}</div>
                                    @endif

                                    <a href="{{ $package['url'] }}">
                                        <img src="{{ $package['image'] }}" alt="{{ $package['title'] }}" loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                    </a>

                                    @if ($package['price'])
                                        <div class="journey-price">{{ $package['price'] }}</div>
                                    @endif
                                </div>

                                <div class="journey-body">
                                    @if ($package['country'])
                                        <div class="journey-country">{{ $package['country'] }}</div>
                                    @endif

                                    <h3 class="journey-title">
                                        <a href="{{ $package['url'] }}">{{ $package['title'] }}</a>
                                    </h3>

                                    <div class="journey-meta">
                                        <span><i class="la la-clock"></i>{{ $package['duration'] }}</span>
                                        <span><i class="la la-users"></i>{{ $package['tour_type'] }}</span>
                                    </div>

                                    <p class="journey-description">{{ $package['description'] }}</p>

                                    <a href="{{ $package['url'] }}" class="journey-btn">
                                        {{ $package['button_text'] }}
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($packages->hasPages())
                    <div class="pagination-wrap mt-4 d-flex justify-content-center">
                        {{ $packages->links() }}
                    </div>
                @endif
            </div>
        </section>
    @endif

    <!-- Related Attractions in same city -->
    @if ($relatedAttractions->count())
        <section class="related-attractions">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker">
                        <i class="la la-landmark"></i>
                        {{ __('Nearby Places') }}
                    </div>
                    <h2>{{ __('Other Highlights in :city', ['city' => $cityName]) }}</h2>
                    <p>
                        {{ __('Explore other top attractions and landmarks to include in your :city trip.', ['city' => $cityName]) }}
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ($relatedAttractions as $rel)
                        @php
                            $relImg = $rel->image ? asset('storage/' . ltrim($rel->image, '/')) : asset('website/photos/home2.webp');
                            $relDesc = Str::limit(trim(strip_tags($rel->display_short_description ?: $rel->display_description)), 120);
                        @endphp
                        <div class="col-lg-3 col-md-6">
                            <article class="attraction-card-item">
                                <div class="attraction-card-img">
                                    <img src="{{ $relImg }}" alt="{{ $rel->display_name }}" loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                </div>
                                <div class="attraction-card-body">
                                    <h3 class="attraction-card-title">
                                        <a href="{{ route('website.attractions.show', $rel->slug) }}">{{ $rel->display_name }}</a>
                                    </h3>
                                    <p class="attraction-card-desc">{{ $relDesc }}</p>
                                    <a href="{{ route('website.attractions.show', $rel->slug) }}" class="attraction-card-link">
                                        {{ __('Explore Place') }}
                                        <i class="la {{ app()->getLocale() === 'ar' ? 'la-angle-left' : 'la-angle-right' }}"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Tailor Made CTA -->
    <section class="attraction-cta">
        <div class="container">
            <div class="cta-card">
                <div>
                    <div class="section-kicker">
                        <i class="la la-gem"></i>
                        {{ __('Customized Experience') }}
                    </div>
                    <h2>{{ __('Want to visit :name on your terms?', ['name' => $attraction->display_name]) }}</h2>
                    <p>
                        {{ __('Our travel experts can build a private itinerary tailored to your preferences including :name and surrounding attractions.', ['name' => $attraction->display_name]) }}
                    </p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('website.tailor_made.index') }}" class="cta-btn">
                        <i class="la la-route"></i>
                        {{ __('Design Custom Tour') }}
                    </a>
                    <a href="{{ route('website.contact.index') }}" class="cta-btn secondary">
                        <i class="la la-envelope"></i>
                        {{ __('Contact Us') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
