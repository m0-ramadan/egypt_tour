@extends('website.layouts.master')

@section('title', ($pageTitle ?? __('Destinations')) . ' - Etro Tours')
@section('description', $overviewText ?? $heroSubtitle)
@section('keywords',
    trim(
    collect([$pageTitle ?? __('Destinations'), 'Etro Tours', 'Egypt destinations', 'travel
    experiences'])->filter()->implode(', '),
    ', ',
    ))
@section('image', $heroImage)

@section('css')
    <style>
        /* Enhanced Hero Section with Responsive Heights */
        .hero-section {
            height: 60vh;
            min-height: 500px;
            max-height: 700px;
            background: linear-gradient(rgba(28, 50, 92, 0.5), rgba(26, 75, 102, 0.6)), var(--hero-bg);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Large Desktop (1400px+) */
        @media (min-width: 1400px) {
            .hero-section {
                height: 65vh;
                max-height: 750px;
            }
        }

        /* Desktop (1200px - 1399px) */
        @media (max-width: 1399px) and (min-width: 1200px) {
            .hero-section {
                height: 60vh;
                min-height: 550px;
                max-height: 700px;
            }
        }

        /* Laptop (992px - 1199px) */
        @media (max-width: 1199px) and (min-width: 992px) {
            .hero-section {
                height: 55vh;
                min-height: 500px;
                max-height: 650px;
            }
        }

        /* Tablet Portrait (768px - 991px) */
        @media (max-width: 991px) and (min-width: 768px) {
            .hero-section {
                height: 50vh;
                min-height: 450px;
                max-height: 600px;
                background-attachment: scroll;
            }
        }

        /* Mobile Landscape (576px - 767px) */
        @media (max-width: 767px) and (min-width: 576px) {
            .hero-section {
                height: 45vh;
                min-height: 400px;
                max-height: 550px;
                background-attachment: scroll;
            }
        }

        /* Mobile Portrait (320px - 575px) */
        @media (max-width: 575px) {
            .hero-section {
                height: 40vh;
                min-height: 350px;
                max-height: 500px;
                background-attachment: scroll;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                height: 35vh;
                min-height: 300px;
                max-height: 450px;
            }
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="none"><path d="M0 10L10 0L20 10L30 0L40 10L50 0L60 10L70 0L80 10L90 0L100 10V20H0V10Z" fill="rgba(197,149,91,0.1)"/></svg>') repeat-x;
            opacity: 0.4;
            animation: wave 20s ease-in-out infinite;
        }

        @keyframes wave {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(-50px);
            }
        }

        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 100px 20px 0;
            animation: fadeInUp 1.2s ease-out;
        }

        @media (max-width: 991px) {
            .hero-content {
                padding: 90px 20px 0;
            }
        }

        @media (max-width: 767px) {
            .hero-content {
                padding: 80px 15px 0;
            }
        }

        @media (max-width: 575px) {
            .hero-content {
                padding: 70px 15px 0;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-badge {
            background: rgba(197, 149, 91, 0.9);
            color: var(--primary-navy);
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(197, 149, 91, 0.3);
        }

        @media (max-width: 575px) {
            .hero-badge {
                padding: 8px 20px;
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #ffffff 0%, #f8f0e0 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
            font-weight: 300;
            letter-spacing: 1px;
        }

        @media (max-width: 767px) {
            .hero-subtitle {
                font-size: 1.1rem;
                margin-bottom: 25px;
            }
        }

        @media (max-width: 575px) {
            .hero-subtitle {
                font-size: 1rem;
                margin-bottom: 20px;
            }
        }

        /* Breadcrumb Section */
        .breadcrumb-section {
            background: var(--pearl-luxury);
            padding: 15px 0;
            border-bottom: 1px solid rgba(197, 149, 91, 0.2);
        }

        .breadcrumb-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .breadcrumb {
            background: transparent;
            margin: 0;
            padding: 0;
        }

        .breadcrumb-item {
            color: var(--primary-navy);
            font-size: 0.95rem;
        }

        .breadcrumb-item a {
            color: var(--primary-navy);
            text-decoration: none;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .breadcrumb-item a:hover {
            color: var(--rich-gold);
        }

        .breadcrumb-icon {
            font-size: 1.1rem;
            color: var(--rich-gold);
        }

        .breadcrumb-item.active {
            color: var(--rich-gold);
            font-weight: 600;
        }

        /* Overview Section */
        .overview-section {
            background: var(--pearl-luxury);
            padding: 60px 0;
        }

        .overview-content {
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: var(--shadow-medium);
            border: 2px solid rgba(197, 149, 91, 0.1);
            transition: all 0.3s ease;
        }

        .overview-content:hover {
            box-shadow: var(--shadow-dramatic);
            border-color: rgba(197, 149, 91, 0.3);
        }

        .overview-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }

        .overview-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--gradient-gold);
            border-radius: 2px;
        }

        .overview-text {
            color: var(--charcoal-deep);
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .overview-text p {
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .overview-section {
                padding: 40px 0;
            }

            .overview-content {
                padding: 30px;
            }

            .overview-title {
                font-size: 1.8rem;
            }

            .overview-text {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .overview-content {
                padding: 25px;
            }

            .overview-title {
                font-size: 1.6rem;
            }
        }

        /* Card Area - Matching Homepage Style */
        .card-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--primary-navy);
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-gold);
            border-radius: 2px;
        }

        .section-subtitle {
            color: var(--warm-gray);
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 50px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .card-area {
                padding: 60px 0;
            }

            .section-subtitle {
                font-size: 1.1rem;
                margin-bottom: 40px;
            }
        }

        .country-filters {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .country-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(197, 149, 91, 0.25);
            color: var(--primary-navy);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 24px rgba(28, 50, 92, 0.06);
        }

        .country-filter:hover,
        .country-filter.active {
            background: var(--gradient-gold);
            color: var(--primary-navy);
            transform: translateY(-2px);
            border-color: rgba(197, 149, 91, 0.4);
        }

        @media (max-width: 480px) {
            .card-area {
                padding: 50px 0;
            }

            .section-subtitle {
                font-size: 1rem;
                margin-bottom: 30px;
            }
        }

        /* Cruise Card - Enhanced */
        .cruise-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            transition: all 0.4s ease;
            border: 2px solid rgba(197, 149, 91, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .cruise-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-dramatic);
            border-color: var(--rich-gold);
        }

        .cruise-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4/3;
            flex-shrink: 0;
            background: linear-gradient(45deg, var(--primary-navy), var(--rich-gold));
        }

        .city-country-badge {
            position: absolute;
            top: 18px;
            left: 18px;
            z-index: 2;
            background: rgba(28, 50, 92, 0.82);
            color: white;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
        }

        .cruise-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.6s ease;
            opacity: 0.9;
        }

        .cruise-card:hover .cruise-img {
            transform: scale(1.1);
            opacity: 1;
        }

        .cruise-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(28, 50, 92, 0.7) 0%, rgba(197, 149, 91, 0.6) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s ease;
            backdrop-filter: blur(2px);
        }

        .cruise-card:hover .cruise-overlay {
            opacity: 1;
        }

        .overlay-content {
            color: white;
            text-align: center;
            transform: translateY(20px);
            transition: transform 0.4s ease;
        }

        .cruise-card:hover .overlay-content {
            transform: translateY(0);
        }

        .overlay-content i {
            display: block;
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .overlay-content span {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cruise-content {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .cruise-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-navy);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .cruise-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .cruise-title a:hover {
            color: var(--rich-gold);
        }

        .cruise-description {
            margin-bottom: 20px;
            flex: 1;
        }

        .cruise-description p {
            color: var(--warm-gray);
            line-height: 1.6;
            margin: 0;
            font-size: 1rem;
        }

        .destination-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
            margin-bottom: 18px;
            color: var(--primary-navy);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .destination-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .destination-meta i {
            color: var(--rich-gold);
        }

        .cruise-footer {
            padding-top: 20px;
            border-top: 1px solid rgba(197, 149, 91, 0.2);
            margin-top: auto;
        }

        .btn-cruise {
            background: var(--gradient-gold);
            color: var(--primary-navy);
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-gold);
            width: 100%;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .btn-cruise::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-cruise:hover::before {
            left: 100%;
        }

        .btn-cruise:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(197, 149, 91, 0.4);
            color: var(--primary-navy);
        }

        @media (max-width: 768px) {
            .cruise-content {
                padding: 25px;
            }

            .cruise-title {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .cruise-content {
                padding: 20px;
            }

            .cruise-title {
                font-size: 1.2rem;
            }

            .cruise-description p {
                font-size: 0.95rem;
            }
        }

        /* Why Choose Section - Matching Homepage */
        .why-choose-section {
            background: var(--pearl-luxury);
            padding: 80px 0;
            position: relative;
            border-top: 1px solid rgba(197, 149, 91, 0.2);
        }

        .choose-card {
            background: white;
            border-radius: 25px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: var(--shadow-medium);
            border: 2px solid transparent;
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .choose-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-gold);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .choose-card:hover::before {
            transform: scaleX(1);
        }

        .choose-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-dramatic);
            border-color: var(--rich-gold);
        }

        .choose-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.2rem;
            color: white;
            box-shadow: var(--shadow-gold);
            transition: all 0.3s ease;
        }

        .choose-card:hover .choose-icon {
            transform: scale(1.1);
        }

        .choose-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .choose-features {
            text-align: left;
        }

        .feature-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(197, 149, 91, 0.2);
            color: var(--warm-gray);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        /* CTA Section - Matching Homepage */
        .luxury-cta-section {
            background: var(--gradient-hero);
            padding: 70px 0;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(197, 149, 91, 0.3);
        }

        .luxury-cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="none"><path d="M0 10L10 0L20 10L30 0L40 10L50 0L60 10L70 0L80 10L90 0L100 10V20H0V10Z" fill="rgba(197,149,91,0.1)"/></svg>') repeat-x;
            opacity: 0.3;
        }

        .luxury-cta-content {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            padding: 50px;
            border: 1px solid rgba(197, 149, 91, 0.3);
            box-shadow: var(--shadow-dramatic);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }

        .cta-icon-container {
            width: 80px;
            height: 80px;
            background: var(--gradient-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--primary-navy);
            box-shadow: var(--shadow-gold);
            flex-shrink: 0;
        }

        .cta-content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }

        .cta-text-content {
            flex: 1;
            min-width: 300px;
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
        }

        .trust-features {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .trust-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            font-size: 0.95rem;
        }

        .trust-feature i {
            color: var(--rich-gold);
            font-size: 1.1rem;
        }

        .luxury-cta-btn {
            background: var(--gradient-gold);
            color: var(--primary-navy);
            padding: 16px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-gold);
            white-space: nowrap;
        }

        .luxury-cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(197, 149, 91, 0.4);
            color: var(--primary-navy);
        }

        /* Mobile WhatsApp Button */
        .fixed-mobile-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 768px) {
            .fixed-mobile-btn {
                display: block;
            }
        }

        .mobile-enquiry-btn {
            background: #25d366;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .mobile-enquiry-btn:hover {
            background: #20b859;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
            color: white;
        }

        .mobile-enquiry-btn i {
            font-size: 1.3rem;
        }

        .pagination-wrap {
            margin-top: 40px;
        }

        .pagination-wrap nav {
            display: flex;
            justify-content: center;
        }

        .pagination-wrap svg {
            width: 18px;
            height: 18px;
        }

        @media (max-width: 768px) {
            .luxury-cta-content {
                padding: 40px;
            }

            .cta-title {
                font-size: 1.8rem;
            }

            .cta-content-wrapper {
                flex-direction: column;
                text-align: center;
            }

            .trust-features {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .luxury-cta-section {
                padding: 50px 0;
            }

            .luxury-cta-content {
                padding: 30px;
            }

            .cta-title {
                font-size: 1.6rem;
            }

            .cta-subtitle {
                font-size: 1rem;
            }

            .trust-feature {
                font-size: 0.9rem;
            }

            .luxury-cta-btn {
                padding: 14px 25px;
                font-size: 1rem;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <div class="breadcrumb-container">
                <nav aria-label="{{ __('Breadcrumb') }}">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('website.home') }}">
                                <i class="la la-home breadcrumb-icon"></i>{{ __('Home') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $selectedCountry?->display_name ?: __('Destinations') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <section class="hero-section" style="--hero-bg:url('{{ $heroImage }}')">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="la la-star"></i>
                    {{ $heroBadge }}
                </div>
                <h1 class="hero-title">{{ $heroTitle }}</h1>
                <p class="hero-subtitle">{{ $heroSubtitle }}</p>
            </div>
        </div>
    </section>

    <section class="overview-section">
        <div class="container">
            <div class="overview-content">
                <h2 class="overview-title">{{ $overviewTitle }}</h2>
                <div class="overview-text">
                    <p>{{ $overviewText }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="card-area">
        <div class="container">
            <h2 class="section-title">{{ $sectionTitle }}</h2>
            <p class="section-subtitle">{{ $sectionSubtitle }}</p>

            @if ($countries->count())
                <div class="country-filters">
                    <a href="{{ route('website.destinations.index') }}"
                        class="country-filter {{ $selectedCountry ? '' : 'active' }}">
                        {{ __('Destinations') }}
                    </a>
                    @foreach ($countries as $country)
                        <a href="{{ route('website.destinations.index', ['country' => $country->slug]) }}"
                            class="country-filter {{ $selectedCountry?->id === $country->id ? 'active' : '' }}">
                            {{ $country->display_name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="row">
                @forelse ($destinations as $destination)
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="cruise-card">
                            <div class="cruise-image">
                                <div class="city-country-badge">{{ $destination['country'] }}</div>
                                <a href="{{ $destination['url'] }}">
                                    <img src="{{ $destination['image'] }}" alt="{{ $destination['title'] }}"
                                        class="cruise-img" loading="lazy">
                                    <div class="cruise-overlay">
                                        <div class="overlay-content">
                                            <i class="la la-eye"></i>
                                            <span>{{ __('Discover') }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="cruise-content">
                                <h3 class="cruise-title">
                                    <a href="{{ $destination['url'] }}">{{ $destination['title'] }}</a>
                                </h3>
                                <div class="destination-meta">
                                    <span><i class="la la-map-marker"></i>{{ $destination['attractions_count'] }}
                                        {{ __('Sites') }}</span>
                                    <span><i class="la la-suitcase"></i>{{ $destination['packages_count'] }}
                                        {{ __('Trips') }}</span>
                                </div>
                                <div class="cruise-description">
                                    <p>{{ $destination['description'] }}</p>
                                </div>
                                <div class="cruise-footer">
                                    <a href="{{ $destination['url'] }}" class="btn-cruise">
                                        {{ __('Discover') }} <i class="las la-angle-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state">
                            {{ __('No active destinations found. Add active cities from the admin panel.') }}
                        </div>
                    </div>
                @endforelse
            </div>

            @if (method_exists($destinations, 'links') && $destinations->hasPages())
                <div class="pagination-wrap">
                    {{ $destinations->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ request()->root() }}/website/js/new/jquery.min.js"></script>
    <script src="{{ request()->root() }}/website/js/new/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const offsetTop = target.offsetTop - 100;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Card hover effects
            document.querySelectorAll('.cruise-card, .choose-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Loading animation
            window.addEventListener('load', () => {
                document.body.classList.add('loaded');
            });
        });
    </script>
@endsection
