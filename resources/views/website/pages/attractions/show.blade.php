@extends('website.layouts.master')

@php
    $cityName = $attraction->city?->display_name ?: __('Destination');
    $countryName = $attraction->city?->country?->display_name ?: __('Egypt');
    $cityRoute = $attraction->city?->slug
        ? route('website.destinations.show', $attraction->city->slug)
        : route('website.destinations.index');
    $countryRoute = $attraction->city?->country?->slug
        ? route('website.destinations.index', ['country' => $attraction->city->country->slug])
        : route('website.destinations.index');

    $heroSubtitle =
        $shortDescription !== ''
            ? $shortDescription
            : __('Explore :name, one of the iconic places to visit in :city.', [
                'name' => $attraction->display_name,
                'city' => $cityName,
            ]);
@endphp

@section('title', $pageTitle . ' - Egypt Tour Pro')
@section('description', $pageDescription)
@section('keywords',
    trim(
    collect([$attraction->display_name, $cityName, $countryName, 'Attraction details', 'Egypt Tour
    Pro'])->filter()->implode(',
    '),
    ', ',
    ))
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/attractions-show.css')
@endsection

@section('content')
    <!-- Breadcrumb -->
    <section class="attraction-breadcrumb">
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
                    <li class="breadcrumb-item">
                        <a href="{{ $cityRoute }}">{{ $cityName }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $attraction->display_name }}
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Hero Banner -->
    <section class="attraction-hero" style="--hero-bg: url('{{ $heroImage }}');">
        <div class="container">
            <div class="attraction-hero-content">
                <div class="hero-badge">
                    <i class="la la-landmark"></i>
                    {{ $cityName }} — {{ $countryName }}
                </div>

                <h1 class="hero-title">{{ $attraction->display_name }}</h1>
                <p class="hero-subtitle">{{ $heroSubtitle }}</p>

                <div class="hero-actions">
                    @if ($packages->count())
                        <a href="#attraction-tours" class="hero-btn">
                            <i class="la la-suitcase"></i>
                            {{ __('Explore Tours Visiting Here') }}
                        </a>
                    @endif
                    <a href="{{ route('website.tailor_made.index') }}" class="hero-btn-outline">
                        <i class="la la-route"></i>
                        {{ __('Customize a Tour') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview & Quick Facts -->
    <section class="attraction-overview">
        <div class="container">
            <div class="overview-grid">
                <div class="overview-panel">
                    <div class="section-kicker">
                        <i class="la la-compass"></i>
                        {{ __('About this place') }}
                    </div>
                    <h2>{{ $attraction->display_name }}</h2>

                    @if ($descriptionHtml)
                        <div class="overview-body">{!! $descriptionHtml !!}</div>
                    @else
                        <div class="overview-body">
                            <p>{{ $overviewText ?: $heroSubtitle }}</p>
                        </div>
                    @endif
                </div>

                <div class="sidebar-card">
                    <h3>{{ __('Location & Info') }}</h3>

                    <div class="fact-list">
                        <div class="fact-item">
                            <span><i class="la la-map-marker"></i> {{ __('City') }}</span>
                            <strong>{{ $cityName }}</strong>
                        </div>

                        <div class="fact-item">
                            <span><i class="la la-globe"></i> {{ __('Country') }}</span>
                            <strong>{{ $countryName }}</strong>
                        </div>

                        @if ($openingHours)
                            <div class="fact-item">
                                <span><i class="la la-clock"></i> {{ __('Opening Hours') }}</span>
                                <strong>{{ $openingHours }}</strong>
                            </div>
                        @endif

                        @if ($attraction->latitude && $attraction->longitude)
                            <div class="fact-item">
                                <span><i class="la la-map-pin"></i> {{ __('Coordinates') }}</span>
                                <strong>{{ $attraction->latitude }}, {{ $attraction->longitude }}</strong>
                            </div>
                        @endif
                    </div>

                    @if ($attraction->map_url)
                        <a href="{{ $attraction->map_url }}" target="_blank" rel="noopener noreferrer" class="map-btn">
                            <i class="la la-map"></i>
                            {{ __('View on Google Maps') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Tours & Packages Visiting This Attraction -->
    @if ($packages->count())
        <section class="attraction-journeys" id="attraction-tours">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker">
                        <i class="la la-suitcase"></i>
                        {{ __('Featured Itineraries') }}
                    </div>
                    <h2>{{ __('Tours & Trips Visiting :name', ['name' => $attraction->display_name]) }}</h2>
                    <p>
                        {{ __('Discover handpicked itineraries that include :name as part of a complete luxury travel experience.', ['name' => $attraction->display_name]) }}
                    </p>
                </div>

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
                                        <img src="{{ $package['image'] }}" alt="{{ $package['title'] }}" loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
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

                                    <p class="journey-description">{{ $package['description'] }}</p>

                                    <a href="{{ $package['url'] }}" class="journey-btn">
                                        {{ $package['button_text'] }}
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($packages->hasPages())
                    <div class="pagination-wrap mt-4 d-flex justify-content-center">
                        {{ $packages->links() }}
                    </div>
                @endif
            </div>
        </section>
    @endif

    <!-- Related Attractions in same city -->
    @if ($relatedAttractions->count())
        <section class="related-attractions">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker">
                        <i class="la la-landmark"></i>
                        {{ __('Nearby Places') }}
                    </div>
                    <h2>{{ __('Other Highlights in :city', ['city' => $cityName]) }}</h2>
                    <p>
                        {{ __('Explore other top attractions and landmarks to include in your :city trip.', ['city' => $cityName]) }}
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ($relatedAttractions as $rel)
                        @php
                            $relImg = $rel->image
                                ? asset('storage/' . ltrim($rel->image, '/'))
                                : asset('website/photos/home2.webp');
                            $relDesc = Str::limit(
                                trim(strip_tags($rel->display_short_description ?: $rel->display_description)),
                                120,
                            );
                        @endphp
                        <div class="col-lg-3 col-md-6">
                            <article class="attraction-card-item">
                                <div class="attraction-card-img">
                                    <img src="{{ $relImg }}" alt="{{ $rel->display_name }}" loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                </div>
                                <div class="attraction-card-body">
                                    <h3 class="attraction-card-title">
                                        <a
                                            href="{{ route('website.attractions.show', $rel->slug) }}">{{ $rel->display_name }}</a>
                                    </h3>
                                    <p class="attraction-card-desc">{{ $relDesc }}</p>
                                    <a href="{{ route('website.attractions.show', $rel->slug) }}"
                                        class="attraction-card-link">
                                        {{ __('Explore Place') }}
                                        <i
                                            class="la {{ app()->getLocale() === 'ar' ? 'la-angle-left' : 'la-angle-right' }}"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Tailor Made CTA -->
    <section class="attraction-cta">
        <div class="container">
            <div class="cta-card">
                <div>
                    <div class="section-kicker">
                        <i class="la la-gem"></i>
                        {{ __('Customized Experience') }}
                    </div>
                    <h2>{{ __('Want to visit :name on your terms?', ['name' => $attraction->display_name]) }}</h2>
                    <p>
                        {{ __('Our travel experts can build a private itinerary tailored to your preferences including :name and surrounding attractions.', ['name' => $attraction->display_name]) }}
                    </p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('website.tailor_made.index') }}" class="cta-btn">
                        <i class="la la-route"></i>
                        {{ __('Design Custom Tour') }}
                    </a>
                    <a href="{{ route('website.contact.index') }}" class="cta-btn secondary">
                        <i class="la la-envelope"></i>
                        {{ __('Contact Us') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
