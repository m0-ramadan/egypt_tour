@extends('website.layouts.master')

@php
    $customHeroCruises = \App\Models\Setting::where('key', 'hero_image_cruises')->value('value');
    $heroImage = $customHeroCruises
        ? asset($customHeroCruises)
        : asset('website/images/nile-cruises/hero-nile-cruises.jpg');
@endphp

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords',
    'Egypt Nile Cruise, Luxor Aswan Nile Cruise, Dahabiya Nile Cruise, Lake Nasser Cruise, Nile River
    Voyages')
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/nile-cruises-index.css')
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="nile-hero" style="--hero-bg: url(\'{{ $heroImage }}\');">
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
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'standard-nile-cruises') }}"
                                            class="category-chip">
                                            {{ __('Standard') }}
                                        </a>
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'deluxe-nile-cruises') }}"
                                            class="category-chip">
                                            {{ __('Deluxe') }}
                                        </a>
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'ultra-deluxe-nile-cruises') }}"
                                            class="category-chip">
                                            {{ __('Ultra Deluxe') }}
                                        </a>
                                        <a href="{{ route('website.nile_cruises.luxor_aswan.category', 'luxury-nile-cruises') }}"
                                            class="category-chip">
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
                    {{ __('Why Choose Egypt Tour Pro Nile Cruises?') }}
                </h2>
                <p class="text-muted fs-6">
                    {{ __('Experience seamless river cruising with unmatched local expertise and 5-star standard service.') }}
                </p>
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
