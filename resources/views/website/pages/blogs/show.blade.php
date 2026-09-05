@extends('website.layouts.master')

@php
    $title = $article->display_title;
    $description = $article->display_seo_description ?: Str::limit(strip_tags($article->display_excerpt), 160);
    $content = $article->display_content;
    $image = asset('storage/' . $article->featured_image);

    $categoryTitle = $article->category?->display_title ?? ($article->category?->title ?? 'Travel Article');

    $categorySlug = $article->category?->slug ?? 'general';

    $authorName = $article->author?->name ?? 'Etro Tours';

    $date = $article->published_at ?? $article->created_at;

    $articleUrl = request()->fullUrl();

    $facebookShare = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($articleUrl);
    $twitterShare = 'https://twitter.com/intent/tweet?url=' . urlencode($articleUrl) . '&text=' . urlencode($title);
@endphp

@section('title', $article->display_seo_title ?: $title . ' - Etro Tours')
@section('description', $description)
@section('keywords', trim(collect([$title, $categoryTitle, 'Etro Tours blog', 'Egypt travel guide'])->filter()->implode(', '), ', '))
@section('image', $image)
@section('og_type', 'article')
@section('published_time', optional($date)->toIso8601String())
@section('modified_time', optional($article->updated_at)->toIso8601String())

