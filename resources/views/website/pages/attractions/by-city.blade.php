@extends('website.layouts.master')

@section('title', __('Attractions in :city', ['city' => $city->display_name]) . ' - Egypt Tour Pro')
@section('description', __('Explore top attractions and iconic sights in :city, Egypt. Find temples, monuments,
    landmarks, and must-see places for your visit.', ['city' => $city->display_name]))
@section('image', $heroImage)

@section('body_class', 'attractions-city-page')

@section('css')
    @vite('resources/css/pages/attractions-by-city.css')
@endsection

@section('content')

    <!-- Hero -->
    <section class="city-hero">
        <div class="city-hero-bg" aria-hidden="true" style="--hero-bg: url('{{ $heroImage }}');"></div>
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
                    <a href="{{ route('website.attractions.index') }}" class="gold-btn mt-2">
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
