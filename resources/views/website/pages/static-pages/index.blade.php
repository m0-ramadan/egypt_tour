@extends('website.layouts.master')

@section('title', $seoTitle)
@section('description', $pageExcerpt)
@section('keywords', trim(collect([$pageTitle, 'Egypt Tour Pro', 'Egypt travel'])->implode(', '), ', '))
@section('image', $heroImage)

@section('css')
    @vite('resources/css/pages/static-pages-index.css')
@endsection

@section('content')
    <section class="static-page-hero" style="--hero-bg: url(\'{{ $heroImage }}\');">
        <div class="container">
            <div class="static-page-hero-content">
                <div class="static-page-badge">
                    <i class="la la-file-alt"></i>
                    <span>{{ __('Egypt Tour Pro') }}</span>
                </div>
                <h1 class="static-page-title">{{ $pageTitle }}</h1>
                @if ($pageExcerpt)
                    <p class="static-page-subtitle">{{ $pageExcerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="static-page-section">
        <div class="container">
            <div class="static-page-card">
                <div class="static-page-meta">
                    <span><i class="la la-link"></i> {{ $page->slug }}</span>
                    @if ($page->published_at)
                        <span><i class="la la-calendar"></i> {{ $page->published_at->format('M d, Y') }}</span>
                    @endif
                </div>

                <div class="static-page-body">
                    {!! $pageBody !!}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
@endsection