@section('css')
    <style>
        .article-hero {
            min-height: 520px;
            background:
                linear-gradient(rgba(28, 50, 92, .45), rgba(26, 75, 102, .55)),
                url('{{ $image }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            position: relative;
            color: #fff;
            overflow: hidden;
        }

        .article-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .15);
            pointer-events: none;
        }

        .article-meta {
            position: relative;
            z-index: 2;
            max-width: 950px;
            margin: auto;
            text-align: center;
            padding: 120px 20px 70px;
        }

        .article-badge {
            background: rgba(197, 149, 91, .95);
            color: var(--primary-navy, #1c325c);
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .article-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 24px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, .35);
        }

        .article-info {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .info-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .28);
            backdrop-filter: blur(10px);
            padding: 9px 15px;
            border-radius: 30px;
            font-size: .95rem;
        }

        .info-item i {
            color: var(--rich-gold, #c5955b);
        }

        .article-content-area {
            background: linear-gradient(135deg, var(--cream-elegant, #f8f2e8), var(--light-sand, #efe4d3));
            padding: 70px 0;
        }

        .article-content-wrapper,
        .article-footer,
        .luxury-sidebar,
        .related-article-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 10px 35px rgba(28, 50, 92, .08);
            border: 1px solid rgba(197, 149, 91, .14);
            overflow: hidden;
        }

        .featured-image-container {
            margin-bottom: 30px;
        }

        .featured-image {
            width: 100%;
            height: 430px;
            object-fit: cover;
            display: block;
        }

        .article-content {
            padding: 34px;
            color: #444;
            line-height: 1.9;
            font-size: 1.05rem;
        }

        .article-content h2,
        .article-content h3,
        .article-content h4 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy, #1c325c);
            font-weight: 800;
            margin-top: 30px;
            margin-bottom: 16px;
        }

        .article-content h2 {
            font-size: 2rem;
            position: relative;
            padding-bottom: 12px;
        }

        .article-content h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 75px;
            height: 4px;
            background: var(--gradient-gold, #c5955b);
            border-radius: 4px;
        }

        .article-content h3 {
            font-size: 1.45rem;
        }

        .article-content p {
            margin-bottom: 18px;
        }

        .article-content a {
            color: var(--rich-gold, #c5955b);
            font-weight: 700;
            text-decoration: none;
        }

        .article-content a:hover {
            text-decoration: underline;
        }

        .article-content ul,
        .article-content ol {
            padding-left: 22px;
            margin-bottom: 24px;
        }

        .article-content li {
            padding: 7px 0;
        }

        .article-footer {
            margin-top: 28px;
            padding: 24px;
        }

        .article-tags-share {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .article-tags {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag-item,
        .category-tag {
            background: rgba(197, 149, 91, .13);
            color: var(--primary-navy, #1c325c);
            border: 1px solid rgba(197, 149, 91, .22);
            padding: 8px 14px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 700;
            transition: .3s;
        }

        .tag-item:hover,
        .category-tag:hover {
            background: var(--rich-gold, #c5955b);
            color: #fff;
        }

        .article-share {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .share-button,
        .social-link {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--gradient-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .3s;
        }

        .share-button:hover,
        .social-link:hover {
            transform: translateY(-3px);
            color: var(--primary-navy, #1c325c);
        }

        .luxury-sidebar {
            margin-bottom: 25px;
            padding: 24px;
        }

        .sidebar-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy, #1c325c);
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 18px;
            position: relative;
            padding-bottom: 12px;
        }

        .sidebar-title::after {
            content: '';
            width: 65px;
            height: 4px;
            background: var(--gradient-gold, #c5955b);
            border-radius: 4px;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        .search-form {
            display: flex;
            gap: 8px;
        }

        .search-input {
            flex: 1;
            border: 2px solid #e9ecef;
            border-radius: 14px;
            padding: 13px 15px;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--rich-gold, #c5955b);
            box-shadow: 0 0 0 .2rem rgba(197, 149, 91, .18);
        }

        .search-btn {
            border: none;
            background: var(--gradient-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
            border-radius: 14px;
            width: 48px;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .category-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .popular-article {
            display: flex;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(197, 149, 91, .14);
        }

        .popular-article:last-child {
            border-bottom: none;
        }

        .popular-img {
            width: 85px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            background: #eee;
            flex-shrink: 0;
        }

        .popular-content h4 {
            font-size: .95rem;
            margin: 0 0 6px;
            line-height: 1.35;
            font-weight: 800;
        }

        .popular-content h4 a {
            color: var(--primary-navy, #1c325c);
            text-decoration: none;
        }

        .popular-content h4 a:hover {
            color: var(--rich-gold, #c5955b);
        }

        .popular-date {
            margin: 0;
            color: #888;
            font-size: .85rem;
        }

        .social-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .related-articles-section {
            background: var(--pearl-luxury, #faf8f3);
            padding: 70px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 42px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy, #1c325c);
            font-size: clamp(1.8rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 14px;
        }

        .section-subtitle {
            color: #777;
            max-width: 700px;
            margin: auto;
            line-height: 1.7;
        }

        .related-article-card {
            height: 100%;
            transition: .35s;
        }

        .related-article-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 45px rgba(28, 50, 92, .16);
        }

        .related-image {
            height: 220px;
            overflow: hidden;
            background: #eee;
        }

        .related-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: .45s;
        }

        .related-article-card:hover .related-img {
            transform: scale(1.08);
        }

        .related-content {
            padding: 22px;
        }

        .related-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.35;
            margin-bottom: 12px;
        }

        .related-title a {
            color: var(--primary-navy, #1c325c);
            text-decoration: none;
        }

        .related-title a:hover {
            color: var(--rich-gold, #c5955b);
        }

        .related-desc {
            color: #777;
            line-height: 1.65;
            margin-bottom: 18px;
        }

        .btn-read-more {
            background: var(--gradient-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: .3s;
        }

        .btn-read-more:hover {
            transform: translateY(-2px);
            color: var(--primary-navy, #1c325c);
        }

        .empty-state {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            color: #777;
            border: 1px solid rgba(197, 149, 91, .15);
        }

        @media(max-width: 991px) {
            .article-hero {
                min-height: 430px;
                background-attachment: scroll;
            }

            .article-meta {
                padding-top: 95px;
            }

            .featured-image {
                height: 300px;
            }

            .article-content {
                padding: 24px;
            }
        }

        @media(max-width: 575px) {
            .article-info {
                flex-direction: column;
                align-items: center;
            }

            .featured-image {
                height: 240px;
            }

            .article-content {
                padding: 20px;
            }

            .article-content h2 {
                font-size: 1.55rem;
            }

            .article-content h3 {
                font-size: 1.25rem;
            }

            .article-tags-share {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('schema')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => [$image],
            'datePublished' => optional($date)->toIso8601String(),
            'dateModified' => optional($article->updated_at)->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $authorName,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Etro Tours',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('website/logo/logo-lat.png'),
                ],
            ],
            'mainEntityOfPage' => request()->fullUrl(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endsection

@section('content')

    <section class="article-hero">
        <div class="container">
            <div class="article-meta">
                <div class="article-badge">
                    <i class="la la-newspaper"></i> {{ __('Travel Article') }}
                </div>

                <h1 class="article-title">{{ $title }}</h1>

                <div class="article-info">
                    <div class="info-item">
                        <i class="la la-user"></i>
                        <span>{{ __('By') }} <strong>{{ $authorName }}</strong></span>
                    </div>

                    <div class="info-item">
                        <i class="la la-calendar"></i>
                        <span>{{ $date ? \Carbon\Carbon::parse($date)->format('D, d M Y') : '' }}</span>
                    </div>

                    <div class="info-item">
                        <i class="la la-tag"></i>
                        <span>{{ $categoryTitle }}</span>
                    </div>

                    @if (!empty($article->reading_time))
                        <div class="info-item">
                            <i class="la la-clock"></i>
                            <span>{{ $article->reading_time }} min read</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="article-content-area">
        <div class="container">
            <div class="row">

                <div class="col-lg-8 mb-5">
                    <div class="article-content-wrapper">
                        <div class="featured-image-container">
                            <img src="{{ $image }}" alt="{{ $title }}" class="featured-image">
                        </div>

                        <div class="article-content">
                            {!! $content !!}
                        </div>
                    </div>

                    <div class="article-footer">
                        <div class="article-tags-share">
                            <div class="article-tags">
                                <span style="color: var(--primary-navy); font-weight: 700;">{{ __('Tags:') }}</span>

                                @if ($article->tags && $article->tags->count())
                                    @foreach ($article->tags as $tag)
                                        @php
                                            $tagTitle = $tag->display_title ?? ($tag->title ?? ($tag->name ?? 'Tag'));

                                            $tagSlug = $tag->slug ?? \Illuminate\Support\Str::slug($tagTitle);
                                        @endphp

                                        <a href="{{ route('website.blogs.index', ['keyword' => $tagTitle]) }}"
                                            class="tag-item">
                                            {{ $tagTitle }}
                                        </a>
                                    @endforeach
                                @else
                                    <a href="{{ route('website.blogs.category', $categorySlug) }}" class="tag-item">
                                        {{ $categoryTitle }}
                                    </a>
                                @endif
                            </div>

                            <div class="article-share">
                                <span style="color: var(--primary-navy); font-weight: 700; margin-right: 10px;">
                                    {{ __('Share:') }}
                                </span>

                                <a href="{{ $facebookShare }}" target="_blank" class="share-button" rel="nofollow">
                                    <i class="lab la-facebook-f"></i>
                                </a>

                                <a href="{{ $twitterShare }}" target="_blank" class="share-button" rel="nofollow">
                                    <i class="lab la-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Search Articles') }}</h3>

                            <form action="{{ route('website.blogs.index') }}" method="get" class="search-form">
                                <input type="text" name="keyword" class="search-input"
                                    placeholder="{{ __('Search for articles...') }}" value="{{ request('keyword') }}">

                                <button type="submit" class="search-btn">
                                    <i class="la la-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Categories') }}</h3>

                            <div class="category-tags">
                                @forelse ($categories as $category)
                                    @php
                                        $catTitle =
                                            $category->display_title ??
                                            ($category->title ?? ($category->name ?? 'Category'));

                                        $catSlug = $category->slug ?? \Illuminate\Support\Str::slug($catTitle);
                                    @endphp

                                    <a href="{{ route('website.blogs.category', $catSlug) }}" class="category-tag">
                                        {{ $catTitle }}
                                    </a>
                                @empty
                                    <div class="empty-state">{{ __('No categories found.') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Popular Articles') }}</h3>

                            @forelse ($popularArticles as $popular)
                                @php
                                    $popularTitle = $popular->display_title ?: 'Article';

                                    $popularImage = $popular->featured_image ?? asset('website/photos/home2.webp');

                                    $popularDate = $popular->published_at ?? ($popular->created_at ?? now());

                                    $popularCategoryTitle =
                                        $popular->category?->display_title ??
                                        ($popular->category?->title ?? ($popular->category?->name ?? 'general'));

                                    $popularCategorySlug =
                                        $popular->category?->slug ??
                                        \Illuminate\Support\Str::slug($popularCategoryTitle);
                                @endphp

                                <div class="popular-article">
                                    <img src="{{ asset('storage/' . $popularImage) }}" alt="{{ $popularTitle }}"
                                        class="popular-img">

                                    <div class="popular-content">
                                        <h4>
                                            <a
                                                href="{{ route('website.blogs.show.legacy', [$popularCategorySlug, $popular->slug]) }}">
                                                {{ $popularTitle }}
                                            </a>
                                        </h4>

                                        <p class="popular-date">
                                            {{ \Carbon\Carbon::parse($popularDate)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">{{ __('No popular articles found.') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Follow & Connect') }}</h3>

                            <div class="social-links">
                                <a href="https://www.facebook.com/" target="_blank" class="social-link">
                                    <i class="lab la-facebook-f"></i>
                                </a>

                                <a href="https://twitter.com/" target="_blank" class="social-link">
                                    <i class="lab la-twitter"></i>
                                </a>

                                <a href="https://www.instagram.com/" target="_blank" class="social-link">
                                    <i class="lab la-instagram"></i>
                                </a>

                                <a href="https://www.tripadvisor.com/" target="_blank" class="social-link">
                                    <i class="la la-tripadvisor"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <section class="related-articles-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ __('You Might Also Like') }}</h2>
                <p class="section-subtitle">
                    {{ __('Discover more fascinating articles about Egypt\'s wonders and travel insights.') }}
                </p>
            </div>

            <div class="row">
                @forelse ($relatedArticles as $related)
                    @php
                        $relatedTitle = $related->display_title ?: 'Article';

                        $relatedImage = asset('storage/' . ($related->featured_image ?? 'website/photos/home2.webp'));

                        $relatedCategoryTitle =
                            $related->category?->display_title ??
                            ($related->category?->title ?? ($related->category?->name ?? 'general'));

                        $relatedCategorySlug =
                            $related->category?->slug ?? \Illuminate\Support\Str::slug($relatedCategoryTitle);

                        $relatedDesc =
                            $related->display_excerpt ?:
                            \Illuminate\Support\Str::limit(strip_tags($related->display_content), 130);
                    @endphp

                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="related-article-card">
                            <div class="related-image">
                                <a href="{{ route('website.blogs.show', $related->slug) }}">
                                    <img src="{{ $relatedImage }}" alt="{{ $relatedTitle }}" class="related-img"
                                        loading="lazy">
                                </a>
                            </div>

                            <div class="related-content">
                                <h3 class="related-title">
                                    <a href="{{ route('website.blogs.show', $related->slug) }}">
                                        {{ $relatedTitle }}
                                    </a>
                                </h3>

                                <p class="related-desc">{{ $relatedDesc }}</p>

                                <a href="{{ route('website.blogs.show', $related->slug) }}" class="btn-read-more">
                                    {{ __('Read More') }} <i class="la la-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state">{{ __('No related articles found.') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

@endsection

@section('js')
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;

                e.preventDefault();

                window.scrollTo({
                    top: target.offsetTop - 90,
                    behavior: 'smooth'
                });
            });
        });
    </script>
@endsection
