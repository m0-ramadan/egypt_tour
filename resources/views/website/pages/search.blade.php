@extends('website.layouts.master')

@section('title', __('Search Egypt Tours') . ' - Egypt Tour Pro')
@section('description', __('Search Egypt Tour Pro packages, Nile cruises, day trips, and tailor-made travel ideas across
    Egypt and nearby destinations.'))
@section('keywords', 'search Egypt tours, Egypt Tour Pro search, find Nile cruises, Egypt package search')
@section('canonical', route('website.search.index'))
@section('robots', 'noindex, follow')
@section('image', asset('website/logo/egypt-tour-pro-charcoal.webp'))

@section('css')
    @vite('resources/css/pages/search.css')
@endsection

@section('content')
    <section class="search-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">{{ __('Discover Egypt') }}</h1>
                <p class="hero-subtitle">
                    {{ __('Search our collection of tours, travel packages, and Nile cruises to find the journey that fits you best.') }}
                </p>

                <div class="search-form-container">
                    <form action="{{ route('website.search.index') }}" method="GET" class="search-form" autocomplete="off">
                        <div class="search-input-wrap">
                            <i class="las la-search search-icon"></i>
                            <input type="text" name="keyword" class="search-input" id="searchKeyword"
                                placeholder="{{ __('Search tours, packages, cruises...') }}" value="{{ $keyword }}"
                                data-suggestions-url="{{ route('website.search.suggestions') }}">
                            <div id="searchSuggestions" class="search-suggestions-dropdown"></div>
                        </div>

                        <button type="submit" class="search-btn">
                            <i class="las la-search"></i>
                            {{ __('Search Now') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="results-section">
        <div class="container">
            @if (!$hasSearch)
                <div class="empty-state">
                    <h3>{{ __('Start Your Egyptian Adventure') }}</h3>
                    <p>{{ __('Enter keywords above to search our collection of tours, travel packages, and Nile cruises.') }}
                    </p>

                    <div class="suggestion-links">
                        @foreach ($suggestedLinks as $link)
                            <a href="{{ $link['url'] }}" class="suggestion-link">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="section-header">
                    <h2 class="section-title">{{ __('Search Results') }}</h2>
                    <div class="search-stats">
                        @if ($results->total() === 1)
                            {{ __('Found :count result for ":keyword".', ['count' => $results->total(), 'keyword' => $keyword]) }}
                        @else
                            {{ __('Found :count results for ":keyword".', ['count' => $results->total(), 'keyword' => $keyword]) }}
                        @endif
                    </div>
                </div>

                @if ($results->count())
                    <div class="row results-grid">
                        @foreach ($results as $result)
                            <div class="col-lg-4 col-md-6">
                                <article class="result-card">
                                    <a href="{{ $result['url'] }}" class="card-image">
                                        <img src="{{ $result['image'] }}" alt="{{ $result['title'] }}">
                                        @if ($result['price'])
                                            <div class="price-badge">{{ $result['price'] }}</div>
                                        @endif
                                    </a>

                                    <div class="card-content">
                                        <span class="result-type">{{ $result['type'] }}</span>

                                        <a href="{{ $result['url'] }}" class="card-title-link">
                                            <h3 class="card-title">{{ $result['title'] }}</h3>
                                        </a>

                                        @if (!empty($result['meta']))
                                            <div class="card-meta">
                                                @foreach ($result['meta'] as $meta)
                                                    <span>
                                                        <i class="{{ $meta['icon'] }}"></i>
                                                        {{ $meta['text'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <p class="card-description">{{ $result['description'] }}</p>

                                        <div class="card-action">
                                            <a href="{{ $result['url'] }}" class="view-btn">
                                                {{ $result['button_text'] }}
                                                <i class="las la-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>

                    @if ($results->hasPages())
                        <div class="results-pagination">
                            {{ $results->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <h3>{{ __('No results found') }}</h3>
                        <p>{{ __('Try a different keyword, browse our popular sections, or let us help you plan a custom journey.') }}
                        </p>

                        <div class="suggestion-links">
                            @foreach ($suggestedLinks as $link)
                                <a href="{{ $link['url'] }}" class="suggestion-link">{{ $link['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection

@section('js')
    @vite('resources/js/pages/search.js')
@endsection
