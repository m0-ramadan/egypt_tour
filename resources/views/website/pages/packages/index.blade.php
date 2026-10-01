@extends('website.layouts.master')

@php
    $isToursPage = request()->routeIs('website.tours.*') || request()->routeIs('website.day_tours.*');
    $indexRoute = $isToursPage ? route('website.tours.all') : route('website.travel_packages.index');
    $firstPackage = $packages->first();
    $heroImage = is_array($firstPackage)
        ? $firstPackage['image'] ?? asset('website/photos/home2.webp')
        : asset('website/photos/home2.webp');
@endphp

@section('title', $pageContent['title'] . ' - Egypt Tour Pro')
@section('description', $pageContent['description'] ?? $pageContent['overview_text'])
@section('keywords',
    trim(
    collect([$pageContent['title'] ?? null, 'Egypt Tour Pro', 'Egypt tours', 'travel packages'])->filter()->implode(', '),
    ', ',
    ))
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/packages-index.css')
@endsection

@section('content')
    <section class="listing-hero" style="--hero-bg: url(\'{{ $heroImage }}\');">
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
