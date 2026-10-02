@extends('website.layouts.master')

@php
    $indexRoute = route('website.destinations.show', $destination->slug, false);
    $countryRoute = $destination->country?->slug
        ? route('website.destinations.index', ['country' => $destination->country->slug])
        : route('website.destinations.index');
    $countryName = $destination->country?->display_name ?: __('Destination');
    $heroSubtitle =
        $shortDescription !== ''
            ? $shortDescription
            : __('Explore curated journeys, private tours, and unforgettable highlights in :destination.', [
                'destination' => $destination->display_name,
            ]);
    $activeType = collect($typeOptions)->firstWhere('value', $selectedType);
    $resultsTitle = $activeType
        ? $activeType['label'] . ' ' . __('in') . ' ' . $destination->display_name
        : __('Trips in') . ' ' . $destination->display_name;
@endphp

@section('title', $pageTitle . ' - Egypt Tour Pro')
@section('description', $pageDescription)
@section('keywords',
    trim(
    collect([
    $destination->display_name,
    $countryName,
    'Egypt Tour Pro',
    'destination travel',
    'Egypt
    trips',
    ])->filter()->implode(', '),
    ', ',
    ))
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/destinations-show.css')
@endsection

@section('content')
    <section class="destination-breadcrumb">
        <div class="container">
            <nav aria-label="{{ __('Breadcrumb') }}">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.destinations.index') }}">{{ __('Destinations') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ $countryRoute }}">{{ $countryName }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $destination->display_name }}
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="destination-hero" style="--hero-bg: url('{{ $heroImage }}');">
        <div class="container">
            <div class="destination-hero-content">
                <div class="hero-badge">
                    <i class="la la-map-marker"></i>
                    {{ $countryName }}
                </div>

                <h1 class="hero-title">{{ $destination->display_name }}</h1>
                <p class="hero-subtitle">{{ $heroSubtitle }}</p>

                <div class="hero-actions">
                    <a href="#destination-journeys" class="hero-btn">
                        <i class="la la-suitcase"></i>
                        {{ __('Browse Trips') }}
                    </a>
                    <a href="{{ route('website.contact.index') }}" class="hero-btn-outline">
                        <i class="la la-phone"></i>
                        {{ __('Plan With an Expert') }}
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['count']) }}</strong>
                        <span>{{ __('Available Journeys') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['attractions']) }}</strong>
                        <span>{{ __('Top Attractions') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['featured']) }}</strong>
                        <span>{{ __('Featured Trips') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="destination-overview">
        <div class="container">
            <div class="overview-grid">
                <div class="overview-panel">
                    <div class="section-kicker">
                        <i class="la la-compass"></i>
                        {{ __('About this destination') }}
                    </div>
                    <h2>{{ __('Discover') }} {{ $destination->display_name }}</h2>

                    @if ($descriptionHtml)
                        <div class="overview-body">{!! $descriptionHtml !!}</div>
                    @else
                        <p class="overview-summary">
                            {{ $overviewText ?: $heroSubtitle }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <section class="destination-results" id="destination-journeys">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 820px;">
                <div class="section-kicker justify-content-center">
                    <i class="la la-compass"></i>
                    {{ __('Explore By Trip Type') }}
                </div>
                <h2>{{ __('Discover :destination by Journey Type', ['destination' => $destination->display_name]) }}</h2>
                @if ($typeCards->contains('value', 'nile_cruise'))
                    <p>{{ __('Choose from our private day excursions, luxury Nile cruises, or comprehensive multi-day vacation packages.') }}
                    </p>
                @else
                    <p>{{ __('Choose from our private day excursions or comprehensive multi-day vacation packages.') }}</p>
                @endif
            </div>

            <div class="row g-4 justify-content-center">
                @foreach ($typeCards as $typeCard)
                    <div class="{{ count($typeCards) === 2 ? 'col-lg-6 col-md-6' : 'col-lg-4 col-md-6' }}">
                        <div class="destination-cat-card">
                            <div class="cat-img-wrapper">
                                <img src="{{ asset($typeCard['image']) }}" alt="{{ $typeCard['label'] }}"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                <div class="cat-card-badge">
                                    <i class="la la-compass"></i> {{ $typeCard['badge'] }}
                                </div>
                            </div>
                            <div class="cat-card-body">
                                <h3 class="cat-card-title">
                                    <a href="{{ $typeCard['url'] }}">{{ $typeCard['label'] }}</a>
                                </h3>
                                <p class="cat-card-desc">{{ $typeCard['description'] }}</p>

                                <div class="mt-auto">
                                    <a href="{{ $typeCard['url'] }}" class="cat-card-btn">
                                        <span>{{ $typeCard['btn_text'] }}</span>
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

    <section class="destination-cta">
        <div class="container">
            <div class="cta-card">
                <div>
                    <div class="section-kicker">
                        <i class="la la-gem"></i>
                        {{ __('Tailor-made planning') }}
                    </div>
                    <h2>{{ __('Need a custom itinerary for :destination?', ['destination' => $destination->display_name]) }}
                    </h2>
                    <p>
                        {{ __('Our specialists can design the right mix of stays, sightseeing, cruises, and private experiences for your travel style.') }}
                    </p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('website.tailor_made.index') }}" class="cta-btn">
                        <i class="la la-route"></i>
                        {{ __('Build My Trip') }}
                    </a>
                    <a href="{{ route('website.contact.index') }}" class="cta-btn secondary">
                        <i class="la la-envelope"></i>
                        {{ __('Talk to an Expert') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    @if ($attractions->count())
        <section class="attractions-section">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker">
                        <i class="la la-landmark"></i>
                        {{ __('Explore Highlights') }}
                    </div>
                    <h2>{{ __('Top places to experience in :destination', ['destination' => $destination->display_name]) }}
                    </h2>
                    <p>
                        {{ __('Blend iconic landmarks, local culture, and unforgettable moments while planning your stay in :destination.', ['destination' => $destination->display_name]) }}
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ($attractions as $attraction)
                        <div class="col-lg-4 col-md-6">
                            <article class="attraction-card">
                                <div class="attraction-image">
                                    <a href="{{ $attraction['url'] }}">
                                        <img src="{{ $attraction['image'] }}" alt="{{ $attraction['title'] }}"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                    </a>
                                </div>
                                <div class="attraction-body">
                                    <h3 class="attraction-title">
                                        <a href="{{ $attraction['url'] }}"
                                            style="color: inherit; text-decoration: none;">
                                            {{ $attraction['title'] }}
                                        </a>
                                    </h3>
                                    <p class="attraction-description">{{ $attraction['description'] }}</p>

                                    @if ($attraction['opening_hours'])
                                        <div class="attraction-meta">
                                            <span>
                                                <i class="la la-clock"></i>
                                                {{ $attraction['opening_hours'] }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="d-flex gap-2 flex-wrap mt-auto pt-2">
                                        <a href="{{ $attraction['url'] }}" class="attraction-link">
                                            <i class="la la-eye"></i>
                                            {{ __('Explore Place') }}
                                        </a>
                                        @if ($attraction['map_url'])
                                            <a href="{{ $attraction['map_url'] }}" target="_blank" rel="noopener"
                                                class="attraction-link">
                                                <i class="la la-map"></i>
                                                {{ __('Open Map') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


@endsection
