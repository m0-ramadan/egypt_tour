@extends('website.layouts.master')

@php
    $isToursPage = request()->routeIs('website.tours.*');
    $indexRoute = $isToursPage ? route('website.tours.all') : route('website.trips');
    $firstPackage = $packages->first();
    $heroImage = is_array($firstPackage) ? ($firstPackage['image'] ?? asset('website/photos/home2.webp')) : asset('website/photos/home2.webp');
@endphp

@section('title', $pageContent['title'] . ' - Etro Tours')
@section('description', $pageContent['description'] ?? $pageContent['overview_text'])
@section('keywords', trim(collect([$pageContent['title'] ?? null, 'Etro Tours', 'Egypt tours', 'travel packages'])->filter()->implode(', '), ', '))
@section('image', $heroImage)

@section('css')
    <style>
        .listing-hero {
            position: relative;
            min-height: 460px;
            display: flex;
            align-items: center;
            margin-top: -85px;
            padding: 140px 0 90px;
            color: #fff;
            background:
                linear-gradient(rgba(16, 33, 63, 0.72), rgba(22, 60, 103, 0.62)),
                url('{{ $heroImage }}') center/cover no-repeat;
            overflow: hidden;
        }

        .listing-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 210, 125, 0.18), transparent 28%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.09), transparent 30%);
        }

        .listing-hero .container,
        .listing-overview .container,
        .listing-results .container {
            position: relative;
            z-index: 1;
        }

        .listing-hero-content {
            max-width: 900px;
            text-align: center;
            margin: 0 auto;
        }

        .listing-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(12px);
            font-weight: 600;
            margin-bottom: 22px;
        }

        .listing-badge i {
            color: #ffd27d;
        }

        .listing-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 6vw, 4.5rem);
            line-height: 1.08;
            margin-bottom: 18px;
            color: #fff;
        }

        .listing-subtitle {
            max-width: 740px;
            margin: 0 auto 34px;
            font-size: 1.08rem;
            line-height: 1.85;
            color: rgba(255, 255, 255, 0.92);
        }

        .listing-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            max-width: 760px;
            margin: 0 auto;
        }

        .listing-stat {
            padding: 18px 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
        }

        .listing-stat strong {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #ffd27d;
            line-height: 1;
            margin-bottom: 8px;
        }

        .listing-stat span {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
        }

        .listing-overview {
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            padding: 52px 0 26px;
        }

        .overview-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 20px 50px rgba(16, 33, 63, 0.08);
            padding: 34px;
            text-align: center;
        }

        .overview-card h2 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            margin-bottom: 16px;
        }

        .overview-card p {
            margin: 0 auto;
            max-width: 900px;
            color: #5b6776;
            line-height: 1.9;
            font-size: 1.03rem;
        }

        .listing-results {
            background: linear-gradient(180deg, #f7fafc 0%, #eef4fb 100%);
            padding: 26px 0 90px;
        }

        .filters-card {
            background: #fff;
            border-radius: 28px;
            padding: 28px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 16px 38px rgba(16, 33, 63, 0.08);
            margin-bottom: 34px;
        }

        .filters-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1c325c;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 22px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr auto auto;
            gap: 16px;
            align-items: end;
        }

        .filters-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #425466;
        }

        .filters-card .form-control,
        .filters-card .form-select {
            min-height: 54px;
            border-radius: 16px;
            border: 1px solid rgba(26, 54, 93, 0.14);
            box-shadow: none;
        }

        .filters-card .form-control:focus,
        .filters-card .form-select:focus {
            border-color: #c5955b;
            box-shadow: 0 0 0 0.2rem rgba(197, 149, 91, 0.14);
        }

        .filter-btn,
        .reset-btn {
            min-height: 54px;
            border-radius: 16px;
            padding: 0 22px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .filter-btn {
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            border: none;
            box-shadow: 0 10px 24px rgba(197, 149, 91, 0.22);
        }

        .reset-btn {
            border: 1px solid rgba(26, 54, 93, 0.14);
            background: #fff;
            color: #1c325c;
        }

        .results-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 26px;
            flex-wrap: wrap;
        }

        .results-head h3 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: clamp(1.7rem, 4vw, 2.4rem);
        }

        .results-head p {
            margin: 0;
            color: #5b6776;
        }

        .results-grid {
            row-gap: 24px;
        }

        .journey-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-radius: 26px;
            background: #fff;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 16px 40px rgba(16, 33, 63, 0.08);
            transition: transform 0.32s ease, box-shadow 0.32s ease, border-color 0.32s ease;
        }

        .journey-card:hover {
            transform: translateY(-8px);
            border-color: rgba(197, 149, 91, 0.34);
            box-shadow: 0 24px 52px rgba(16, 33, 63, 0.14);
        }

        .journey-image {
            position: relative;
            height: 255px;
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
            padding: 9px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
        }

        .journey-type {
            top: 18px;
            left: 18px;
            color: #fff;
            background: rgba(16, 33, 63, 0.82);
        }

        .journey-badge {
            top: 18px;
            right: 18px;
            color: #1c325c;
            background: rgba(255, 210, 125, 0.95);
        }

        .journey-price {
            left: 18px;
            right: 18px;
            bottom: 18px;
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
            margin-bottom: 12px;
        }

        .journey-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            line-height: 1.35;
            margin-bottom: 16px;
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
            padding: 8px 12px;
            border-radius: 999px;
            background: #f4f7fb;
            color: #425466;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .journey-meta i,
        .journey-schedule i {
            color: #c5955b;
        }

        .journey-schedule {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 18px;
            background: #f8fbff;
            color: #5b6776;
            margin-bottom: 16px;
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .journey-description {
            color: #5b6776;
            line-height: 1.8;
            margin-bottom: 18px;
        }

        .journey-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        .journey-highlights span {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #edf4fb;
            color: #1c325c;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .journey-btn {
            margin-top: auto;
            display: inline-flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 18px;
            padding: 14px 18px;
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(197, 149, 91, 0.22);
        }

        .journey-empty {
            text-align: center;
            background: #fff;
            border-radius: 28px;
            padding: 48px 28px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 16px 38px rgba(16, 33, 63, 0.08);
        }

        .journey-empty h4 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .journey-empty p {
            color: #5b6776;
            margin: 0 auto;
            max-width: 620px;
            line-height: 1.85;
        }

        .listing-pagination {
            margin-top: 38px;
        }

        .listing-pagination nav {
            display: flex;
            justify-content: center;
        }

        .listing-pagination svg {
            width: 18px;
            height: 18px;
        }

        html[data-theme='dark'] .listing-overview,
        html[data-theme='dark'] .listing-results {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
        }

        html[data-theme='dark'] .overview-card,
        html[data-theme='dark'] .filters-card,
        html[data-theme='dark'] .journey-card,
        html[data-theme='dark'] .journey-empty {
            background: #111827 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        html[data-theme='dark'] .overview-card h2,
        html[data-theme='dark'] .filters-title,
        html[data-theme='dark'] .results-head h3,
        html[data-theme='dark'] .journey-title a,
        html[data-theme='dark'] .journey-empty h4,
        html[data-theme='dark'] .filters-card label {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .overview-card p,
        html[data-theme='dark'] .results-head p,
        html[data-theme='dark'] .journey-description,
        html[data-theme='dark'] .journey-empty p {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .filters-card .form-control,
        html[data-theme='dark'] .filters-card .form-select,
        html[data-theme='dark'] .reset-btn {
            background: #0f172a !important;
            color: var(--charcoal-deep) !important;
            border-color: rgba(148, 163, 184, 0.2) !important;
        }

        html[data-theme='dark'] .filters-card .form-control::placeholder {
            color: #94a3b8 !important;
        }

        html[data-theme='dark'] .reset-btn {
            box-shadow: none !important;
        }

        html[data-theme='dark'] .reset-btn:hover {
            background: #172033 !important;
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .journey-image {
            background: #0f172a !important;
        }

        html[data-theme='dark'] .journey-title a:hover {
            color: var(--rich-gold) !important;
        }

        html[data-theme='dark'] .journey-meta span,
        html[data-theme='dark'] .journey-schedule,
        html[data-theme='dark'] .journey-highlights span {
            background: #172033 !important;
            color: var(--warm-gray) !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
        }

        html[data-theme='dark'] .journey-country {
            color: #f4c36a !important;
        }

        html[data-theme='dark'] .journey-price {
            background: rgba(15, 23, 42, 0.94) !important;
            color: var(--charcoal-deep) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28) !important;
        }

        html[data-theme='dark'] .journey-type {
            background: rgba(15, 23, 42, 0.88) !important;
        }

        html[data-theme='dark'] .listing-pagination .page-link {
            background: #111827 !important;
            color: var(--charcoal-deep) !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        html[data-theme='dark'] .listing-pagination .page-item.active .page-link {
            color: #0f172a !important;
        }

        html[data-theme='dark'] .listing-pagination .page-link:hover {
            background: #172033 !important;
            color: var(--charcoal-deep) !important;
        }

        @media (max-width: 1199px) {
            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .listing-hero {
                min-height: 400px;
                padding: 125px 0 72px;
            }

            .listing-stats,
            .filters-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .overview-card,
            .filters-card,
            .journey-body {
                padding: 22px;
            }

            .results-head {
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    <section class="listing-hero">
        <div class="container">
            <div class="listing-hero-content">
                <div class="listing-badge">
                    <i class="la la-compass"></i>
                    {{ $pageContent['badge'] }}
                </div>
                <h1 class="listing-title">{{ $pageContent['title'] }}</h1>
                <p class="listing-subtitle">{{ $pageContent['subtitle'] }}</p>

                <div class="listing-stats">
                    <div class="listing-stat">
                        <strong>{{ number_format($stats['count']) }}</strong>
                        <span>{{ __('Trips') }}</span>
                    </div>
                    <div class="listing-stat">
                        <strong>{{ number_format($stats['categories']) }}</strong>
                        <span>{{ __('Categories') }}</span>
                    </div>
                    <div class="listing-stat">
                        <strong>{{ number_format($stats['featured']) }}</strong>
                        <span>{{ __('Featured') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="listing-overview">
        <div class="container">
            <div class="overview-card">
                <h2>{{ $pageContent['overview_title'] }}</h2>
                <p>{{ $pageContent['overview_text'] }}</p>
            </div>
        </div>
    </section>

    <section class="listing-results">
        <div class="container">
            <div class="filters-card">
                <h2 class="filters-title">
                    <i class="la la-sliders-h"></i>
                    {{ __('Filter Results') }}
                </h2>

                <form action="{{ $indexRoute }}" method="GET">
                    <div class="filters-grid">
                        <div>
                            <label for="listing-search">{{ __('Search by keyword') }}</label>
                            <input id="listing-search" type="text" name="q" class="form-control"
                                value="{{ $search }}" placeholder="{{ __('Search packages, cruises, tours...') }}">
                        </div>

                        <div>
                            <label for="listing-type">{{ __('Type') }}</label>
                            <select id="listing-type" name="type" class="form-select">
                                <option value="">{{ __('All Types') }}</option>
                                @foreach ($typeOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected($selectedType === $option['value'])>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="listing-category">{{ __('Categories') }}</label>
                            <select id="listing-category" name="category" class="form-select">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category['slug'] }}" @selected($selectedCategorySlug === $category['slug'])>
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="filter-btn">
                            <i class="la la-search"></i>
                            {{ __('Filter Results') }}
                        </button>

                        <a href="{{ $indexRoute }}" class="reset-btn">
                            <i class="la la-undo"></i>
                            {{ __('Reset Filters') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="results-head">
                <div>
                    <h3>{{ $selectedCategoryName ?: $pageContent['title'] }}</h3>
                    <p>{{ __('Matching Results') }}: {{ number_format($stats['count']) }}</p>
                </div>
            </div>

            @if ($packages->count())
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
                                        <img src="{{ $package['image'] }}" alt="{{ $package['title'] }}" loading="lazy">
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

                                    @if ($package['schedule'])
                                        <div class="journey-schedule">
                                            <i class="la la-calendar-alt"></i>
                                            <span>{{ $package['schedule'] }}</span>
                                        </div>
                                    @endif

                                    <p class="journey-description">{{ $package['description'] }}</p>

                                    @if (!empty($package['highlights']))
                                        <div class="journey-highlights">
                                            @foreach ($package['highlights'] as $highlight)
                                                <span>{{ $highlight }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <a href="{{ $package['url'] }}" class="journey-btn">
                                        {{ $package['button_text'] }}
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="journey-empty">
                    <h4>{{ $pageContent['empty_title'] }}</h4>
                    <p>{{ $pageContent['empty_text'] }}</p>
                </div>
            @endif

            @if (method_exists($packages, 'links') && $packages->hasPages())
                <div class="listing-pagination">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
