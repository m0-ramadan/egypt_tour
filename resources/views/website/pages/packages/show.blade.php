@extends('website.layouts.master')

@php
    $ncSeoDetail = $package->package_type === 'nile_cruise' ? $package->nileCruiseDetail : null;
    $sharedSocialPath = $package->og_image_path ?: $ncSeoDetail?->social_image_path ?? null;
    $socialImage = $sharedSocialPath ? asset('storage/' . ltrim($sharedSocialPath, '/')) : $heroImage;
    $metaKeywordList = collect((array) ($package->meta_keywords ?: $ncSeoDetail?->meta_keywords ?? []))
        ->push($package->focus_keyword ?: $ncSeoDetail?->focus_keyword ?? null)
        ->filter()
        ->unique()
        ->values();
    $robotsIndex = $package->robots_index;
    $robotsFollow = $package->robots_follow;
    if ($package->package_type === 'nile_cruise' && $ncSeoDetail) {
        $robotsIndex = $package->robots_index ?? $ncSeoDetail->robots_index;
        $robotsFollow = $package->robots_follow ?? $ncSeoDetail->robots_follow;
    }
    $pageRobotsOverride =
        ($robotsIndex === false ? 'noindex' : 'index') .
        ', ' .
        ($robotsFollow === false ? 'nofollow' : 'follow') .
        ', max-image-preview:large';
    $pageOgTitle = $package->og_title ?: $ncSeoDetail?->og_title ?? null;
    $pageOgDescription = $package->og_description ?: $ncSeoDetail?->og_description ?? null;
    $pageTwitterCard = $package->twitter_card ?: $ncSeoDetail?->twitter_card ?? null;
    $pageTwitterTitle = $package->twitter_title ?: $ncSeoDetail?->twitter_title ?? null;
    $pageTwitterDescription = $package->twitter_description ?: $ncSeoDetail?->twitter_description ?? null;
@endphp

@section('title', $package->getTranslation('seo_title') ?: $title . ' - Egypt Tour Pro')
@section('description', $package->getTranslation('seo_description') ?: $shortDescription)
@section('body_class', trim('package-show-template ' . ($package->package_type === 'nile_cruise' ? 'nile-cruise-page' :
    '')))
@section('keywords',
    $metaKeywordList->isNotEmpty()
    ? $metaKeywordList->implode(', ')
    : trim(
    collect([
    $title,
    $tourTypeText ?? null,
    $package->primaryCountry?->display_name ?? null,
    'Egypt Tour
    Pro',
    ])->filter()->implode(', '),
    ', ',
    ))
@section('image', $socialImage)
@section('canonical', $canonicalUrl)
@section('robots', $pageRobotsOverride)
@if ($pageOgTitle)
    @section('og_title', $pageOgTitle)
@endif
@if ($pageOgDescription)
    @section('og_description', $pageOgDescription)
@endif
@if ($pageTwitterCard)
    @section('twitter_card', $pageTwitterCard)
@endif
@if ($pageTwitterTitle)
    @section('twitter_title', $pageTwitterTitle)
@endif
@if ($pageTwitterDescription)
    @section('twitter_description', $pageTwitterDescription)
@endif
@section('twitter_image', $socialImage)

