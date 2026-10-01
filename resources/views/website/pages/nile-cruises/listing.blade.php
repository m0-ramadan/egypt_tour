@extends('website.layouts.master')

@php
    $heroImage =
        isset($category) && $category->banner_url
            ? $category->banner_url
            : ($type->banner_url ?:
            asset('website/images/nile-cruises/luxor-aswan.jpg'));
@endphp

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords', $pageContent['title'] . ', Egypt Nile Cruise, Nile River Tours')
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/nile-cruises-listing.css')
@endsection

@section('content')
    <!-- Hero Banner -->
    <section class="nile-listing-hero text-center" style="--hero-bg: url('{{ $heroImage }}');">
        <div class="container">
            <nav aria-label="breadcrumb" class="d-flex justify-content-center mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('website.nile_cruises.index') }}">{{ __('Nile Cruise') }}</a></li>
                    @if (isset($category))
                        <li class="breadcrumb-item"><a
                                href="{{ route('website.nile_cruises.luxor_aswan') }}">{{ $type->display_name }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $category->display_name }}</li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">{{ $type->display_name }}</li>
                    @endif
                </ol>
            </nav>

            <div class="nile-badge">
                <i class="la la-ship"></i> {{ $pageContent['badge'] }}
            </div>
            <h1 class="nile-title">{{ $pageContent['title'] }}</h1>
            <p class="nile-subtitle">{{ $pageContent['subtitle'] }}</p>

            @unless (in_array($type->slug, ['dahabiya-nile-cruise', 'lake-nasser-cruise'], true))
                <div class="search-box-wrapper">
                    <form action="{{ url()->current() }}" method="GET" class="search-box">
                        <input type="text" name="q" value="{{ $search }}"
                            placeholder="{{ __('Search Nile cruise packages...') }}">
                        <button type="submit">
                            <i class="la la-search"></i> {{ __('Search') }}
                        </button>
                    </form>
                </div>
            @endunless
        </div>
    </section>

    <!-- Listings Section -->
    <section class="py-5 my-3">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h3 font-serif fw-bold text-dark mb-1" style="font-family: 'Playfair Display', serif;">
                        {{ $pageContent['overview_title'] }}
                    </h2>
                    <p class="text-muted mb-0">
                        {{ __('Showing') }} <strong>{{ $packages->count() }}</strong> {{ __('of') }}
                        <strong>{{ $stats['count'] }}</strong> {{ __('available Nile cruise packages') }}
                    </p>
                </div>

                @if ($search !== '' && !in_array($type->slug, ['dahabiya-nile-cruise', 'lake-nasser-cruise'], true))
                    <div>
                        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="la la-times"></i> {{ __('Clear Search') }}
                        </a>
                    </div>
                @endif
            </div>

            @if ($packages->isNotEmpty())
                <div class="row g-4">
                    @foreach ($packages as $pkg)
                        <div class="col-lg-4 col-md-6">
                            <div class="deal-card">
                                <div class="card-image">
                                    @if (!empty($pkg['is_ultra_luxury']))
                                        <div class="badge-top">{{ __('Ultra Luxury') }}</div>
                                    @elseif (!empty($pkg['is_best_seller']))
                                        <div class="badge-top">{{ __('Best Seller') }}</div>
                                    @elseif (!empty($pkg['badge']))
                                        <div class="badge-top">{{ $pkg['badge'] }}</div>
                                    @endif

                                    @if (!empty($pkg['price']))
                                        <div class="deal-price">{{ $pkg['price'] }}</div>
                                    @endif

                                    <a href="{{ $pkg['url'] }}">
                                        <img src="{{ $pkg['image'] }}" alt="{{ $pkg['title'] }}" width="800"
                                            height="500" loading="lazy" decoding="async">
                                    </a>
                                </div>

                                <div class="card-body">
                                    <h3 class="deal-title">
                                        <a href="{{ $pkg['url'] }}">{{ $pkg['title'] }}</a>
                                    </h3>

                                    <div class="deal-meta">
                                        @if (!empty($pkg['duration']))
                                            <span><i class="la la-clock"></i> {{ $pkg['duration'] }}</span>
                                        @endif
                                        @if (!empty($pkg['tour_type']))
                                            <span><i class="la la-users"></i> {{ $pkg['tour_type'] }}</span>
                                        @endif
                                        @if (!empty($pkg['route_text']))
                                            <span><i class="la la-map-marker"></i> {{ $pkg['route_text'] }}</span>
                                        @endif
                                    </div>

                                    @if (!empty($pkg['description']))
                                        <p class="deal-description">{{ $pkg['description'] }}</p>
                                    @endif

                                    @if (!empty($pkg['tags']))
                                        <div class="tag-list">
                                            @foreach ($pkg['tags'] as $tag)
                                                <span class="feature-tag">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <a href="{{ $pkg['url'] }}" class="gold-btn deal-btn">
                                        {{ $pkg['button_text'] ?? __('Explore Journey') }}
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($paginated->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $paginated->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5 my-4 bg-light rounded-4">
                    <div class="mb-3 text-muted">
                        <i class="la la-ship" style="font-size: 3.5rem;"></i>
                    </div>
                    <h3 class="h4 fw-bold text-dark mb-2">{{ $pageContent['empty_title'] }}</h3>
                    <p class="text-muted max-w-lg mx-auto mb-4">{{ $pageContent['empty_text'] }}</p>
                    <a href="{{ route('website.tailor_made.index') }}" class="btn btn-primary rounded-pill px-4 py-2">
                        <i class="la la-magic"></i> {{ __('Customize a Cruise Package') }}
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
