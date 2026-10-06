@extends('website.layouts.master')

@php
    $heroImage = $type->banner_url ?: $type->image_url;
@endphp

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords',
    'Luxor and Aswan Nile Cruises, Standard Nile Cruises, Deluxe Nile Cruises, Ultra Deluxe Nile
    Cruises, Luxury Nile Cruises')
@section('image', $heroImage)

@section('css')
    @vite(['resources/css/pages/nile-cruises-luxor-aswan.css', 'resources/css/pages/packages-index.css'])
@endsection

@section('content')
    <!-- Hero -->
    <section class="nile-hero text-center" style="--hero-bg: url('{{ $heroImage }}');">
        <div class="container">
            <div class="nile-badge">
                <i class="la la-anchor"></i> {{ $pageContent['badge'] }}
            </div>
            <h1 class="nile-title">{{ $pageContent['title'] }}</h1>
            <p class="nile-subtitle">{{ $pageContent['subtitle'] }}</p>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="luxor-aswan-content py-5 my-3">
        <div class="container">
            <div class="text-center max-w-3xl mx-auto mb-5">
                <h2 class="h1 font-serif fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">
                    {{ $pageContent['overview_title'] }}
                </h2>
                <p class="text-muted lead fs-6">
                    {{ $pageContent['overview_text'] }}
                </p>
            </div>

            <!-- 4 Categories Cards -->
            <div class="row g-4 mb-5">
                @foreach ($categories as $cat)
                    <div class="col-lg-3 col-md-6">
                        <div class="cat-card">
                            <div class="cat-img-wrapper">
                                <img src="{{ $cat->image_url }}" alt="{{ $cat->display_name }}" loading="lazy">
                                <div class="cat-badge">
                                    <i class="la la-ship"></i> {{ $cat->packages_count ?? 0 }} {{ __('Packages') }}
                                </div>
                            </div>
                            <div class="cat-body">
                                <h3 class="cat-title">{{ $cat->display_name }}</h3>
                                <p class="cat-desc">{{ $cat->display_short_description }}</p>

                                <a href="{{ route('website.nile_cruises.luxor_aswan.category', $cat->slug) }}"
                                    class="cat-btn mt-auto">
                                    <span>{{ __('View Category') }}</span>
                                    <i class="la la-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Featured Packages Grid if available -->
            @if ($featuredPackages->isNotEmpty())
                <div class="pt-4 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h3 class="h2 font-serif fw-bold text-dark mb-1"
                                style="font-family: 'Playfair Display', serif;">
                                {{ __('Featured Luxor & Aswan Packages') }}
                            </h3>
                            <p class="text-muted mb-0">{{ __('Top-rated Nile River itineraries selected by our experts') }}
                            </p>
                        </div>
                    </div>

                    <div class="featured-cruises-grid row g-4">
                        @foreach ($featuredPackages as $pkg)
                            <div class="col-lg-4 col-md-6">
                                <article class="journey-card">
                                    <div class="journey-image">
                                        <div class="journey-type">{{ $pkg['type_label'] ?? __('Nile Cruise') }}</div>

                                        @if (!empty($pkg['badge']))
                                            <div class="journey-badge">{{ $pkg['badge'] }}</div>
                                        @elseif (!empty($pkg['is_ultra_luxury']))
                                            <div class="journey-badge">{{ __('Ultra Luxury') }}</div>
                                        @elseif (!empty($pkg['is_best_seller']))
                                            <div class="journey-badge">{{ __('Best Seller') }}</div>
                                        @endif

                                        <div class="destination-watermark-logo" style="position: absolute; top: 12px; left: 12px; z-index: 3; pointer-events: none; opacity: 0.85;">
                                            <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}"
                                                alt="Egypt Tour Pro" width="96" height="39" style="height: 28px; width: auto;" loading="lazy" decoding="async">
                                        </div>

                                        <a href="{{ $pkg['url'] }}">
                                            <img src="{{ $pkg['image'] }}" alt="{{ $pkg['title'] }}" width="800"
                                                height="500" loading="lazy" decoding="async">
                                        </a>

                                        @if (!empty($pkg['price']))
                                            <div class="journey-price">{{ $pkg['price'] }}</div>
                                        @endif
                                    </div>

                                    <div class="journey-body">
                                        @if (!empty($pkg['country']))
                                            <div class="journey-country">{{ $pkg['country'] }}</div>
                                        @elseif (!empty($pkg['route_text']))
                                            <div class="journey-country"><i class="la la-map-marker me-1"></i>{{ $pkg['route_text'] }}</div>
                                        @endif

                                        <h3 class="journey-title">
                                            <a href="{{ $pkg['url'] }}">{{ $pkg['title'] }}</a>
                                        </h3>

                                        <div class="journey-meta">
                                            @if (!empty($pkg['duration']))
                                                <span><i class="la la-clock"></i> {{ $pkg['duration'] }}</span>
                                            @endif
                                            @if (!empty($pkg['tour_type']))
                                                <span><i class="la la-users"></i> {{ $pkg['tour_type'] }}</span>
                                            @endif
                                        </div>

                                        @if (!empty($pkg['schedule']))
                                            <div class="journey-schedule">
                                                <i class="la la-calendar-alt"></i>
                                                <span>{{ $pkg['schedule'] }}</span>
                                            </div>
                                        @endif

                                        @if (!empty($pkg['description']))
                                            <p class="journey-description">{{ $pkg['description'] }}</p>
                                        @endif

                                        @if (!empty($pkg['highlights']))
                                            <div class="journey-highlights">
                                                @foreach ($pkg['highlights'] as $highlight)
                                                    <span>{{ $highlight }}</span>
                                                @endforeach
                                            </div>
                                        @elseif (!empty($pkg['tags']))
                                            <div class="journey-highlights">
                                                @foreach ($pkg['tags'] as $tag)
                                                    <span>{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <a href="{{ $pkg['url'] }}" class="journey-btn">
                                            {{ $pkg['button_text'] ?? __('Explore Journey') }}
                                            <i class="la la-arrow-right"></i>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
