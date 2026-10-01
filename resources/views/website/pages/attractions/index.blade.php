@extends('website.layouts.master')

@section('title', __('Attractions & Sights in Egypt') . ' - Egypt Tour Pro')
@section('description',
    __('Discover Egypt\'s top attractions and iconic sights. Browse temples, monuments, beaches, and
    landmarks by city to plan your perfect Egypt trip.'))

@section('body_class', 'attractions-index-page')

@section('css')
    @vite('resources/css/pages/attractions-index.css')
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
