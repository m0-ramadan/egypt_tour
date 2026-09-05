@extends('website.layouts.master')

@section('title', __('Travel Deals') . ' - Etro Tours')
@section('description', __('Explore the latest Etro Tours travel deals, limited-time Egypt tour offers, luxury escapes, and curated savings on unforgettable journeys.'))
@section('keywords', 'Egypt travel deals, Etro Tours offers, luxury tour discounts, Nile cruise deals, Egypt holiday promotions')
@section('image', asset('website/photos/home2.webp'))

@section('css')
    <style>
        .offers-hero {
            position: relative;
            min-height: 420px;
            display: flex;
            align-items: center;
            background:
                linear-gradient(rgba(16, 33, 63, 0.62), rgba(23, 58, 99, 0.55)),
                url('{{ asset('website/photos/home2.webp') }}') center/cover no-repeat;
            margin-top: -85px;
            padding-top: 85px;
            overflow: hidden;
        }

        .offers-hero::before {
            content: '';
            position: absolute;
            inset: auto -10% -90px;
            height: 180px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12), transparent 65%);
            filter: blur(20px);
        }

        .offers-hero-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
            color: white;
            text-align: center;
            margin: 0 auto;
            padding: 50px 0 70px;
        }

        .offers-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.14);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            padding: 10px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 22px;
            backdrop-filter: blur(12px);
        }

        .offers-badge i {
            color: #ffd27d;
        }

        .offers-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 6vw, 4.4rem);
            line-height: 1.08;
            margin-bottom: 16px;
            color: white;
        }

        .offers-subtitle {
            font-size: 1.12rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.92);
            margin: 0 auto;
            max-width: 680px;
        }

        .offers-breadcrumb {
            background: #f8fafc;
            border-bottom: 1px solid rgba(26, 54, 93, 0.08);
            padding: 16px 0;
        }

        .offers-summary {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 55px 0 20px;
        }

        .offers-summary-card {
            background: white;
            border: 1px solid rgba(26, 54, 93, 0.08);
            border-radius: 24px;
            box-shadow: 0 20px 45px rgba(20, 41, 74, 0.08);
            padding: 32px;
        }

        .offers-summary-card p {
            margin: 0;
            color: #4a5568;
            font-size: 1.05rem;
            line-height: 1.85;
            text-align: center;
        }

        .offers-section {
            background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
            padding: 40px 0 90px;
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 30px;
        }

        @media (max-width: 1199px) {
            .offers-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .offers-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .offer-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 14px 35px rgba(20, 41, 74, 0.08);
            border: 1px solid rgba(26, 54, 93, 0.08);
            display: flex;
            flex-direction: column;
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            height: 100%;
        }

        .offer-card:hover {
            transform: translateY(-8px);
            border-color: rgba(66, 153, 225, 0.28);
            box-shadow: 0 24px 50px rgba(20, 41, 74, 0.14);
        }

        .offer-image-wrap {
            position: relative;
            height: 245px;
            overflow: hidden;
            background: #dce8f5;
        }

        .offer-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.55s ease;
        }

        .offer-card:hover .offer-image-wrap img {
            transform: scale(1.08);
        }

        .offer-country-badge,
        .offer-save-badge {
            position: absolute;
            top: 18px;
            z-index: 2;
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1;
            backdrop-filter: blur(10px);
        }

        .offer-country-badge {
            left: 18px;
            color: white;
            background: rgba(16, 33, 63, 0.76);
        }

        .offer-save-badge {
            right: 18px;
            color: #10213f;
            background: rgba(255, 210, 125, 0.95);
        }

        .offer-price-panel {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 18px;
            padding: 14px 16px;
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 14px;
            box-shadow: 0 14px 28px rgba(16, 33, 63, 0.12);
        }

        .offer-price-label {
            display: block;
            color: #718096;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        .offer-price-current {
            color: #1a365d;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1;
        }

        .offer-price-regular {
            text-align: right;
            color: #718096;
            font-size: 0.92rem;
        }

        .offer-price-regular strong {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .offer-price-regular span {
            text-decoration: line-through;
        }

        .offer-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .offer-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.45rem;
            line-height: 1.35;
            margin-bottom: 12px;
        }

        .offer-title a {
            color: #1a365d;
            text-decoration: none;
        }

        .offer-title a:hover {
            color: #4299e1;
        }

        .offer-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 16px;
            color: #4a5568;
            font-size: 0.92rem;
        }

        .offer-meta span {
            display: inline-flex;
            align-items: center;
        }

        .offer-meta i {
            color: #4299e1;
            margin-right: 6px;
        }

        .offer-description {
            color: #718096;
            line-height: 1.75;
            margin-bottom: 18px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .offer-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        .offer-tag {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            background: #edf4fb;
            color: #1a365d;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .offer-btn {
            margin-top: auto;
            width: 100%;
            justify-content: center;
        }

        .offers-empty {
            background: white;
            border-radius: 22px;
            padding: 40px 24px;
            text-align: center;
            color: #718096;
            box-shadow: 0 14px 35px rgba(20, 41, 74, 0.08);
        }

        .offers-pagination {
            margin-top: 40px;
        }

        .offers-pagination nav {
            display: flex;
            justify-content: center;
        }

        .offers-pagination svg {
            width: 18px;
            height: 18px;
        }

        @media (max-width: 767px) {
            .offers-hero {
                min-height: 360px;
                background-attachment: scroll;
            }

            .offers-hero-content {
                padding: 35px 0 55px;
            }

            .offers-summary-card,
            .offer-body {
                padding: 24px;
            }

            .offer-price-panel {
                flex-direction: column;
                align-items: flex-start;
            }

            .offer-price-regular {
                text-align: left;
            }
        }
    </style>
@endsection

@section('content')
    <section class="offers-hero">
        <div class="container">
            <div class="offers-hero-content">
                <div class="offers-badge">
                    <i class="la la-fire"></i>
                    {{ __('Travel Deals') }}
                </div>
                <h1 class="offers-title">{{ __('Latest Offers') }}</h1>
                <p class="offers-subtitle">
                    {{ __('Exclusive savings on handpicked journeys and experiences updated directly from your package database.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="offers-breadcrumb">
        <div class="container">
            <nav aria-label="{{ __('Breadcrumb') }}">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">
                            <i class="la la-home breadcrumb-icon"></i>{{ __('Home') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Travel Deals') }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="offers-summary">
        <div class="container">
            <div class="offers-summary-card">
                <p>{{ __('For travelers looking to save on their next journey, explore our latest special offers and limited-time package prices in one place.') }}</p>
            </div>
        </div>
    </section>

    <section class="offers-section">
        <div class="container">
            <div class="offers-grid">
                @forelse ($offers as $offer)
                    <article class="offer-card">
                        <div class="offer-image-wrap">
                            <div class="offer-country-badge">{{ $offer['country'] }}</div>
                            @if ($offer['savings_percent'])
                                <div class="offer-save-badge">{{ __('Save') }} {{ $offer['savings_percent'] }}%</div>
                            @endif

                            <a href="{{ $offer['url'] }}">
                                <img src="{{ $offer['image'] }}" alt="{{ $offer['title'] }}" loading="lazy">
                            </a>

                            <div class="offer-price-panel">
                                <div>
                                    <span class="offer-price-label">{{ __('Current Offer') }}</span>
                                    <div class="offer-price-current">{{ $offer['offer_price'] }}</div>
                                </div>
                                @if ($offer['regular_price'])
                                    <div class="offer-price-regular">
                                        <strong>{{ __('Regular Price') }}</strong>
                                        <span>{{ $offer['regular_price'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="offer-body">
                            <h3 class="offer-title"><a href="{{ $offer['url'] }}">{{ $offer['title'] }}</a></h3>

                            <div class="offer-meta">
                                <span><i class="la la-clock"></i>{{ $offer['duration'] }}</span>
                                <span><i class="la la-users"></i>{{ $offer['tour_type'] }}</span>
                            </div>

                            <p class="offer-description">{{ $offer['description'] }}</p>

                            @if (!empty($offer['tags']))
                                <div class="offer-tags">
                                    @foreach ($offer['tags'] as $tag)
                                        <span class="offer-tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ $offer['url'] }}" class="gold-btn offer-btn">
                                {{ __('View Offer') }} <i class="la la-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="offers-empty">
                        {{ __('No active offers found. Add offer prices to packages from the admin panel.') }}
                    </div>
                @endforelse
            </div>

            @if (method_exists($offers, 'links') && $offers->hasPages())
                <div class="offers-pagination">
                    {{ $offers->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
