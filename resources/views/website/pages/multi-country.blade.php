@extends('website.layouts.master')

@section('title', ($pageContent['title'] ?? __('Nile Cruise')) . ' - Egypt Tour Pro')
@section('description', $pageContent['description'] ?? $pageContent['overview_text'])
@section('keywords', 'Egypt Nile Cruise, Nile cruises in Egypt, Luxor to Aswan cruise, luxury Nile cruise, Egypt Tour
    Pro Nile cruise')
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/multi-country.css')
@endsection

@section('content')
    @php
        $routeWith = function (array $params = []) {
            $filters = request()->only(['pricerange', 'days', 'sort']);

            foreach ($params as $key => $value) {
                if ($value === null || $value === '') {
                    unset($filters[$key]);
                } else {
                    $filters[$key] = $value;
                }
            }

            return route('website.multi_country', $filters);
        };
    @endphp

    <section class="breadcrumb-top-bar">
        <div class="container">
            <div class="breadcrumb-list">
                <ul>
                    <li><a href="{{ route('website.home') }}">{{ __('Home') }}</a></li>
                    <li>{{ $pageContent['breadcrumb_title'] ?? __('Nile Cruise') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="hero-section">
        <img src="{{ $heroImage }}" alt="{{ $pageContent['title'] }}" class="hero-media">
        <div class="hero-overlay"></div>
        <div class="hero-pattern"></div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="las la-globe"></i>
                    {{ $pageContent['badge'] }}
                </div>
                <h1 class="hero-title">{{ $pageContent['title'] }}</h1>
                <p class="hero-subtitle">{{ $pageContent['subtitle'] }}</p>
                <p class="hero-description">{{ $pageContent['description'] }}</p>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['count']) }}</strong>
                        <span>{{ $pageContent['stats_count_label'] ?? __('Cruises') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['categories']) }}</strong>
                        <span>{{ $pageContent['stats_categories_label'] ?? __('Categories') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['featured']) }}</strong>
                        <span>{{ $pageContent['stats_featured_label'] ?? __('Featured Packages') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="overview-section">
        <div class="container">
            <div class="overview-card">
                <h2>{{ $pageContent['overview_title'] }}</h2>
                <p>{{ $pageContent['overview_text'] }}</p>
            </div>
        </div>
    </section>

    <section class="filters-section">
        <div class="container">
            <div class="filters-container">
                <div class="filter-group">
                    <label>
                        <i class="las la-dollar-sign"></i>
                        {{ __('Filter by Price') }}
                    </label>
                    <select onchange="window.location.href=this.value" class="filter-select">
                        <option value="{{ $routeWith(['pricerange' => null]) }}" @selected(!request()->filled('pricerange'))>
                            {{ __('All Prices') }}</option>
                        <option value="{{ $routeWith(['pricerange' => 1]) }}" @selected(request('pricerange') == '1')>
                            {{ __('Less than $1,500') }}</option>
                        <option value="{{ $routeWith(['pricerange' => 2]) }}" @selected(request('pricerange') == '2')>$1,500 - $2,500
                        </option>
                        <option value="{{ $routeWith(['pricerange' => 3]) }}" @selected(request('pricerange') == '3')>$2,500+</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>
                        <i class="las la-calendar"></i>
                        {{ __('Filter by Duration') }}
                    </label>
                    <select onchange="window.location.href=this.value" class="filter-select">
                        <option value="{{ $routeWith(['days' => null]) }}" @selected(!request()->filled('days'))>
                            {{ __('All Durations') }}</option>
                        <option value="{{ $routeWith(['days' => 1]) }}" @selected(request('days') == '1')>
                            {{ __('Less than 10 Days') }}</option>
                        <option value="{{ $routeWith(['days' => 2]) }}" @selected(request('days') == '2')>
                            {{ __('10 to 20 Days') }}</option>
                        <option value="{{ $routeWith(['days' => 3]) }}" @selected(request('days') == '3')>{{ __('20+ Days') }}
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>
                        <i class="las la-sort"></i>
                        {{ __('Sort by') }}
                    </label>
                    <select onchange="window.location.href=this.value" class="filter-select">
                        <option value="{{ $routeWith(['sort' => null]) }}" @selected(!request()->filled('sort'))>
                            {{ __('Default Order') }}</option>
                        <option value="{{ $routeWith(['sort' => 'price']) }}" @selected(request('sort') === 'price')>
                            {{ __('Sort by Price') }}</option>
                        <option value="{{ $routeWith(['sort' => 'duration']) }}" @selected(request('sort') === 'duration')>
                            {{ __('Sort by Duration') }}</option>
                    </select>
                </div>

                <a href="{{ route('website.multi_country') }}" class="filter-reset">
                    <i class="las la-undo"></i>
                    {{ __('Reset Filters') }}
                </a>
            </div>
        </div>
    </section>

    <section class="tours-section">
        <div class="container">
            <div class="results-head">
                <div>
                    <h3>{{ $pageContent['results_title'] ?? __('Matching Nile Cruises') }}</h3>
                    <p>{{ number_format($stats['count']) }} {{ $pageContent['results_count_label'] ?? __('Cruises') }}</p>
                </div>
            </div>

            <div class="tours-grid">
                @forelse($packages as $tour)
                    <article class="tour-card">
                        <a href="{{ $tour['url'] }}" class="tour-image">
                            <img src="{{ $tour['image'] }}" alt="{{ $tour['title'] }}" loading="lazy">

                            @if ($tour['badge'])
                                <div class="tour-badge">{{ $tour['badge'] }}</div>
                            @endif

                            <div class="price-badge">{{ $tour['price'] }}</div>
                        </a>

                        <div class="tour-content">
                            @if ($tour['country'])
                                <div class="tour-country">{{ $tour['country'] }}</div>
                            @endif

                            <h3 class="tour-title">
                                <a href="{{ $tour['url'] }}">{{ $tour['title'] }}</a>
                            </h3>

                            <div class="tour-meta">
                                <div class="meta-item">
                                    <i class="las la-calendar"></i>
                                    <span>{{ $tour['duration'] }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="las la-tag"></i>
                                    <span>{{ $tour['tour_type'] }}</span>
                                </div>
                            </div>

                            <p class="tour-description">{{ $tour['description'] }}</p>

                            @if (!empty($tour['tags']))
                                <div class="tour-tags">
                                    @foreach ($tour['tags'] as $tag)
                                        <span class="tour-tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ $tour['url'] }}" class="view-btn">
                                {{ $pageContent['cta_label'] ?? __('View Cruise') }}
                                <i class="las la-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="empty-tours-box">
                        <h3>{{ $pageContent['empty_title'] ?? __('No Nile cruises found') }}</h3>
                        <p>{{ $pageContent['empty_text'] ?? __('Please change the filters or add Nile cruise packages from the admin panel.') }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($packages->hasPages())
                <div class="pagination-wrapper">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