@section('css')
    <style>
        .package-hero {
            min-height: clamp(560px, 72vh, 760px);
            background: linear-gradient(rgba(6, 27, 62, .38), rgba(26, 75, 102, .48)), var(--hero-bg);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            padding: clamp(120px, 14vh, 150px) 0 clamp(45px, 7vh, 70px)
        }

        .package-hero:after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .18);
            pointer-events: none
        }

        .package-hero .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
            max-width: 1000px;
            margin: auto;
            padding: 0 20px
        }

        .package-hero .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.85rem, 4.2vw, 3.6rem);
            font-weight: 700;
            line-height: 1.12;
            margin-bottom: 14px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, .35)
        }

        .package-hero .hero-subtitle {
            font-size: clamp(.9rem, 1.35vw, 1.05rem);
            opacity: .95;
            max-width: 780px;
            margin: 0 auto 20px;
            line-height: 1.55
        }

        .package-hero .hero-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 3
        }

        .package-hero .hero-badges {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px
        }

        .package-hero .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 11px;
            border: 1px solid rgba(255, 255, 255, .36);
            border-radius: 999px;
            background: rgba(11, 18, 32, .36);
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            backdrop-filter: blur(7px)
        }

        .gold-btn,
        .outline-btn,
        .submit-btn {
            background: var(--etp-orange-500, #F36B0A);
            color: var(--etp-navy-950, #061B3E);
            padding: 11px 22px;
            border-radius: 50px;
            text-decoration: none;
            font-size: .9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border: 0;
            transition: .3s;
            cursor: pointer
        }

        .outline-btn {
            background: rgba(255, 255, 255, .13);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .45);
            backdrop-filter: blur(8px)
        }

        .gold-btn:hover,
        .submit-btn:hover {
            transform: translateY(-3px);
            color: var(--etp-navy-950, #061B3E);
            box-shadow: 0 12px 28px rgba(243, 107, 10, .35)
        }

        .outline-btn:hover {
            background: #fff;
            color: var(--etp-navy-950, #061B3E)
        }

        .breadcrumb-top-bar {
            background: var(--etp-surface-muted, #f8fafc);
            padding: 15px 0;
            border-bottom: 1px solid rgba(243, 107, 10, .18)
        }

        .breadcrumb-list ul {
            list-style: none;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0;
            padding: 0
        }

        .breadcrumb-list li {
            color: #777
        }

        .breadcrumb-list li:not(:last-child):after {
            content: '›';
            margin-left: 10px;
            color: var(--etp-orange-500, #F36B0A);
            font-size: 1.2rem
        }

        .breadcrumb-list a {
            color: var(--etp-navy-950, #061B3E);
            text-decoration: none
        }

        .main-container {
            background: linear-gradient(135deg, var(--cream-elegant, #f8f2e8), var(--light-sand, #efe4d3));
            display: flow-root;
            padding: 70px 0
        }

        body.package-show-template .footer {
            clear: both;
            float: none;
            width: 100%;
        }

        .content-wrapper {
            position: relative
        }

        .content-section {
            background: #fff;
            border-radius: 24px;
            padding: 34px;
            margin-bottom: 30px;
            box-shadow: 0 10px 35px rgba(6, 27, 62, .08);
            border: 1px solid rgba(243, 107, 10, .14)
        }

        .section-header {
            font-family: 'Playfair Display', serif;
            color: var(--etp-navy-950, #061B3E);
            font-size: clamp(1.45rem, 3vw, 2.15rem);
            font-weight: 700;
            margin-bottom: 18px;
            position: relative
        }

        .section-header:after {
            content: '';
            display: block;
            width: 78px;
            height: 4px;
            background: var(--etp-orange-500, #F36B0A);
            border-radius: 4px;
            margin-top: 12px
        }

        .section-subtitle {
            color: #777;
            line-height: 1.7;
            margin-bottom: 25px
        }

        .about-content {
            color: #555;
            line-height: 1.85
        }

        .cruise-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 28px
        }

        .detail-item {
            background: var(--etp-surface-muted, #f8fafc);
            border: 1px solid rgba(243, 107, 10, .16);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start
        }

        .detail-item i {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 50%;
            background: var(--etp-orange-500, #F36B0A);
            color: var(--etp-navy-950, #061B3E);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem
        }

        .detail-text {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px 6px;
            line-height: 1.65
        }

        .detail-label {
            color: var(--etp-navy-950, #061B3E);
            white-space: nowrap
        }

        .detail-value {
            color: #555
        }

        .day-card {
            border: 1px solid rgba(243, 107, 10, .18);
            border-radius: 18px;
            margin-bottom: 16px;
            overflow: hidden;
            background: #fff
        }

        .day-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px;
            cursor: pointer;
            background: var(--etp-surface-muted, #f8fafc);
            border: 0;
            width: 100%;
            color: inherit;
            font: inherit;
            text-align: start
        }

        .day-header:focus-visible {
            outline: 3px solid rgba(243, 107, 10, .45);
            outline-offset: -3px
        }

        .day-number {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--etp-orange-500, #F36B0A);
            color: var(--etp-navy-950, #061B3E);
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 46px
        }

        .day-title {
            font-family: 'Playfair Display', serif;
            color: var(--etp-navy-950, #061B3E);
            font-size: 1.15rem;
            margin: 0
        }

        .collapsible-content {
            display: block;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transition: max-height .4s ease, opacity .25s ease, visibility .25s ease
        }

        .collapsible-content.open,
        .collapsible-content.active {
            max-height: 3200px;
            opacity: 1;
            visibility: visible
        }

        .day-header[aria-expanded='true'] .collapse-icon {
            transform: rotate(180deg)
        }

        .day-content {
            padding: 22px;
            color: #555;
            line-height: 1.85
        }

        .meals-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px
        }

        .meal-badge {
            background: rgba(243, 107, 10, .13);
            color: var(--etp-navy-950, #061B3E);
            border-radius: 20px;
            padding: 6px 12px;
            font-weight: 600;
            font-size: .85rem
        }

        .styled-list ul {
            padding-left: 20px;
            margin: 0
        }

        .styled-list li {
            padding: 9px 0;
            color: #555;
            line-height: 1.65;
            border-bottom: 1px solid rgba(243, 107, 10, .12)
        }

        .styled-list li:last-child {
            border-bottom: 0
        }

        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .cruise-facility-list {
            list-style: none;
            margin: 0 0 28px;
            padding: 0;
        }

        .cruise-facility-list li {
            position: relative;
            padding: 9px 0 11px 24px;
            border-bottom: 1px solid rgba(6, 27, 62, .1);
            color: #4f5d6f;
            font-size: 1.02rem;
            line-height: 1.45;
        }

        .cruise-facility-list li:before {
            content: '';
            position: absolute;
            top: 18px;
            left: 3px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--etp-orange-500, #F36B0A);
        }

        .cruise-facility-list li:last-child {
            border-bottom: 0;
        }

        .facility-card {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 56px;
            padding: 14px 16px;
            background: var(--etp-surface-muted, #f8fafc);
            border: 1px solid rgba(243, 107, 10, .16);
            border-radius: 14px;
            color: var(--etp-navy-950, #061B3E);
            font-weight: 600;
        }

        .facility-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            min-width: 18px;
            color: var(--etp-orange-500, #F36B0A);
        }

        .facility-icon svg {
            width: 18px;
            height: 18px;
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .included-box,
        .excluded-box,
        .price-box {
            background: var(--etp-surface-muted, #f8fafc);
            border-radius: 20px;
            padding: 26px;
            border: 1px solid rgba(243, 107, 10, .16);
            height: 100%
        }

        .box-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--etp-navy-950, #061B3E);
            margin-bottom: 16px
        }

        .price-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px
        }

        .price-table-wrap {
            overflow-x: auto;
            border-radius: 14px
        }

        .price-table {
            min-width: 660px
        }

        .price-meta {
            display: block;
            color: #777;
            font-size: .82rem;
            font-weight: 500;
            margin-top: 4px
        }

        .compare-price {
            display: block;
            margin-top: 6px;
            color: rgba(255, 255, 255, .72);
            font-size: .95rem;
            text-decoration: line-through
        }

        .price-table tr {
            background: #fff
        }

        .price-table td,
        .price-table th {
            padding: 13px 15px
        }

        .price-table th {
            color: var(--etp-navy-950, #061B3E)
        }

        .price-table td:first-child,
        .price-table th:first-child {
            border-radius: 12px 0 0 12px
        }

        .price-table td:last-child,
        .price-table th:last-child {
            border-radius: 0 12px 12px 0;
            text-align: right;
            font-weight: 800;
            color: var(--etp-orange-500, #F36B0A)
        }

        .pricing-showcase {
            padding: clamp(32px, 4vw, 48px);
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 0, rgba(255, 255, 255, .98), rgba(255, 253, 248, .94) 74%),
                #fffdf9;
            border: 1px solid rgba(6, 27, 62, .11);
            border-radius: 28px;
            box-shadow: 0 12px 38px rgba(6, 27, 62, .08)
        }

        .pricing-showcase .section-header {
            margin-bottom: 20px;
            text-align: center;
            font-size: clamp(2rem, 4.2vw, 3.25rem);
            line-height: 1.15
        }

        .pricing-showcase .section-header:after {
            width: 90px;
            height: 4px;
            margin: 16px auto 0;
            background: #2897ee
        }

        .pricing-showcase .section-subtitle {
            margin: 0 0 27px;
            color: #476181;
            font-size: clamp(1rem, 2vw, 1.25rem);
            line-height: 1.5;
            text-align: center
        }

        .pricing-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: clamp(14px, 2.2vw, 24px)
        }

        .pricing-card {
            position: relative;
            min-width: 0;
            min-height: 280px;
            padding: 28px 18px 32px;
            overflow: hidden;
            text-align: center;
            background: #ffffff;
            border: 1px solid rgba(6, 27, 62, .12);
            border-radius: 22px;
            box-shadow: 0 9px 22px rgba(6, 27, 62, .08);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .pricing-card:after {
            content: '';
            position: absolute;
            z-index: 1;
            right: -12%;
            bottom: -50px;
            left: -12%;
            height: 90px;
            background: linear-gradient(135deg, rgba(243, 248, 254, 0.6), rgba(237, 244, 252, 0.6));
            border-radius: 48% 55% 0 0 / 34% 52% 0 0;
            transform: rotate(-3deg);
            pointer-events: none;
        }

        .pricing-card>* {
            position: relative;
            z-index: 3;
        }

        .pricing-card-icon,
        .pricing-info-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0966bd;
            background: linear-gradient(145deg, #f1f7fd, #e9f2fb);
            border-radius: 50%
        }

        .pricing-card-icon {
            width: 78px;
            height: 78px;
            margin: 0 auto 18px
        }

        .pricing-card-icon svg {
            width: 50px;
            height: 50px
        }

        .pricing-card-title {
            margin: 0 0 13px;
            color: #062c56;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.65rem, 3vw, 2.15rem);
            font-weight: 700;
            line-height: 1.1
        }

        .pricing-card-age {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            margin: 0;
            padding: 7px 16px;
            color: #064e9d;
            font-size: .94rem;
            line-height: 1.2;
            background: linear-gradient(110deg, #f1f7fd, #eaf3fb);
            border-radius: 999px
        }

        .pricing-card-divider {
            display: block;
            width: 58%;
            height: 2px;
            margin: 21px auto 18px;
            background: #dbe5ef;
            border-radius: 2px
        }

        .pricing-card-price {
            margin: auto 0 0;
            color: #0870cf;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.7rem, 3.1vw, 2.2rem);
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            position: relative;
            z-index: 5;
        }

        .pricing-options {
            margin-top: 25px
        }

        /* Dynamic Pricing Calculator & Tier Cards */
        .price-calculator-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(243, 107, 10, 0.25);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .counter-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid rgba(243, 107, 10, 0.4);
            background: #ffffff;
            color: #061B3E;
            font-size: 1.25rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .counter-btn:hover:not(:disabled) {
            background: #F36B0A;
            color: #ffffff;
            border-color: #F36B0A;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 107, 10, 0.3);
        }

        .counter-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .counter-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #061B3E;
            min-width: 44px;
            text-align: center;
        }

        .pax-tier-card {
            background: #ffffff;
            border: 2px solid rgba(6, 27, 62, 0.1);
            border-radius: 18px;
            padding: 18px 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .pax-tier-card:hover {
            border-color: #F36B0A;
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(243, 107, 10, 0.15);
        }

        .pax-tier-card.active {
            border-color: #F36B0A;
            background: linear-gradient(145deg, rgba(243, 107, 10, 0.1), rgba(243, 107, 10, 0.02));
            box-shadow: 0 8px 24px rgba(243, 107, 10, 0.22);
        }

        .pax-tier-card.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #F36B0A;
        }

        .pax-tier-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #061B3E;
            margin-bottom: 6px;
        }

        .pax-tier-price {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0870cf;
            margin-bottom: 4px;
            font-family: 'Playfair Display', serif;
        }

        .pax-tier-sub {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
        }

        .pricing-information {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-top: 34px;
            padding-top: 27px;
            border-top: 1px solid #dce4eb
        }

        .pricing-info-icon {
            width: 72px;
            height: 72px;
            min-width: 72px
        }

        .pricing-info-icon svg {
            width: 47px;
            height: 47px
        }

        .pricing-info-content {
            min-width: 0
        }

        .pricing-info-title {
            margin: 0 0 7px;
            color: #1689e6;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.35rem, 2.6vw, 1.75rem);
            font-weight: 700
        }

        .pricing-info-text,
        .pricing-info-text>*:last-child {
            margin-bottom: 0
        }

        .pricing-info-text {
            color: #293648;
            line-height: 1.72
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px
        }

        .gallery-item {
            height: 170px;
            border-radius: 18px;
            overflow: hidden;
            display: block;
            background: #eee;
            position: relative
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: .4s
        }

        .gallery-item:hover img {
            transform: scale(1.06)
        }

        .gallery-item::after {
            content: '\f00e';
            font-family: 'Line Awesome Free';
            font-weight: 900;
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
            background: rgba(6, 27, 62, .26);
            opacity: 0;
            transition: .3s
        }

        .gallery-item:hover::after {
            opacity: 1
        }

        .gallery-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(8, 14, 27, .88);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            z-index: 10050;
            opacity: 0;
            visibility: hidden;
            transition: .25s ease
        }

        .gallery-lightbox.open {
            opacity: 1;
            visibility: visible
        }

        .gallery-lightbox-dialog {
            position: relative;
            width: min(1100px, 100%);
            max-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .gallery-lightbox-img {
            max-width: 100%;
            max-height: 88vh;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .35);
            object-fit: contain;
            background: #fff
        }

        .gallery-lightbox-close,
        .gallery-lightbox-nav {
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .25s ease
        }

        .gallery-lightbox-close {
            position: absolute;
            top: -18px;
            right: -18px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #fff;
            color: var(--etp-navy-950, #061B3E);
            font-size: 2rem;
            line-height: 1;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .2)
        }

        .gallery-lightbox-close:hover,
        .gallery-lightbox-nav:hover {
            transform: scale(1.06)
        }

        .gallery-lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            font-size: 1.6rem;
            backdrop-filter: blur(6px)
        }

        .gallery-lightbox-nav.prev {
            left: 18px
        }

        .gallery-lightbox-nav.next {
            right: 18px
        }

        .gallery-lightbox-counter {
            position: absolute;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(12, 20, 36, .72);
            color: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: .9rem
        }

        .review-card {
            background: var(--etp-surface-muted, #f8fafc);
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 16px;
            border: 1px solid rgba(243, 107, 10, .15)
        }

        .rating-stars {
            color: #FFD700;
            margin-bottom: 8px
        }

        .verified-badge {
            display: inline-flex;
            background: #2358e6;
            color: #fff;
            font-size: .75rem;
            padding: 5px 10px;
            border-radius: 15px;
            margin-left: 8px
        }

        .sidebar {
            position: sticky;
            top: 100px;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(6, 27, 62, .14);
            border: 1px solid rgba(243, 107, 10, .18)
        }

        .sidebar-header {
            background: linear-gradient(135deg, var(--etp-navy-950, #061B3E), #1a4b66);
            color: #fff;
            padding: 25px;
            text-align: center
        }

        .sidebar-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            margin: 0 0 12px
        }

        .sidebar-price span.item {
            font-size: 2.1rem;
            font-weight: 900;
            color: var(--etp-orange-500, #F36B0A)
        }

        .sidebar-content {
            padding: 25px
        }

        .reserve-action-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #fff;
            border-bottom: 1px solid rgba(6, 27, 62, .1)
        }

        .package-show-template .reserve-tab-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 18px 10px;
            border: 0;
            border-bottom: 4px solid transparent;
            background: #fff;
            color: #68727d !important;
            font-weight: 800;
            cursor: pointer
        }

        .package-show-template .reserve-tab-btn.is-active {
            color: #061B3E !important;
            border-bottom-color: #d7a035 !important
        }

        .package-show-template .reserve-tab-btn i {
            color: inherit !important
        }

        .reserve-tab-panel[hidden] {
            display: none !important
        }

        .booking-request-title {
            color: var(--etp-navy-950, #061B3E);
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            margin-bottom: 6px
        }

        .booking-request-copy {
            color: var(--warm-gray, #6f7782);
            font-size: .82rem;
            margin-bottom: 18px
        }

        .sidebar-booking-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px
        }

        .sidebar-booking-grid .input-box {
            margin: 0
        }

        .sidebar-booking-grid .form-control {
            padding: 11px 12px
        }

        .sidebar-price-options {
            display: grid;
            gap: 10px;
            max-height: 390px;
            overflow-y: auto;
            padding: 2px 3px 4px
        }

        .sidebar-price-option {
            display: block;
            cursor: pointer;
            margin: 0
        }

        .sidebar-price-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none
        }

        .package-show-template .sidebar-price-option-card {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            padding: 13px;
            border: 2px solid #e7eaee;
            border-radius: 13px;
            background: #fff !important;
            transition: .2s ease
        }

        .package-show-template .sidebar-price-option input:checked+.sidebar-price-option-card {
            border-color: #F36B0A;
            background: #fffaf3 !important;
            box-shadow: 0 0 0 3px rgba(210, 154, 78, .1)
        }

        .day-tour-price-box {
            background: linear-gradient(135deg, #fdfbf7 0%, #f9f4ea 100%);
            border: 1.5px solid rgba(210, 154, 78, 0.35);
            border-radius: 12px;
            padding: 14px 16px;
            margin: 14px 0 10px;
        }

        .day-tour-price-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .day-tour-price-label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .day-tour-tier-badge {
            background: rgba(210, 154, 78, 0.15);
            color: #b0782b;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
        }

        .day-tour-price-total {
            font-size: 26px;
            font-weight: 800;
            color: #061B3E;
            line-height: 1.1;
        }

        .day-tour-price-breakdown {
            font-size: 12px;
            margin-top: 4px;
            color: #777;
        }

        .package-show-template .sidebar-option-name {
            color: #061B3E !important;
            font-weight: 800;
            font-size: .86rem
        }

        .package-show-template .sidebar-option-desc {
            display: block;
            margin-top: 3px;
            color: #66717e !important;
            font-size: .68rem;
            line-height: 1.35
        }

        .package-show-template .sidebar-option-price {
            color: #a96f2c !important;
            font-weight: 900;
            white-space: nowrap
        }

        .sidebar-checkout-btn {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 18px;
            padding: 14px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(90deg, #F36B0A, #FF8A33);
            color: #173763;
            font-weight: 900;
            cursor: pointer
        }

        .input-box {
            margin-bottom: 18px
        }

        .label-text {
            display: block;
            color: var(--etp-navy-950, #061B3E);
            font-weight: 700;
            margin-bottom: 8px
        }

        .form-group {
            position: relative
        }

        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--etp-orange-500, #F36B0A);
            z-index: 2
        }

        .form-control,
        .select-contain-select {
            width: 100%;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 14px 18px 14px 44px;
            background: #fff;
            transition: .3s
        }

        .select-contain-select {
            padding-left: 18px
        }

        .message-control {
            min-height: 100px;
            padding-top: 14px
        }

        .form-control:focus,
        .select-contain-select:focus {
            border-color: var(--etp-orange-500, #F36B0A);
            box-shadow: 0 0 0 .25rem rgba(243, 107, 10, .22);
            outline: 0
        }

        .quantity-control {
            background: var(--etp-surface-muted, #f8fafc);
            border-radius: 15px;
            padding: 14px;
            margin-bottom: 12px
        }

        .qty-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px
        }

        .qty-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 50%;
            background: var(--etp-orange-500, #F36B0A);
            color: var(--etp-navy-950, #061B3E);
            font-weight: 900
        }

        .qty-input {
            width: 55px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 7px
        }

        .day-tour-booking-form .day-tour-date-wrap {
            position: relative
        }

        .day-tour-booking-form .day-tour-date-display {
            cursor: pointer;
            padding-left: 44px
        }

        .day-tour-calendar {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            z-index: 30;
            width: min(310px, 100%);
            padding: 14px;
            border: 1px solid #e5bd72;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 14px 35px rgba(6, 27, 62, .18)
        }

        .day-tour-calendar[hidden] {
            display: none
        }

        .day-tour-calendar-header {
            display: grid;
            grid-template-columns: 34px 1fr 34px;
            align-items: center;
            margin-bottom: 10px
        }

        .day-tour-calendar-title {
            color: #34445a;
            font-size: .86rem;
            font-weight: 800;
            text-align: center
        }

        .day-tour-calendar-nav {
            border: 0;
            background: transparent;
            color: #061B3E;
            font-size: 1.25rem;
            cursor: pointer
        }

        .day-tour-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
            text-align: center
        }

        .day-tour-calendar-weekday {
            padding: 5px 0;
            color: #34445a;
            font-size: .72rem;
            font-weight: 800
        }

        .day-tour-calendar-day {
            width: 34px;
            height: 34px;
            margin: auto;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: #34445a;
            font-size: .78rem;
            cursor: pointer
        }

        .day-tour-calendar-day:hover,
        .day-tour-calendar-day.is-selected {
            background: #3b86bd;
            color: #fff
        }

        .day-tour-calendar-day:disabled,
        .day-tour-calendar-day.is-outside {
            color: #aaa;
            cursor: not-allowed;
            text-decoration: line-through;
            background: transparent
        }

        .day-tour-quantity-list {
            margin-top: 14px
        }

        .day-tour-quantity-list .quantity-control {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px
        }

        .day-tour-quantity-list .quantity-control label,
        .day-tour-quantity-list .qty-buttons {
            margin: 0
        }

        .trust-indicators {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 18px
        }

        .trust-item-small {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--etp-navy-950, #061B3E);
            font-weight: 700
        }

        .trust-item-small i {
            color: var(--etp-orange-500, #F36B0A)
        }

        #mobileBookingModal .sidebar-content {
            padding: 0;
        }

        #mobileBookingModal .day-tour-calendar {
            position: static;
            width: 100%;
            margin-top: 8px;
        }

        .fixed-mobile-btn {
            position: fixed;
            bottom: 18px;
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            z-index: 999;
            width: auto;
            max-width: 85%;
            display: flex;
            justify-content: center;
        }

        .fixed-mobile-btn.is-footer-visible {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translate(-50%, 14px);
        }

        .mobile-enquiry-btn {
            width: auto;
            min-width: 180px;
            max-width: 260px;
            background: var(--etp-orange-500, #F36B0A);
            color: var(--etp-navy-950, #061B3E);
            border-radius: 50px;
            padding: 12px 24px;
            text-decoration: none;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
            white-space: nowrap;
        }

        .alert-success {
            background: #e8f8ee;
            color: #146c2e;
            border-radius: 15px;
            padding: 14px 18px;
            margin-bottom: 20px
        }

        .alert-danger {
            background: #fff0f0;
            color: #b42318;
            border-radius: 15px;
            padding: 14px 18px;
            margin-bottom: 20px
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px
        }

        .related-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(243, 107, 10, .15);
            box-shadow: 0 8px 24px rgba(6, 27, 62, .08)
        }

        .related-card img {
            width: 100%;
            height: 150px;
            object-fit: cover
        }

        .related-card-body {
            padding: 16px
        }

        .related-card-title {
            font-family: 'Playfair Display', serif;
            color: var(--etp-navy-950, #061B3E);
            font-weight: 800
        }

        .empty-state {
            background: var(--etp-surface-muted, #f8fafc);
            padding: 22px;
            border-radius: 16px;
            color: #777;
            text-align: center
        }

        .modal-content {
            border-radius: 24px;
            overflow: hidden
        }

        .modal-header {
            background: linear-gradient(135deg, var(--etp-navy-950, #061B3E), #1a4b66);
            color: #fff
        }

        .btn-close {
            filter: invert(1)
        }

        @media(max-width:991px) {
            body.package-show-template #main-content {
                padding-bottom: 76px;
            }

            .package-hero {
                min-height: auto;
                padding: 105px 0 46px;
                background-attachment: scroll
            }

            .package-hero .hero-title {
                font-size: clamp(1.75rem, 6vw, 3rem)
            }

            .package-hero .hero-subtitle {
                max-width: 680px
            }

            .cruise-details,
            .related-grid {
                grid-template-columns: 1fr
            }

            .sidebar {
                position: static;
                margin-top: 25px
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .gallery-lightbox {
                padding: 18px
            }

            .gallery-lightbox-close {
                top: 10px;
                right: 10px;
                width: 42px;
                height: 42px;
                font-size: 1.8rem
            }

            .gallery-lightbox-nav.prev {
                left: 10px
            }

            .gallery-lightbox-nav.next {
                right: 10px
            }
        }

        @media(max-width:575px) {
            .package-hero {
                min-height: auto;
                padding: 92px 0 38px
            }

            .package-hero .hero-content {
                padding: 0 12px
            }

            .package-hero .hero-title {
                font-size: clamp(1.55rem, 7.5vw, 2.15rem);
                line-height: 1.15;
                margin-bottom: 12px
            }

            .package-hero .hero-subtitle {
                font-size: .88rem;
                line-height: 1.45;
                margin-bottom: 18px
            }

            .package-hero .hero-badges {
                gap: 6px;
                margin-bottom: 12px
            }

            .package-hero .hero-badge {
                padding: 5px 9px;
                font-size: .7rem
            }

            .package-hero .hero-actions {
                gap: 10px
            }

            .package-hero .hero-actions .gold-btn,
            .package-hero .hero-actions .outline-btn {
                min-height: 42px;
                padding: 10px 16px;
                font-size: .82rem
            }

            .content-section {
                padding: 23px
            }

            .gallery-grid {
                grid-template-columns: 1fr
            }

            .facilities-grid {
                grid-template-columns: 1fr
            }

            .gallery-item {
                height: 210px
            }

            .gallery-lightbox-nav {
                width: 44px;
                height: 44px;
                font-size: 1.35rem
            }

            .price-table {
                font-size: .9rem
            }

            .pricing-showcase {
                padding: 28px 18px
            }

            .pricing-cards {
                grid-template-columns: 1fr
            }

            .pricing-card {
                min-height: 290px
            }

            .pricing-information {
                align-items: flex-start;
                gap: 15px
            }

            .pricing-info-icon {
                width: 54px;
                height: 54px;
                min-width: 54px
            }

            .pricing-info-icon svg {
                width: 35px;
                height: 35px
            }
        }

        @media(min-width:576px) and (max-width:767px) {
            .pricing-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }
        }

        @media(max-width:420px) {
            .package-hero .hero-actions {
                flex-direction: column;
                align-items: center
            }

            .package-hero .hero-actions .gold-btn,
            .package-hero .hero-actions .outline-btn {
                width: min(100%, 240px)
            }
        }

        @media(max-height:650px) {
            .package-hero {
                min-height: auto;
                padding: 52px 0 24px
            }

            .package-hero .hero-title {
                font-size: clamp(1.55rem, 4vw, 2.5rem);
                margin-bottom: 10px
            }

            .package-hero .hero-subtitle {
                font-size: .84rem;
                line-height: 1.4;
                margin-bottom: 14px
            }

            .package-hero .hero-badges {
                margin-bottom: 10px
            }
        }

        /* Attractions Highlight Section & Cards */
        .attractions-highlight-section {
            background: #faf7f2;
            border: 1px solid rgba(243, 107, 10, 0.22);
            border-radius: 28px;
            padding: 36px 32px;
            box-shadow: 0 10px 30px rgba(6, 27, 62, 0.04);
        }

        .attractions-highlight-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.6rem, 3.5vw, 2.2rem);
            font-weight: 700;
            color: #061B3E;
            text-align: center;
            margin-bottom: 0;
        }

        .attractions-highlight-divider {
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #F36B0A 0%, #D95600 100%);
            border-radius: 4px;
            margin: 10px auto 28px auto;
        }

        .attractions-highlight-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        @media (max-width: 767.98px) {
            .attractions-highlight-list {
                grid-template-columns: 1fr;
            }
        }

        .attraction-highlight-card {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #ffffff;
            border: 1.5px solid rgba(6, 27, 62, 0.08);
            border-radius: 18px;
            padding: 14px 18px;
            text-decoration: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
            position: relative;
        }

        .attraction-highlight-card:hover,
        .attraction-highlight-card:focus {
            border-color: #F36B0A;
            box-shadow: 0 8px 25px rgba(243, 107, 10, 0.22);
            transform: translateY(-2px);
        }

        .attraction-highlight-img {
            width: 64px;
            height: 64px;
            min-width: 64px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .attraction-highlight-content {
            flex: 1;
            min-width: 0;
        }

        .attraction-highlight-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.12rem;
            font-weight: 700;
            color: #061B3E;
            margin-bottom: 4px;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attraction-highlight-card:hover .attraction-highlight-name {
            color: #F36B0A;
        }

        .attraction-highlight-sub {
            font-size: 0.88rem;
            font-style: italic;
            color: #718096;
            margin-bottom: 0;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attraction-highlight-arrow {
            color: #F36B0A;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
            margin-inline-start: auto;
        }

        .attraction-highlight-card:hover .attraction-highlight-arrow {
            transform: translateX(4px);
        }

        html[dir="rtl"] .attraction-highlight-card:hover .attraction-highlight-arrow,
        body.rtl .attraction-highlight-card:hover .attraction-highlight-arrow {
            transform: translateX(-4px);
        }

        html[data-theme='dark'] .attractions-highlight-section {
            background: #111827;
            border-color: rgba(243, 107, 10, 0.3);
        }

        html[data-theme='dark'] .attractions-highlight-title {
            color: #ffffff;
        }

        html[data-theme='dark'] .attraction-highlight-card {
            background: #1a233a;
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-theme='dark'] .attraction-highlight-name {
            color: #ffffff;
        }

        html[data-theme='dark'] .attraction-highlight-sub {
            color: #a0aec0;
        }

        /* Attraction Details Modal */
        .attraction-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(6, 27, 62, 0.75);
            backdrop-filter: blur(6px);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .attraction-modal-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .attraction-modal-card {
            background: #ffffff;
            border-radius: 24px;
            max-width: 540px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            transform: scale(0.92) translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .attraction-modal-overlay.open .attraction-modal-card {
            transform: scale(1) translateY(0);
        }

        .attraction-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            color: #ffffff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
        }

        html[dir="rtl"] .attraction-modal-close,
        body.rtl .attraction-modal-close {
            right: auto;
            left: 16px;
        }

        .attraction-modal-close:hover {
            background: #F36B0A;
            transform: rotate(90deg);
        }

        .attraction-modal-img-wrap {
            position: relative;
            width: 100%;
            height: 240px;
            overflow: hidden;
            background: #f1f5f9;
            flex-shrink: 0;
        }

        .attraction-modal-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .attraction-modal-body {
            padding: 22px 24px 24px;
            overflow-y: auto;
        }

        .attraction-modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #061B3E;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .attraction-modal-teaser {
            font-size: 0.95rem;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .attraction-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid rgba(6, 27, 62, 0.08);
        }

        .attraction-modal-btn-more {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #F36B0A 0%, #e05a00 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 12px 26px;
            border-radius: 12px;
            text-decoration: none !important;
            box-shadow: 0 4px 15px rgba(243, 107, 10, 0.35);
            transition: all 0.3s ease;
        }

        .attraction-modal-btn-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(243, 107, 10, 0.45);
            background: linear-gradient(135deg, #ff7819 0%, #F36B0A 100%);
        }

        @media (max-width: 767.98px) {
            .attraction-modal-overlay {
                padding: 14px;
            }

            .attraction-modal-card {
                border-radius: 20px;
                max-height: 88vh;
            }

            .attraction-modal-img-wrap {
                height: 200px;
            }

            .attraction-modal-body {
                padding: 18px 20px 20px;
            }

            .attraction-modal-title {
                font-size: 1.25rem;
            }

            .attraction-modal-teaser {
                font-size: 0.9rem;
                margin-bottom: 16px;
            }
        }

        @media (max-width: 575.98px) {
            .attraction-modal-overlay {
                padding: 10px;
            }

            .attraction-modal-card {
                border-radius: 16px;
                max-height: 85vh;
            }

            .attraction-modal-img-wrap {
                height: 170px;
            }

            .attraction-modal-body {
                padding: 14px 16px 16px;
            }

            .attraction-modal-title {
                font-size: 1.15rem;
                margin-bottom: 6px;
            }

            .attraction-modal-teaser {
                font-size: 0.85rem;
                line-height: 1.5;
                margin-bottom: 14px;
            }

            .attraction-modal-footer {
                padding-top: 12px;
            }

            .attraction-modal-btn-more {
                width: 100%;
                padding: 10px 16px;
                font-size: 0.88rem;
            }
        }

        html[data-theme='dark'] .attraction-modal-card {
            background: #1a233a;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        html[data-theme='dark'] .attraction-modal-title {
            color: #ffffff;
        }

        html[data-theme='dark'] .attraction-modal-teaser {
            color: #cbd5e0;
        }

        html[data-theme='dark'] .attraction-modal-footer {
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-theme='dark'] .pricing-showcase {
            background: #111827;
            border-color: rgba(243, 107, 10, 0.3);
        }

        html[data-theme='dark'] .pricing-showcase .section-header {
            color: #ffffff;
        }

        html[data-theme='dark'] .pricing-card {
            background: #1a233a;
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 9px 22px rgba(0, 0, 0, 0.3);
        }

        html[data-theme='dark'] .pricing-card:after {
            background: linear-gradient(135deg, #151d30, #101625);
        }

        html[data-theme='dark'] .pricing-card-icon {
            background: rgba(243, 107, 10, 0.18);
            color: #F36B0A;
        }

        html[data-theme='dark'] .pricing-card-title {
            color: #ffffff;
        }

        html[data-theme='dark'] .pricing-card-age {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.15);
        }

        html[data-theme='dark'] .pricing-card-price {
            color: #F36B0A;
        }

        html[data-theme='dark'] .pricing-info-title {
            color: #F36B0A;
        }

        html[data-theme='dark'] .pricing-info-text {
            color: #e2e8f0;
        }

        html[data-theme='dark'] .price-table {
            color: #e2e8f0;
        }

        html[data-theme='dark'] .price-table th {
            background: #151d30;
            color: #F36B0A;
            border-color: rgba(255, 255, 255, 0.1);
        }

        html[data-theme='dark'] .price-table td {
            border-color: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
        }

        .free-price-badge {
            display: inline-block;
            padding: 6px 22px;
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 800;
            font-family: inherit;
            background-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            line-height: 1.2;
            letter-spacing: 0.5px;
        }

        html[data-theme='dark'] .price-badge-item {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        html[data-theme='dark'] .price-calculator-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-color: rgba(243, 107, 10, 0.35);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        html[data-theme='dark'] .counter-btn {
            background: #1e293b;
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
        }

        html[data-theme='dark'] .counter-btn:hover:not(:disabled) {
            background: #F36B0A;
            color: #ffffff;
            border-color: #F36B0A;
        }

        html[data-theme='dark'] .counter-value {
            color: #ffffff;
        }

        html[data-theme='dark'] .pax-tier-card {
            background: #1a233a;
            border-color: rgba(255, 255, 255, 0.12);
        }

        html[data-theme='dark'] .pax-tier-card.active {
            background: linear-gradient(145deg, rgba(243, 107, 10, 0.22), rgba(15, 23, 42, 0.85));
            border-color: #F36B0A;
            box-shadow: 0 8px 24px rgba(243, 107, 10, 0.3);
        }

        html[data-theme='dark'] .pax-tier-title {
            color: #ffffff;
        }

        html[data-theme='dark'] .pax-tier-price {
            color: #F36B0A;
        }

        html[data-theme='dark'] .pax-tier-sub {
            color: #94a3b8;
        }

        html[data-theme='dark'] .price-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        html[data-theme='dark'] .facility-card,
        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card {
            background: #172033 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
        }

        html[data-theme='dark'] .facility-card:hover,
        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card:hover {
            background: rgba(243, 107, 10, 0.18) !important;
            border-color: rgba(243, 107, 10, 0.45) !important;
        }

        html[data-theme='dark'] .facility-card span,
        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card span {
            color: #ffffff !important;
        }

        html[data-theme='dark'] .facility-icon {
            color: #F36B0A !important;
        }

        html[data-theme='dark'] .facility-icon svg {
            stroke: #F36B0A !important;
        }

        html[data-theme='dark'] .cruise-facility-list li {
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Group Pricing Grid CSS */
        .group-pricing-subtitle {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .group-pricing-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 768px) {
            .group-pricing-grid {
                grid-template-columns: 1fr;
            }
        }

        .group-tier-card {
            position: relative;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 24px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .group-tier-card:hover {
            border-color: rgba(243, 107, 10, 0.4);
            box-shadow: 0 8px 20px rgba(243, 107, 10, 0.1);
        }

        .group-tier-badge {
            position: absolute;
            top: -14px;
            right: 20px;
            background: linear-gradient(135deg, #F36B0A 0%, #a87943 100%);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(243, 107, 10, 0.35);
            letter-spacing: 0.3px;
            z-index: 2;
        }

        [dir="rtl"] .group-tier-badge {
            right: auto;
            left: 20px;
        }

        .group-tier-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .group-tier-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #061B3E;
            margin: 0 0 6px 0;
            line-height: 1.2;
        }

        .group-tier-pax-tag {
            display: inline-block;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #F36B0A;
            background: rgba(243, 107, 10, 0.12);
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .group-tier-price-wrap {
            margin-top: 14px;
        }

        .group-tier-price {
            font-size: 2.2rem;
            font-weight: 800;
            color: #061B3E;
            line-height: 1;
            font-family: 'Playfair Display', serif;
        }

        .group-tier-sub {
            font-size: 0.88rem;
            color: #64748b;
            font-style: italic;
            margin-top: 6px;
        }

        html[data-theme='dark'] .group-tier-card {
            background: #1a233a;
            border-color: rgba(255, 255, 255, 0.12);
        }

        html[data-theme='dark'] .group-tier-title,
        html[data-theme='dark'] .group-tier-price {
            color: #ffffff;
        }

        html[data-theme='dark'] .group-tier-subtitle {
            color: #94a3b8;
        }


        /* =========================================================
                                                                   Nile Cruise body redesign — body only, shared header/footer untouched
                                                                   ========================================================= */
        .nile-cruise-page .main-container {
            background:
                radial-gradient(circle at 8% 8%, rgba(215, 239, 250, .58), transparent 34%),
                radial-gradient(circle at 92% 36%, rgba(245, 226, 194, .38), transparent 31%),
                #fffdf9;
            padding: 54px 0 64px;
        }

        .nile-cruise-page .content-wrapper>.row {
            --bs-gutter-x: 28px;
            align-items: flex-start;
        }

        .nile-cruise-page .content-section {
            border-radius: 18px;
            padding: 26px 28px;
            margin-bottom: 18px;
            border: 1px solid rgba(6, 27, 62, .09);
            box-shadow: 0 12px 34px rgba(6, 27, 62, .075);
            background: rgba(255, 255, 255, .96);
        }

        .nile-cruise-page .section-header {
            text-align: center;
            font-size: clamp(1.28rem, 2.3vw, 1.78rem);
            margin-bottom: 18px;
            line-height: 1.2;
        }

        .nile-cruise-page .section-header:after {
            width: 48px;
            height: 3px;
            margin: 9px auto 0;
        }

        .nile-cruise-page .section-subtitle {
            max-width: 720px;
            margin: -4px auto 18px;
            text-align: center;
            font-size: .92rem;
            line-height: 1.65;
        }

        .nile-cruise-page #about .about-content {
            text-align: center;
            color: #5d6878;
            font-size: .95rem;
            line-height: 1.8;
        }

        .nc-about-features {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid rgba(6, 27, 62, .08);
        }

        .nc-about-feature {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #061B3E;
            font-size: .82rem;
            font-weight: 700;
        }

        .nc-about-feature i {
            color: #F36B0A;
            font-size: 1.2rem;
        }

        .nile-cruise-page #nile-cruise-details .cruise-details {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 0;
        }

        .nile-cruise-page #nile-cruise-details .detail-item {
            min-height: 78px;
            padding: 14px 15px;
            background: #fff;
            border-color: rgba(6, 27, 62, .10);
            box-shadow: 0 4px 14px rgba(6, 27, 62, .035);
        }

        .nile-cruise-page #nile-cruise-details .detail-item i {
            background: rgba(243, 107, 10, .12);
            /* color: #F36B0A !important; */
            width: 34px;
            height: 34px;
            min-width: 34px;
        }

        .nile-cruise-page #nile-cruise-details .detail-text {
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .nile-cruise-page #nile-cruise-details .detail-label {
            font-size: .72rem;
            color: #7a8594;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .nile-cruise-page #nile-cruise-details .detail-value {
            color: #061B3E;
            font-size: .9rem;
            font-weight: 750;
        }

        .nc-subsection {
            margin-bottom: 25px;
        }

        .nc-subsection:last-child {
            margin-bottom: 0;
        }

        .nc-subsection-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 13px;
            padding-bottom: 9px;
            border-bottom: 1px solid rgba(6, 27, 62, .08);
            color: #061B3E;
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .nc-subsection-title .title-left {
            display: flex;
            align-items: center;
            gap: 9px
        }

        .nc-subsection-title i {
            color: #F36B0A
        }

        .nile-cruise-page .nc-schedule-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .nile-cruise-page .nc-schedule {
            padding: 14px 16px;
            border-radius: 13px;
            background: #fbfcfe;
            border-color: rgba(6, 27, 62, .09);
            box-shadow: none;
        }

        .nile-cruise-page .nc-cabin-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .nile-cruise-page .nc-cabin {
            padding: 0;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 7px 20px rgba(6, 27, 62, .06);
        }

        .nile-cruise-page .nc-cabin-body {
            padding: 16px;
        }

        .nile-cruise-page .nc-cabin img {
            height: 165px;
            margin: 0;
            border-radius: 0;
        }

        .nile-cruise-page .nc-pill {
            padding: 5px 9px;
            font-size: .72rem;
            background: #f8fafc;
        }

        .nc-route-line {
            display: grid;
            grid-template-columns: repeat(var(--route-count, 5), minmax(82px, 1fr));
            position: relative;
            gap: 0;
            padding: 12px 6px 0;
            overflow-x: auto;
        }

        .nc-route-line:before {
            content: '';
            position: absolute;
            left: 8%;
            right: 8%;
            top: 25px;
            height: 2px;
            background: linear-gradient(90deg, #061B3E, #3d76aa);
        }

        .nc-route-stop {
            position: relative;
            min-width: 82px;
            text-align: center;
            z-index: 1;
        }

        .nc-route-dot {
            width: 22px;
            height: 22px;
            margin: 2px auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #061B3E;
            color: #fff;
            border: 4px solid #fff;
            box-shadow: 0 0 0 1px rgba(6, 27, 62, .12);
            font-size: .62rem;
        }

        .nc-route-name {
            color: #061B3E;
            font-weight: 800;
            font-size: .82rem;
        }

        .nc-route-note {
            color: #7a8594;
            font-size: .68rem;
            line-height: 1.25;
            margin-top: 2px;
        }

        .nile-cruise-page .nc-duration-tabs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 6px;
            background: #f5f7fa;
            padding: 4px;
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .nile-cruise-page .nc-duration-tab {
            border: 0;
            border-radius: 999px;
            padding: 9px 12px;
            background: transparent;
            color: #43536a;
            font-size: .8rem;
        }

        .nile-cruise-page .nc-duration-tab.active,
        .nile-cruise-page .nc-duration-tab:hover {
            background: #0e3c68;
            color: #fff;
            box-shadow: 0 5px 15px rgba(14, 60, 104, .18);
        }

        .nile-cruise-page .nc-duration-summary {
            padding: 12px 14px;
            margin-bottom: 12px;
            border: 1px solid rgba(6, 27, 62, .08);
            border-radius: 12px;
            background: #fbfcfe;
        }

        .nile-cruise-page .nc-day {
            margin-bottom: 7px;
            border-radius: 10px;
            box-shadow: none;
        }

        .nile-cruise-page .nc-day summary {
            padding: 12px 14px;
            font-size: .88rem;
            background: #fff;
        }

        .nile-cruise-page .nc-day-body {
            padding: 4px 15px 15px;
            font-size: .88rem;
        }

        .nile-cruise-page #includes-excludes .nc-list-card {
            height: 100%;
            padding: 20px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid rgba(6, 27, 62, .09);
        }

        .nc-list-title {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #061B3E;
            font-family: 'Playfair Display', serif;
            font-size: 1.08rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .nc-list-title .ok {
            color: #F36B0A
        }

        .nc-list-title .no {
            color: #e29352
        }

        .nc-clean-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nc-clean-list li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #536072;
            font-size: .82rem;
            line-height: 1.45;
        }

        .nc-clean-list li i {
            color: #F36B0A;
            margin-top: 2px;
        }

        .nile-cruise-page #pricing-packages {
            padding: 28px;
        }

        .nc-price-duration {
            border: 1px solid rgba(6, 27, 62, .10);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 11px;
            background: #fff;
        }

        .nc-price-duration summary {
            list-style: none;
            cursor: pointer;
            display: grid;
            grid-template-columns: minmax(150px, 1.2fr) 1fr auto;
            align-items: center;
            gap: 14px;
            padding: 15px 17px;
            background: linear-gradient(100deg, #16295a, #18516e);
            color: #fff;
        }

        .nc-price-duration summary::-webkit-details-marker {
            display: none
        }

        .nc-price-duration-name {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            font-size: 1rem
        }

        .nc-price-seasons {
            font-size: .76rem;
            opacity: .82;
            text-align: center
        }

        .nc-price-from {
            color: #e5ad45;
            font-weight: 800;
            white-space: nowrap
        }

        .nc-price-body {
            padding: 0;
            overflow-x: auto
        }

        .nc-price-matrix {
            width: 100%;
            min-width: 620px;
            border-collapse: collapse
        }

        .nc-price-matrix th {
            background: #d59c05;
            color: #142653;
            padding: 11px 10px;
            text-align: center;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            border-right: 1px solid rgba(255, 255, 255, .16)
        }

        .nc-price-matrix td {
            padding: 11px 10px;
            border-bottom: 1px solid #edf0f3;
            text-align: center;
            font-size: .79rem;
            color: #34455f
        }

        .nc-price-matrix td:first-child {
            font-weight: 800;
            color: #061B3E;
            text-transform: uppercase
        }

        .nc-price-matrix .price-value {
            color: #c58d39;
            font-family: 'Playfair Display', serif;
            font-size: .92rem;
            font-weight: 800
        }

        .nile-cruise-page #cruise-facilities .facilities-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .nile-cruise-page #cruise-facilities .facility-card {
            min-height: 48px;
            padding: 11px 12px;
            border-radius: 11px;
            background: #fbfcfe;
            font-size: .78rem;
            box-shadow: none;
        }

        .nc-policy-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .nc-policy-card {
            padding: 14px;
            border: 1px solid rgba(6, 27, 62, .09);
            border-radius: 12px;
            background: #fbfcfe;
        }

        .nc-policy-card h4 {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 6px;
            color: #061B3E;
            font-size: .84rem;
            font-weight: 800;
        }

        .nc-policy-card h4 i {
            color: #F36B0A
        }

        .nc-policy-card .policy-copy {
            font-size: .75rem;
            color: #667386;
            line-height: 1.5;
            margin: 0
        }

        .nile-cruise-page .faq-accordion {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .nile-cruise-page .faq-accordion .day-card {
            margin: 0 !important;
            border-radius: 12px;
        }

        .nile-cruise-page .faq-accordion .day-header {
            padding: 14px 18px;
            background: #fff;
            gap: 12px;
        }

        .nile-cruise-page .faq-accordion .day-number {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            font-size: 1.15rem !important;
        }

        .nile-cruise-page .faq-accordion .day-title {
            font-size: 1.05rem !important;
        }

        .nile-cruise-page .related-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .nile-cruise-page .related-card {
            border-radius: 13px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(6, 27, 62, .06);
        }

        .nile-cruise-page .related-card img {
            height: 120px;
        }

        .nile-cruise-page .related-card-body {
            padding: 12px;
        }

        .nile-cruise-page .related-card-title {
            font-size: .82rem;
            line-height: 1.35;
            min-height: 2.2em;
        }

        .nile-cruise-page .related-card .gold-btn {
            padding: 7px 13px;
            font-size: .72rem;
        }

        .nile-cruise-page .sidebar {
            position: sticky;
            top: 96px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(6, 27, 62, .13);
        }

        .nile-cruise-page .sidebar-header {
            padding: 19px 20px;
            background: linear-gradient(135deg, #163f70, #0f5272);
        }

        .nile-cruise-page .sidebar-title {
            font-size: 1.12rem;
            margin-bottom: 4px;
        }

        .nile-cruise-page .nc-sidebar-subtitle {
            margin: 0 0 7px;
            color: rgba(255, 255, 255, .78);
            font-size: .72rem;
            line-height: 1.45;
        }

        .nile-cruise-page .sidebar-content {
            padding: 18px;
        }

        .nile-cruise-page .sidebar-price {
            margin-top: 5px;
        }

        html[data-theme='dark'] .nile-cruise-page .main-container {
            background: #101827;
        }

        html[data-theme='dark'] .nile-cruise-page .content-section,
        html[data-theme='dark'] .nile-cruise-page .nc-list-card,
        html[data-theme='dark'] .nile-cruise-page .nc-policy-card {
            background: #1a233a;
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, .1);
        }

        html[data-theme='dark'] .nile-cruise-page #nile-cruise-details .detail-item {
            background: #172033 !important;
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, .12) !important;
        }

        html[data-theme='dark'] .nile-cruise-page #nile-cruise-details .detail-label {
            color: #ffffff !important;
        }

        html[data-theme='dark'] .nile-cruise-page #nile-cruise-details .detail-value {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card {
            background: #172033 !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, .12) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .25) !important;
        }

        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card span {
            color: #ffffff !important;
        }

        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card .facility-icon {
            color: #F36B0A !important;
        }

        html[data-theme='dark'] .nile-cruise-page #cruise-facilities .facility-card .facility-icon svg {
            stroke: #F36B0A !important;
        }

        html[data-theme='dark'] .nile-cruise-page .nc-day summary,
        html[data-theme='dark'] .nile-cruise-page .faq-accordion .day-header {
            background: #172033 !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, .1) !important;
        }

        html[data-theme='dark'] .nile-cruise-page .nc-day-body {
            color: #e2e8f0 !important;
        }

        html[data-theme='dark'] .nile-cruise-page .nc-pill {
            background: rgba(255, 255, 255, .08) !important;
            color: #e2e8f0 !important;
        }

        html[data-theme='dark'] .nile-cruise-page .nc-policy-card h4 {
            color: #ffffff !important;
        }

        html[data-theme='dark'] .nile-cruise-page .nc-policy-card .policy-copy {
            color: #cbd5e1 !important;
        }

        html[data-theme='dark'] .nile-cruise-page .nc-price-duration {
            background: #1a233a;
            border-color: rgba(255, 255, 255, .1);
        }

        html[data-theme='dark'] .nile-cruise-page .nc-price-matrix td {
            color: #d7e0ec;
            border-color: rgba(255, 255, 255, .08);
        }

        @media(max-width:991.98px) {
            .nc-about-features {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .nile-cruise-page #nile-cruise-details .cruise-details {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .nile-cruise-page #cruise-facilities .facilities-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .nc-policy-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }
        }

        @media(max-width:767.98px) {
            .nile-cruise-page .main-container {
                padding: 34px 0 80px
            }

            .nile-cruise-page .content-section {
                padding: 21px 17px;
                border-radius: 15px;
                margin-bottom: 13px
            }

            .nile-cruise-page #nile-cruise-details .cruise-details {
                grid-template-columns: 1fr
            }

            .nile-cruise-page .nc-cabin-grid,
            .nile-cruise-page .nc-schedule-grid {
                grid-template-columns: 1fr
            }

            .nile-cruise-page .faq-accordion {
                grid-template-columns: 1fr
            }

            .nile-cruise-page .related-grid {
                grid-template-columns: 1fr
            }

            .nc-policy-grid {
                grid-template-columns: 1fr
            }

            .nc-price-duration summary {
                grid-template-columns: 1fr auto;
                gap: 6px
            }

            .nc-price-seasons {
                text-align: left;
                grid-column: 1/2
            }

            .nc-price-from {
                grid-row: 1/3;
                grid-column: 2/3
            }
        }

        @media(max-width:520px) {
            .nc-about-features {
                grid-template-columns: 1fr 1fr
            }

            .nc-about-feature {
                font-size: .72rem
            }

            .nile-cruise-page #cruise-facilities .facilities-grid {
                grid-template-columns: 1fr
            }

            .nile-cruise-page .nc-duration-tabs {
                grid-template-columns: 1fr;
                border-radius: 15px
            }

            .nile-cruise-page .nc-duration-tab {
                border-radius: 11px
            }
        }

        @media (max-width: 576px) {
            .attractions-highlight-section {
                padding: 20px 12px;
                border-radius: 18px;
            }

            .attractions-highlight-list {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .attraction-highlight-card {
                padding: 10px 12px;
                gap: 12px;
                border-radius: 14px;
                width: 100%;
                box-sizing: border-box;
            }

            .attraction-highlight-img {
                width: 50px;
                height: 50px;
                min-width: 50px;
                border-radius: 10px;
            }

            .attraction-highlight-name {
                font-size: 0.95rem;
            }

            .attraction-highlight-sub {
                font-size: 0.8rem;
            }

            .attraction-highlight-arrow {
                font-size: 1.1rem;
            }
        }

        .room-capacity-notice {
            margin: 12px 0;
            padding: 10px 14px;
            border: 1px solid rgba(243, 107, 10, 0.35);
            border-radius: 8px;
            background: rgba(243, 107, 10, 0.08);
            color: #061B3E;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.45;
        }

        .room-group-container {
            background: #fdfbf7;
            border: 1px solid rgba(6, 27, 62, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 12px;
        }

        .room-header {
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px dashed rgba(6, 27, 62, 0.12);
        }

        .room-title {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 700;
            color: #061B3E;
        }

        .room-inline-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .room-inline-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .age-info {
            font-size: 11px;
            color: #8c7355;
            font-weight: 400;
        }

        .select-inline {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #dce2e8;
            border-radius: 8px;
            font-size: 14px;
            background: #fff;
            color: #061B3E;
        }

        .room-price-badge {
            background: rgba(243, 107, 10, 0.15);
            color: #a87940;
            font-weight: 700;
            font-size: 13px;
            padding: 3px 9px;
            border-radius: 6px;
            letter-spacing: 0.2px;
        }

        .tp-price-summary-box {
            background: #fff;
            border: 1px solid rgba(243, 107, 10, 0.35);
            border-radius: 14px;
            padding: 16px;
            margin-top: 14px;
            margin-bottom: 14px;
            box-shadow: 0 4px 15px rgba(6, 27, 62, 0.05);
        }

        .tp-price-summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed rgba(6, 27, 62, 0.14);
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-weight: 700;
            color: #061B3E;
            font-size: 14.5px;
        }

        .tp-price-total {
            font-size: 19px;
            color: #F36B0A;
            font-family: 'Playfair Display', serif;
            font-weight: 800;
        }

        .tp-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .tp-price-row strong {
            color: #061B3E;
        }

        .text-gold {
            color: #F36B0A !important;
            font-weight: 700;
        }
    </style>
@endsection

@section('schema')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $title,
            'description' => trim(preg_replace('/\s+/', ' ', strip_tags($shortDescription ?: $title))),
            'image' => $heroImage,
            'url' => $canonicalUrl,
            'provider' => [
                '@type' => 'TravelAgency',
                'name' => 'Egypt Tour Pro',
                'url' => url('/'),
            ],
            'touristType' => $tourTypeText ?? __('Private'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @if ($faqs->count())
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq, $index) => [
                    '@type' => 'Question',
                    'name' => $faq['question'] ?: __('Question') . ' ' . ($index + 1),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($faq['answer']),
                    ],
                ])->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
@endsection

@section('content')
    @php
        $currencySymbol = $package->currency?->symbol ?? '$';
        $priceFrom = (float) ($package->price_from ?? ($package->start_from_price ?? 0));
        $priceTo = (float) ($package->price_to ?? 0);
        $comparePrice = (float) ($package->compare_price ?? 0);
        $offerPrice = (float) ($package->offer_price ?? 0);
        $priceText = null;
        $hasCategoryPricing =
            ($package->adult_price !== null && (float) $package->adult_price > 0) ||
            $package->child_price !== null ||
            $package->infant_price !== null;
        $pricingInformation = $package->getTranslation('pricing_information');

        if ($priceFrom > 0 || $priceTo > 0) {
            $effectivePrice = $priceFrom > 0 ? $priceFrom : $priceTo;
            $priceText = __('trips.from_price', [
                'currency' => $currencySymbol,
                'amount' => number_format($effectivePrice, 2),
            ]);
        }
        if (!$priceText && ($hasBookablePrice ?? false) && ($firstBookableOption = $bookingPricingOptions->first())) {
            $priceText = __('trips.from_price', [
                'currency' => $firstBookableOption['currency_symbol'],
                'amount' => number_format($firstBookableOption['amount'], 2),
            ]);
        }
    @endphp

    <section class="breadcrumb-top-bar">
        <div class="container">
            <div class="breadcrumb-list">
                <ul>
                    <li><a href="{{ route('website.home') }}">{{ __('Home') }}</a></li>
                    <li><a href="{{ $listingUrl }}">{{ $listingLabel }}</a></li>
                    <li>{{ $breadcrumbTitle }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="package-hero" style="--hero-bg:url('{{ $heroImage }}')">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badges" aria-label="{{ __('Trip badges') }}">
                    <span class="hero-badge"><i class="la la-compass"></i> {{ $packageTypeText }}</span>
                    @if ($package->package_type === 'nile_cruise')
                        @if ($package->category?->display_name)
                            <span class="hero-badge"><i class="la la-anchor"></i>
                                {{ $package->category->display_name }}</span>
                        @endif
                        @if ($package->cruise?->cruise_class)
                            <span class="hero-badge"><i class="la la-crown"></i> {{ $package->cruise->cruise_class }}</span>
                        @endif
                    @endif
                    @if ($package->is_best_seller)
                        <span class="hero-badge"><i class="la la-fire"></i> {{ __('Best Seller') }}</span>
                    @endif
                    @if ($package->is_ultra_luxury)
                        <span class="hero-badge"><i class="la la-gem"></i> {{ __('Ultra Luxury') }}</span>
                    @endif
                    @if ($package->is_featured)
                        <span class="hero-badge"><i class="la la-star"></i> {{ __('Featured') }}</span>
                    @endif
                </div>
                <h1 class="hero-title">{{ $title }}</h1>
                @if ($subtitle || $shortDescription)
                    <p class="hero-subtitle">{{ $subtitle ?: $shortDescription }}</p>
                @endif
                <div class="hero-actions">
                    @if (!empty($gallery))
                        <a class="outline-btn js-gallery-trigger" href="{{ $gallery[0] }}" data-gallery-index="0">
                            <i class="la la-image"></i> {{ __('View Gallery') }}
                        </a>
                    @endif
                    @if ($hasBookablePrice)
                        <a href="{{ route('website.checkout.show', $package->slug) }}" class="gold-btn"
                            data-mobile-booking>
                            <i class="la la-calendar-check"></i> {{ __('Book Now') }}
                        </a>
                    @endif
                    <a href="#reserve" class="gold-btn d-none d-lg-inline-flex"><i class="la la-envelope"></i>
                        {{ $hasBookablePrice ? __('Enquire Now') : __('Submit Enquiry') }}</a>
                    <a href="#" class="gold-btn d-inline-flex d-lg-none" data-bs-toggle="modal"
                        data-bs-target="#simpleEnquiryModal">
                        <i class="la la-envelope"></i> {{ $hasBookablePrice ? __('Enquire Now') : __('Submit Enquiry') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="main-container">
        <div class="container content-wrapper">
            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    @php
                        $isExtendedNileCruise =
                            $package->package_type === 'nile_cruise' &&
                            $package->nileCruiseDurations->where('is_active', true)->isNotEmpty();
                        $hasNileCruisePrices =
                            $isExtendedNileCruise &&
                            $package->nileCruiseDurations
                                ->where('is_active', true)
                                ->contains(
                                    fn($duration) => $duration->seasonPrices
                                        ->where('is_active', true)
                                        ->contains(
                                            fn($season) => $season->items->contains(
                                                fn($item) => (float) $item->price > 0,
                                            ),
                                        ),
                                );
                    @endphp
                    <section id="about" class="content-section">
                        <h2 class="section-header">{{ __('About') }} {{ $title }}</h2>
                        @if ($shortDescription)
                            <p class="section-subtitle">{{ $shortDescription }}</p>
                        @endif

                        <div class="about-content">
                            @if ($description)
                                {!! $description !!}
                            @else
                                <p class="empty-state">{{ __('No description added for this package yet.') }}</p>
                            @endif

                            @if ($package->package_type === 'nile_cruise')
                                @php
                                    $aboutNcDetail = $package->nileCruiseDetail;
                                    $aboutNcCruise = $package->cruise;
                                    $aboutNcLanguages = collect(
                                        (array) ($aboutNcDetail?->on_tour_languages ?? []),
                                    )->filter();
                                @endphp
                                <div class="nc-about-features">
                                    @if ($aboutNcCruise?->cruise_class)
                                        <div class="nc-about-feature"><i
                                                class="la la-ship"></i><span>{{ $aboutNcCruise->cruise_class }}</span>
                                        </div>
                                    @endif
                                    @if ($aboutNcDetail?->tour_style)
                                        <div class="nc-about-feature"><i
                                                class="la la-user-friends"></i><span>{{ $aboutNcDetail->tour_style }}</span>
                                        </div>
                                    @endif
                                    @if ($aboutNcDetail?->all_inclusive)
                                        <div class="nc-about-feature"><i
                                                class="la la-utensils"></i><span>{{ __('All Meals Included') }}</span>
                                        </div>
                                    @endif
                                    @if ($aboutNcLanguages->isNotEmpty())
                                        <div class="nc-about-feature"><i
                                                class="la la-language"></i><span>{{ $aboutNcLanguages->take(3)->implode(' · ') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if (!in_array($package->package_type, ['day_tour', 'travel_package', 'nile_cruise'], true))
                                <div class="cruise-details">
                                    @if ($durationText)
                                        <div class="detail-item"><i class="la la-calendar"></i>
                                            <div class="detail-text">
                                                <strong class="detail-label">{{ __('Duration:') }}</strong>
                                                <span class="detail-value">{{ $durationText }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($schedule)
                                        <div class="detail-item"><i class="la la-clock"></i>
                                            <div class="detail-text">
                                                <strong class="detail-label">{{ __('Schedule:') }}</strong>
                                                <span class="detail-value">{{ $schedule }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- @if ($packageTypeText)
                                    <div class="detail-item"><i class="la la-suitcase"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Package Type:') }}</strong>
                                            <span class="detail-value">{{ $packageTypeText }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    {{-- @if ($countryText)
                                    <div class="detail-item"><i class="la la-globe"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Country:') }}</strong>
                                            <span class="detail-value">{{ $countryText }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    @if ($destinations)
                                        <div class="detail-item"><i class="la la-map-marker"></i>
                                            <div class="detail-text">
                                                <strong class="detail-label">{{ __('Destinations:') }}</strong>
                                                <span class="detail-value">{{ $destinations }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($routeText)
                                        <div class="detail-item"><i class="la la-route"></i>
                                            <div class="detail-text">
                                                <strong class="detail-label">{{ __('Route:') }}</strong>
                                                <span class="detail-value">{{ $routeText }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- @if ($locationSummary)
                                    <div class="detail-item"><i class="la la-map"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Location:') }}</strong>
                                            <span class="detail-value">{{ $locationSummary }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    @if ($pickup)
                                        <div class="detail-item"><i class="la la-map-pin"></i>
                                            <div class="detail-text">
                                                <strong class="detail-label">{{ __('Pickup Location:') }}</strong>
                                                <span class="detail-value">{{ $pickup }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- @if ($dropoff)
                                    <div class="detail-item"><i class="la la-location-arrow"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Dropoff Location:') }}</strong>
                                            <span class="detail-value">{{ $dropoff }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    @if ($tourTypeText)
                                        <div class="detail-item"><i class="la la-users"></i>
                                            <div class="detail-text">
                                                <strong class="detail-label">{{ __('Tour Type:') }}</strong>
                                                <span class="detail-value">{{ $tourTypeText }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- @if ($package->category)
                                    <div class="detail-item"><i class="la la-tag"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Category:') }}</strong>
                                            <span class="detail-value">{{ $package->category->display_name }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    {{-- @if ($package->difficulty_level)
                                    <div class="detail-item"><i class="la la-hiking"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Difficulty:') }}</strong>
                                            <span class="detail-value">{{ __(ucfirst($package->difficulty_level)) }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    {{-- @if ($package->min_participants || $package->max_participants)
                                    <div class="detail-item"><i class="la la-user-friends"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Group Size:') }}</strong>
                                            <span class="detail-value">
                                                @if ($package->min_participants && $package->max_participants)
                                                    {{ $package->min_participants }} - {{ $package->max_participants }}
                                                    {{ __('Pax') }}
                                                @elseif($package->max_participants)
                                                    {{ __('Up to') }} {{ $package->max_participants }}
                                                    {{ __('Pax') }}
                                                @else
                                                    {{ __('Min') }} {{ $package->min_participants }}
                                                    {{ __('Pax') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endif --}}
                                    {{-- @if ($package->booking_lead_days)
                                    <div class="detail-item"><i class="la la-hourglass-half"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Booking Window:') }}</strong>
                                            <span
                                                class="detail-value">{{ __('Min. :days days before', ['days' => $package->booking_lead_days]) }}</span>
                                        </div>
                                    </div>
                                @endif
                                @if ($bookingModeText)
                                    <div class="detail-item"><i class="la la-calendar-check"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Booking Mode:') }}</strong>
                                            <span class="detail-value">{{ $bookingModeText }}</span>
                                        </div>
                                    </div>
                                @endif --}}
                                    {{-- @if ((float) $package->rating_avg > 0 || (int) $package->reviews_count > 0)
                                    <div class="detail-item"><i class="la la-star"></i>
                                        <div class="detail-text">
                                            <strong class="detail-label">{{ __('Rating:') }}</strong>
                                            <span class="detail-value">
                                                {{ number_format((float) $package->rating_avg, 1) }}/5
                                                @if ((int) $package->reviews_count > 0)
                                                    ({{ trans_choice(':count review|:count reviews', (int) $package->reviews_count, ['count' => (int) $package->reviews_count]) }})
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endif --}}
                                </div>
                            @endif
                        </div>
                    </section>

                    @include('website.pages.packages.partials.day_trip_details')
                    @include('website.pages.packages.partials.tour_package_details')
                    @include('website.pages.packages.partials.nile_cruise_details')

                    @if ($highlights->count())
                        <section class="content-section">
                            <h2 class="section-header">{{ __('Tour Highlights') }}</h2>
                            <div class="styled-list">
                                <ul
                                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                                    @foreach ($highlights as $highlight)
                                        <li style="border: none; padding: 5px 0;">
                                            <i class="la la-check-circle"
                                                style="color:var(--etp-orange-500, #F36B0A); margin-right:8px; font-size: 1.2rem; vertical-align: middle;"></i>
                                            @if ($highlight->display_title)
                                                <strong>{{ $highlight->display_title }}</strong>
                                            @endif
                                            @if ($highlight->display_description)
                                                <span class="price-meta">{{ $highlight->display_description }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif

                    @php
                        $isNileCruisePackage = $package->package_type === 'nile_cruise';
                        $nileDetailForFacilities = $package->nileCruiseDetail;
                        $nileCabinTotal =
                            $package->nileCruiseCabins?->sum(fn($cabin) => (int) ($cabin->quantity ?? 0)) ?? 0;
                        $nileFacilityStats = collect([
                            $nileDetailForFacilities?->decks
                                ? ['label' => $nileDetailForFacilities->decks . ' ' . __('Decks'), 'icon' => 'deck']
                                : null,
                            $nileCabinTotal > 0
                                ? ['label' => $nileCabinTotal . ' ' . __('Cabins / Suites'), 'icon' => 'cabin']
                                : null,
                            $nileDetailForFacilities?->sun_beds
                                ? [
                                    'label' => $nileDetailForFacilities->sun_beds . ' ' . __('Sun Beds'),
                                    'icon' => 'sun',
                                ]
                                : null,
                            $nileDetailForFacilities?->sun_deck_pergolas
                                ? [
                                    'label' =>
                                        $nileDetailForFacilities->sun_deck_pergolas .
                                        ' ' .
                                        __('Sun Deck Private Pergolas'),
                                    'icon' => 'sun',
                                ]
                                : null,
                        ])
                            ->filter()
                            ->values();
                        $nileFacilityIcon = function (string $title): string {
                            $normalized = strtolower(trim($title));
                            return match (true) {
                                str_contains($normalized, 'wifi') || str_contains($normalized, 'internet') => 'wifi',
                                str_contains($normalized, 'pool') || str_contains($normalized, 'swim') => 'pool',
                                str_contains($normalized, 'air') || str_contains($normalized, 'ac') => 'snowflake',
                                str_contains($normalized, 'bath') || str_contains($normalized, 'shower') => 'bath',
                                str_contains($normalized, 'tv') ||
                                    str_contains($normalized, 'screen') ||
                                    str_contains($normalized, 'satellite')
                                    => 'tv',
                                str_contains($normalized, 'bar') ||
                                    str_contains($normalized, 'lounge') ||
                                    str_contains($normalized, 'drink') ||
                                    str_contains($normalized, 'dining') ||
                                    str_contains($normalized, 'restaurant')
                                    => 'glass',
                                str_contains($normalized, 'doctor') || str_contains($normalized, 'medical')
                                    => 'medical',
                                str_contains($normalized, 'gift') || str_contains($normalized, 'shop') => 'gift',
                                str_contains($normalized, 'gym') || str_contains($normalized, 'fitness') => 'gym',
                                str_contains($normalized, 'sun') ||
                                    str_contains($normalized, 'deck') ||
                                    str_contains($normalized, 'bed') ||
                                    str_contains($normalized, 'pergola')
                                    => 'sun',
                                default => 'check',
                            };
                        };
                        $hasDynamicNileFacilities =
                            $isNileCruisePackage && ($facilities->isNotEmpty() || $nileFacilityStats->isNotEmpty());
                    @endphp

                    @if (!$isNileCruisePackage && $facilities->count())
                        <section class="content-section">
                            <h2 class="section-header">
                                {{ __('Trip Facilities') }}
                            </h2>
                            <div class="facilities-grid">
                                @foreach ($facilities as $facility)
                                    @php $facilityIconName = $nileFacilityIcon($facility->display_title); @endphp
                                    <div class="facility-card">
                                        <span class="facility-icon" aria-hidden="true">
                                            @switch($facilityIconName)
                                                @case('wifi')
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M5 13a10 10 0 0 1 14 0"></path>
                                                        <path d="M8.5 16.5a5 5 0 0 1 7 0"></path>
                                                        <path d="M12 20h.01"></path>
                                                    </svg>
                                                @break

                                                @case('pool')
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M4 18c2 0 2-1 4-1s2 1 4 1 2-1 4-1 2 1 4 1"></path>
                                                        <path d="M4 21c2 0 2-1 4-1s2 1 4 1 2-1 4-1 2 1 4 1"></path>
                                                        <path d="M8 17V5a3 3 0 0 1 6 0"></path>
                                                        <path d="M8 9h8"></path>
                                                    </svg>
                                                @break

                                                @case('snowflake')
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M12 2v20"></path>
                                                        <path d="m17 5-5 5-5-5"></path>
                                                        <path d="m17 19-5-5-5 5"></path>
                                                        <path d="M2 12h20"></path>
                                                    </svg>
                                                @break

                                                @case('bath')
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M4 12h16v4a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-4Z"></path>
                                                        <path d="M7 12V6a3 3 0 0 1 5.1-2.1"></path>
                                                    </svg>
                                                @break

                                                @case('tv')
                                                    <svg viewBox="0 0 24 24">
                                                        <rect x="3" y="5" width="18" height="12" rx="2"></rect>
                                                        <path d="M8 21h8"></path>
                                                        <path d="M12 17v4"></path>
                                                    </svg>
                                                @break

                                                @case('glass')
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M8 3h8l-1 8a3 3 0 0 1-6 0L8 3Z"></path>
                                                        <path d="M12 14v7"></path>
                                                        <path d="M9 21h6"></path>
                                                    </svg>
                                                @break

                                                @case('medical')
                                                    <svg viewBox="0 0 24 24">
                                                        <rect x="3" y="7" width="18" height="13" rx="2"></rect>
                                                        <path d="M12 10v7M8.5 13.5h7"></path>
                                                    </svg>
                                                @break

                                                @case('gift')
                                                    <svg viewBox="0 0 24 24">
                                                        <rect x="3" y="8" width="18" height="13" rx="2"></rect>
                                                        <path d="M12 8v13M3 12h18"></path>
                                                    </svg>
                                                @break

                                                @case('gym')
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M6 9v6M18 9v6M3 10v4M21 10v4M6 12h12"></path>
                                                    </svg>
                                                @break

                                                @case('sun')
                                                    <svg viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="4"></circle>
                                                        <path d="M12 2v2M12 20v2M2 12h2M20 12h2"></path>
                                                    </svg>
                                                @break

                                                @default
                                                    <svg viewBox="0 0 24 24">
                                                        <path d="M20 6 9 17l-5-5"></path>
                                                    </svg>
                                            @endswitch
                                        </span>
                                        <span>{{ __($facility->display_title) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($package->packageAttractions && $package->packageAttractions->count())
                        <section class="content-section attractions-highlight-section">
                            <h2 class="attractions-highlight-title">{{ __('Places You\'ll Visit') }}</h2>
                            <div class="attractions-highlight-divider"></div>
                            <div class="attractions-highlight-list">
                                @foreach ($package->packageAttractions as $attraction)
                                    @php
                                        $attractionModel = $attraction->attraction;
                                        $attractionTitle =
                                            $attraction->display_title ?: $attractionModel?->display_name;
                                        $attractionTeaser =
                                            $attraction->getTranslation('teaser') ?:
                                            $attractionModel?->display_short_description;

                                        $citySlug =
                                            $attractionModel?->city?->slug ?:
                                            $package->destination?->city?->slug ?:
                                            $package->destination?->slug;

                                        if ($attractionModel && $attractionModel->slug) {
                                            $attractionUrl = route('website.attractions.show', $attractionModel->slug);
                                            $target = '_self';
                                        } elseif ($attractionModel?->map_url) {
                                            $attractionUrl = $attractionModel->map_url;
                                            $target = '_blank';
                                        } elseif ($citySlug) {
                                            $attractionUrl = route('website.destinations.show', $citySlug);
                                            $target = '_self';
                                        } else {
                                            $attractionUrl = route('website.destinations.index');
                                            $target = '_self';
                                        }

                                        $rawImg = $attraction->image ?: $attractionModel?->image;
                                        if ($rawImg) {
                                            $rawImg = ltrim($rawImg, '/');
                                            if (Str::startsWith($rawImg, ['http://', 'https://'])) {
                                                $imgSrc = $rawImg;
                                            } elseif (Str::startsWith($rawImg, 'storage/')) {
                                                $imgSrc = '/' . $rawImg;
                                            } else {
                                                $imgSrc = '/storage/' . $rawImg;
                                            }
                                        } else {
                                            $imgSrc = '/website/photos/home2.webp';
                                        }
                                    @endphp
                                    <button type="button" class="attraction-highlight-card js-attraction-modal-trigger"
                                        data-attraction-title="{{ $attractionTitle }}"
                                        data-attraction-teaser="{{ strip_tags((string) $attractionTeaser) }}"
                                        data-attraction-img="{{ $imgSrc }}"
                                        data-attraction-url="{{ $attractionUrl }}"
                                        data-attraction-target="{{ $target }}">
                                        <img src="{{ $imgSrc }}" alt="{{ $attractionTitle }}"
                                            class="attraction-highlight-img" loading="lazy">
                                        <div class="attraction-highlight-content">
                                            <h3 class="attraction-highlight-name">{{ $attractionTitle }}</h3>
                                            <p class="attraction-highlight-sub">{{ __('Click to explore') }}</p>
                                        </div>
                                        <div class="attraction-highlight-arrow" aria-hidden="true">
                                            <i
                                                class="la {{ app()->getLocale() === 'ar' ? 'la-angle-left' : 'la-angle-right' }}"></i>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if (!$isExtendedNileCruise && $itineraries->count())
                        @php
                            $isDayTourActive = !empty($isDayTour);
                            $sectionHeadingText = $isDayTourActive ? __('Activity Timeline') : __('Itinerary');
                            $stepUnitText = $isDayTourActive ? __('Stop') : $itineraryUnit ?? __('Day');
                        @endphp
                        <section id="itinerary" class="content-section">
                            <h2 class="section-header">{{ $title }} {{ $sectionHeadingText }}</h2>
                            <div class="itinerary-section">
                                @foreach ($itineraries as $day)
                                    <div class="day-card">
                                        <button type="button" class="day-header"
                                            data-collapse-target="day-{{ $day->id }}"
                                            aria-controls="day-{{ $day->id }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                            <div class="day-number" style="color: white !important;">
                                                {{ $day->day_number }}</div>
                                            <div>
                                                <h3 class="day-title">
                                                    @if ($isDayTourActive)
                                                        {{ $day->display_title ?: __('Activity') . ' ' . $day->day_number }}
                                                    @else
                                                        {{ $stepUnitText }} {{ $day->day_number }}@if ($day->display_title)
                                                            : {{ $day->display_title }}
                                                        @endif
                                                    @endif
                                                </h3>
                                                @if ($day->duration && !$isDayTourActive)
                                                    <small>
                                                        <i class="la la-clock"></i>
                                                        {{ $day->duration }}
                                                    </small>
                                                @endif
                                            </div>
                                            <i class="la la-chevron-down collapse-icon" style="margin-left:auto"></i>
                                        </button>
                                        <div class="collapsible-content {{ $loop->first ? 'open active' : '' }}"
                                            id="day-{{ $day->id }}">
                                            <div class="day-content">
                                                @if ($day->display_description)
                                                    {!! $day->display_description !!}
                                                @endif

                                                @if (!empty($day->display_activities_list))
                                                    <div class="mt-3 p-3 rounded"
                                                        style="background: rgba(243, 107, 10, 0.05); border: 1px solid rgba(243, 107, 10, 0.2);">
                                                        <span class="d-block mb-2"
                                                            style="font-size: 13px; font-weight: 700; color: #F36B0A;">{{ __('Activities') }}</span>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach ($day->display_activities_list as $actName)
                                                                <span class="badge"
                                                                    style="background: #F36B0A; color: #ffffff; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 20px;">
                                                                    {{ $actName }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($day->display_overnight && !$isDayTourActive)
                                                    <p class="mt-2"><strong>{{ __('Overnight:') }}</strong>
                                                        {{ $day->display_overnight }}</p>
                                                @endif
                                                @if ($package->package_type === 'travel_package' && $day->display_accommodation)
                                                    <p><strong>{{ __('Accommodation:') }}</strong>
                                                        {{ $day->display_accommodation }}</p>
                                                @endif
                                                @if ($package->package_type === 'travel_package' && $day->display_transport_notes)
                                                    <p><strong>{{ __('Transport:') }}</strong>
                                                        {{ $day->display_transport_notes }}</p>
                                                @endif
                                                @php
                                                    $dayMeals = [];
                                                    if (
                                                        !empty($day->meals) &&
                                                        (is_array($day->meals) ||
                                                            $day->meals instanceof \Illuminate\Support\Collection)
                                                    ) {
                                                        $dayMeals = is_array($day->meals)
                                                            ? $day->meals
                                                            : $day->meals->toArray();
                                                    }
                                                    if (
                                                        !empty($day->meals_breakfast) &&
                                                        !in_array('breakfast', $dayMeals)
                                                    ) {
                                                        $dayMeals[] = 'breakfast';
                                                    }
                                                    if (!empty($day->meals_lunch) && !in_array('lunch', $dayMeals)) {
                                                        $dayMeals[] = 'lunch';
                                                    }
                                                    if (!empty($day->meals_dinner) && !in_array('dinner', $dayMeals)) {
                                                        $dayMeals[] = 'dinner';
                                                    }
                                                @endphp
                                                @if (!empty($dayMeals))
                                                    <div class="meals-included-card mt-3 p-3 rounded-3"
                                                        style="background-color: #f8f6f0; border-left: 4px solid #F36B0A;">
                                                        <div class="fw-bold mb-2"
                                                            style="color: #1e293b; font-size: 0.9rem;">
                                                            {{ __('Meals Included') }}</div>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach ($dayMeals as $m)
                                                                @php
                                                                    $mLower = strtolower((string) $m);
                                                                    if (
                                                                        in_array($mLower, [
                                                                            'breakfast',
                                                                            'إفطار',
                                                                            'افطار',
                                                                        ])
                                                                    ) {
                                                                        $mealText = __('Breakfast');
                                                                    } elseif (in_array($mLower, ['lunch', 'غداء'])) {
                                                                        $mealText = __('Lunch');
                                                                    } elseif (in_array($mLower, ['dinner', 'عشاء'])) {
                                                                        $mealText = __('Dinner');
                                                                    } else {
                                                                        $mealText = __(ucfirst($mLower));
                                                                    }
                                                                @endphp
                                                                <span class="badge px-3 py-2 rounded-pill fw-medium"
                                                                    style="background-color: #F36B0A; color: #ffffff; font-size: 0.85rem; border: none;">
                                                                    {{ $mealText }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif



                    @if (!$isNileCruisePackage && ($included->count() || $excluded->count()))
                        <section class="content-section">
                            <h2 class="section-header">{{ __('What\'s Included') }}</h2>
                            <div class="row g-4">
                                @if ($included->count())
                                    <div class="{{ $excluded->count() ? 'col-md-6' : 'col-12' }}">
                                        <div class="included-box">
                                            <h4 class="box-title">{{ __('Included in Your Journey') }}</h4>
                                            <div class="styled-list">
                                                <ul>
                                                    @foreach ($included as $item)
                                                        <li>{{ $item->display_content }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($excluded->count())
                                    <div class="{{ $included->count() ? 'col-md-6' : 'col-12' }}">
                                        <div class="excluded-box">
                                            <h4 class="box-title">{{ __('Not Included') }}</h4>
                                            <div class="styled-list">
                                                <ul>
                                                    @foreach ($excluded as $item)
                                                        <li>{{ $item->display_content }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endif

                    @include('website.pages.packages.partials.common_experience_details')
                    @php
                        $groupTiersForDisplay = collect(
                            $package->package_type === 'nile_cruise'
                                ? []
                                : (array) ($package->group_pricing_tiers ?? []),
                        )->filter(fn($tier) => is_array($tier) && (float) ($tier['price_per_person'] ?? 0) > 0);
                        $hasAccommodations =
                            $package->tourPackageAccommodations && $package->tourPackageAccommodations->isNotEmpty();
                        $package->package_type !== 'day_tour' &&
                            $package->tourPackageAccommodations &&
                            $package->tourPackageAccommodations->isNotEmpty();
                        $hasAnyStandardPricing =
                            $prices->count() ||
                            $hasCategoryPricing ||
                            $pricingInformation ||
                            $priceFrom > 0 ||
                            $priceTo > 0 ||
                            $groupTiersForDisplay->isNotEmpty() ||
                            $hasAccommodations;
                    @endphp
                    @if (!$isNileCruisePackage && !$hasNileCruisePrices && $hasAnyStandardPricing)
                        <section class="content-section pricing-showcase" id="pricing-section">
                            <h2 class="section-header">{{ __('Pricing & Packages') }}</h2>
                            <p class="group-pricing-subtitle">
                                {{ __('Choose the pricing option that suits your trip. Prices use :currency.', ['currency' => $package->currency?->code ?: 'USD']) }}
                            </p>

                            @if ($groupTiersForDisplay->isNotEmpty())
                                <div class="group-pricing-grid">
                                    @foreach ($groupTiersForDisplay as $tier)
                                        @php
                                            $tierMin = $tier['min'] ?? null;
                                            $tierMax = $tier['max'] ?? null;
                                            $tierLabel = trim((string) ($tier['label'] ?? ($tier['title'] ?? '')));
                                            $personsLabel =
                                                $tierMin && $tierMax
                                                    ? ($tierMin == $tierMax
                                                        ? $tierMin . ' ' . __('Pax')
                                                        : $tierMin . '–' . $tierMax . ' ' . __('Pax'))
                                                    : ($tierMin
                                                        ? $tierMin . '+ ' . __('Pax')
                                                        : ($tierMax
                                                            ? __('Up to') . ' ' . $tierMax . ' ' . __('Pax')
                                                            : __('Group')));
                                        @endphp
                                        <div class="group-tier-card">
                                            <div class="group-tier-header">
                                                <div>
                                                    <h3 class="group-tier-title">{{ __($tierLabel) ?: __('Group Price') }}
                                                    </h3>
                                                    <span class="group-tier-pax-tag">{{ $personsLabel }}</span>
                                                </div>
                                            </div>
                                            <div class="group-tier-price-wrap">
                                                <div class="group-tier-price">
                                                    {{ $currencySymbol }}{{ number_format((float) ($tier['price_per_person'] ?? 0), 0) }}
                                                </div>
                                                <div class="group-tier-sub">{{ __('per person') }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($hasAccommodations)
                                <div class="tour-accommodations-showcase mt-4">
                                    <h3 class="fw-bold mb-3"
                                        style="color: var(--etp-navy-950, #061B3E); font-family: 'Playfair Display', serif;">
                                        {{ __('Accommodation Tiers & Season Pricing') }}</h3>
                                    <div class="accordion" id="accPricingAccordion">
                                        @foreach ($package->tourPackageAccommodations as $accIndex => $acc)
                                            <div class="accordion-item mb-3 border rounded shadow-sm"
                                                style="border-color: rgba(6, 27, 62, 0.12) !important;">
                                                <h2 class="accordion-header" id="accHeading{{ $acc->id }}">
                                                    <button
                                                        class="accordion-button {{ $accIndex > 0 ? 'collapsed' : '' }} fw-bold"
                                                        type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#accCollapse{{ $acc->id }}"
                                                        style="color: var(--etp-navy-950, #061B3E);">
                                                        <i class="la la-building me-2"
                                                            style="color: var(--etp-orange-500, #F36B0A);"></i>
                                                        {{ $acc->name }}
                                                        @if ($acc->description)
                                                            <small
                                                                class="text-muted ms-2">({{ $acc->description }})</small>
                                                        @endif
                                                    </button>
                                                </h2>
                                                <div id="accCollapse{{ $acc->id }}"
                                                    class="accordion-collapse collapse {{ $accIndex === 0 ? 'show' : '' }}"
                                                    data-bs-parent="#accPricingAccordion">
                                                    <div class="accordion-body">
                                                        @if ($acc->hotels->isNotEmpty())
                                                            <div class="mb-4 p-3 rounded"
                                                                style="background: var(--etp-surface-muted, #f8fafc); border: 1px solid rgba(6, 27, 62, 0.08);">
                                                                <h5 class="fw-bold mb-2"
                                                                    style="color: var(--etp-navy-950, #061B3E);"><i
                                                                        class="la la-hotel"
                                                                        style="color: var(--etp-orange-500, #F36B0A);"></i>
                                                                    {{ __('Assigned Hotels') }}</h5>
                                                                <div class="row g-2">
                                                                    @foreach ($acc->hotels as $hotel)
                                                                        <div class="col-md-6 col-lg-4">
                                                                            <div class="p-2 border rounded bg-white">
                                                                                <span class="badge mb-1"
                                                                                    style="background: var(--etp-navy-950, #061B3E); color: #fff;">{{ $hotel->city_name ?: __('Hotel') }}</span>
                                                                                <strong
                                                                                    class="d-block text-dark">{{ $hotel->hotel_name }}</strong>
                                                                                @if ($hotel->star_rating)
                                                                                    <div class="text-warning small">
                                                                                        {{ str_repeat('★', $hotel->star_rating) }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($acc->seasons->isNotEmpty())
                                                            <div class="price-table-wrap">
                                                                <table class="price-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{{ __('Season / Period') }}</th>
                                                                            <th>{{ __('Occupancy / Room Type') }}</th>
                                                                            <th>{{ __('Price per Person') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($acc->seasons as $season)
                                                                            @foreach ($season->items as $item)
                                                                                <tr>
                                                                                    @if ($loop->first)
                                                                                        <td rowspan="{{ $season->items->count() }}"
                                                                                            class="fw-bold"
                                                                                            style="background: var(--etp-surface-muted, #f8fafc);">
                                                                                            {{ $season->display_season_name }}
                                                                                            @if ($season->date_from || $season->date_to)
                                                                                                <div
                                                                                                    class="small text-muted fw-normal">
                                                                                                    {{ $season->date_from?->format('M d') }}
                                                                                                    -
                                                                                                    {{ $season->date_to?->format('M d') }}
                                                                                                </div>
                                                                                            @endif
                                                                                        </td>
                                                                                    @endif
                                                                                    <td>{{ $item->display_label }}</td>
                                                                                    <td><strong
                                                                                            style="color: var(--etp-orange-500, #F36B0A); font-size: 1.1rem;">{{ $currencySymbol }}{{ number_format((float) $item->price, 0) }}</strong>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($prices->count())
                                <div class="price-box pricing-options">
                                    <div class="price-table-wrap">
                                        <table class="price-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Option') }}</th>
                                                    <th>{{ __('Pax / Group') }}</th>
                                                    <th>{{ __('Price Details') }}</th>
                                                    <th>{{ __('Validity') }}</th>
                                                    <th>{{ __('Price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($prices as $price)
                                                    <tr>
                                                        <td>
                                                            {{ $price->display_label }}
                                                            @if ($price->display_season_name)
                                                                <span class="price-meta"><i class="la la-sun"></i>
                                                                    {{ $price->display_season_name }}</span>
                                                            @endif
                                                            @if ($price->display_notes)
                                                                <span
                                                                    class="price-meta">{{ $price->display_notes }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($price->pax_min && $price->pax_max && $price->pax_min === $price->pax_max)
                                                                {{ $price->pax_min }} {{ __('Pax') }}
                                                            @elseif ($price->pax_min && $price->pax_max)
                                                                {{ $price->pax_min }} - {{ $price->pax_max }}
                                                                {{ __('Pax') }}
                                                            @elseif ($price->pax_min)
                                                                {{ $price->pax_min }}+ {{ __('Pax') }}
                                                            @elseif ($price->pax_max)
                                                                1 - {{ $price->pax_max }} {{ __('Pax') }}
                                                            @elseif ($price->group_size_min || $price->group_size_max)
                                                                @if ($price->group_size_min && $price->group_size_max && $price->group_size_min === $price->group_size_max)
                                                                    {{ $price->group_size_min }} {{ __('Pax') }}
                                                                @elseif ($price->group_size_min && $price->group_size_max)
                                                                    {{ $price->group_size_min }} -
                                                                    {{ $price->group_size_max }} {{ __('Pax') }}
                                                                @elseif ($price->group_size_min)
                                                                    {{ $price->group_size_min }}+ {{ __('Pax') }}
                                                                @elseif ($price->group_size_max)
                                                                    1 - {{ $price->group_size_max }} {{ __('Pax') }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $price->display_price_type }}
                                                            @if ($price->display_room_type)
                                                                <span class="price-meta">{{ __('Room:') }}
                                                                    {{ $price->display_room_type }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($price->display_valid_from || $price->display_valid_to)
                                                                @if ($price->display_valid_from)
                                                                    <span class="price-meta">{{ __('From:') }}
                                                                        {{ $price->display_valid_from }}</span>
                                                                @endif
                                                                @if ($price->display_valid_to)
                                                                    <span class="price-meta">{{ __('To:') }}
                                                                        {{ $price->display_valid_to }}</span>
                                                                @endif
                                                            @else
                                                                {{ __('All Year') }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $price->formatted_amount }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if ($pricingInformation)
                                <div class="pricing-information">
                                    <div class="pricing-info-icon" aria-hidden="true">
                                        <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3.2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M32 6c7 5 14 7.5 23 8.5V30c0 13.5-8.4 22.6-23 29-14.6-6.4-23-15.5-23-29V14.5C18 13.5 25 11 32 6Z">
                                            </path>
                                            <path d="m23.5 31.5 6 6 12-13"></path>
                                        </svg>
                                    </div>
                                    <div class="pricing-info-content">
                                        <h4 class="pricing-info-title">{{ __('Pricing Information') }}</h4>
                                        <div class="pricing-info-text">{!! $pricingInformation !!}</div>
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- @if (count($gallery) > 1)
                        <section class="content-section">
                            <h2 class="section-header">{{ __('Gallery') }}</h2>
                            <div class="gallery-grid">
                                @foreach ($gallery as $img)
                                    <a class="gallery-item js-gallery-trigger" href="{{ $img }}"
                                        data-gallery-index="{{ $loop->index }}">
                                        <img src="{{ $img }}" alt="{{ $title }}" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif --}}

                    @php
                        $cancellationPolicy = $package->getTranslation('cancellation_policy');
                        $termsConditions = $package->getTranslation('terms_conditions');
                        $childrenPolicy = $package->getTranslation('children_policy');
                        $pickupPolicy = $package->getTranslation('pickup_policy');
                    @endphp
                    @if (($cancellationPolicy || $termsConditions || $childrenPolicy || $pickupPolicy) && !$isNileCruisePackage)
                        <section class="content-section">
                            <h2 class="section-header">{{ __('Important Information') }}</h2>

                            @if ($childrenPolicy)
                                <div class="mb-4">
                                    <h4 class="mb-3"
                                        style="color: var(--etp-navy-950, #061B3E); font-family: 'Playfair Display', serif;">
                                        <i class="la la-child" style="color: var(--etp-orange-500, #F36B0A);"></i>
                                        {{ __('Children Policy') }}
                                    </h4>
                                    <div class="about-content">{!! $childrenPolicy !!}</div>
                                </div>
                            @endif

                            @if ($pickupPolicy)
                                <div class="mb-4">
                                    <h4 class="mb-3"
                                        style="color: var(--etp-navy-950, #061B3E); font-family: 'Playfair Display', serif;">
                                        <i class="la la-shuttle-van" style="color: var(--etp-orange-500, #F36B0A);"></i>
                                        {{ __('Pickup & Drop-off Policy') }}
                                    </h4>
                                    <div class="about-content">{!! $pickupPolicy !!}</div>
                                </div>
                            @endif

                            @if ($cancellationPolicy)
                                <div class="mb-4">
                                    <h4 class="mb-3"
                                        style="color: var(--etp-navy-950, #061B3E); font-family: 'Playfair Display', serif;">
                                        <i class="la la-info-circle" style="color: var(--etp-orange-500, #F36B0A);"></i>
                                        {{ __('Cancellation Policy') }}
                                    </h4>
                                    <div class="about-content">
                                        {!! $cancellationPolicy !!}
                                    </div>
                                </div>
                            @endif

                            @if ($termsConditions)
                                <div>
                                    <h4 class="mb-3"
                                        style="color: var(--etp-navy-950, #061B3E); font-family: 'Playfair Display', serif;">
                                        <i class="la la-file-alt" style="color: var(--etp-orange-500, #F36B0A);"></i>
                                        {{ __('Terms & Conditions') }}
                                    </h4>
                                    <div class="about-content">
                                        {!! $termsConditions !!}
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif

                    @if ($faqs->count())
                        <section class="content-section">
                            <h2 class="section-header">{{ __('Frequently Asked Questions') }}</h2>
                            <div class="faq-accordion">
                                @foreach ($faqs as $index => $faq)
                                    <div class="day-card mb-3">
                                        <button type="button" class="day-header"
                                            data-collapse-target="faq-{{ $package->id }}-{{ $index }}"
                                            aria-controls="faq-{{ $package->id }}-{{ $index }}"
                                            aria-expanded="false">
                                            <div class="day-number"
                                                style="width: 36px; height: 36px; min-width: 36px; font-size: 1.2rem;"><i
                                                    class="las la-question-circle" style="color: #fff !important;"
                                                    aria-hidden="true"></i></div>
                                            <div>
                                                <h3 class="day-title"
                                                    style="font-size: 1.05rem; font-family: inherit; font-weight: 700;">
                                                    {{ $faq['question'] ?: __('Question') . ' ' . ($index + 1) }}
                                                </h3>
                                            </div>
                                            <i class="la la-chevron-down collapse-icon" style="margin-left:auto"></i>
                                        </button>
                                        <div class="collapsible-content"
                                            id="faq-{{ $package->id }}-{{ $index }}">
                                            <div class="day-content" style="padding: 15px 22px;">
                                                <div class="mb-0 about-content">{!! nl2br(e($faq['answer'] ?: __('Answer will be added soon.'))) !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($reviews->count() || $testimonials->count())
                        <section id="reviews" class="content-section">
                            <h2 class="section-header">{{ __('Guest Reviews') }}</h2>
                            @if ($reviews->count())
                                @foreach ($reviews as $review)
                                    <div class="review-card">
                                        <div class="rating-stars">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="la {{ $i <= round($review->rating) ? 'la-star' : 'la-star-o' }}"></i>
                                            @endfor
                                        </div>
                                        @if ($review->title)
                                            <h5>{{ $review->title }}</h5>
                                        @endif
                                        <p>{{ $review->content }}</p>
                                    </div>
                                @endforeach
                            @else
                                @foreach ($testimonials as $testimonial)
                                    <div class="review-card">
                                        <div class="rating-stars">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="la {{ $i <= (int) $testimonial->rating ? 'la-star' : 'la-star-o' }}"></i>
                                            @endfor
                                            @if ($testimonial->is_verified)
                                                <span class="verified-badge">{{ __('Verified') }}</span>
                                            @endif
                                        </div>
                                        <p>"{{ $testimonial->content }}"</p>
                                        <strong>{{ $testimonial->customer_name }}</strong>
                                        @if ($testimonial->source)
                                            <small> - {{ $testimonial->source }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </section>
                    @endif

                    @if ($relatedPackages->count())
                        <section class="content-section">
                            <h2 class="section-header">{{ __('Related Tours') }}</h2>
                            <div class="related-grid">
                                @foreach ($relatedPackages as $related)
                                    <div class="related-card">
                                        <img src="{{ $related['image'] }}" alt="{{ $related['title'] }}"
                                            loading="lazy">
                                        <div class="related-card-body">
                                            <div class="related-card-title">{{ $related['title'] }}</div>
                                            <a class="gold-btn mt-3"
                                                href="{{ $related['url'] }}">{{ $related['button_text'] }}</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <div class="col-lg-4 d-none d-lg-block">
                    <div class="sidebar" id="reserve">
                        <div class="sidebar-header">
                            <h3 class="sidebar-title">
                                {{ $hasBookablePrice ? __('Ask About This Trip') : __('Submit Enquiry') }}</h3>
                            @if ($package->package_type === 'nile_cruise')
                                <p class="nc-sidebar-subtitle">
                                    {{ __('Fill out the form and our travel team will get back to you shortly.') }}</p>
                            @endif
                            @if ($priceText)
                                <div class="sidebar-price"><span class="item">{{ $priceText }}</span></div>
                            @else
                                <div class="sidebar-price"><span class="item">{{ __('Ask for Price') }}</span></div>
                            @endif
                            @if ($comparePrice > max($priceFrom, $priceTo, 0))
                                <span class="compare-price">
                                    {{ __('Was') }} {{ $currencySymbol }}{{ number_format($comparePrice, 2) }}
                                </span>
                            @endif
                        </div>
                        @if ($hasBookablePrice)
                            <div class="reserve-action-tabs" role="tablist" aria-label="{{ __('Booking actions') }}">
                                <button type="button" class="reserve-tab-btn is-active" role="tab"
                                    aria-selected="true" aria-controls="reserveBookingPanel"
                                    data-reserve-tab="booking"><i
                                        class="la la-calendar-check"></i>{{ __('Book Now') }}</button>
                                <button type="button" class="reserve-tab-btn" role="tab" aria-selected="false"
                                    aria-controls="reserveEnquiryPanel" data-reserve-tab="enquiry"><i
                                        class="la la-envelope"></i>{{ __('Enquiry Form') }}</button>
                            </div>
                            <div class="sidebar-content reserve-tab-panel" id="reserveBookingPanel" role="tabpanel">
                                @if ($package->package_type === 'travel_package')
                                    <form method="get" action="{{ route('website.checkout.show', $package->slug) }}"
                                        id="sidebarTravelPackageForm">
                                        <input type="hidden" name="pricing_option" value="travel_package">
                                        <input type="hidden" name="totalAdults" id="tp_totalAdults" value="2">
                                        <input type="hidden" name="totalChildren" id="tp_totalChildren" value="0">
                                        <input type="hidden" name="adults" id="tp_form_adults" value="2">
                                        <input type="hidden" name="children" id="tp_form_children" value="0">
                                        <input type="hidden" name="infants" value="0">

                                        <!-- Date Field -->
                                        <div class="input-box mb-3">
                                            <label class="label-text" for="tp_travel_date"
                                                style="font-weight: 600; color: #061B3E; font-size: 13px; margin-bottom: 6px; display: block;">{{ __('Date') }}
                                                *</label>
                                            <div class="form-group position-relative">
                                                <span class="la la-calendar form-icon"
                                                    style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #F36B0A; z-index: 2;"></span>
                                                <input id="tp_travel_date" name="travel_date" class="form-control"
                                                    type="date" value="{{ today()->toDateString() }}"
                                                    min="{{ today()->toDateString() }}" required
                                                    style="padding-left: 42px; border-radius: 10px; height: 46px; border: 1px solid #dce2e8; font-size: 14px;">
                                            </div>
                                        </div>

                                        <!-- Rooms Field -->
                                        <div class="input-box mb-3">
                                            <label class="label-text" for="tp_rooms"
                                                style="font-weight: 600; color: #061B3E; font-size: 13px; margin-bottom: 6px; display: block;">{{ __('Rooms') }}
                                                *</label>
                                            <div class="form-group">
                                                <div class="select-contain w-auto">
                                                    <select id="tp_rooms" name="rooms"
                                                        class="form-select select-contain-select" required
                                                        style="border-radius: 10px; height: 46px; border: 1px solid #dce2e8; font-size: 14px;">
                                                        <option value="" disabled>{{ __('Select Rooms') }}</option>
                                                        @for ($r = 1; $r <= 10; $r++)
                                                            <option value="{{ $r }}"
                                                                {{ $r === 1 ? 'selected' : '' }}>{{ $r }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div id="tp_roomError"
                                                    style="color:red; display:none; font-size: 13px; margin-top: 4px;">
                                                    {{ __('Please select the number of rooms.') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div id="tp_capacityNotice" class="room-capacity-notice mb-2"
                                            style="display:none;"></div>

                                        <!-- Dynamic Room Cards with Accommodations Type, Adults, Children -->
                                        <div id="tp_roomFields" class="tp-room-fields mb-2"></div>

                                        <!-- Live Estimated Price Summary Box -->
                                        <div id="tp_priceSummaryBox" class="tp-price-summary-box mb-3"
                                            style="display: none;">
                                            <div class="tp-price-summary-header">
                                                <span>{{ __('Estimated Price') }}</span>
                                                <span class="tp-price-total" id="tp_displayTotal">$0.00</span>
                                            </div>
                                            <div class="tp-price-summary-details">
                                                <div class="tp-price-row">
                                                    <span>{{ __('Pay Today (50% Deposit)') }}:</span>
                                                    <strong id="tp_displayDeposit" class="text-gold">$0.00</strong>
                                                </div>
                                                <div class="tp-price-row">
                                                    <span>{{ __('Remaining Balance') }}:</span>
                                                    <span id="tp_displayBalance">$0.00</span>
                                                </div>
                                                <small class="text-muted d-block mt-2"
                                                    style="font-size: 11.5px; line-height: 1.35;">
                                                    💡 {{ __('Remaining balance due 30 days prior to departure.') }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="btn-box mt-3">
                                            <button type="submit" id="tp_bookButton" class="submit-btn w-100"
                                                style="background: linear-gradient(135deg, #F36B0A 0%, #a87940 100%); color: #fff; font-weight: 700; border-radius: 10px; padding: 14px; font-size: 16px; border: none; box-shadow: 0 4px 15px rgba(243, 107, 10, 0.35); cursor: pointer; transition: all 0.2s;">
                                                <i class="la la-calendar-check me-1"></i>
                                                {{ __('Book Now') }}
                                            </button>
                                        </div>
                                    </form>
                                @elseif ($package->package_type === 'day_tour')
                                    <h4 class="booking-request-title">{{ __('Select Your Booking') }}</h4>
                                    <form method="get" action="{{ route('website.checkout.show', $package->slug) }}"
                                        id="sidebarBookingForm" class="day-tour-booking-form"
                                        data-operating-days='@json($operatingDays->values())'
                                        data-min-date="{{ today()->toDateString() }}">
                                        <div class="input-box">
                                            <label class="label-text" for="day_tour_date_display">{{ __('Date') }}
                                                *</label>
                                            <div class="form-group day-tour-date-wrap">
                                                <span class="la la-calendar form-icon"></span>
                                                <input id="day_tour_date_display"
                                                    class="form-control day-tour-date-display" type="text"
                                                    autocomplete="off" placeholder="{{ __('Travel Date') }}" readonly
                                                    required aria-haspopup="dialog" aria-expanded="false">
                                                <input id="sidebar_travel_date" type="hidden" name="travel_date">
                                                <div class="day-tour-calendar" id="dayTourCalendar" role="dialog"
                                                    aria-label="{{ __('Choose an available travel date') }}" hidden>
                                                    <div class="day-tour-calendar-header">
                                                        <button type="button" class="day-tour-calendar-nav"
                                                            data-calendar-prev
                                                            aria-label="{{ __('Previous month') }}">‹</button>
                                                        <div class="day-tour-calendar-title" aria-live="polite"></div>
                                                        <button type="button" class="day-tour-calendar-nav"
                                                            data-calendar-next
                                                            aria-label="{{ __('Next month') }}">›</button>
                                                    </div>
                                                    <div class="day-tour-calendar-grid"></div>
                                                </div>
                                            </div>
                                            <small class="text-danger d-none" id="dayTourDateError">
                                                {{ __('Please choose an available travel date.') }}
                                            </small>
                                        </div>

                                        <div class="day-tour-quantity-list">
                                            <div class="quantity-control">
                                                <label for="sidebar_adults">{{ __('Adults (12+ years)') }}</label>
                                                <div class="qty-buttons">
                                                    <button type="button" class="qty-btn"
                                                        onclick="changeQty('sidebar_adults', -1)"
                                                        aria-label="{{ __('Decrease adults') }}">−</button>
                                                    <input type="number" id="sidebar_adults" name="adults"
                                                        class="qty-input" value="2" min="1" max="40"
                                                        readonly>
                                                    <button type="button" class="qty-btn"
                                                        onclick="changeQty('sidebar_adults', 1)"
                                                        aria-label="{{ __('Increase adults') }}">+</button>
                                                </div>
                                            </div>
                                            <div class="quantity-control">
                                                <label for="sidebar_children">{{ __('Children (2–11 years)') }}</label>
                                                <div class="qty-buttons">
                                                    <button type="button" class="qty-btn"
                                                        onclick="changeQty('sidebar_children', -1)"
                                                        aria-label="{{ __('Decrease children') }}">−</button>
                                                    <input type="number" id="sidebar_children" name="children"
                                                        class="qty-input" value="0" min="0" max="40"
                                                        readonly>
                                                    <button type="button" class="qty-btn"
                                                        onclick="changeQty('sidebar_children', 1)"
                                                        aria-label="{{ __('Increase children') }}">+</button>
                                                </div>
                                            </div>
                                            <div class="quantity-control">
                                                <label for="sidebar_infants">{{ __('Infants (Under 2 years)') }}</label>
                                                <div class="qty-buttons">
                                                    <button type="button" class="qty-btn"
                                                        onclick="changeQty('sidebar_infants', -1)"
                                                        aria-label="{{ __('Decrease infants') }}">−</button>
                                                    <input type="number" id="sidebar_infants" name="infants"
                                                        class="qty-input" value="0" min="0" max="20"
                                                        readonly>
                                                    <button type="button" class="qty-btn"
                                                        onclick="changeQty('sidebar_infants', 1)"
                                                        aria-label="{{ __('Increase infants') }}">+</button>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="rooms" value="1">
                                        <div class="sidebar-price-options d-none">
                                            @foreach ($bookingPricingOptions as $option)
                                                <label class="sidebar-price-option">
                                                    <input type="radio" name="pricing_option"
                                                        value="{{ $option['id'] }}"
                                                        data-valid-from="{{ $option['valid_from'] }}"
                                                        data-valid-to="{{ $option['valid_to'] }}"
                                                        data-pax-min="{{ $option['pax_min'] ?? '' }}"
                                                        data-pax-max="{{ $option['pax_max'] ?? '' }}"
                                                        data-amount="{{ $option['amount'] ?? 0 }}"
                                                        data-price-unit="{{ $option['price_unit'] ?? '' }}"
                                                        data-adult-price="{{ $option['adult_price'] ?? 0 }}"
                                                        data-child-price="{{ $option['child_price'] ?? 0 }}"
                                                        data-infant-price="{{ $option['infant_price'] ?? 0 }}"
                                                        data-currency-symbol="{{ $option['currency_symbol'] ?? '$' }}"
                                                        data-label="{{ $option['label'] ?? '' }}"
                                                        data-description="{{ $option['description'] ?? '' }}" required>
                                                    <span class="sidebar-price-option-card">
                                                        <span><span
                                                                class="sidebar-option-name">{{ $option['label'] }}</span><span
                                                                class="sidebar-option-desc">{{ $option['description'] }}</span></span>
                                                        <span
                                                            class="sidebar-option-price">{{ $option['currency_symbol'] }}{{ number_format($option['amount'], 2) }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="day-tour-price-box" id="dayTourPriceBox">
                                            <div class="day-tour-price-header">
                                                <span class="day-tour-price-label">{{ __('Total Price') }}</span>
                                                <span class="day-tour-tier-badge" id="dayTourTierBadge"></span>
                                            </div>
                                            <div class="day-tour-price-total" id="dayTourPriceTotal"></div>
                                            <div class="day-tour-price-breakdown text-muted" id="dayTourPriceBreakdown">
                                            </div>
                                        </div>
                                        <div class="alert-danger mt-3" id="sidebarNoPrices" style="display:none">
                                            {{ __('No booking price is available for these details.') }}
                                        </div>
                                        <button type="submit" class="sidebar-checkout-btn"><i
                                                class="la la-calendar-check"></i>{{ __('Book Now') }}</button>
                                    </form>
                                @else
                                    <h4 class="booking-request-title">{{ __('Select Your Booking') }}</h4>
                                    <p class="booking-request-copy">
                                        {{ __('Choose your travel details and an available price, then continue to checkout.') }}
                                    </p>
                                    <form method="get" action="{{ route('website.checkout.show', $package->slug) }}"
                                        id="sidebarBookingForm">
                                        <div class="sidebar-booking-grid">
                                            <div class="input-box"><label class="label-text"
                                                    for="sidebar_travel_date">{{ __('Travel Date') }}</label><input
                                                    class="form-control" id="sidebar_travel_date" type="date"
                                                    name="travel_date" min="{{ today()->toDateString() }}" required>
                                            </div>
                                            <div class="input-box"><label class="label-text"
                                                    for="sidebar_rooms">{{ $package->package_type === 'nile_cruise' ? __('Cabins') : __('Rooms') }}</label><input
                                                    class="form-control" id="sidebar_rooms" type="number"
                                                    name="rooms" min="1" max="20" value="1"
                                                    required></div>
                                            <div class="input-box"><label class="label-text"
                                                    for="sidebar_adults">{{ __('Adults') }}</label><input
                                                    class="form-control" id="sidebar_adults" type="number"
                                                    name="adults" min="1" max="40" value="1"
                                                    required></div>
                                            <div class="input-box"><label class="label-text"
                                                    for="sidebar_children">{{ __('Children') }}</label><input
                                                    class="form-control" id="sidebar_children" type="number"
                                                    name="children" min="0" max="40" value="0"
                                                    required>
                                            </div>
                                        </div>
                                        <input type="hidden" name="infants" value="0">
                                        <div class="sidebar-price-options">
                                            @foreach ($bookingPricingOptions as $option)
                                                <label class="sidebar-price-option">
                                                    <input type="radio" name="pricing_option"
                                                        value="{{ $option['id'] }}"
                                                        data-valid-from="{{ $option['valid_from'] }}"
                                                        data-valid-to="{{ $option['valid_to'] }}"
                                                        data-pax-min="{{ $option['pax_min'] ?? '' }}"
                                                        data-pax-max="{{ $option['pax_max'] ?? '' }}" required>
                                                    <span class="sidebar-price-option-card">
                                                        <span><span
                                                                class="sidebar-option-name">{{ $option['label'] }}</span><span
                                                                class="sidebar-option-desc">{{ $option['description'] }}</span></span>
                                                        <span
                                                            class="sidebar-option-price">{{ $option['currency_symbol'] }}{{ number_format($option['amount'], 2) }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="alert-danger mt-3" id="sidebarNoPrices" style="display:none">
                                            {{ __('No booking price is available for these details.') }}</div>
                                        <button type="submit" class="sidebar-checkout-btn"><i
                                                class="la la-arrow-right"></i>{{ __('Continue to Checkout') }}</button>
                                    </form>
                                @endif
                            </div>
                            <div class="sidebar-content reserve-tab-panel" id="reserveEnquiryPanel" role="tabpanel"
                                hidden>
                                <div id="enquiryFormDesktop">
                                    @include('website.pages.packages.partials.enquiry-form', [
                                        'formSuffix' => 'desktop',
                                    ])
                                </div>
                            </div>
                        @else
                            <div class="sidebar-content" id="enquiryFormDesktop">
                                @include('website.pages.packages.partials.enquiry-form', [
                                    'formSuffix' => 'desktop',
                                ])
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($gallery))
        <div class="gallery-lightbox" id="galleryLightbox" aria-hidden="true">
            <div class="gallery-lightbox-dialog">
                <button type="button" class="gallery-lightbox-close" id="galleryLightboxClose"
                    aria-label="{{ __('Close') }}">×</button>
                <button type="button" class="gallery-lightbox-nav prev" id="galleryLightboxPrev"
                    aria-label="{{ __('Previous') }}">
                    <i class="la la-angle-left"></i>
                </button>
                <img src="" alt="{{ $title }}" class="gallery-lightbox-img" id="galleryLightboxImage">
                <button type="button" class="gallery-lightbox-nav next" id="galleryLightboxNext"
                    aria-label="{{ __('Next') }}">
                    <i class="la la-angle-right"></i>
                </button>
                <div class="gallery-lightbox-counter" id="galleryLightboxCounter"></div>
            </div>
        </div>
    @endif

    <div class="fixed-mobile-btn d-lg-none">
        @if ($hasBookablePrice)
            <a href="{{ route('website.checkout.show', $package->slug) }}" class="mobile-enquiry-btn"
                data-mobile-booking style="margin-right:8px"><i class="la la-calendar-check"></i>
                {{ __('Book Now') }}</a>
        @endif
        <a href="#" class="mobile-enquiry-btn" data-bs-toggle="modal" data-bs-target="#simpleEnquiryModal">
            <i class="la la-envelope"></i> {{ $hasBookablePrice ? __('Enquire') : __('Submit Enquiry') }}
        </a>
    </div>

    @if ($hasBookablePrice)
        <div class="modal fade" id="mobileBookingModal" tabindex="-1" aria-labelledby="mobileBookingModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="mobileBookingModalLabel">{{ __('Select Your Booking') }}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body" id="mobileBookingBody"></div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="simpleEnquiryModal" tabindex="-1" aria-labelledby="simpleEnquiryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">{{ __('Enquire About This Tour') }}</h3>
                        <p class="mb-0">{{ __('Get a personalized quote') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    @include('website.pages.packages.partials.enquiry-form', [
                        'formSuffix' => 'mobile',
                    ])
                </div>
            </div>
        </div>
    </div>

    <!-- Attraction Details Modal -->
    <div class="attraction-modal-overlay" id="attractionModalOverlay" aria-hidden="true">
        <div class="attraction-modal-card" role="dialog" aria-modal="true">
            <button type="button" class="attraction-modal-close" id="attractionModalClose"
                aria-label="{{ __('Close') }}">×</button>
            <div class="attraction-modal-img-wrap">
                <img src="" alt="" class="attraction-modal-img" id="attractionModalImg">
            </div>
            <div class="attraction-modal-body">
                <h3 class="attraction-modal-title" id="attractionModalTitle"></h3>
                <p class="attraction-modal-teaser" id="attractionModalTeaser"></p>
                <div class="attraction-modal-footer">
                    <a href="#" class="attraction-modal-btn-more" id="attractionModalBtnMore">
                        <span>{{ __('Learn More') }}</span>
                        <i class="la {{ app()->getLocale() === 'ar' ? 'la-arrow-left' : 'la-arrow-right' }}"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        window.changeQty = function(id, amount) {
            const input = document.getElementById(id);
            if (!input) return;
            const min = parseInt(input.getAttribute('min') || '0');
            const current = parseInt(input.value || min);
            const max = parseInt(input.getAttribute('max') || '999');
            input.value = Math.min(max, Math.max(min, current + amount));
            input.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Attraction Details Modal Script
            const attractionModal = document.getElementById('attractionModalOverlay');
            const attractionModalImg = document.getElementById('attractionModalImg');
            const attractionModalTitle = document.getElementById('attractionModalTitle');
            const attractionModalTeaser = document.getElementById('attractionModalTeaser');
            const attractionModalBtnMore = document.getElementById('attractionModalBtnMore');
            const attractionModalClose = document.getElementById('attractionModalClose');

            if (attractionModal) {
                const openAttractionModal = (data) => {
                    if (attractionModalImg) {
                        attractionModalImg.src = data.img;
                        attractionModalImg.alt = data.title;
                    }
                    if (attractionModalTitle) attractionModalTitle.textContent = data.title;
                    if (attractionModalTeaser) attractionModalTeaser.textContent = data.teaser;
                    if (attractionModalBtnMore) {
                        attractionModalBtnMore.href = data.url;
                        attractionModalBtnMore.target = data.target || '_self';
                    }

                    attractionModal.classList.add('open');
                    attractionModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                const closeAttractionModal = () => {
                    attractionModal.classList.remove('open');
                    attractionModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                };

                document.querySelectorAll('.js-attraction-modal-trigger').forEach((trigger) => {
                    trigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        openAttractionModal({
                            title: this.dataset.attractionTitle || '',
                            teaser: this.dataset.attractionTeaser || '',
                            img: this.dataset.attractionImg || '',
                            url: this.dataset.attractionUrl || '#',
                            target: this.dataset.attractionTarget || '_self'
                        });
                    });
                });

                if (attractionModalClose) {
                    attractionModalClose.addEventListener('click', function(e) {
                        e.preventDefault();
                        closeAttractionModal();
                    });
                }

                attractionModal.addEventListener('click', function(e) {
                    if (e.target === attractionModal) {
                        closeAttractionModal();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && attractionModal.classList.contains('open')) {
                        closeAttractionModal();
                    }
                });
            }

            const reserveTabs = document.querySelectorAll('[data-reserve-tab]');
            const reserveBookingPanel = document.getElementById('reserveBookingPanel');
            const reserveEnquiryPanel = document.getElementById('reserveEnquiryPanel');

            const activateReserveTab = (tabName) => {
                if (!reserveBookingPanel || !reserveEnquiryPanel) return;

                reserveBookingPanel.hidden = tabName !== 'booking';
                reserveEnquiryPanel.hidden = tabName !== 'enquiry';
                reserveTabs.forEach((button) => {
                    const active = button.dataset.reserveTab === tabName;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-selected', active ? 'true' : 'false');
                });

                if (tabName === 'enquiry') {
                    history.replaceState(null, '', '#enquiryFormDesktop');
                } else if (location.hash === '#enquiryFormDesktop') {
                    history.replaceState(null, '', location.pathname + location.search);
                }
            };

            reserveTabs.forEach((button) => button.addEventListener('click', () => {
                activateReserveTab(button.dataset.reserveTab);
            }));

            if (reserveTabs.length) {
                activateReserveTab(location.hash === '#enquiryFormDesktop' ? 'enquiry' : 'booking');
            }

            const mobileBookingModal = document.getElementById('mobileBookingModal');
            if (mobileBookingModal && reserveBookingPanel) {
                const bookingHome = document.createComment('Booking panel desktop position');
                reserveBookingPanel.before(bookingHome);
                const mobileViewport = window.matchMedia('(max-width: 991.98px)');
                let bookingWasHidden = false;

                // Move the live form so its values, validation and price handlers stay shared.
                mobileBookingModal.addEventListener('show.bs.modal', () => {
                    bookingWasHidden = reserveBookingPanel.hidden;
                    reserveBookingPanel.hidden = false;
                    document.getElementById('mobileBookingBody').append(reserveBookingPanel);
                });
                mobileBookingModal.addEventListener('hidden.bs.modal', () => {
                    bookingHome.after(reserveBookingPanel);
                    reserveBookingPanel.hidden = bookingWasHidden;
                });
                document.querySelectorAll('[data-mobile-booking]').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        if (!mobileViewport.matches) return;
                        event.preventDefault();
                        window.bootstrap.Modal.getOrCreateInstance(mobileBookingModal).show(button);
                    });
                });
                const closeDesktopBookingModal = () => {
                    if (!mobileViewport.matches) {
                        window.bootstrap.Modal.getInstance(mobileBookingModal)?.hide();
                    }
                };
                mobileViewport.addEventListener('change', closeDesktopBookingModal);
                mobileBookingModal.addEventListener('shown.bs.modal', closeDesktopBookingModal);
            }

            const sidebarBookingForm = document.getElementById('sidebarBookingForm');
            if (sidebarBookingForm) {
                const travelDate = document.getElementById('sidebar_travel_date');
                const adults = document.getElementById('sidebar_adults');
                const children = document.getElementById('sidebar_children');
                const infants = document.getElementById('sidebar_infants');
                const noPrices = document.getElementById('sidebarNoPrices');
                const dayTourPriceBox = document.getElementById('dayTourPriceBox');
                const dayTourPriceTotal = document.getElementById('dayTourPriceTotal');
                const dayTourTierBadge = document.getElementById('dayTourTierBadge');
                const dayTourPriceBreakdown = document.getElementById('dayTourPriceBreakdown');

                const calendar = document.getElementById('dayTourCalendar');
                if (calendar) {
                    const displayInput = document.getElementById('day_tour_date_display');
                    const dateError = document.getElementById('dayTourDateError');
                    const minDateParts = sidebarBookingForm.dataset.minDate.split('-').map(Number);
                    const minDate = new Date(minDateParts[0], minDateParts[1] - 1, minDateParts[2]);
                    const selectedDays = JSON.parse(sidebarBookingForm.dataset.operatingDays || '[]')
                        .map(day => String(day).trim().toLowerCase()).filter(Boolean);
                    const daily = !selectedDays.length || selectedDays.some(day => ['daily', 'everyday',
                        'every day', 'all'
                    ].includes(day));
                    const weekdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    const allowedIndexes = selectedDays.map(day => weekdays.findIndex(name => name === day || name
                        .slice(0, 3) === day.slice(0, 3))).filter(index => index >= 0);
                    const title = calendar.querySelector('.day-tour-calendar-title');
                    const grid = calendar.querySelector('.day-tour-calendar-grid');
                    let viewedMonth = new Date(minDate.getFullYear(), minDate.getMonth(), 1);

                    const toIso = date => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(
                        date.getDate()).padStart(2, '0')].join('-');
                    const isAvailable = date => date >= minDate && (daily || allowedIndexes.includes(date
                        .getDay()));
                    const renderCalendar = () => {
                        const locale = document.documentElement.lang || 'en';
                        title.textContent = viewedMonth.toLocaleDateString(locale, {
                            month: 'short',
                            year: 'numeric'
                        });
                        grid.innerHTML = '';
                        weekdays.forEach((day, index) => {
                            const label = document.createElement('div');
                            label.className = 'day-tour-calendar-weekday';
                            const sample = new Date(2026, 7, 2 + index);
                            label.textContent = sample.toLocaleDateString(locale, {
                                weekday: 'short'
                            }).slice(0, 2);
                            grid.appendChild(label);
                        });

                        const first = new Date(viewedMonth.getFullYear(), viewedMonth.getMonth(), 1);
                        const start = new Date(first);
                        start.setDate(1 - first.getDay());
                        for (let offset = 0; offset < 42; offset++) {
                            const date = new Date(start);
                            date.setDate(start.getDate() + offset);
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'day-tour-calendar-day';
                            button.textContent = date.getDate();
                            button.dataset.date = toIso(date);
                            if (date.getMonth() !== viewedMonth.getMonth()) button.classList.add('is-outside');
                            if (!isAvailable(date) || date.getMonth() !== viewedMonth.getMonth()) button
                                .disabled = true;
                            if (travelDate.value === button.dataset.date) button.classList.add('is-selected');
                            button.addEventListener('click', () => {
                                travelDate.value = button.dataset.date;
                                displayInput.value = date.toLocaleDateString(locale, {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                });
                                displayInput.setAttribute('aria-expanded', 'false');
                                calendar.hidden = true;
                                dateError?.classList.add('d-none');
                                travelDate.dispatchEvent(new Event('change', {
                                    bubbles: true
                                }));
                            });
                            grid.appendChild(button);
                        }
                    };

                    displayInput.addEventListener('click', () => {
                        calendar.hidden = !calendar.hidden;
                        displayInput.setAttribute('aria-expanded', calendar.hidden ? 'false' : 'true');
                        if (!calendar.hidden) renderCalendar();
                    });
                    calendar.querySelector('[data-calendar-prev]').addEventListener('click', () => {
                        const previous = new Date(viewedMonth.getFullYear(), viewedMonth.getMonth() - 1, 1);
                        if (previous >= new Date(minDate.getFullYear(), minDate.getMonth(), 1))
                            viewedMonth = previous;
                        renderCalendar();
                    });
                    calendar.querySelector('[data-calendar-next]').addEventListener('click', () => {
                        viewedMonth = new Date(viewedMonth.getFullYear(), viewedMonth.getMonth() + 1, 1);
                        renderCalendar();
                    });
                    document.addEventListener('click', event => {
                        if (!calendar.hidden && !event.target.closest('.day-tour-date-wrap')) {
                            calendar.hidden = true;
                            displayInput.setAttribute('aria-expanded', 'false');
                        }
                    });
                    renderCalendar();
                }

                const refreshSidebarPrices = () => {
                    const selectedDate = travelDate.value;
                    const numAdults = Number(adults?.value || 1);
                    const numChildren = Number(children?.value || 0);
                    const numInfants = Number(infants?.value || 0);
                    const guests = Math.max(1, numAdults + numChildren + numInfants);
                    let firstAvailable = null;
                    let bestOption = null;

                    sidebarBookingForm.querySelectorAll('.sidebar-price-option').forEach((label) => {
                        const input = label.querySelector('input[type="radio"]');
                        const min = Number(input.dataset.paxMin || 0);
                        const max = Number(input.dataset.paxMax || 0);
                        const dateMatches = !selectedDate ||
                            ((!input.dataset.validFrom || selectedDate >= input.dataset.validFrom) &&
                                (!input.dataset.validTo || selectedDate <= input.dataset.validTo));
                        const guestsMatch = (!min || guests >= min) && (!max || guests <= max);
                        const available = dateMatches && guestsMatch;

                        if (!dayTourPriceBox) {
                            label.style.display = available ? '' : 'none';
                        }
                        input.disabled = !available;
                        if (available && !firstAvailable) firstAvailable = input;
                        if (available) {
                            if (!bestOption || (min > 0 && Number(bestOption.dataset.paxMin || 0) ===
                                    0)) {
                                bestOption = input;
                            }
                        }
                    });

                    const activeInput = bestOption || firstAvailable;
                    if (activeInput) {
                        activeInput.checked = true;
                    }

                    if (dayTourPriceBox) {
                        if (activeInput) {
                            const symbol = activeInput.dataset.currencySymbol || '$';
                            const unit = activeInput.dataset.priceUnit;
                            const amount = parseFloat(activeInput.dataset.amount || 0);
                            const adultPrice = parseFloat(activeInput.dataset.adultPrice || 0);
                            const childPrice = parseFloat(activeInput.dataset.childPrice || 0);
                            const infantPrice = parseFloat(activeInput.dataset.infantPrice || 0);

                            let total = 0;
                            let breakdown = '';
                            if (unit === 'category') {
                                total = (numAdults * adultPrice) + (numChildren * childPrice) + (numInfants *
                                    infantPrice);
                                const parts = [];
                                parts.push(`${numAdults} × ${symbol}${adultPrice.toFixed(0)}`);
                                if (numChildren > 0) parts.push(
                                    `${numChildren} × ${symbol}${childPrice.toFixed(0)}`);
                                if (numInfants > 0) parts.push(
                                    `${numInfants} × ${symbol}${infantPrice.toFixed(0)}`);
                                breakdown = parts.join(' + ');
                            } else if (unit === 'per_booking') {
                                total = amount;
                                breakdown = `${symbol}${amount.toFixed(0)} per booking`;
                            } else if (unit === 'per_adult') {
                                total = amount * numAdults;
                                breakdown = `${numAdults} × ${symbol}${amount.toFixed(0)}`;
                            } else {
                                total = amount * Math.max(1, numAdults + numChildren);
                                breakdown = `${numAdults + numChildren} × ${symbol}${amount.toFixed(0)}`;
                            }

                            dayTourPriceTotal.textContent = `${symbol}${total.toFixed(0)}`;
                            if (dayTourTierBadge) {
                                dayTourTierBadge.textContent = activeInput.dataset.label || '';
                            }
                            if (dayTourPriceBreakdown) {
                                dayTourPriceBreakdown.textContent = breakdown;
                            }
                            dayTourPriceBox.style.display = 'block';
                            noPrices.style.display = 'none';
                        } else {
                            dayTourPriceBox.style.display = 'none';
                            noPrices.style.display = 'block';
                        }
                    } else {
                        noPrices.style.display = firstAvailable ? 'none' : 'block';
                    }
                };

                [travelDate, adults, children, infants].filter(Boolean).forEach((input) => input.addEventListener(
                    'change',
                    refreshSidebarPrices));
                sidebarBookingForm.addEventListener('submit', (event) => {
                    if (calendar && !travelDate.value) {
                        event.preventDefault();
                        document.getElementById('dayTourDateError')?.classList.remove('d-none');
                        return;
                    }
                    if (!sidebarBookingForm.querySelector(
                            'input[name="pricing_option"]:checked:not(:disabled)')) {
                        event.preventDefault();
                        noPrices.style.display = 'block';
                    }
                });
                refreshSidebarPrices();
            }

            const tpForm = document.getElementById('sidebarTravelPackageForm');
            if (tpForm) {
                const matrixData = @json($travelPackageMatrix['matrix'] ?? []);
                const accommodationsList = @json($travelPackageMatrix['accommodations'] ?? []);
                const currencySymbol = @json($currencySymbol ?? '$');
                const travelDateInput = document.getElementById('tp_travel_date');
                const roomsSelect = document.getElementById('tp_rooms');
                const roomFields = document.getElementById('tp_roomFields');
                const totalAdultsInput = document.getElementById('tp_totalAdults');
                const totalChildrenInput = document.getElementById('tp_totalChildren');
                const formAdultsInput = document.getElementById('tp_form_adults');
                const formChildrenInput = document.getElementById('tp_form_children');
                const priceSummaryBox = document.getElementById('tp_priceSummaryBox');
                const displayTotal = document.getElementById('tp_displayTotal');
                const displayDeposit = document.getElementById('tp_displayDeposit');
                const displayBalance = document.getElementById('tp_displayBalance');

                function getSelectedSeason() {
                    const val = travelDateInput ? travelDateInput.value : '';
                    if (!val) return 'winter';
                    const date = new Date(val);
                    if (isNaN(date.getTime())) return 'winter';
                    const month = date.getMonth() + 1;
                    return (month >= 5 && month <= 8) ? 'summer' : 'winter';
                }

                function getRoomRates(accName) {
                    const season = getSelectedSeason();
                    if (!accName && accommodationsList.length > 0) {
                        accName = accommodationsList[0].name;
                    }
                    if (accName && matrixData[accName] && matrixData[accName][season]) {
                        return matrixData[accName][season];
                    }
                    if (accName) {
                        const lower = accName.toLowerCase();
                        for (const key of Object.keys(matrixData)) {
                            if (key.toLowerCase() === lower && matrixData[key][season]) {
                                return matrixData[key][season];
                            }
                        }
                    }
                    const firstKey = Object.keys(matrixData)[0];
                    if (firstKey && matrixData[firstKey][season]) {
                        return matrixData[firstKey][season];
                    }
                    return {
                        single: 0,
                        double: 0,
                        triple: 0
                    };
                }

                function calculateRoomPrice(rates, adults, children) {
                    const single = Number(rates.single || 0);
                    const double = Number(rates.double || 0);
                    const triple = Number(rates.triple || 0);

                    if (adults === 1 && children === 0) {
                        return single;
                    }
                    if (adults === 1 && (children === 1 || children === 2)) {
                        return double * 2;
                    }
                    if (adults === 2) {
                        const base = double * 2;
                        const childRate = double * 0.50;
                        return base + (children * childRate);
                    }
                    if (adults === 3) {
                        return triple * 3;
                    }
                    if (triple > 0) {
                        return (triple * adults) + (children * triple * 0.50);
                    }
                    return (double * adults) + (children * double * 0.50);
                }

                function generateRooms() {
                    const numRooms = parseInt(roomsSelect ? roomsSelect.value : 1, 10) || 1;
                    const existingData = [];
                    roomFields.querySelectorAll('.room-group-container').forEach((roomEl) => {
                        const accEl = roomEl.querySelector('.tp-room-acc-select');
                        const adultEl = roomEl.querySelector('.tp-adult-select');
                        const childEl = roomEl.querySelector('.tp-child-select');
                        if (accEl) {
                            existingData.push({
                                acc: accEl.value,
                                adults: parseInt(adultEl.value, 10) || 2,
                                children: parseInt(childEl.value, 10) || 0,
                            });
                        }
                    });

                    roomFields.innerHTML = '';

                    for (let i = 1; i <= numRooms; i++) {
                        const prev = existingData[i - 1] || {};
                        const selectedAcc = prev.acc || (accommodationsList[0]?.name || 'Standard');
                        const selectedAdults = prev.adults !== undefined ? prev.adults : (i === 1 ? 2 : 1);
                        const selectedChildren = prev.children !== undefined ? prev.children : 0;

                        let accOptions = '';
                        accommodationsList.forEach((a) => {
                            const isSel = a.name === selectedAcc ? 'selected' : '';
                            accOptions += `<option value="${a.name}" ${isSel}>${a.name}</option>`;
                        });

                        const roomHtml = `
                                    <div class="room-group-container mb-2 p-3 rounded">
                                        <div class="room-header d-flex justify-content-between align-items-center mb-2" style="padding-bottom: 6px;">
                                            <h6 class="room-title mb-0" style="font-weight: 700; font-size: 14.5px;">{{ __('Room') }} ${i}</h6>
                                            <span class="room-price-badge" id="tp_room_${i}_priceTag">$0.00</span>
                                        </div>
                                        <div class="input-box mb-2">
                                            <label class="room-inline-label">{{ __('Accommodations Type') }} *</label>
                                            <select name="room_${i}_accommodation" class="form-select select-inline tp-room-acc-select" required style="border-radius: 8px; font-size: 13px; height: 38px;">
                                                ${accOptions}
                                            </select>
                                        </div>
                                        <div class="room-inline-row d-flex gap-2">
                                            <div class="room-inline-option flex-fill">
                                                <label class="room-inline-label">{{ __('Adults') }} <span class="age-info">(+12)</span></label>
                                                <select name="room_${i}_adults" class="form-select select-inline tp-adult-select" autocomplete="off" style="border-radius: 8px; font-size: 13px; height: 38px;">
                                                    <option value="1" ${selectedAdults === 1 ? 'selected' : ''}>1</option>
                                                    <option value="2" ${selectedAdults === 2 ? 'selected' : ''}>2</option>
                                                    <option value="3" ${selectedAdults === 3 ? 'selected' : ''}>3</option>
                                                </select>
                                            </div>
                                            <div class="room-inline-option flex-fill">
                                                <label class="room-inline-label">{{ __('Children') }} <span class="age-info">(2-11)</span></label>
                                                <select name="room_${i}_children" class="form-select select-inline tp-child-select" autocomplete="off" style="border-radius: 8px; font-size: 13px; height: 38px;">
                                                    <option value="0" ${selectedChildren === 0 ? 'selected' : ''}>0</option>
                                                    <option value="1" ${selectedChildren === 1 ? 'selected' : ''}>1</option>
                                                    <option value="2" ${selectedChildren === 2 ? 'selected' : ''}>2</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        roomFields.insertAdjacentHTML('beforeend', roomHtml);
                    }

                    recalculateAllPrices();
                }

                function recalculateAllPrices() {
                    let totalPkgPrice = 0;
                    let totalA = 0;
                    let totalC = 0;

                    roomFields.querySelectorAll('.room-group-container').forEach((roomEl, idx) => {
                        const i = idx + 1;
                        const accSel = roomEl.querySelector('.tp-room-acc-select');
                        const adultSel = roomEl.querySelector('.tp-adult-select');
                        const childSel = roomEl.querySelector('.tp-child-select');
                        const priceTag = roomEl.querySelector(`#tp_room_${i}_priceTag`);
                        if (!accSel || !adultSel || !childSel) return;

                        const accName = accSel.value;
                        const rates = getRoomRates(accName);
                        const maxGuests = Number(rates.triple || 0) > 0 ? 3 : 2;

                        let a = parseInt(adultSel.value, 10) || 1;
                        let c = parseInt(childSel.value, 10) || 0;

                        if (a + c > maxGuests) {
                            c = Math.max(0, maxGuests - a);
                            childSel.value = c;
                        }

                        const rPrice = calculateRoomPrice(rates, a, c);
                        if (priceTag) {
                            priceTag.textContent =
                                `${currencySymbol}${rPrice.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
                        }

                        totalPkgPrice += rPrice;
                        totalA += a;
                        totalC += c;
                    });

                    if (totalAdultsInput) totalAdultsInput.value = totalA;
                    if (totalChildrenInput) totalChildrenInput.value = totalC;
                    if (formAdultsInput) formAdultsInput.value = totalA;
                    if (formChildrenInput) formChildrenInput.value = totalC;

                    if (totalPkgPrice > 0) {
                        const deposit = totalPkgPrice * 0.5;
                        const balance = totalPkgPrice - deposit;

                        if (displayTotal) {
                            displayTotal.textContent =
                                `${currencySymbol}${totalPkgPrice.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
                        }
                        if (displayDeposit) {
                            displayDeposit.textContent =
                                `${currencySymbol}${deposit.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
                        }
                        if (displayBalance) {
                            displayBalance.textContent =
                                `${currencySymbol}${balance.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
                        }
                        if (priceSummaryBox) {
                            priceSummaryBox.style.display = 'block';
                        }
                    } else if (priceSummaryBox) {
                        priceSummaryBox.style.display = 'none';
                    }
                }

                if (travelDateInput) {
                    travelDateInput.addEventListener('change', recalculateAllPrices);
                }
                if (roomsSelect) {
                    roomsSelect.addEventListener('change', generateRooms);
                }

                roomFields.addEventListener('change', (e) => {
                    if (e.target.matches('.tp-room-acc-select, .tp-adult-select, .tp-child-select')) {
                        recalculateAllPrices();
                    }
                });

                tpForm.addEventListener('submit', (e) => {
                    if (travelDateInput && !travelDateInput.value) {
                        e.preventDefault();
                        travelDateInput.focus();
                        return;
                    }
                    if (roomsSelect && !roomsSelect.value) {
                        e.preventDefault();
                        const roomErr = document.getElementById('tp_roomError');
                        if (roomErr) roomErr.style.display = 'block';
                        roomsSelect.focus();
                        return;
                    }
                    recalculateAllPrices();
                });

                generateRooms();
            }

            const collapseTriggers = document.querySelectorAll(
                '[data-collapse-target]');

            const setCollapseState = (trigger, content, isOpen) => {
                content.classList.toggle('open', isOpen);
                content.classList.toggle('active', isOpen);
                content.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                trigger.setAttribute('aria-expanded', isOpen ? 'true' :
                    'false');
                content.style.maxHeight = isOpen ? `${content.scrollHeight}px` :
                    '0px';
            };

            collapseTriggers.forEach((trigger) => {
                const content = document.getElementById(trigger.dataset
                    .collapseTarget);

                if (!content) {
                    return;
                }

                setCollapseState(
                    trigger,
                    content,
                    content.classList.contains('open') || content.classList
                    .contains('active')
                );

                trigger.addEventListener('click', function() {
                    setCollapseState(this, content, this.getAttribute(
                        'aria-expanded') !== 'true');
                });
            });

            window.addEventListener('resize', function() {
                document.querySelectorAll('.collapsible-content.open').forEach((
                    content) => {
                    content.style.maxHeight =
                        `${content.scrollHeight}px`;
                });
            });

            const mobileFixedButton = document.querySelector('.fixed-mobile-btn');
            const footer = document.querySelector('.footer');

            if (mobileFixedButton && footer) {
                if ('IntersectionObserver' in window) {
                    const footerObserver = new IntersectionObserver((entries) => {
                        mobileFixedButton.classList.toggle(
                            'is-footer-visible',
                            entries.some((entry) => entry
                                .isIntersecting)
                        );
                    }, {
                        rootMargin: '0px 0px -24px 0px',
                        threshold: 0
                    });

                    footerObserver.observe(footer);
                } else {
                    const syncMobileButtonWithFooter = () => {
                        const footerTop = footer.getBoundingClientRect().top;
                        mobileFixedButton.classList.toggle(
                            'is-footer-visible',
                            footerTop < window.innerHeight - 24
                        );
                    };

                    window.addEventListener('scroll', syncMobileButtonWithFooter, {
                        passive: true
                    });
                    window.addEventListener('resize', syncMobileButtonWithFooter);
                    syncMobileButtonWithFooter();
                }
            }

            document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');

                    if (!href || href === '#' || this.hasAttribute(
                            'data-bs-toggle')) {
                        return;
                    }

                    let target = null;

                    try {
                        target = document.querySelector(href);
                    } catch (error) {
                        return;
                    }

                    if (!target) return;

                    e.preventDefault();
                    window.scrollTo({
                        top: target.offsetTop - 90,
                        behavior: 'smooth'
                    });
                });
            });

            const galleryImages = @json(array_values($gallery ?? []));
            const lightbox = document.getElementById('galleryLightbox');

            if (lightbox && galleryImages.length) {
                const lightboxImage = document.getElementById('galleryLightboxImage');
                const lightboxCounter = document.getElementById('galleryLightboxCounter');
                const closeButton = document.getElementById('galleryLightboxClose');
                const prevButton = document.getElementById('galleryLightboxPrev');
                const nextButton = document.getElementById('galleryLightboxNext');
                const triggers = document.querySelectorAll('.js-gallery-trigger');
                let currentIndex = 0;

                const updateLightbox = () => {
                    lightboxImage.src = galleryImages[currentIndex];
                    lightboxCounter.textContent = `${currentIndex + 1} / ${galleryImages.length}`;
                    prevButton.style.display = galleryImages.length > 1 ? 'inline-flex' : 'none';
                    nextButton.style.display = galleryImages.length > 1 ? 'inline-flex' : 'none';
                };

                const openLightbox = (index) => {
                    currentIndex = index;
                    updateLightbox();
                    lightbox.classList.add('open');
                    lightbox.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                const closeLightbox = () => {
                    lightbox.classList.remove('open');
                    lightbox.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                };

                const showNext = () => {
                    currentIndex = (currentIndex + 1) % galleryImages.length;
                    updateLightbox();
                };

                const showPrev = () => {
                    currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
                    updateLightbox();
                };

                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        openLightbox(Number(this.dataset.galleryIndex || 0));
                    });
                });

                if (closeButton) closeButton.addEventListener('click', closeLightbox);
                if (nextButton) nextButton.addEventListener('click', showNext);
                if (prevButton) prevButton.addEventListener('click', showPrev);

                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        closeLightbox();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (!lightbox.classList.contains('open')) {
                        return;
                    }

                    if (e.key === 'Escape') {
                        closeLightbox();
                    } else if (e.key === 'ArrowRight') {
                        showNext();
                    } else if (e.key === 'ArrowLeft') {
                        showPrev();
                    }
                });
            }
        });
    </script>
@endsection
