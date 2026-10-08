@extends('website.layouts.master')

@php
    $title = $article->display_title;
    $description = $article->display_seo_description ?: Str::limit(strip_tags($article->display_excerpt), 160);
    $content = $article->display_content;
    $imagePath = ltrim((string) $article->featured_image, '/');
    $image = str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')
        ? $imagePath
        : ($imagePath
            ? asset((str_starts_with($imagePath, 'storage/') || str_starts_with($imagePath, 'website/')) ? $imagePath : 'storage/' . $imagePath)
            : asset('website/photos/home2.webp'));

    $categoryTitle = $article->category?->display_title ?? ($article->category?->title ?? 'Travel Article');

    $categorySlug = $article->category?->slug ?? 'general';

    $authorName = $article->author?->name ?? 'Egypt Tour Pro';

    $date = $article->published_at ?? $article->created_at;

    $articleUrl = request()->fullUrl();

    $facebookShare = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($articleUrl);
    $twitterShare = 'https://twitter.com/intent/tweet?url=' . urlencode($articleUrl) . '&text=' . urlencode($title);
@endphp

@section('title', $article->display_seo_title ?: $title . ' - Egypt Tour Pro')
@section('description', $description)
@section('keywords',
    trim(
    collect([$title, $categoryTitle, 'Egypt Tour Pro blog', 'Egypt travel guide'])->filter()->implode(', '),
    ', ',
    ))
@section('image', $image)
@section('og_type', 'article')
@section('published_time', optional($date)->toIso8601String())
@section('modified_time', optional($article->updated_at)->toIso8601String())

@section('css')
    @vite('resources/css/pages/blogs-show.css')
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
                'name' => 'Egypt Tour Pro',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('website/logo/egypt-tour-pro-charcoal.webp'),
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

                                    $popularImagePath = ltrim((string) $popular->featured_image, '/');
                                    $popularImage = str_starts_with($popularImagePath, 'http://') || str_starts_with($popularImagePath, 'https://')
                                        ? $popularImagePath
                                        : ($popularImagePath
                                            ? asset((str_starts_with($popularImagePath, 'storage/') || str_starts_with($popularImagePath, 'website/')) ? $popularImagePath : 'storage/' . $popularImagePath)
                                            : asset('website/photos/home2.webp'));

                                    $popularDate = $popular->published_at ?? ($popular->created_at ?? now());

                                    $popularCategoryTitle =
                                        $popular->category?->display_title ??
                                        ($popular->category?->title ?? ($popular->category?->name ?? 'general'));

                                    $popularCategorySlug =
                                        $popular->category?->slug ??
                                        \Illuminate\Support\Str::slug($popularCategoryTitle);
                                @endphp

                                <div class="popular-article">
                                    <img src="{{ $popularImage }}" alt="{{ $popularTitle }}" class="popular-img"
                                        loading="lazy" onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">

                                    <div class="popular-content">
                                        <h4>
                                            <a
                                                href="{{ route('website.blogs.show', $popular->slug) }}">
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
                                <a href="https://www.facebook.com/Egypttourpro/" target="_blank" class="social-link">
                                    <i class="lab la-facebook-f"></i>
                                </a>

                                <a href="https://twitter.com/" target="_blank" class="social-link">
                                    <i class="lab la-twitter"></i>
                                </a>

                                <a href="https://www.instagram.com/egypt_tour_pro" target="_blank" class="social-link">
                                    <i class="lab la-instagram"></i>
                                </a>

                                <a href="https://www.tripadvisor.com/Attraction_Review-g294205-d34060381-Reviews-Egypt_Tour_Pro-Luxor_Nile_River_Valley.html" target="_blank" class="social-link">
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
    @vite('resources/js/pages/blogs-show.js')
@endsection
