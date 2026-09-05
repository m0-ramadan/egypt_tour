@extends('website.layouts.master')

@php
    $heroImage = $type->banner_url ?: $type->image_url;
@endphp

@section('title', $pageContent['title'] . ' - Etro Tours')
@section('description', $pageContent['subtitle'])
@section('keywords', 'Luxor and Aswan Nile Cruises, Standard Nile Cruises, Deluxe Nile Cruises, Ultra Deluxe Nile Cruises, Luxury Nile Cruises')
@section('image', $heroImage)

@section('css')
    @vite('resources/css/website-home.css')
    <style>
        .nile-hero {
            position: relative;
            min-height: 440px;
            display: flex;
            align-items: center;
            margin-top: -85px;
            padding: 145px 0 85px;
            color: #fff;
            background: linear-gradient(rgba(16, 33, 63, 0.75), rgba(22, 60, 103, 0.65)),
                url('{{ $heroImage }}') center/cover no-repeat;
            overflow: hidden;
        }

        .nile-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 210, 125, 0.2), transparent 35%);
        }

        .nile-hero .container {
            position: relative;
            z-index: 1;
        }

        .nile-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.24);
            backdrop-filter: blur(12px);
            font-weight: 600;
            margin-bottom: 20px;
            color: #ffd27d;
        }

        .nile-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.3rem, 5vw, 4rem);
            line-height: 1.1;
            margin-bottom: 16px;
            color: #fff;
        }

        .nile-subtitle {
            max-width: 760px;
            margin: 0 auto 28px;
            font-size: 1.08rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.92);
        }

        .cat-card {
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.07);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .cat-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.14);
        }

        .cat-img-wrapper {
            position: relative;
            height: 230px;
            overflow: hidden;
        }

        .cat-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .cat-card:hover .cat-img-wrapper img {
            transform: scale(1.06);
        }

        .cat-badge {
            position: absolute;
            top: 16px;
            left: 16px;
            background: rgba(16, 33, 63, 0.85);
            color: #ffd27d;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(8px);
        }

        .cat-body {
            padding: 26px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .cat-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.45rem;
            font-weight: 700;
            color: #10213f;
            margin-bottom: 10px;
        }

        .cat-desc {
            color: #5b6b7c;
            font-size: 0.94rem;
            line-height: 1.65;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .cat-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            border-radius: 14px;
            background: linear-gradient(135deg, #10213f, #163c67);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .cat-btn:hover {
            background: linear-gradient(135deg, #163c67, #20548a);
            color: #fff;
        }

        /* Dark Mode Support */
        html[data-theme='dark'] .cat-card {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        }

        html[data-theme='dark'] .cat-title {
            color: #f8fafc;
        }

        html[data-theme='dark'] .cat-desc {
            color: #cbd5e1;
        }

        html[data-theme='dark'] .cat-btn {
            background: linear-gradient(135deg, #c5955b, #e7b762);
            color: #0f172a;
        }

        html[data-theme='dark'] .cat-btn:hover {
            background: linear-gradient(135deg, #e7b762, #f4c36a);
            color: #0f172a;
        }
    </style>
@endsection

@section('content')
    <!-- Hero -->
    <section class="nile-hero text-center">
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

                                <a href="{{ route('website.nile_cruises.luxor_aswan.category', $cat->slug) }}" class="cat-btn mt-auto">
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
                            <h3 class="h2 font-serif fw-bold text-dark mb-1" style="font-family: 'Playfair Display', serif;">
                                {{ __('Featured Luxor & Aswan Packages') }}
                            </h3>
                            <p class="text-muted mb-0">{{ __('Top-rated Nile River itineraries selected by our experts') }}</p>
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
                                            <img src="{{ $pkg['image'] }}" alt="{{ $pkg['title'] }}" width="800" height="500" loading="lazy" decoding="async">
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
