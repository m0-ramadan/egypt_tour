@extends('website.layouts.master')

@php
    $heroImage = asset('website/images/nile-cruises/luxor-aswan.jpg');
@endphp

@section('title', $pageContent['title'] . ' - Etro Tours')
@section('description', $pageContent['subtitle'])
@section('keywords', 'Egypt Nile Cruise, Luxor Aswan Nile Cruise, Dahabiya Nile Cruise, Lake Nasser Cruise, Nile River Voyages')
@section('image', $heroImage)

@section('css')
    <style>
        .nile-hero {
            position: relative;
            min-height: 480px;
            display: flex;
            align-items: center;
            margin-top: -85px;
            padding: 150px 0 90px;
            color: #fff;
            background: linear-gradient(rgba(16, 33, 63, 0.75), rgba(22, 60, 103, 0.65)),
                url('{{ $heroImage }}') center/cover no-repeat;
            overflow: hidden;
        }

        .nile-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 210, 125, 0.22), transparent 35%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.08), transparent 30%);
        }

        .nile-hero .container,
        .nile-section .container {
            position: relative;
            z-index: 1;
        }

        .nile-badge {
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

        .nile-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 5.5vw, 4.2rem);
            line-height: 1.1;
            margin-bottom: 18px;
            color: #fff;
        }

        .nile-subtitle {
            max-width: 780px;
            margin: 0 auto 34px;
            font-size: 1.12rem;
            line-height: 1.85;
            color: rgba(255, 255, 255, 0.92);
        }

        .nile-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            max-width: 760px;
            margin: 0 auto;
        }

        .nile-stat {
            padding: 20px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(14px);
            text-align: center;
        }

        .nile-stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffd27d;
            margin-bottom: 4px;
        }

        .nile-stat-label {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
        }

        .cruise-type-card {
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.08);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .cruise-type-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.14);
        }

        .cruise-type-img-wrapper {
            position: relative;
            height: 280px;
            overflow: hidden;
        }

        .cruise-type-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .cruise-type-card:hover .cruise-type-img-wrapper img {
            transform: scale(1.06);
        }

        .cruise-type-badge {
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

        .cruise-type-body {
            padding: 30px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .cruise-type-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.65rem;
            font-weight: 700;
            color: #10213f;
            margin-bottom: 12px;
        }

        .cruise-type-desc {
            color: #5b6b7c;
            font-size: 0.98rem;
            line-height: 1.7;
            margin-bottom: 22px;
            flex-grow: 1;
        }

        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
        }

        .category-chip {
            background: #f0f4f9;
            color: #163c67;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .category-chip:hover {
            background: #163c67;
            color: #fff;
        }

        .cruise-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 24px;
            border-radius: 16px;
            background: linear-gradient(135deg, #10213f, #163c67);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(16, 33, 63, 0.18);
        }

        .cruise-btn:hover {
            background: linear-gradient(135deg, #163c67, #20548a);
            color: #fff;
            box-shadow: 0 12px 28px rgba(16, 33, 63, 0.28);
        }

        .feature-box {
            background: #fff;
            padding: 32px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .feature-icon {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: rgba(22, 60, 103, 0.08);
            color: #163c67;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        /* Dark Mode Support */
        html[data-theme='dark'] .cruise-type-card {
            background: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.4) !important;
        }

        html[data-theme='dark'] .cruise-type-name {
            color: #f8fafc !important;
        }

        html[data-theme='dark'] .cruise-type-desc {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .category-chip {
            background: rgba(244, 195, 106, 0.12) !important;
            color: #f4c36a !important;
            border: 1px solid rgba(244, 195, 106, 0.25) !important;
        }

        html[data-theme='dark'] .category-chip:hover {
            background: #f4c36a !important;
            color: #0f172a !important;
        }

        html[data-theme='dark'] .cruise-btn {
            background: linear-gradient(135deg, #c5955b, #e7b762) !important;
            color: #0f172a !important;
            box-shadow: 0 8px 20px rgba(197, 149, 91, 0.25) !important;
        }

        html[data-theme='dark'] .cruise-btn:hover {
            background: linear-gradient(135deg, #e7b762, #f4c36a) !important;
            color: #0f172a !important;
        }

        html[data-theme='dark'] .feature-box {
            background: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        html[data-theme='dark'] .feature-box h4 {
            color: #f4c36a !important;
        }

        html[data-theme='dark'] .feature-box p {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .feature-icon {
            background: rgba(244, 195, 106, 0.15) !important;
            color: #f4c36a !important;
        }
    </style>
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="nile-hero">
        <div class="container text-center">
            <div class="nile-badge">
                <i class="la la-ship"></i> {{ $pageContent['badge'] }}
            </div>
            <h1 class="nile-title">{{ $pageContent['title'] }}</h1>
            <p class="nile-subtitle">{{ $pageContent['subtitle'] }}</p>

            <div class="nile-stats">
                <div class="nile-stat">
                    <div class="nile-stat-number">3</div>
                    <p class="nile-stat-label">{{ __('Main Cruise Types') }}</p>
                </div>
                <div class="nile-stat">
                    <div class="nile-stat-number">4</div>
                    <p class="nile-stat-label">{{ __('Luxury Tiers') }}</p>
                </div>
                <div class="nile-stat">
                    <div class="nile-stat-number">{{ $totalPackages > 0 ? $totalPackages : '15+' }}</div>
                    <p class="nile-stat-label">{{ __('Curated Packages') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview & Types Section -->
    <section class="nile-section py-5 my-4">
        <div class="container">
            <div class="text-center max-w-3xl mx-auto mb-5">
                <h2 class="h1 font-serif fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">
                    {{ $pageContent['overview_title'] }}
                </h2>
                <p class="text-muted lead fs-6">
                    {{ $pageContent['overview_text'] }}
                </p>
            </div>

            <!-- 3 Main Cruise Types Grid -->
            <div class="row g-4">
                @foreach ($types as $type)
                    @php
                        $targetUrl = match ($type->slug) {
                            'luxor-aswan-nile-cruises' => route('website.nile_cruises.luxor_aswan'),
                            default => route('website.nile_cruises.type', $type->slug),
                        };
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="cruise-type-card">
                            <div class="cruise-type-img-wrapper">
                                <img src="{{ $type->image_url }}" alt="{{ $type->display_name }}" loading="lazy">
                                <div class="cruise-type-badge">
                                    <i class="la la-ship"></i> {{ $type->packages_count ?? 0 }} {{ __('Packages') }}
                                </div>
                            </div>
                            <div class="cruise-type-body">
                                <h3 class="cruise-type-name">{{ $type->display_name }}</h3>
                                <p class="cruise-type-desc">{{ $type->display_short_description }}</p>

                                @if ($type->slug === 'luxor-aswan-nile-cruises')
                                    <div class="category-chips">
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'standard-nile-cruises') }}" class="category-chip">
                                            {{ __('Standard') }}
                                        </a>
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'deluxe-nile-cruises') }}" class="category-chip">
                                            {{ __('Deluxe') }}
                                        </a>
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'ultra-deluxe-nile-cruises') }}" class="category-chip">
                                            {{ __('Ultra Deluxe') }}
                                        </a>
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'luxury-nile-cruises') }}" class="category-chip">
                                            {{ __('Luxury') }}
                                        </a>
                                    </div>
                                @endif

                                <div class="mt-auto">
                                    <a href="{{ $targetUrl }}" class="cruise-btn">
                                        <span>{{ __('Explore Cruises') }}</span>
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

    <!-- Why Book Nile Cruise With Us -->
    <section class="py-5 bg-light">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h2 class="h1 font-serif fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">
                    {{ __('Why Choose EtroTours Nile Cruises?') }}
                </h2>
                <p class="text-muted fs-6">{{ __('Experience seamless river cruising with unmatched local expertise and 5-star standard service.') }}</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="la la-shield-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-2">{{ __('Handpicked Cruise Ships') }}</h4>
                        <p class="text-muted fs-6 mb-0">
                            {{ __('We inspect every vessel to ensure maximum comfort, fine dining, hygienic safety, and optimal itineraries.') }}
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="la la-user-tie"></i>
                        </div>
                        <h4 class="fw-bold mb-2">{{ __('Expert Egyptologists') }}</h4>
                        <p class="text-muted fs-6 mb-0">
                            {{ __('All shore excursions in Luxor, Kom Ombo, Edfu, and Aswan are guided by licensed expert Egyptologists.') }}
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="la la-headset"></i>
                        </div>
                        <h4 class="fw-bold mb-2">{{ __('24/7 Dedicated Support') }}</h4>
                        <p class="text-muted fs-6 mb-0">
                            {{ __('From pick-up transfers to embarkation and departure, our dedicated team is available every step of the journey.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
