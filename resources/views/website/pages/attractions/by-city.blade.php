@extends('website.layouts.master')

@section('title', __('Attractions in :city', ['city' => $city->display_name]) . ' - Egypt Tour Pro')
@section('description', __('Explore top attractions and iconic sights in :city, Egypt. Find temples, monuments,
    landmarks, and must-see places for your visit.', ['city' => $city->display_name]))
@section('image', $heroImage)

@section('body_class', 'attractions-city-page')

@section('css')
    <style>
        /* ============================================================
       Attractions By City Page
       ============================================================ */
        .city-hero {
            position: relative;
            min-height: 360px;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 100px 0 60px;
        }

        .city-hero-bg {
            position: absolute;
            inset: 0;
            background-image: url('{{ $heroImage }}');
            background-size: cover;
            background-position: center;
        }

        .city-hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.4) 100%);
        }

        .city-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }

        .city-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 50px;
            margin-bottom: 18px;
        }

        .city-hero-content h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: 12px;
        }

        .city-hero-content p {
            font-size: 1.05rem;
            opacity: 0.85;
            max-width: 560px;
            margin: 0 auto;
        }

        .city-breadcrumb {
            background: var(--color-surface-elevated, #fff);
            border-bottom: 1px solid var(--color-border, #e8e3da);
            padding: 12px 0;
        }

        .city-breadcrumb ol {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }

        .city-breadcrumb li+li::before {
            content: '›';
            color: var(--color-text-muted, #999);
            margin-right: 8px;
        }

        .city-breadcrumb a {
            color: var(--color-primary, #c8860a);
            text-decoration: none;
        }

        .city-breadcrumb a:hover {
            text-decoration: underline;
        }

        .city-breadcrumb .current {
            color: var(--color-text-muted, #999);
        }

        /* Attractions grid */
        .city-attractions-section {
            padding: 64px 0;
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
            margin-bottom: 10px;
            display: block;
        }

        .section-head h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 700;
            color: var(--color-text, #1a1a1a);
            margin-bottom: 10px;
        }

        .section-head p {
            color: var(--color-text-muted, #666);
            font-size: 1rem;
            max-width: 540px;
            margin: 0 auto;
        }

        .city-attractions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 1100px) {
            .city-attractions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .city-attractions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
        }

        @media (max-width: 480px) {
            .city-attractions-grid {
                grid-template-columns: 1fr;
            }
        }

        .attraction-card {
            background: var(--color-surface, #fff);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .attraction-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
            text-decoration: none;
        }

        .attraction-card-img-wrap {
            overflow: hidden;
            aspect-ratio: 4/3;
        }

        .attraction-card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
            display: block;
        }

        .attraction-card:hover .attraction-card-img-wrap img {
            transform: scale(1.07);
        }

        .attraction-card-body {
            padding: 16px 18px 18px;
        }

        .attraction-card-body h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--color-text, #1a1a1a);
            margin: 0 0 8px;
            line-height: 1.3;
        }

        .attraction-card-body p {
            font-size: 13px;
            color: var(--color-text-muted, #666);
            line-height: 1.55;
            margin: 0 0 12px;
        }

        .attraction-card-cta {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            color: var(--color-primary, #c8860a);
            letter-spacing: 0.5px;
        }

        /* Other cities */
        .other-cities-section {
            padding: 50px 0 64px;
            background: var(--color-surface-alt, #f9f6f1);
        }

        .other-cities-scroll {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }

        .other-cities-scroll::-webkit-scrollbar {
            display: none;
        }

        .other-city-chip {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--color-surface, #fff);
            border: 1px solid var(--color-border, #e8e3da);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text, #1a1a1a);
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .other-city-chip:hover {
            border-color: var(--color-primary, #c8860a);
            color: var(--color-primary, #c8860a);
            box-shadow: 0 4px 14px rgba(200, 134, 10, 0.15);
            text-decoration: none;
        }

        .other-city-chip i {
            font-size: 15px;
        }

        /* Dark mode */
        html[data-theme='dark'] .city-breadcrumb {
            background: #1a1a28;
            border-color: #2a2a3a;
        }

        html[data-theme='dark'] .section-head h2,
        html[data-theme='dark'] .attraction-card-body h3 {
            color: #f0ece4;
        }

        html[data-theme='dark'] .attraction-card {
            background: #1e1e2a;
        }

        html[data-theme='dark'] .section-head p,
        html[data-theme='dark'] .attraction-card-body p {
            color: #a0a0b0;
        }

        html[data-theme='dark'] .other-cities-section {
            background: #141420;
        }

        html[data-theme='dark'] .other-city-chip {
            background: #1e1e2a;
            border-color: #2a2a3a;
            color: #d0ccc4;
        }

        html[data-theme='dark'] .other-city-chip:hover {
            border-color: var(--color-primary, #c8860a);
            color: var(--color-primary, #c8860a);
        }
    </style>
@endsection

@section('content')

    <!-- Hero -->
    <section class="city-hero">
        <div class="city-hero-bg" aria-hidden="true"></div>
        <div class="container">
            <div class="city-hero-content">
                <span class="city-hero-badge">
                    <i class="la la-map-marker"></i>
                    {{ __('Attractions & Sights') }}
                </span>
                <h1>{{ __('Explore :city', ['city' => $city->display_name]) }}</h1>
                <p>{{ __('Discover the most iconic attractions, temples, and landmarks that :city has to offer.', ['city' => $city->display_name]) }}
                </p>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="city-breadcrumb" aria-label="breadcrumb">
        <div class="container">
            <ol>
                <li><a href="{{ route('website.home') }}"><i class="la la-home"></i> {{ __('Home') }}</a></li>
                <li><a href="{{ route('website.attractions.index') }}">{{ __('Attractions') }}</a></li>
                <li><span class="current">{{ $city->display_name }}</span></li>
            </ol>
        </div>
    </nav>

    <!-- Attractions Grid -->
    <section class="city-attractions-section">
        <div class="container">
            <div class="section-head">
                <span class="tag">{{ $city->display_name }}</span>
                <h2>{{ __('Top Attractions in :city', ['city' => $city->display_name]) }}</h2>
                <p>{{ __('Browse :count iconic sights and must-visit places in :city.', ['count' => $attractions->total(), 'city' => $city->display_name]) }}
                </p>
            </div>

            @if ($attractions->count())
                <div class="city-attractions-grid">
                    @foreach ($attractions as $attraction)
                        <a href="{{ $attraction['url'] }}" class="attraction-card">
                            <div class="attraction-card-img-wrap">
                                <img src="{{ $attraction['image'] }}" alt="{{ $attraction['name'] }}" loading="lazy"
                                    width="400" height="300">
                            </div>
                            <div class="attraction-card-body">
                                <h3>{{ $attraction['name'] }}</h3>
                                @if ($attraction['description'])
                                    <p>{{ $attraction['description'] }}</p>
                                @endif
                                <span class="attraction-card-cta">
                                    {{ __('Explore') }} <i class="la la-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($attractions->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $attractions->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="la la-map-marker" style="font-size:48px;color:var(--color-primary,#c8860a);opacity:0.4;"></i>
                    <p class="mt-3 text-muted">{{ __('No attractions found for this city yet.') }}</p>
                    <a href="{{ route('website.attractions.index') }}" class="btn btn-outline-primary mt-2">
                        {{ __('Browse all cities') }}
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Other Cities -->
    @php
        $otherCities = \App\Models\City::where('is_active', true)
            ->where('id', '!=', $city->id)
            ->withCount(['attractions' => fn($q) => $q->where('is_active', true)])
            ->having('attractions_count', '>', 0)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();
    @endphp
    @if ($otherCities->count())
        <section class="other-cities-section">
            <div class="container">
                <div class="section-head">
                    <span class="tag">{{ __('Explore More') }}</span>
                    <h2>{{ __('Other Destinations') }}</h2>
                </div>
                <div class="other-cities-scroll">
                    @foreach ($otherCities as $otherCity)
                        <a href="{{ route('website.attractions.by-city', $otherCity->slug) }}" class="other-city-chip">
                            <i class="la la-map-marker"></i>
                            {{ $otherCity->display_name }}
                            <span style="font-size:11px;opacity:0.6;">({{ $otherCity->attractions_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
