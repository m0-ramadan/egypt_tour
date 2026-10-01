@extends('website.layouts.master')

@section('title', ($pageTitle ?? __('Destinations')) . ' - Egypt Tour Pro')
@section('description', $overviewText ?? $heroSubtitle)
@section('keywords',
    trim(
    collect([
    $pageTitle ?? __('Destinations'),
    'Egypt Tour Pro',
    'Egypt destinations',
    'travel
    experiences',
    ])->filter()->implode(', '),
    ', ',
    ))
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/destinations-index.css')
@endsection

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="breadcrumb-container">
                <nav aria-label="{{ __('Breadcrumb') }}">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('website.home') }}">
                                <i class="la la-home breadcrumb-icon"></i>{{ __('Home') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $selectedCountry?->display_name ?: __('Destinations') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <section class="hero-section" style="--hero-bg:url('{{ $heroImage }}')">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="la la-star"></i>
                    {{ $heroBadge }}
                </div>
                <h1 class="hero-title">{{ $heroTitle }}</h1>
                <p class="hero-subtitle">{{ $heroSubtitle }}</p>
            </div>
        </div>
    </section>

    <section class="overview-section">
        <div class="container">
            <div class="overview-content">
                <h2 class="overview-title">{{ $overviewTitle }}</h2>
                <div class="overview-text">
                    <p>{{ $overviewText }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="card-area">
        <div class="container">
            <h2 class="section-title">{{ $sectionTitle }}</h2>
            <p class="section-subtitle">{{ $sectionSubtitle }}</p>

            @if ($countries->count())
                <div class="country-filters">
                    <a href="{{ route('website.destinations.index') }}"
                        class="country-filter {{ $selectedCountry ? '' : 'active' }}">
                        {{ __('Destinations') }}
                    </a>
                    @foreach ($countries as $country)
                        <a href="{{ route('website.destinations.index', ['country' => $country->slug]) }}"
                            class="country-filter {{ $selectedCountry?->id === $country->id ? 'active' : '' }}">
                            {{ $country->display_name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="row">
                @forelse ($destinations as $destination)
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="cruise-card">
                            <div class="cruise-image">
                                <div class="city-country-badge">{{ $destination['country'] }}</div>
                                <a href="{{ $destination['url'] }}">
                                    <img src="{{ $destination['image'] }}" alt="{{ $destination['title'] }}"
                                        class="cruise-img" loading="lazy">
                                    <div class="cruise-overlay">
                                        <div class="overlay-content">
                                            <i class="la la-eye"></i>
                                            <span>{{ __('Discover') }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="cruise-content">
                                <h3 class="cruise-title">
                                    <a href="{{ $destination['url'] }}">{{ $destination['title'] }}</a>
                                </h3>
                                <div class="destination-meta">
                                    <span><i class="la la-map-marker"></i>{{ $destination['attractions_count'] }}
                                        {{ __('Sites') }}</span>
                                    <span><i class="la la-suitcase"></i>{{ $destination['packages_count'] }}
                                        {{ __('Trips') }}</span>
                                </div>
                                <div class="cruise-description">
                                    <p>{{ $destination['description'] }}</p>
                                </div>
                                <div class="cruise-footer">
                                    <a href="{{ $destination['url'] }}" class="btn-cruise">
                                        {{ __('Discover') }} <i class="las la-angle-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state">
                            {{ __('No active destinations found. Add active cities from the admin panel.') }}
                        </div>
                    </div>
                @endforelse
            </div>

            @if (method_exists($destinations, 'links') && $destinations->hasPages())
                <div class="pagination-wrap">
                    {{ $destinations->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

@section('js')
    @vite('resources/js/pages/destinations-index.js')
@endsection
