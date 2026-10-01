@extends('website.layouts.master')

@section('title', __('Travel Deals') . ' - Egypt Tour Pro')
@section('description', __('Explore the latest Egypt Tour Pro travel deals, limited-time Egypt tour offers, luxury escapes,
    and curated savings on unforgettable journeys.'))
@section('keywords', 'Egypt travel deals, Egypt Tour Pro offers, luxury tour discounts, Nile cruise deals, Egypt holiday
    promotions')
@section('image', asset('website/photos/home2.webp'))

@section('css')
    @vite('resources/css/pages/offers.css')
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
                <p>{{ __('For travelers looking to save on their next journey, explore our latest special offers and limited-time package prices in one place.') }}
                </p>
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
