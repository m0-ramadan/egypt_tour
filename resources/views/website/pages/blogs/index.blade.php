@extends('website.layouts.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $pageTitle = isset($category)
        ? ($category->display_title ?? ($category->title ?? ($category->name ?? __('Blog Category')))) .
            ' - ' .
            __('Egypt Tour Pro')
        : __('Blogs') . ' - ' . __('Egypt Tour Pro');

    $heroTitle = isset($category)
        ? $category->display_title ?? ($category->title ?? ($category->name ?? __('Travel Blog')))
        : __('Egypt Tour Pro Travel Blog');

    $heroSubtitle = isset($category)
        ? __('Discover useful travel articles, destination guides, and expert insights about :category.', [
            'category' => $category->display_title ?? ($category->title ?? ($category->name ?? __('Category'))),
        ])
        : __(
            'Discover the wonders of Egypt through our expert travel insights, destination guides, and cultural explorations.',
        );

    $blogsRoute = Route::has('website.blogs') ? route('website.blogs') : url('/blog');
@endphp

@section('title', $pageTitle)
@section('description', $heroSubtitle)
@section('keywords',
    trim(
    collect([$heroTitle, 'Egypt Tour Pro blog', 'Egypt travel blog', 'destination guides'])->filter()->implode(', '),
    ', ',
    ))
@section('image', asset('website/photos/home2.webp'))

@section('css')
    @vite('resources/css/pages/blogs-index.css')
@endsection

@section('content')

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="la la-newspaper"></i>
                    {{ __('Travel Insights') }}
                </div>

                <h1 class="hero-title">{{ $heroTitle }}</h1>

                <p class="hero-subtitle">
                    {{ $heroSubtitle }}
                </p>
            </div>
        </div>
    </section>

    <section class="breadcrumb-section">
        <div class="container">
            <div class="breadcrumb-container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">
                            <i class="la la-home breadcrumb-icon"></i>
                            {{ __('Home') }}
                        </a>
                    </li>

                    @if (isset($category))
                        <li class="breadcrumb-item">
                            <a href="{{ $blogsRoute }}">{{ __('Blogs') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ $category->display_title ?? ($category->title ?? ($category->name ?? __('Category'))) }}
                        </li>
                    @else
                        <li class="breadcrumb-item active">{{ __('Blogs') }}</li>
                    @endif
                </ol>
            </div>
        </div>
    </section>

    <section class="blog-card-area">
        <div class="container">
            <div class="row">

                <div class="col-lg-8">
                    <div class="row">
                        @forelse ($articles as $article)
                            @php
                                $articleTitle = $article->display_title ?: __('Article');

                                $articleImage = $article->featured_image
                                    ? asset('storage/' . ltrim($article->featured_image, '/'))
                                    : asset('website/photos/home2.webp');

                                $articleDate = $article->published_at ?? ($article->created_at ?? now());

                                $articleCategoryTitle =
                                    $article->category?->display_title ??
                                    ($article->category?->title ?? ($article->category?->name ?? __('General')));

                                $articleCategorySlug = $article->category?->slug ?? Str::slug($articleCategoryTitle);

                                $articleExcerpt =
                                    $article->display_excerpt ?: Str::limit(strip_tags($article->display_content), 120);

                                $articleUrl = Route::has('website.blogs.show')
                                    ? route('website.blogs.show', $article->slug)
                                    : url('/blog/' . $article->slug);
                            @endphp

                            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                                <div class="modern-blog-card">
                                    <div class="blog-image">
                                        <a href="{{ $articleUrl }}">
                                            <img src="{{ $articleImage }}" alt="{{ $articleTitle }}" class="blog-img"
                                                loading="lazy">

                                            <div class="blog-overlay">
                                                <div class="overlay-content">
                                                    <i class="la la-eye"></i>
                                                    <span>{{ __('Read Article') }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="blog-content-wrapper">
                                        <h3 class="blog-card-title">
                                            <a href="{{ $articleUrl }}">
                                                {{ $articleTitle }}
                                            </a>
                                        </h3>

                                        <div class="blog-date">
                                            <i class="la la-calendar"></i>
                                            <span>{{ \Carbon\Carbon::parse($articleDate)->locale(app()->getLocale())->translatedFormat('D, d M Y') }}</span>
                                        </div>

                                        <div class="blog-description">
                                            <p>{{ $articleExcerpt }}</p>
                                        </div>

                                        <div class="blog-footer">
                                            <a href="{{ $articleUrl }}" class="btn-blog">
                                                {{ __('Read More') }} <i class="la la-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    {{ __('No articles found.') }}
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if (method_exists($articles, 'links') && $articles->hasPages())
                        <div class="pagination-wrapper">
                            {{ $articles->links() }}
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Search Articles') }}</h3>

                            <form action="{{ $blogsRoute }}" method="get" class="search-form">
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
                                @forelse ($categories as $blogCategory)
                                    @php
                                        $catTitle =
                                            $blogCategory->display_title ??
                                            ($blogCategory->title ?? ($blogCategory->name ?? __('Category')));

                                        $catSlug = $blogCategory->slug ?? Str::slug($catTitle);

                                        $catUrl = Route::has('website.blogs.category')
                                            ? route('website.blogs.category', $catSlug)
                                            : url('/blog/' . $catSlug);
                                    @endphp

                                    <a href="{{ $catUrl }}" class="category-tag">
                                        {{ $catTitle }}
                                    </a>
                                @empty
                                    <div class="empty-state">
                                        {{ __('No categories found.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Popular Articles') }}</h3>

                            @forelse ($popularArticles as $popular)
                                @php
                                    $popularTitle = $popular->display_title ?: __('Article');

                                    $popularImage = $popular->featured_image
                                        ? asset('storage/' . ltrim($popular->featured_image, '/'))
                                        : asset('website/photos/home2.webp');

                                    $popularDate = $popular->published_at ?? ($popular->created_at ?? now());

                                    $popularCategoryTitle =
                                        $popular->category?->display_title ??
                                        ($popular->category?->title ?? ($popular->category?->name ?? __('General')));

                                    $popularCategorySlug =
                                        $popular->category?->slug ?? Str::slug($popularCategoryTitle);

                                    $popularUrl = Route::has('website.blogs.show')
                                        ? route('website.blogs.show', $popular->slug)
                                        : url('/blog/' . $popular->slug);
                                @endphp

                                <div class="popular-article">
                                    <img src="{{ $popularImage }}" alt="{{ $popularTitle }}" class="popular-img"
                                        loading="lazy" onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">

                                    <div class="popular-content">
                                        <h4>
                                            <a href="{{ $popularUrl }}">
                                                {{ $popularTitle }}
                                            </a>
                                        </h4>

                                        <p class="popular-date">
                                            {{ \Carbon\Carbon::parse($popularDate)->locale(app()->getLocale())->translatedFormat('D, d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    {{ __('No popular articles found.') }}
                                </div>
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

    {{-- <div class="fixed-mobile-btn d-lg-none">
        <a href="https://api.whatsapp.com/send?phone=201062217720" target="_blank" class="mobile-enquiry-btn">
            <i class="lab la-whatsapp"></i>
            {{ __('WhatsApp Us') }}
        </a>
    </div> --}}

    <section class="why-choose-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-header">
                        {{ __('Why Travel With Egypt Tour Pro?') }}
                    </h2>
                    <p class="section-subtitle">
                        {{ __('Your entire vacation is designed around your requirements with expert guidance every step of the way.') }}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-cog"></i>
                        </div>

                        <h3 class="choose-title">{{ __('100% Tailor Made') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Your entire vacation is designed around your requirements.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Explore your interests at your own speed.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Select your preferred style of accommodations.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Create the perfect trip with the help of our specialists.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-lightbulb"></i>
                        </div>

                        <h3 class="choose-title">{{ __('Expert Knowledge') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Our specialists have traveled extensively across Egypt.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('The same specialist will handle your trip from start to finish.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Make the most of your time and budget.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-user-graduate"></i>
                        </div>

                        <h3 class="choose-title">{{ __('The Best Guides') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Our guides make the difference between a good trip and an outstanding one.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Safety and wellbeing are always our number one priority.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Real insight into Egypt, not just dates and names.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-shield-alt"></i>
                        </div>

                        <h3 class="choose-title">{{ __('Fully Protected') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Trusted travel planning from arrival to departure.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Professional support before and during your journey.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Comfortable service standards and secure travel coordination.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="luxury-cta-section">
        <div class="container">
            <div class="luxury-cta-content">
                <div class="cta-icon-container">
                    <i class="la la-phone"></i>
                </div>

                <div class="cta-content-wrapper">
                    <div class="cta-text-content">
                        <h2 class="cta-title">{{ __('Ready to Plan Your Dream Trip?') }}</h2>
                        <p class="cta-subtitle">
                            {{ __('Speak with our Egypt specialists for your perfect luxury journey.') }}
                        </p>

                        <div class="trust-features">
                            <div class="trust-feature">
                                <i class="la la-shield-alt"></i>
                                <span>{{ __('Free Consultation') }}</span>
                            </div>

                            <div class="trust-feature">
                                <i class="la la-clock"></i>
                                <span>{{ __('24/7 Support') }}</span>
                            </div>

                            <div class="trust-feature">
                                <i class="la la-award"></i>
                                <span>{{ __('Best Price Guarantee') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('website.contact.index') }}" class="luxury-cta-btn">
                        <i class="la la-calendar-check"></i>
                        {{ __('Start Planning') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('js')
    @vite('resources/js/pages/blogs-index.js')
@endsection
