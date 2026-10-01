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
    @vite('resources/css/pages/nile-cruises-luxor-aswan.css')
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
    <section class="py-5 my-3">
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

                    <div class="row g-4">
                        @foreach ($featuredPackages as $pkg)
                            <div class="col-lg-4 col-md-6">
                                <div class="deal-card">
                                    <div class="card-image">
                                        @if (!empty($pkg['is_ultra_luxury']))
                                            <div class="badge-top">{{ __('Ultra Luxury') }}</div>
                                        @elseif (!empty($pkg['is_best_seller']))
                                            <div class="badge-top">{{ __('Best Seller') }}</div>
                                        @elseif (!empty($pkg['badge']))
                                            <div class="badge-top">{{ $pkg['badge'] }}</div>
                                        @endif

                                        @if (!empty($pkg['price']))
                                            <div class="deal-price">{{ $pkg['price'] }}</div>
                                        @endif

                                        <a href="{{ $pkg['url'] }}">
                                            <img src="{{ $pkg['image'] }}" alt="{{ $pkg['title'] }}" width="800"
                                                height="500" loading="lazy" decoding="async">
                                        </a>
                                    </div>

                                    <div class="card-body">
                                        <h3 class="deal-title">
                                            <a href="{{ $pkg['url'] }}">{{ $pkg['title'] }}</a>
                                        </h3>

                                        <div class="deal-meta">
                                            @if (!empty($pkg['duration']))
                                                <span><i class="la la-clock"></i> {{ $pkg['duration'] }}</span>
                                            @endif
                                            @if (!empty($pkg['tour_type']))
                                                <span><i class="la la-users"></i> {{ $pkg['tour_type'] }}</span>
                                            @endif
                                            @if (!empty($pkg['route_text']))
                                                <span><i class="la la-map-marker"></i> {{ $pkg['route_text'] }}</span>
                                            @endif
                                        </div>

                                        @if (!empty($pkg['description']))
                                            <p class="deal-description">{{ $pkg['description'] }}</p>
                                        @endif

                                        @if (!empty($pkg['tags']))
                                            <div class="tag-list">
                                                @foreach ($pkg['tags'] as $tag)
                                                    <span class="feature-tag">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <a href="{{ $pkg['url'] }}" class="gold-btn deal-btn">
                                            {{ $pkg['button_text'] ?? __('Explore Journey') }}
                                            <i class="la la-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
