@extends('website.layouts.master')

@php
    $indexRoute = route('website.destinations.show', $destination->slug, false);
    $countryRoute = $destination->country?->slug
        ? route('website.destinations.index', ['country' => $destination->country->slug])
        : route('website.destinations.index');
    $countryName = $destination->country?->display_name ?: __('Destination');
    $heroSubtitle =
        $shortDescription !== ''
            ? $shortDescription
            : __('Explore curated journeys, private tours, and unforgettable highlights in :destination.', [
                'destination' => $destination->display_name,
            ]);
    $activeType = collect($typeOptions)->firstWhere('value', $selectedType);
    $resultsTitle = $activeType
        ? $activeType['label'] . ' ' . __('in') . ' ' . $destination->display_name
        : __('Trips in') . ' ' . $destination->display_name;
@endphp

@section('title', $pageTitle . ' - Etro Tours')
@section('description', $pageDescription)
@section('keywords',
    trim(
    collect([
    $destination->display_name,
    $countryName,
    'Etro Tours',
    'destination travel',
    'Egypt
    trips',
    ])->filter()->implode(', '),
    ', ',
    ))
@section('image', $heroImage)

@section('css')
    <style>
        .destination-breadcrumb {
            background: var(--pearl-luxury, #faf8f3);
            border-bottom: 1px solid rgba(197, 149, 91, 0.16);
            padding: 16px 0;
        }

        .destination-breadcrumb .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
        }

        .destination-breadcrumb .breadcrumb-item,
        .destination-breadcrumb .breadcrumb-item a {
            color: #1c325c;
            font-size: 0.95rem;
            text-decoration: none;
        }

        .destination-breadcrumb .breadcrumb-item.active {
            color: #9b6a2c;
            font-weight: 700;
        }

        .destination-hero {
            position: relative;
            min-height: 520px;
            margin-top: -85px;
            padding: 150px 0 88px;
            color: #fff;
            background:
                linear-gradient(rgba(16, 33, 63, 0.7), rgba(18, 61, 102, 0.6)),
                url('{{ $heroImage }}') center/cover no-repeat;
            overflow: hidden;
        }

        .destination-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 210, 125, 0.16), transparent 28%),
                radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.08), transparent 34%);
        }

        .destination-hero .container,
        .destination-overview .container,
        .attractions-section .container,
        .destination-results .container,
        .destination-cta .container {
            position: relative;
            z-index: 1;
        }

        .destination-hero-content {
            max-width: 980px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.24);
            backdrop-filter: blur(12px);
            font-weight: 700;
            margin-bottom: 22px;
        }

        .hero-badge i {
            color: #ffd27d;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.8rem, 6vw, 4.8rem);
            line-height: 1.05;
            margin-bottom: 18px;
            color: #fff;
        }

        .hero-subtitle {
            max-width: 760px;
            font-size: 1.08rem;
            line-height: 1.9;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 30px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 34px;
        }

        .hero-btn,
        .hero-btn-outline,
        .filter-btn,
        .reset-btn,
        .journey-btn,
        .cta-btn,
        .attraction-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        }

        .hero-btn,
        .filter-btn,
        .journey-btn,
        .cta-btn {
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #1c325c;
            box-shadow: 0 12px 26px rgba(197, 149, 91, 0.22);
        }

        .hero-btn,
        .hero-btn-outline {
            min-height: 54px;
            padding: 0 24px;
            border-radius: 18px;
        }

        .hero-btn-outline {
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .hero-btn:hover,
        .filter-btn:hover,
        .journey-btn:hover,
        .cta-btn:hover,
        .attraction-link:hover {
            transform: translateY(-2px);
            color: #1c325c;
        }

        .hero-btn-outline:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: translateY(-2px);
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            max-width: 780px;
        }

        .hero-stat {
            border-radius: 24px;
            padding: 20px 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(10px);
        }

        .hero-stat strong {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            line-height: 1;
            color: #ffd27d;
            margin-bottom: 8px;
        }

        .hero-stat span {
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.94rem;
        }

        .destination-overview {
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            padding: 72px 0 92px;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        .overview-panel,
        .sidebar-card,
        .filters-card,
        .journey-card,
        .journey-empty,
        .cta-card,
        .attraction-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 18px 46px rgba(16, 33, 63, 0.08);
        }

        .overview-panel {
            padding: 34px;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: #9b6a2c;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .overview-panel h2,
        .section-heading h2,
        .results-head h2,
        .cta-card h2 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .overview-panel h2 {
            font-size: clamp(2rem, 4vw, 2.9rem);
        }

        .overview-summary,
        .overview-body,
        .section-heading p,
        .results-head p,
        .journey-description,
        .journey-empty p,
        .attraction-description,
        .cta-card p {
            color: #5b6776;
            line-height: 1.9;
        }

        .overview-body p:last-child {
            margin-bottom: 0;
        }

        .overview-sidebar {
            display: grid;
            gap: 22px;
        }

        .sidebar-card {
            padding: 28px;
        }

        .sidebar-card h3 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 1.45rem;
            margin-bottom: 18px;
        }

        .fact-list {
            display: grid;
            gap: 14px;
        }

        .fact-item {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 18px;
            background: #f7fafc;
        }

        .fact-item span {
            color: #5b6776;
        }

        .fact-item strong {
            color: #1c325c;
            font-weight: 700;
            text-align: right;
        }

        .destination-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .destination-tags span {
            display: inline-flex;
            align-items: center;
            padding: 8px 13px;
            border-radius: 999px;
            background: #edf4fb;
            color: #1c325c;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .attractions-section {
            background: linear-gradient(180deg, #f7fafc 0%, #ffffff 100%);
            padding: 92px 0 72px;
        }

        .section-heading {
            max-width: 760px;
            margin-bottom: 28px;
        }

        .section-heading h2 {
            font-size: clamp(1.9rem, 4vw, 2.6rem);
        }

        .attraction-card {
            overflow: hidden;
            height: 100%;
        }

        .attraction-image {
            height: 230px;
            overflow: hidden;
            background: #dbe6f2;
        }

        .attraction-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.45s ease;
        }

        .attraction-card:hover .attraction-image img {
            transform: scale(1.06);
        }

        .attraction-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 230px);
        }

        .attraction-title {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 1.35rem;
            margin-bottom: 12px;
        }

        .attraction-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
            margin-bottom: 18px;
        }

        .attraction-meta span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f4f7fb;
            color: #425466;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .attraction-meta i {
            color: #c5955b;
        }

        .attraction-link {
            margin-top: auto;
            align-self: flex-start;
            padding: 12px 18px;
            border-radius: 16px;
            background: #f8fbff;
            color: #1c325c;
            border: 1px solid rgba(26, 54, 93, 0.1);
        }

        .destination-results {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 58px 0 92px;
        }

        .filters-card {
            padding: 28px;
            margin-bottom: 32px;
        }

        .journey-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .journey-type-card {
            position: relative;
            display: flex;
            align-items: flex-end;
            min-height: 180px;
            overflow: hidden;
            padding: 0;
            border-radius: 24px;
            background: #dfe8ef;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 16px 36px rgba(16, 33, 63, 0.08);
            color: #fff;
            text-decoration: none;
            transition: transform 0.28s ease, border-color 0.28s ease, box-shadow 0.28s ease;
        }

        .journey-type-card:hover,
        .journey-type-card.is-active {
            transform: translateY(-4px);
            border-color: rgba(197, 149, 91, 0.42);
            box-shadow: 0 22px 48px rgba(16, 33, 63, 0.13);
            color: #fff;
        }

        .journey-type-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .journey-type-card:hover .journey-type-image,
        .journey-type-card.is-active .journey-type-image {
            transform: scale(1.06);
        }

        .journey-type-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(7, 16, 34, 0.08) 0%, rgba(7, 16, 34, 0.42) 45%, rgba(7, 16, 34, 0.82) 100%);
            z-index: 1;
        }

        .journey-type-content strong,
        .journey-type-content span {
            display: block;
        }

        .journey-type-content {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 24px;
        }

        .journey-type-content strong {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            line-height: 1.25;
            margin-bottom: 6px;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.38);
        }

        .journey-type-content span {
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.92rem;
            font-weight: 700;
        }

        .filters-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1c325c;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 22px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr auto auto;
            gap: 16px;
            align-items: end;
        }

        .filters-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #425466;
        }

        .filters-card .form-control,
        .filters-card .form-select {
            min-height: 54px;
            border-radius: 16px;
            border: 1px solid rgba(26, 54, 93, 0.14);
            box-shadow: none;
        }

        .filters-card .form-control:focus,
        .filters-card .form-select:focus {
            border-color: #c5955b;
            box-shadow: 0 0 0 0.2rem rgba(197, 149, 91, 0.14);
        }

        .filter-btn,
        .reset-btn {
            min-height: 54px;
            border-radius: 16px;
            padding: 0 22px;
            white-space: nowrap;
        }

        .filter-btn {
            border: none;
        }

        .reset-btn {
            background: #fff;
            color: #1c325c;
            border: 1px solid rgba(26, 54, 93, 0.14);
        }

        .results-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 26px;
            flex-wrap: wrap;
        }

        .results-head h2 {
            margin-bottom: 8px;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
        }

        .results-grid {
            row-gap: 24px;
        }

        .journey-card {
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.32s ease, box-shadow 0.32s ease, border-color 0.32s ease;
        }

        .journey-card:hover {
            transform: translateY(-8px);
            border-color: rgba(197, 149, 91, 0.34);
            box-shadow: 0 24px 52px rgba(16, 33, 63, 0.14);
        }

        .journey-image {
            position: relative;
            height: 255px;
            overflow: hidden;
            background: #dbe6f2;
        }

        .journey-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .journey-card:hover .journey-image img {
            transform: scale(1.07);
        }

        .journey-type,
        .journey-badge,
        .journey-price {
            position: absolute;
            z-index: 2;
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
        }

        .journey-type {
            top: 18px;
            left: 18px;
            color: #fff;
            background: rgba(16, 33, 63, 0.82);
        }

        .journey-badge {
            top: 18px;
            right: 18px;
            color: #1c325c;
            background: rgba(255, 210, 125, 0.95);
        }

        .journey-price {
            left: 18px;
            right: 18px;
            bottom: 18px;
            text-align: center;
            color: #1c325c;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 12px 28px rgba(16, 33, 63, 0.14);
        }

        .journey-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .journey-country {
            color: #9b6a2c;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 12px;
        }

        .journey-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.38rem;
            line-height: 1.35;
            margin-bottom: 16px;
        }

        .journey-title a {
            color: #1c325c;
            text-decoration: none;
        }

        .journey-title a:hover {
            color: #c5955b;
        }

        .journey-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .journey-meta span,
        .journey-schedule,
        .journey-highlights span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f4f7fb;
            color: #425466;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .journey-meta i,
        .journey-schedule i {
            color: #c5955b;
        }

        .journey-schedule {
            align-items: flex-start;
            border-radius: 18px;
            background: #f8fbff;
            margin-bottom: 16px;
            line-height: 1.7;
        }

        .journey-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        .journey-btn {
            margin-top: auto;
            width: 100%;
            border-radius: 18px;
            padding: 14px 18px;
        }

        .journey-empty {
            padding: 48px 28px;
            text-align: center;
        }

        .journey-empty h3 {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .journey-empty .empty-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .pagination-wrap {
            margin-top: 38px;
        }

        .pagination-wrap nav {
            display: flex;
            justify-content: center;
        }

        .pagination-wrap svg {
            width: 18px;
            height: 18px;
        }

        .destination-cta {
            background: linear-gradient(135deg, #0f2749 0%, #123d66 100%);
            padding: 92px 0 92px;
        }

        .cta-card {
            padding: 36px;
            margin-top: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 210, 125, 0.14), transparent 28%),
                #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .cta-card h2 {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
        }

        .cta-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .cta-btn {
            min-height: 54px;
            padding: 0 22px;
            border-radius: 18px;
        }

        .cta-btn.secondary {
            background: #f8fbff;
            color: #1c325c;
            border: 1px solid rgba(26, 54, 93, 0.12);
            box-shadow: none;
        }

        html[data-theme='dark'] .destination-overview,
        html[data-theme='dark'] .attractions-section,
        html[data-theme='dark'] .destination-results {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
        }

        html[data-theme='dark'] .destination-breadcrumb {
            background: #0b1220 !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
        }

        html[data-theme='dark'] .destination-breadcrumb .breadcrumb-item,
        html[data-theme='dark'] .destination-breadcrumb .breadcrumb-item a,
        html[data-theme='dark'] .overview-panel h2,
        html[data-theme='dark'] .sidebar-card h3,
        html[data-theme='dark'] .section-heading h2,
        html[data-theme='dark'] .results-head h2,
        html[data-theme='dark'] .journey-title a,
        html[data-theme='dark'] .journey-empty h3,
        html[data-theme='dark'] .cta-card h2,
        html[data-theme='dark'] .attraction-title,
        html[data-theme='dark'] .filters-title,
        html[data-theme='dark'] .filters-card label {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .overview-panel,
        html[data-theme='dark'] .sidebar-card,
        html[data-theme='dark'] .filters-card,
        html[data-theme='dark'] .journey-type-card,
        html[data-theme='dark'] .journey-card,
        html[data-theme='dark'] .journey-empty,
        html[data-theme='dark'] .cta-card,
        html[data-theme='dark'] .attraction-card {
            background: #111827 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        html[data-theme='dark'] .overview-summary,
        html[data-theme='dark'] .overview-body,
        html[data-theme='dark'] .section-heading p,
        html[data-theme='dark'] .results-head p,
        html[data-theme='dark'] .journey-description,
        html[data-theme='dark'] .journey-empty p,
        html[data-theme='dark'] .attraction-description,
        html[data-theme='dark'] .cta-card p,
        html[data-theme='dark'] .fact-item span {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .journey-type-content span {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .fact-item,
        html[data-theme='dark'] .journey-meta span,
        html[data-theme='dark'] .journey-schedule,
        html[data-theme='dark'] .journey-highlights span,
        html[data-theme='dark'] .destination-tags span,
        html[data-theme='dark'] .attraction-meta span,
        html[data-theme='dark'] .attraction-link,
        html[data-theme='dark'] .cta-btn.secondary {
            background: #172033 !important;
            color: var(--warm-gray) !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
        }

        html[data-theme='dark'] .fact-item strong,
        html[data-theme='dark'] .journey-price,
        html[data-theme='dark'] .reset-btn,
        html[data-theme='dark'] .filters-card .form-control,
        html[data-theme='dark'] .filters-card .form-select {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .filters-card .form-control,
        html[data-theme='dark'] .filters-card .form-select,
        html[data-theme='dark'] .reset-btn {
            background: #0f172a !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        html[data-theme='dark'] .journey-image,
        html[data-theme='dark'] .attraction-image {
            background: #0f172a !important;
        }

        html[data-theme='dark'] .journey-type {
            background: rgba(15, 23, 42, 0.88) !important;
        }

        html[data-theme='dark'] .journey-price {
            background: rgba(15, 23, 42, 0.94) !important;
        }

        html[data-theme='dark'] .pagination-wrap .page-link {
            background: #111827 !important;
            color: var(--charcoal-deep) !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        html[data-theme='dark'] .pagination-wrap .page-item.active .page-link {
            color: #0f172a !important;
        }

        @media (max-width: 1199px) {
            .overview-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .destination-hero {
                min-height: 460px;
                padding: 138px 0 72px;
            }

            .hero-stats,
            .filters-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .hero-actions,
            .cta-actions {
                flex-direction: column;
            }

            .hero-btn,
            .hero-btn-outline,
            .cta-btn,
            .filter-btn,
            .reset-btn {
                width: 100%;
            }

            .overview-panel,
            .sidebar-card,
            .filters-card,
            .journey-body,
            .attraction-body,
            .cta-card {
                padding: 22px;
            }

            .cta-card {
                text-align: center;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <section class="destination-breadcrumb">
        <div class="container">
            <nav aria-label="{{ __('Breadcrumb') }}">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">{{ __('Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.destinations.index') }}">{{ __('Destinations') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ $countryRoute }}">{{ $countryName }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $destination->display_name }}
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="destination-hero">
        <div class="container">
            <div class="destination-hero-content">
                <div class="hero-badge">
                    <i class="la la-map-marker"></i>
                    {{ $countryName }}
                </div>

                <h1 class="hero-title">{{ $destination->display_name }}</h1>
                <p class="hero-subtitle">{{ $heroSubtitle }}</p>

                <div class="hero-actions">
                    <a href="#destination-journeys" class="hero-btn">
                        <i class="la la-suitcase"></i>
                        {{ __('Browse Trips') }}
                    </a>
                    <a href="{{ route('website.contact.index') }}" class="hero-btn-outline">
                        <i class="la la-phone"></i>
                        {{ __('Plan With an Expert') }}
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['count']) }}</strong>
                        <span>{{ __('Available Journeys') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['attractions']) }}</strong>
                        <span>{{ __('Top Attractions') }}</span>
                    </div>
                    <div class="hero-stat">
                        <strong>{{ number_format($stats['featured']) }}</strong>
                        <span>{{ __('Featured Trips') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="destination-overview">
        <div class="container">
            <div class="overview-grid">
                <div class="overview-panel">
                    <div class="section-kicker">
                        <i class="la la-compass"></i>
                        {{ __('About this destination') }}
                    </div>
                    <h2>{{ __('Discover') }} {{ $destination->display_name }}</h2>

                    @if ($descriptionHtml)
                        <div class="overview-body">{!! $descriptionHtml !!}</div>
                    @else
                        <p class="overview-summary">
                            {{ $overviewText ?: $heroSubtitle }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <section class="destination-results" id="destination-journeys">
        <div class="container">
            @if ($typeCards->count())
                <div class="results-head">
                    <div>
                        <h2>{{ __('Explore :destination by trip type', ['destination' => $destination->display_name]) }}</h2>
                        <p>{{ __('Choose a section to see matching trips only for this city.') }}</p>
                    </div>
                </div>

                <div class="journey-type-grid" aria-label="{{ __('Journey types') }}">
                    @foreach ($typeCards as $typeCard)
                        <a href="{{ $typeCard['url'] }}"
                            class="journey-type-card{{ $typeCard['active'] ? ' is-active' : '' }}">
                            <img src="{{ asset($typeCard['image']) }}"
                                alt="{{ $typeCard['label'] }}"
                                class="journey-type-image"
                                loading="lazy">
                            <span class="journey-type-content">
                                <strong>{{ $typeCard['label'] }}</strong>
                                <span>{{ trans_choice(':count trip|:count trips', $typeCard['count'], ['count' => $typeCard['count']]) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="filters-card">
                <h2 class="filters-title">
                    <i class="la la-sliders-h"></i>
                    {{ __('Filter Trips in :destination', ['destination' => $destination->display_name]) }}
                </h2>

                <form action="{{ $indexRoute }}" method="GET">
                    <div class="filters-grid">
                        <div>
                            <label for="destination-search">{{ __('Search by keyword') }}</label>
                            <input id="destination-search" type="text" name="q" class="form-control"
                                value="{{ $search }}"
                                placeholder="{{ __('Search packages, tours, cruises, or highlights...') }}">
                        </div>

                        <div>
                            <label for="destination-type">{{ __('Journey Type') }}</label>
                            <select id="destination-type" name="type" class="form-select">
                                <option value="">{{ __('All Journey Types') }}</option>
                                @foreach ($typeOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected($selectedType === $option['value'])>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="filter-btn">
                            <i class="la la-search"></i>
                            {{ __('Filter Results') }}
                        </button>

                        <a href="{{ $indexRoute }}" class="reset-btn">
                            <i class="la la-undo"></i>
                            {{ __('Reset Filters') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="results-head">
                <div>
                    <h2>{{ $resultsTitle }}</h2>
                    <p>{{ __('Matching results') }}: {{ number_format($packages->total()) }}</p>
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
                                        <img src="{{ $package['image'] }}" alt="{{ $package['title'] }}" loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
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
                    <h3>{{ __('No trips found for this destination') }}</h3>
                    <p>
                        {{ __('Try adjusting your filters or contact our travel experts to create a tailor-made itinerary in :destination.', ['destination' => $destination->display_name]) }}
                    </p>
                    <div class="empty-actions">
                        <a href="{{ $indexRoute }}" class="journey-btn" style="width:auto;">
                            <i class="la la-refresh"></i>
                            {{ __('Show All Trips') }}
                        </a>
                        <a href="{{ route('website.contact.index') }}" class="reset-btn">
                            <i class="la la-phone"></i>
                            {{ __('Contact Us') }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($packages->hasPages())
                <div class="pagination-wrap">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </section>

    <section class="destination-cta">
        <div class="container">
            <div class="cta-card">
                <div>
                    <div class="section-kicker">
                        <i class="la la-gem"></i>
                        {{ __('Tailor-made planning') }}
                    </div>
                    <h2>{{ __('Need a custom itinerary for :destination?', ['destination' => $destination->display_name]) }}
                    </h2>
                    <p>
                        {{ __('Our specialists can design the right mix of stays, sightseeing, cruises, and private experiences for your travel style.') }}
                    </p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('website.tailor_made.index') }}" class="cta-btn">
                        <i class="la la-route"></i>
                        {{ __('Build My Trip') }}
                    </a>
                    <a href="{{ route('website.contact.index') }}" class="cta-btn secondary">
                        <i class="la la-envelope"></i>
                        {{ __('Talk to an Expert') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    @if ($attractions->count())
        <section class="attractions-section">
            <div class="container">
                <div class="section-heading">
                    <div class="section-kicker">
                        <i class="la la-landmark"></i>
                        {{ __('Explore Highlights') }}
                    </div>
                    <h2>{{ __('Top places to experience in :destination', ['destination' => $destination->display_name]) }}
                    </h2>
                    <p>
                        {{ __('Blend iconic landmarks, local culture, and unforgettable moments while planning your stay in :destination.', ['destination' => $destination->display_name]) }}
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ($attractions as $attraction)
                        <div class="col-lg-4 col-md-6">
                            <article class="attraction-card">
                                <div class="attraction-image">
                                    <a href="{{ $attraction['url'] }}">
                                        <img src="{{ $attraction['image'] }}" alt="{{ $attraction['title'] }}"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('website/photos/home2.webp') }}';">
                                    </a>
                                </div>
                                <div class="attraction-body">
                                    <h3 class="attraction-title">
                                        <a href="{{ $attraction['url'] }}" style="color: inherit; text-decoration: none;">
                                            {{ $attraction['title'] }}
                                        </a>
                                    </h3>
                                    <p class="attraction-description">{{ $attraction['description'] }}</p>

                                    @if ($attraction['opening_hours'])
                                        <div class="attraction-meta">
                                            <span>
                                                <i class="la la-clock"></i>
                                                {{ $attraction['opening_hours'] }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="d-flex gap-2 flex-wrap mt-auto pt-2">
                                        <a href="{{ $attraction['url'] }}" class="attraction-link">
                                            <i class="la la-eye"></i>
                                            {{ __('Explore Place') }}
                                        </a>
                                        @if ($attraction['map_url'])
                                            <a href="{{ $attraction['map_url'] }}" target="_blank" rel="noopener"
                                                class="attraction-link">
                                                <i class="la la-map"></i>
                                                {{ __('Open Map') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

 
@endsection
