@extends('website.layouts.master')

@section('title', $seoTitle)
@section('description', $pageExcerpt)
@section('keywords', trim(collect([$pageTitle, 'Etro Tours', 'Egypt travel'])->implode(', '), ', '))
@section('image', $heroImage)

@section('css')
    <style>
        .static-page-hero {
            position: relative;
            overflow: hidden;
            margin-top: -85px;
            padding: 150px 0 90px;
            background:
                linear-gradient(rgba(10, 24, 48, 0.74), rgba(18, 44, 78, 0.72)),
                url('{{ $heroImage }}') center/cover no-repeat;
        }

        .static-page-hero::before {
            content: '';
            position: absolute;
            inset: auto -10% -90px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 65%);
            filter: blur(24px);
        }

        .static-page-hero .container,
        .static-page-section .container {
            position: relative;
            z-index: 1;
        }

        .static-page-hero-content {
            max-width: 860px;
            color: #fff;
        }

        .static-page-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 999px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            font-weight: 600;
        }

        .static-page-badge i {
            color: #f4c36a;
        }

        .static-page-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5vw, 4.2rem);
            line-height: 1.08;
            margin-bottom: 18px;
            color: #fff;
        }

        .static-page-subtitle {
            max-width: 760px;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.08rem;
            line-height: 1.9;
        }

        .static-page-section {
            padding: 72px 0 90px;
            background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .static-page-card {
            background: #fff;
            border-radius: 30px;
            padding: 34px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 18px 44px rgba(20, 41, 74, 0.08);
        }

        .static-page-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(26, 54, 93, 0.08);
            color: #617189;
            font-size: 0.95rem;
        }

        .static-page-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .static-page-body {
            color: #344256;
            line-height: 1.95;
            font-size: 1.04rem;
        }

        .static-page-body h1,
        .static-page-body h2,
        .static-page-body h3,
        .static-page-body h4 {
            color: #1c325c;
            font-family: 'Playfair Display', serif;
            margin-top: 0;
            margin-bottom: 16px;
        }

        .static-page-body p,
        .static-page-body ul,
        .static-page-body ol,
        .static-page-body blockquote {
            margin-bottom: 18px;
        }

        .static-page-body ul,
        .static-page-body ol {
            padding-inline-start: 22px;
        }

        .static-page-body a {
            color: #c5955b;
            font-weight: 600;
            text-decoration: none;
        }

        .static-page-body a:hover {
            color: #b8860b;
        }

        .static-page-body img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            margin: 10px 0 18px;
        }

        html[data-theme='dark'] .static-page-section {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
        }

        html[data-theme='dark'] .static-page-card {
            background: #111827 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        html[data-theme='dark'] .static-page-meta {
            color: var(--warm-gray) !important;
            border-color: rgba(148, 163, 184, 0.12) !important;
        }

        html[data-theme='dark'] .static-page-body {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .static-page-body h1,
        html[data-theme='dark'] .static-page-body h2,
        html[data-theme='dark'] .static-page-body h3,
        html[data-theme='dark'] .static-page-body h4 {
            color: var(--charcoal-deep) !important;
        }

        html[dir='rtl'] .static-page-hero-content,
        html[dir='rtl'] .static-page-card {
            text-align: right;
        }

        @media (max-width: 767px) {
            .static-page-hero {
                padding: 135px 0 70px;
            }

            .static-page-section {
                padding: 54px 0 70px;
            }

            .static-page-card {
                padding: 24px 20px;
                border-radius: 24px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="static-page-hero">
        <div class="container">
            <div class="static-page-hero-content">
                <div class="static-page-badge">
                    <i class="la la-file-alt"></i>
                    <span>{{ __('Etro Tours') }}</span>
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
