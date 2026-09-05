@extends('website.layouts.master')

@section('title', __('Search Egypt Tours') . ' - Etro Tours')
@section('description', __('Search Etro Tours packages, Nile cruises, day trips, and tailor-made travel ideas across Egypt and nearby destinations.'))
@section('keywords', 'search Egypt tours, Etro Tours search, find Nile cruises, Egypt package search')
@section('canonical', route('website.search.index'))
@section('robots', 'noindex, follow')
@section('image', asset('website/logo/logo-lat.png'))

@section('css')
    <style>
        .search-hero {
            background: linear-gradient(135deg, #1c325c 0%, #1a4b66 55%, #2c3e50 100%);
            color: #fff;
            padding: 130px 0 90px;
            position: relative;
            overflow: hidden;
        }

        .search-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(197, 149, 91, 0.18), transparent 35%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.08), transparent 28%);
        }

        .search-hero .container,
        .results-section .container {
            position: relative;
            z-index: 1;
        }

        .hero-content {
            max-width: 880px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5vw, 3.6rem);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.2rem);
            line-height: 1.8;
            opacity: 0.95;
            max-width: 720px;
            margin: 0 auto 40px;
        }

        .search-form-container {
            max-width: 760px;
            margin: 0 auto;
            padding: 28px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 26px;
            backdrop-filter: blur(14px);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: start;
        }

        .search-input-wrap {
            position: relative;
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.25rem;
        }

        .search-input {
            width: 100%;
            min-height: 62px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.95);
            padding: 16px 20px 16px 56px;
            color: #2c3e50;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #c5955b;
            box-shadow: 0 0 0 0.25rem rgba(197, 149, 91, 0.2);
        }

        .search-btn {
            min-width: 190px;
            min-height: 62px;
            border: none;
            border-radius: 18px;
            padding: 16px 28px;
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(197, 149, 91, 0.28);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(197, 149, 91, 0.34);
        }

        .search-suggestions-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 18px;
            border: 1px solid rgba(197, 149, 91, 0.18);
            box-shadow: 0 18px 40px rgba(28, 50, 92, 0.14);
            overflow: hidden;
            display: none;
            z-index: 20;
        }

        .search-suggestions-dropdown.is-visible {
            display: block;
        }

        .suggestion-item {
            display: block;
            padding: 14px 18px;
            text-decoration: none;
            color: #2c3e50;
            transition: background 0.25s ease, color 0.25s ease;
            border-bottom: 1px solid #f2f2f2;
        }

        .suggestion-item:last-child {
            border-bottom: 0;
        }

        .suggestion-item:hover,
        .suggestion-item.active {
            background: #f8f6f1;
            color: #1c325c;
        }

        .suggestion-type {
            display: block;
            font-size: 0.82rem;
            color: #8a8f98;
            margin-top: 4px;
        }

        .results-section {
            padding: 70px 0 90px;
            background: linear-gradient(180deg, #f8f6f1 0%, #fff 120px);
        }

        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: clamp(2rem, 4vw, 2.8rem);
            margin-bottom: 14px;
        }

        .search-stats {
            color: #6c757d;
            font-size: 1rem;
        }

        .results-grid {
            row-gap: 24px;
        }

        .result-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(197, 149, 91, 0.14);
            box-shadow: 0 12px 36px rgba(28, 50, 92, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-6px);
            border-color: rgba(197, 149, 91, 0.45);
            box-shadow: 0 18px 44px rgba(28, 50, 92, 0.12);
        }

        .card-image {
            position: relative;
            display: block;
            height: 240px;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.45s ease;
        }

        .result-card:hover .card-image img {
            transform: scale(1.06);
        }

        .price-badge {
            position: absolute;
            top: 18px;
            right: 18px;
            padding: 9px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            font-size: 0.88rem;
            font-weight: 700;
            box-shadow: 0 10px 22px rgba(197, 149, 91, 0.25);
        }

        .card-content {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 22px;
        }

        .result-type {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(197, 149, 91, 0.12);
            color: #9b6a2c;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .card-title-link {
            text-decoration: none;
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 1.35rem;
            line-height: 1.45;
            margin-bottom: 14px;
            transition: color 0.3s ease;
        }

        .card-title-link:hover .card-title {
            color: #c5955b;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #f8f6f1;
            border-radius: 14px;
            color: #44505c;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .card-meta i {
            color: #c5955b;
        }

        .card-description {
            color: #6c757d;
            line-height: 1.75;
            flex: 1;
        }

        .card-action {
            margin-top: 20px;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border-radius: 999px;
            padding: 14px 24px;
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(197, 149, 91, 0.24);
        }

        .view-btn:hover {
            color: #1c325c;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(197, 149, 91, 0.32);
        }

        .empty-state {
            max-width: 760px;
            margin: 0 auto;
            padding: 50px 30px;
            border-radius: 28px;
            background: #fff;
            border: 1px solid rgba(197, 149, 91, 0.14);
            box-shadow: 0 16px 40px rgba(28, 50, 92, 0.08);
            text-align: center;
        }

        .empty-state h3 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 2rem;
            margin-bottom: 14px;
        }

        .empty-state p {
            color: #6c757d;
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 26px;
        }

        .suggestion-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .suggestion-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 999px;
            background: #1c325c;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .suggestion-link:hover {
            background: #c5955b;
            color: #1c325c;
            transform: translateY(-2px);
        }

        .results-pagination {
            margin-top: 34px;
            display: flex;
            justify-content: center;
        }

        .results-pagination nav {
            display: flex;
            justify-content: center;
        }

        .results-pagination .pagination {
            gap: 6px;
        }

        .results-pagination .page-link {
            border-radius: 12px;
            border-color: rgba(197, 149, 91, 0.2);
            color: #1c325c;
        }

        .results-pagination .active > .page-link,
        .results-pagination .page-link.active {
            background: #c5955b;
            border-color: #c5955b;
            color: #1c325c;
        }

        @media (max-width: 991px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .search-btn {
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            .search-hero {
                padding: 110px 0 70px;
            }

            .search-form-container {
                padding: 20px;
            }

            .card-meta span {
                width: 100%;
                justify-content: center;
            }

            .suggestion-links {
                flex-direction: column;
            }

            .suggestion-link {
                width: 100%;
            }
        }
    </style>
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
                            <input
                                type="text"
                                name="keyword"
                                class="search-input"
                                id="searchKeyword"
                                placeholder="{{ __('Search tours, packages, cruises...') }}"
                                value="{{ $keyword }}"
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
                    <p>{{ __('Enter keywords above to search our collection of tours, travel packages, and Nile cruises.') }}</p>

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
                        <p>{{ __('Try a different keyword, browse our popular sections, or let us help you plan a custom journey.') }}</p>

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('searchKeyword');
            const dropdown = document.getElementById('searchSuggestions');

            if (!input || !dropdown) {
                return;
            }

            let activeIndex = -1;
            let suggestions = [];
            let debounceTimer;

            const hideSuggestions = () => {
                dropdown.classList.remove('is-visible');
                dropdown.innerHTML = '';
                activeIndex = -1;
                suggestions = [];
            };

            const renderSuggestions = (items) => {
                suggestions = items;
                activeIndex = -1;

                if (!items.length) {
                    hideSuggestions();
                    return;
                }

                dropdown.innerHTML = items.map((item, index) => `
                    <a href="${item.url}" class="suggestion-item" data-index="${index}">
                        ${item.title}
                        <span class="suggestion-type">${item.type}</span>
                    </a>
                `).join('');

                dropdown.classList.add('is-visible');
            };

            const fetchSuggestions = () => {
                const keyword = input.value.trim();

                if (keyword.length < 2) {
                    hideSuggestions();
                    return;
                }

                const url = new URL(input.dataset.suggestionsUrl);
                url.searchParams.set('keyword', keyword);

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then((response) => response.ok ? response.json() : [])
                    .then((items) => renderSuggestions(Array.isArray(items) ? items : []))
                    .catch(() => hideSuggestions());
            };

            input.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchSuggestions, 180);
            });

            input.addEventListener('keydown', function(event) {
                if (!suggestions.length) {
                    return;
                }

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    activeIndex = (activeIndex + 1) % suggestions.length;
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    activeIndex = activeIndex <= 0 ? suggestions.length - 1 : activeIndex - 1;
                } else if (event.key === 'Enter' && activeIndex >= 0) {
                    event.preventDefault();
                    window.location.href = suggestions[activeIndex].url;
                    return;
                } else if (event.key === 'Escape') {
                    hideSuggestions();
                    return;
                } else {
                    return;
                }

                dropdown.querySelectorAll('.suggestion-item').forEach((item, index) => {
                    item.classList.toggle('active', index === activeIndex);
                });
            });

            document.addEventListener('click', function(event) {
                if (!event.target.closest('.search-input-wrap')) {
                    hideSuggestions();
                }
            });
        });
    </script>
@endsection
