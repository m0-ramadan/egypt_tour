@extends('website.layouts.master')

@php
    $heroImage =
        isset($category) && $category->banner_url
            ? $category->banner_url
            : ($type->banner_url ?:
            asset('website/images/nile-cruises/luxor-aswan.webp'));
@endphp

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['subtitle'])
@section('keywords', $pageContent['title'] . ', Egypt Nile Cruise, Nile River Tours')
@section('image', $heroImage)

@section('css')
    @vite(['resources/css/pages/nile-cruises-listing.css', 'resources/css/pages/packages-index.css'])
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
                            <article class="journey-card">
                                <div class="journey-image">
                                    <div class="journey-type">{{ $pkg['type_label'] ?? __('Nile Cruise') }}</div>

                                    @if (!empty($pkg['badge']))
                                        <div class="journey-badge">{{ $pkg['badge'] }}</div>
                                    @elseif (!empty($pkg['is_ultra_luxury']))
                                        <div class="journey-badge">{{ __('Ultra Luxury') }}</div>
                                    @elseif (!empty($pkg['is_best_seller']))
                                        <div class="journey-badge">{{ __('Best Seller') }}</div>
                                    @endif

                                    <div class="destination-watermark-logo" style="position: absolute; top: 12px; left: 12px; z-index: 3; pointer-events: none; opacity: 0.85;">
                                        <img src="{{ asset('website/logo/egypt-tour-pro-light-96.webp') }}"
                                            alt="Egypt Tour Pro" width="96" height="39" style="height: 28px; width: auto;" loading="lazy" decoding="async">
                                    </div>

                                    <a href="{{ $pkg['url'] }}">
                                        <img src="{{ $pkg['image'] }}" alt="{{ $pkg['title'] }}" width="800"
                                            height="500" loading="lazy" decoding="async">
                                    </a>

                                    @if (!empty($pkg['price']))
                                        <div class="journey-price">{{ $pkg['price'] }}</div>
                                    @endif
                                </div>

                                <div class="journey-body">
                                    @if (!empty($pkg['country']))
                                        <div class="journey-country">{{ $pkg['country'] }}</div>
                                    @elseif (!empty($pkg['route_text']))
                                        <div class="journey-country"><i class="la la-map-marker me-1"></i>{{ $pkg['route_text'] }}</div>
                                    @endif

                                    <h3 class="journey-title">
                                        <a href="{{ $pkg['url'] }}">{{ $pkg['title'] }}</a>
                                    </h3>

                                    <div class="journey-meta">
                                        @if (!empty($pkg['duration']))
                                            <span><i class="la la-clock"></i> {{ $pkg['duration'] }}</span>
                                        @endif
                                        @if (!empty($pkg['tour_type']))
                                            <span><i class="la la-users"></i> {{ $pkg['tour_type'] }}</span>
                                        @endif
                                    </div>

                                    @if (!empty($pkg['schedule']))
                                        <div class="journey-schedule">
                                            <i class="la la-calendar-alt"></i>
                                            <span>{{ $pkg['schedule'] }}</span>
                                        </div>
                                    @endif

                                    @if (!empty($pkg['description']))
                                        <p class="journey-description">{{ $pkg['description'] }}</p>
                                    @endif

                                    @if (!empty($pkg['highlights']))
                                        <div class="journey-highlights">
                                            @foreach ($pkg['highlights'] as $highlight)
                                                <span>{{ $highlight }}</span>
                                            @endforeach
                                        </div>
                                    @elseif (!empty($pkg['tags']))
                                        <div class="journey-highlights">
                                            @foreach ($pkg['tags'] as $tag)
                                                <span>{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <a href="{{ $pkg['url'] }}" class="journey-btn">
                                        {{ $pkg['button_text'] ?? __('Explore Journey') }}
                                        <i class="la la-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
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
