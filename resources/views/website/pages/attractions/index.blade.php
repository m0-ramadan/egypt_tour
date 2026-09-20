@extends('website.layouts.master')

@section('title', __('Attractions & Sights in Egypt') . ' - Egypt Tour Pro')
@section('description',
    __('Discover Egypt\'s top attractions and iconic sights. Browse temples, monuments, beaches, and
    landmarks by city to plan your perfect Egypt trip.'))

@section('body_class', 'attractions-index-page')

@section('css')
    <style>
        /* ============================================================
               Attractions Index Page Styles
               ============================================================ */
        .attractions-hero {
            position: relative;
            min-height: 380px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 100px 0 60px;
        }

        .attractions-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('{{ asset('website/photos/home2.webp') }}') center/cover no-repeat;
            opacity: 0.3;
        }

        .attractions-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }

        .attractions-hero-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 50px;
            margin-bottom: 18px;
        }

        .attractions-hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
            color: #fff !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        .attractions-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            color: #fff !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
        }

        .attractions-breadcrumb {
            background: var(--color-surface-elevated, #fff);
            border-bottom: 1px solid var(--color-border, #e8e3da);
            padding: 12px 0;
        }

        .attractions-breadcrumb ol {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }

        .attractions-breadcrumb li+li::before {
            content: '›';
            color: var(--color-text-muted, #999);
            margin-right: 8px;
        }

        .attractions-breadcrumb a {
            color: var(--color-primary, #c8860a);
            text-decoration: none;
        }

        .attractions-breadcrumb a:hover {
            text-decoration: underline;
        }

        .attractions-breadcrumb .current {
            color: var(--color-text-muted, #999);
        }

        /* Cities Grid */
        .cities-section {
            padding: 64px 0 40px;
        }

        .section-head {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-head .tag {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--color-primary, #c8860a);
            margin-bottom: 12px;
            display: block;
        }

        .section-head h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 700;
            color: var(--color-text, #1a1a1a);
            margin-bottom: 12px;
        }

        .section-head p {
            color: var(--color-text-muted, #666);
            font-size: 1rem;
            max-width: 550px;
            margin: 0 auto;
        }

        .cities-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 1100px) {
            .cities-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .cities-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
        }

        @media (max-width: 480px) {
            .cities-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
        }

        .city-card {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 3/2.5;
            display: block;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .city-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }

        .city-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .city-card:hover img {
            transform: scale(1.08);
        }

        .city-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.1) 60%, transparent 100%);
        }

        .city-card-body {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px 18px;
            color: #fff !important;
        }

        .city-card-body h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 4px;
            line-height: 1.2;
            color: #fff !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
        }

        .city-card-body .meta {
            font-size: 12px;
            color: #fff !important;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        .city-card-body .meta i {
            font-size: 13px;
            color: #fff !important;
        }

        .city-card-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--color-primary, #c8860a);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
        }

        /* Featured Attractions */
        .featured-attractions-section {
            padding: 60px 0;
            background: var(--color-surface-alt, #f9f6f1);
        }

        .attractions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 1100px) {
            .attractions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .attractions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
        }

        @media (max-width: 480px) {
            .attractions-grid {
                grid-template-columns: 1fr;
            }
        }

        .attraction-item {
            background: var(--color-surface, #fff);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .attraction-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.13);
            text-decoration: none;
        }

        .attraction-item-img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .attraction-item:hover .attraction-item-img {
            transform: scale(1.05);
        }

        .attraction-item-body {
            padding: 14px 16px 16px;
        }

        .attraction-item-city {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--color-primary, #c8860a);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .attraction-item-body h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--color-text, #1a1a1a);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .attraction-item-body p {
            font-size: 13px;
            color: var(--color-text-muted, #666);
            line-height: 1.5;
            margin: 0;
        }

        .attraction-item-arrow {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            color: var(--color-primary, #c8860a);
            margin-top: 10px;
        }

        /* Dark mode */
        html[data-theme='dark'] .section-head h2,
        html[data-theme='dark'] .attraction-item-body h3 {
            color: #f0ece4;
        }

        html[data-theme='dark'] .attraction-item {
            background: #1e1e2a;
        }

        html[data-theme='dark'] .featured-attractions-section {
            background: #141420;
        }

        html[data-theme='dark'] .attractions-breadcrumb {
            background: #1a1a28;
            border-color: #2a2a3a;
        }

        html[data-theme='dark'] .section-head p,
        html[data-theme='dark'] .attraction-item-body p {
            color: #a0a0b0;
        }
    </style>
@endsection

@section('content')

    <!-- Hero -->
    <section class="attractions-hero">
        <div class="attractions-hero__bg" aria-hidden="true"></div>
        <div class="container">
            <div class="attractions-hero-content">
                <span class="attractions-hero-badge">
                    <i class="la la-map-marker"></i>
                    {{ __('Egypt') }}
                </span>
                <h1>{{ __('Attractions & Sights') }}</h1>
                <p>{{ __('Explore Egypt\'s iconic temples, ancient pyramids, stunning beaches, and legendary landmarks. Discover extraordinary places across all cities.') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="attractions-breadcrumb" aria-label="breadcrumb">
        <div class="container">
            <ol>
                <li><a href="{{ route('website.home') }}"><i class="la la-home"></i> {{ __('Home') }}</a></li>
                <li><span class="current">{{ __('Attractions & Sights') }}</span></li>
            </ol>
        </div>
    </nav>

    <!-- Cities Grid -->
    <section class="cities-section">
        <div class="container">
            <div class="section-head">
                <span class="tag">{{ __('Browse by City') }}</span>
                <h2>{{ __('Choose Your Destination') }}</h2>
                <p>{{ __('Select a city to explore its top attractions and must-see landmarks.') }}</p>
            </div>

            <div class="cities-grid">
                @foreach ($cities as $city)
                    <a href="{{ $city['url'] }}" class="city-card">
                        <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}" loading="lazy" width="400"
                            height="280">
                        <div class="city-card-overlay"></div>
                        @if ($city['attractions_count'] > 0)
                            <div class="city-card-badge">{{ $city['attractions_count'] }}</div>
                        @endif
                        <div class="city-card-body">
                            <h3>{{ $city['name'] }}</h3>
                            <div class="meta">
                                <i class="la la-map-marker"></i>
                                {{ $city['attractions_count'] }} {{ __('Attractions') }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Featured Attractions -->
    @if ($featuredAttractions->count())
        <section class="featured-attractions-section">
            <div class="container">
                <div class="section-head">
                    <span class="tag">{{ __('Top Picks') }}</span>
                    <h2>{{ __('Featured Attractions') }}</h2>
                    <p>{{ __('Handpicked iconic sights and landmarks you must visit in Egypt.') }}</p>
                </div>

                <div class="attractions-grid">
                    @foreach ($featuredAttractions as $attraction)
                        <a href="{{ $attraction['url'] }}" class="attraction-item">
                            <img src="{{ $attraction['image'] }}" alt="{{ $attraction['name'] }}"
                                class="attraction-item-img" loading="lazy" width="400" height="300">
                            <div class="attraction-item-body">
                                @if ($attraction['city'])
                                    <div class="attraction-item-city">
                                        <i class="la la-map-marker"></i>
                                        {{ $attraction['city'] }}
                                    </div>
                                @endif
                                <h3>{{ $attraction['name'] }}</h3>
                                @if ($attraction['description'])
                                    <p>{{ $attraction['description'] }}</p>
                                @endif
                                <div class="attraction-item-arrow">
                                    {{ __('Discover More') }} <i class="la la-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
