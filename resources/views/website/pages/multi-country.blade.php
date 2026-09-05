@extends('website.layouts.master')

@section('title', ($pageContent['title'] ?? __('Egypt Nile Cruise')) . ' - Etro Tours')
@section('description', $pageContent['description'] ?? $pageContent['overview_text'])
@section('keywords', 'Egypt Nile Cruise, Nile cruises in Egypt, Luxor to Aswan cruise, luxury Nile cruise, Etro Tours Nile cruise')
@section('image', $heroImage)

@section('css')
    <style>
        .hero-section {
            position: relative;
            min-height: 520px;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: #10213f;
        }

        .hero-media,
        .hero-overlay,
        .hero-pattern {
            position: absolute;
            inset: 0;
        }

        .hero-media {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
        }

        .hero-overlay {
            background: linear-gradient(rgba(16, 33, 63, 0.68), rgba(23, 58, 99, 0.58));
            z-index: 1;
        }

        .hero-pattern {
            background:
                radial-gradient(circle at top left, rgba(255, 210, 125, 0.22), transparent 34%),
                radial-gradient(circle at bottom right, rgba(103, 197, 255, 0.16), transparent 28%),
                linear-gradient(135deg, rgba(8, 18, 36, 0.04), rgba(255, 255, 255, 0));
            opacity: 1;
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 3;
            max-width: 920px;
            margin: 0 auto;
            padding: 130px 20px 80px;
            text-align: center;
            color: #fff;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            margin-bottom: 24px;
            font-weight: 700;
        }

        .hero-badge i {
            color: #ffd27d;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 6vw, 4.4rem);
            line-height: 1.08;
            margin-bottom: 16px;
            color: #fff;
        }

        .hero-subtitle {
            max-width: 760px;
            margin: 0 auto 18px;
            font-size: 1.1rem;
            line-height: 1.85;
            color: rgba(255, 255, 255, 0.92);
        }

        .hero-description {
            max-width: 720px;
            margin: 0 auto 34px;
            font-size: 1rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.85);
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            max-width: 780px;
            margin: 0 auto;
        }

        .hero-stat {
            padding: 18px 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
        }

        .hero-stat strong {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            line-height: 1;
            color: #ffd27d;
            margin-bottom: 8px;
        }

        .hero-stat span {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.94rem;
        }

        .breadcrumb-top-bar {
            background: var(--pearl-luxury);
            padding: 15px 0;
            border-bottom: 1px solid rgba(197, 149, 91, 0.16);
        }

        .breadcrumb-list ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .breadcrumb-list li {
            display: flex;
            align-items: center;
            color: var(--warm-gray);
        }

        .breadcrumb-list li:not(:last-child)::after {
            content: '›';
            margin-left: 10px;
            color: var(--rich-gold);
        }

        .breadcrumb-list a {
            color: var(--primary-navy);
            text-decoration: none;
        }

        .overview-section {
            background: #fff;
            padding: 42px 0 28px;
        }

        .overview-card {
            max-width: 1040px;
            margin: 0 auto;
            padding: 32px;
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 18px 42px rgba(16, 33, 63, 0.08);
            text-align: center;
        }

        .overview-card h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            margin-bottom: 14px;
        }

        .overview-card p {
            margin: 0;
            color: var(--warm-gray);
            line-height: 1.9;
            font-size: 1.03rem;
        }

        .filters-section {
            background: #fff;
            padding: 0 0 34px;
        }

        .filters-container {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            align-items: end;
            background: var(--pearl-luxury);
            padding: 28px;
            border-radius: 22px;
            box-shadow: var(--shadow-subtle);
        }

        .filter-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-navy);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .filter-group label i {
            color: var(--rich-gold);
        }

        .filter-select {
            width: 100%;
            min-height: 52px;
            padding: 12px 15px;
            border: 2px solid rgba(197, 149, 91, 0.2);
            border-radius: 14px;
            background: #fff;
            color: var(--primary-navy);
            font-size: 0.95rem;
            cursor: pointer;
            appearance: none;
            transition: all 0.3s ease;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23c5955b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
        }

        .filter-select:focus,
        .filter-select:hover {
            border-color: var(--rich-gold);
            box-shadow: 0 0 0 0.2rem rgba(197, 149, 91, 0.14);
            outline: none;
        }

        .filter-reset {
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid rgba(26, 54, 93, 0.12);
            background: #fff;
            color: var(--primary-navy);
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tours-section {
            background: linear-gradient(135deg, var(--cream-elegant) 0%, var(--light-sand) 100%);
            padding: 42px 0 80px;
        }

        .results-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .results-head h3 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: clamp(1.8rem, 4vw, 2.4rem);
        }

        .results-head p {
            margin: 0;
            color: var(--warm-gray);
        }

        .tours-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 30px;
        }

        .tour-card {
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(197, 149, 91, 0.12);
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .tour-card:hover {
            transform: translateY(-8px);
            border-color: var(--rich-gold);
            box-shadow: var(--shadow-dramatic);
        }

        .tour-image {
            position: relative;
            display: block;
            height: 245px;
            overflow: hidden;
            background: #dce8f5;
        }

        .tour-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.45s ease;
        }

        .tour-card:hover .tour-image img {
            transform: scale(1.06);
        }

        .price-badge,
        .tour-badge {
            position: absolute;
            top: 18px;
            z-index: 2;
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
        }

        .tour-badge {
            left: 18px;
            color: #fff;
            background: rgba(16, 33, 63, 0.82);
        }

        .price-badge {
            right: 18px;
            color: var(--primary-navy);
            background: rgba(255, 210, 125, 0.95);
            box-shadow: var(--shadow-gold);
        }

        .tour-content {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .tour-country {
            color: var(--rich-gold);
            font-size: 0.86rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .tour-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            line-height: 1.35;
            margin-bottom: 14px;
        }

        .tour-title a {
            color: var(--primary-navy);
            text-decoration: none;
        }

        .tour-title a:hover {
            color: var(--rich-gold);
        }

        .tour-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--warm-gray);
            font-size: 0.9rem;
            background: rgba(197, 149, 91, 0.1);
            padding: 7px 12px;
            border-radius: 999px;
        }

        .meta-item i {
            color: var(--rich-gold);
        }

        .tour-description {
            color: var(--warm-gray);
            line-height: 1.75;
            margin-bottom: 18px;
            flex: 1;
        }

        .tour-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 22px;
        }

        .tour-tag {
            background: rgba(197, 149, 91, 0.14);
            color: var(--primary-navy);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .view-btn {
            margin-top: auto;
            display: inline-flex;
            width: 100%;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 13px 24px;
            border-radius: 18px;
            text-decoration: none;
            background: var(--gradient-gold);
            color: var(--primary-navy);
            font-weight: 700;
        }

        .empty-tours-box {
            grid-column: 1 / -1;
            background: #fff;
            border-radius: 24px;
            padding: 46px 24px;
            text-align: center;
            color: var(--primary-navy);
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(197, 149, 91, 0.15);
        }

        .empty-tours-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .empty-tours-box p {
            margin: 0;
            color: var(--warm-gray);
        }

        .pagination-wrapper {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 1199px) {
            .tours-grid,
            .filters-container {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .hero-section {
                min-height: 460px;
            }

            .hero-content {
                padding: 115px 18px 65px;
            }

            .hero-stats,
            .tours-grid,
            .filters-container {
                grid-template-columns: minmax(0, 1fr);
            }

            .overview-card,
            .tour-content {
                padding: 22px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $routeWith = function (array $params = []) {
            $filters = request()->only(['pricerange', 'days', 'sort']);

            foreach ($params as $key => $value) {
                if ($value === null || $value === '') {
                    unset($filters[$key]);
                } else {
                    $filters[$key] = $value;
                }
            }

            return route('website.multi_country', $filters);
        };
    @endphp

    <section class="breadcrumb-top-bar">
        <div class="container">
            <div class="breadcrumb-list">
                <ul>
                    <li><a href="{{ route('website.home') }}">{{ __('Home') }}</a></li>
                    <li>{{ $pageContent['breadcrumb_title'] ?? __('Egypt Nile Cruise') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="hero-section">
        <img src="{{ $heroImage }}" alt="{{ $pageContent['title'] }}" class="hero-media">
        <div class="hero-overlay"></div>
        <div class="hero-pattern"></div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="las la-globe"></i>
                    {{ $pageContent['badge'] }}
                </div>
                <h1 class="hero-title">{{ $pageContent['title'] }}</h1>
                <p class="hero-subtitle">{{ $pageContent['subtitle'] }}</p>
                <p class="hero-description">{{ $pageContent['description'] }}</p>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['count']) }}</strong>
                        <span>{{ $pageContent['stats_count_label'] ?? __('Cruises') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['categories']) }}</strong>
                        <span>{{ $pageContent['stats_categories_label'] ?? __('Categories') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['featured']) }}</strong>
                        <span>{{ $pageContent['stats_featured_label'] ?? __('Featured Packages') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="overview-section">
        <div class="container">
            <div class="overview-card">
                <h2>{{ $pageContent['overview_title'] }}</h2>
                <p>{{ $pageContent['overview_text'] }}</p>
            </div>
        </div>
    </section>

    <section class="filters-section">
        <div class="container">
            <div class="filters-container">
                <div class="filter-group">
                    <label>
                        <i class="las la-dollar-sign"></i>
                        {{ __('Filter by Price') }}
                    </label>
                    <select onchange="window.location.href=this.value" class="filter-select">
                        <option value="{{ $routeWith(['pricerange' => null]) }}" @selected(!request()->filled('pricerange'))>{{ __('All Prices') }}</option>
                        <option value="{{ $routeWith(['pricerange' => 1]) }}" @selected(request('pricerange') == '1')>{{ __('Less than $1,500') }}</option>
                        <option value="{{ $routeWith(['pricerange' => 2]) }}" @selected(request('pricerange') == '2')>$1,500 - $2,500</option>
                        <option value="{{ $routeWith(['pricerange' => 3]) }}" @selected(request('pricerange') == '3')>$2,500+</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>
                        <i class="las la-calendar"></i>
                        {{ __('Filter by Duration') }}
                    </label>
                    <select onchange="window.location.href=this.value" class="filter-select">
                        <option value="{{ $routeWith(['days' => null]) }}" @selected(!request()->filled('days'))>{{ __('All Durations') }}</option>
                        <option value="{{ $routeWith(['days' => 1]) }}" @selected(request('days') == '1')>{{ __('Less than 10 Days') }}</option>
                        <option value="{{ $routeWith(['days' => 2]) }}" @selected(request('days') == '2')>{{ __('10 to 20 Days') }}</option>
                        <option value="{{ $routeWith(['days' => 3]) }}" @selected(request('days') == '3')>{{ __('20+ Days') }}</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>
                        <i class="las la-sort"></i>
                        {{ __('Sort by') }}
                    </label>
                    <select onchange="window.location.href=this.value" class="filter-select">
                        <option value="{{ $routeWith(['sort' => null]) }}" @selected(!request()->filled('sort'))>{{ __('Default Order') }}</option>
                        <option value="{{ $routeWith(['sort' => 'price']) }}" @selected(request('sort') === 'price')>{{ __('Sort by Price') }}</option>
                        <option value="{{ $routeWith(['sort' => 'duration']) }}" @selected(request('sort') === 'duration')>{{ __('Sort by Duration') }}</option>
                    </select>
                </div>

                <a href="{{ route('website.multi_country') }}" class="filter-reset">
                    <i class="las la-undo"></i>
                    {{ __('Reset Filters') }}
                </a>
            </div>
        </div>
    </section>

    <section class="tours-section">
        <div class="container">
            <div class="results-head">
                <div>
                    <h3>{{ $pageContent['results_title'] ?? __('Matching Nile Cruises') }}</h3>
                    <p>{{ number_format($stats['count']) }} {{ $pageContent['results_count_label'] ?? __('Cruises') }}</p>
                </div>
            </div>

            <div class="tours-grid">
                @forelse($packages as $tour)
                    <article class="tour-card">
                        <a href="{{ $tour['url'] }}" class="tour-image">
                            <img src="{{ $tour['image'] }}" alt="{{ $tour['title'] }}" loading="lazy">

                            @if ($tour['badge'])
                                <div class="tour-badge">{{ $tour['badge'] }}</div>
                            @endif

                            <div class="price-badge">{{ $tour['price'] }}</div>
                        </a>

                        <div class="tour-content">
                            @if ($tour['country'])
                                <div class="tour-country">{{ $tour['country'] }}</div>
                            @endif

                            <h3 class="tour-title">
                                <a href="{{ $tour['url'] }}">{{ $tour['title'] }}</a>
                            </h3>

                            <div class="tour-meta">
                                <div class="meta-item">
                                    <i class="las la-calendar"></i>
                                    <span>{{ $tour['duration'] }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="las la-tag"></i>
                                    <span>{{ $tour['tour_type'] }}</span>
                                </div>
                            </div>

                            <p class="tour-description">{{ $tour['description'] }}</p>

                            @if (!empty($tour['tags']))
                                <div class="tour-tags">
                                    @foreach ($tour['tags'] as $tag)
                                        <span class="tour-tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ $tour['url'] }}" class="view-btn">
                                {{ $pageContent['cta_label'] ?? __('View Cruise') }}
                                <i class="las la-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="empty-tours-box">
                        <h3>{{ $pageContent['empty_title'] ?? __('No Nile cruises found') }}</h3>
                        <p>{{ $pageContent['empty_text'] ?? __('Please change the filters or add Nile cruise packages from the admin panel.') }}</p>
                    </div>
                @endforelse
            </div>

            @if ($packages->hasPages())
                <div class="pagination-wrapper">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
