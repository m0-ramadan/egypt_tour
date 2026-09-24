@extends('website.layouts.master')

@section('title', $seoTitle ?: __('Egypt Travel Tips & Visitor Guide - Egypt Tour Pro'))
@section('description',
    $pageExcerpt ?:
    __('Essential Egypt travel tips: visa rules, weather, money & tipping, dress
    code, safety, Nile cruise advice, and packing lists by Egypt Tour Pro.'))
@section('keywords',
    'Egypt travel tips, Egypt visitor guide, Egypt Tour Pro tips, Egypt visa rules, Egypt tipping
    guide, Nile cruise tips, packing for Egypt, Cairo travel tips')
@section('image', $heroImage)

@section('css')
    <style>
        /* Travel Tips Hero */
        .tips-hero {
            position: relative;
            margin-top: -85px;
            padding: 150px 0 90px;
            background: linear-gradient(rgba(17, 17, 17, 0.76), rgba(28, 28, 28, 0.74)),
                url('{{ $heroImage }}') center/cover no-repeat;
            color: #fff;
            overflow: hidden;
        }

        .tips-hero-content {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .tips-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(12px);
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 24px;
        }

        .tips-badge i {
            color: var(--etp-orange-400, #FB923C);
            font-size: 1.2rem;
        }

        .tips-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5.5vw, 4.2rem);
            line-height: 1.1;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .tips-subtitle {
            max-width: 780px;
            margin: 0 auto 30px;
            font-size: 1.12rem;
            line-height: 1.85;
            color: rgba(255, 255, 255, 0.92);
        }

        .tips-quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-top: 36px;
        }

        .quick-stat-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            padding: 18px 16px;
            text-align: center;
            transition: transform 0.25s ease;
        }

        .quick-stat-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.16);
        }

        .quick-stat-card i {
            font-size: 2rem;
            color: var(--etp-orange-400, #FB923C);
            margin-bottom: 8px;
            display: block;
        }

        .quick-stat-card strong {
            display: block;
            font-size: 1.05rem;
            color: #fff;
            margin-bottom: 4px;
        }

        .quick-stat-card span {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.82);
        }

        /* Nav Pills Sticky Bar */
        .tips-sticky-nav {
            position: sticky;
            top: 80px;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid rgba(43, 43, 43, 0.08);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            padding: 12px 0;
        }

        .tips-nav-list {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 4px;
            scrollbar-width: thin;
        }

        .tips-nav-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 999px;
            background: #f4f6f8;
            color: #2b2b2b;
            font-weight: 600;
            font-size: 0.92rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .tips-nav-item:hover,
        .tips-nav-item.active {
            background: var(--etp-orange-500, #F36B0A);
            color: #fff;
            border-color: var(--etp-orange-500, #F36B0A);
        }

        /* Main Section Wrapper */
        .tips-section {
            padding: 70px 0 90px;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .tips-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 40px;
            border: 1px solid rgba(43, 43, 43, 0.08);
            box-shadow: 0 16px 40px rgba(28, 28, 28, 0.06);
            margin-bottom: 40px;
        }

        .tips-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.85rem;
            color: #2b2b2b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 2px solid var(--etp-orange-400, #FB923C);
            padding-bottom: 12px;
        }

        .tips-card-title i {
            color: var(--etp-orange-500, #F36B0A);
            font-size: 2.2rem;
        }

        /* Brand Spotlight Box */
        .brand-spotlight {
            background: linear-gradient(135deg, #1c1c1c 0%, #2b2b2b 100%);
            color: #ffffff;
            border-radius: 24px;
            padding: 36px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(243, 107, 10, 0.3);
        }

        .brand-spotlight::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(243, 107, 10, 0.25), transparent 70%);
            border-radius: 50%;
        }

        .brand-spotlight h3 {
            color: var(--etp-orange-400, #FB923C);
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            margin-bottom: 14px;
        }

        .brand-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 24px;
        }

        .brand-feature-item {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .brand-feature-item i {
            color: var(--etp-orange-400, #FB923C);
            font-size: 1.6rem;
            margin-bottom: 10px;
            display: block;
        }

        .brand-feature-item h4 {
            color: #ffffff;
            font-size: 1.05rem;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .brand-feature-item p {
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.92rem;
            line-height: 1.6;
            margin: 0;
        }

        .brand-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 26px;
        }

        .btn-brand-primary {
            background: var(--etp-orange-500, #F36B0A);
            color: #ffffff !important;
            padding: 12px 26px;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
        }

        .btn-brand-primary:hover {
            background: #d95600;
            transform: translateY(-2px);
        }

        .btn-brand-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff !important;
            padding: 12px 26px;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.25s ease;
        }

        .btn-brand-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Content Grids & Tables */
        .tips-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }

        .info-box {
            background: #f8fbff;
            border: 1px solid rgba(43, 43, 43, 0.08);
            border-radius: 20px;
            padding: 24px;
            transition: border-color 0.2s ease;
        }

        .info-box:hover {
            border-color: var(--etp-orange-400, #FB923C);
        }

        .info-box h4 {
            font-size: 1.15rem;
            color: #2b2b2b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .info-box h4 i {
            color: var(--etp-orange-500, #F36B0A);
            font-size: 1.4rem;
        }

        .info-box p,
        .info-box ul {
            font-size: 0.96rem;
            color: #555;
            line-height: 1.75;
            margin: 0;
        }

        .info-box ul {
            padding-inline-start: 18px;
        }

        .info-box ul li {
            margin-bottom: 8px;
        }

        /* Tipping Table */
        .custom-table-wrapper {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid rgba(43, 43, 43, 0.1);
            margin-top: 18px;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: start;
        }

        .custom-table th {
            background: #2b2b2b;
            color: #ffffff;
            padding: 14px 18px;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .custom-table td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(43, 43, 43, 0.08);
            font-size: 0.94rem;
            color: #333;
        }

        .custom-table tr:nth-child(even) td {
            background: #fdfdfd;
        }

        /* Checklist */
        .checklist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fdfdfd;
            border: 1px solid rgba(43, 43, 43, 0.08);
            padding: 16px 18px;
            border-radius: 16px;
        }

        .checklist-item i {
            color: var(--etp-orange-500, #F36B0A);
            font-size: 1.4rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .checklist-item strong {
            display: block;
            color: #2b2b2b;
            font-size: 0.98rem;
            margin-bottom: 2px;
        }

        .checklist-item span {
            font-size: 0.88rem;
            color: #666;
            line-height: 1.5;
        }

        /* FAQ Accordion */
        .tips-faq-item {
            border: 1px solid rgba(43, 43, 43, 0.08);
            border-radius: 18px;
            margin-bottom: 14px;
            overflow: hidden;
            background: #fff;
        }

        .tips-faq-header {
            width: 100%;
            background: #f8fbff;
            border: none;
            padding: 18px 24px;
            text-align: start;
            font-weight: 700;
            font-size: 1.05rem;
            color: #2b2b2b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .tips-faq-header i {
            color: var(--etp-orange-500, #F36B0A);
            transition: transform 0.3s ease;
        }

        .tips-faq-body {
            padding: 20px 24px;
            font-size: 0.98rem;
            line-height: 1.8;
            color: #555;
            border-top: 1px solid rgba(43, 43, 43, 0.06);
        }

        /* CTA Banner */
        .tips-cta-banner {
            background: linear-gradient(135deg, var(--etp-orange-500, #F36B0A) 0%, #d95600 100%);
            color: #fff;
            border-radius: 28px;
            padding: 50px 36px;
            text-align: center;
            margin-top: 50px;
            box-shadow: 0 20px 48px rgba(243, 107, 10, 0.25);
        }

        .tips-cta-banner h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #fff;
            margin-bottom: 14px;
        }

        .tips-cta-banner p {
            max-width: 680px;
            margin: 0 auto 28px;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.94);
            line-height: 1.8;
        }

        .tips-cta-banner .btn-cta {
            background: #ffffff;
            color: var(--etp-orange-600, #D95600) !important;
            font-weight: 800;
            padding: 14px 36px;
            border-radius: 999px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.05rem;
            transition: all 0.25s ease;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
        }

        .tips-cta-banner .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.25);
            background: #fff8f4;
        }

        /* Dark Mode Support */
        html[data-theme='dark'] .tips-sticky-nav {
            background: #111111;
            border-color: rgba(170, 163, 155, 0.16);
        }

        html[data-theme='dark'] .tips-nav-item {
            background: #1c1c1c;
            color: #efe9e2;
        }

        html[data-theme='dark'] .tips-section {
            background: #111111;
        }

        html[data-theme='dark'] .tips-card {
            background: #181818;
            border-color: rgba(170, 163, 155, 0.16);
        }

        html[data-theme='dark'] .tips-card-title {
            color: #efe9e2;
        }

        html[data-theme='dark'] .info-box {
            background: #1c1c1c;
            border-color: rgba(170, 163, 155, 0.16);
        }

        html[data-theme='dark'] .info-box h4 {
            color: #efe9e2;
        }

        html[data-theme='dark'] .info-box p,
        html[data-theme='dark'] .info-box ul {
            color: #ccc;
        }

        html[data-theme='dark'] .custom-table th {
            background: #111;
            color: #fff;
        }

        html[data-theme='dark'] .custom-table td {
            color: #ddd;
            border-color: rgba(170, 163, 155, 0.12);
        }

        html[data-theme='dark'] .custom-table tr:nth-child(even) td {
            background: #1a1a1a;
        }

        html[data-theme='dark'] .checklist-item {
            background: #1c1c1c;
            border-color: rgba(170, 163, 155, 0.16);
        }

        html[data-theme='dark'] .checklist-item strong {
            color: #fff;
        }

        html[data-theme='dark'] .checklist-item span {
            color: #aaa;
        }

        html[data-theme='dark'] .tips-faq-item {
            background: #181818;
            border-color: rgba(170, 163, 155, 0.16);
        }

        html[data-theme='dark'] .tips-faq-header {
            background: #1c1c1c;
            color: #fff;
        }

        html[data-theme='dark'] .tips-faq-body {
            color: #ccc;
            border-color: rgba(170, 163, 155, 0.12);
        }

        html[dir='rtl'] .tips-hero-content,
        html[dir='rtl'] .tips-card {
            text-align: right;
        }

        @media (max-width: 767px) {
            .tips-hero {
                padding: 135px 0 70px;
            }

            .tips-card {
                padding: 26px 20px;
                border-radius: 22px;
            }

            .brand-spotlight {
                padding: 24px 20px;
            }

            .tips-cta-banner {
                padding: 36px 20px;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="tips-hero">
        <div class="container">
            <div class="tips-hero-content">
                <div class="tips-badge">
                    <i class="la la-compass"></i>
                    <span>{{ __('Egypt Tour Pro | Essential Visitor Guide') }}</span>
                </div>
                <h1 class="tips-title">{{ __('Egypt Travel Tips & Complete Visitor Guide') }}</h1>
                <p class="tips-subtitle">
                    {{ __('Everything you need to know before visiting Egypt: visa procedures, ideal weather, currency & tipping guidelines, dress code, safety, Nile cruises, and expert travel insights curated by Egypt Tour Pro.') }}
                </p>

                <!-- Quick Stats Pills -->
                <div class="tips-quick-stats">
                    <div class="quick-stat-card">
                        <i class="la la-sun"></i>
                        <strong>{{ __('Best Season') }}</strong>
                        <span>{{ __('Oct – Apr (Pleasant)') }}</span>
                    </div>
                    <div class="quick-stat-card">
                        <i class="la la-passport"></i>
                        <strong>{{ __('Visa Entry') }}</strong>
                        <span>{{ __('e-Visa & Arrival (70+ Countries)') }}</span>
                    </div>
                    <div class="quick-stat-card">
                        <i class="la la-money-bill-wave"></i>
                        <strong>{{ __('Currency') }}</strong>
                        <span>{{ __('EGP, USD & EUR Accepted') }}</span>
                    </div>
                    <div class="quick-stat-card">
                        <i class="la la-headset"></i>
                        <strong>{{ __('24/7 Support') }}</strong>
                        <span>{{ __('Egypt Tour Pro Local Team') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sticky Navigation Bar -->
    <div class="tips-sticky-nav">
        <div class="container">
            <div class="tips-nav-list">
                <a href="#about-agency" class="tips-nav-item"><i class="la la-star"></i>
                    {{ __('About Egypt Tour Pro') }}</a>
                <a href="#visa-entry" class="tips-nav-item"><i class="la la-passport"></i> {{ __('Visa & Entry') }}</a>
                <a href="#weather-season" class="tips-nav-item"><i class="la la-cloud-sun"></i>
                    {{ __('Weather & Seasons') }}</a>
                <a href="#money-tipping" class="tips-nav-item"><i class="la la-wallet"></i> {{ __('Money & Tipping') }}</a>
                <a href="#dress-culture" class="tips-nav-item"><i class="la la-tshirt"></i>
                    {{ __('Dress & Etiquette') }}</a>
                <a href="#nile-cruises" class="tips-nav-item"><i class="la la-ship"></i> {{ __('Nile Cruise Tips') }}</a>
                <a href="#health-safety" class="tips-nav-item"><i class="la la-shield-alt"></i>
                    {{ __('Health & Safety') }}</a>
                <a href="#packing-list" class="tips-nav-item"><i class="la la-suitcase"></i>
                    {{ __('Packing Checklist') }}</a>
                <a href="#faq-section" class="tips-nav-item"><i class="la la-question-circle"></i> {{ __('FAQ') }}</a>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <section class="tips-section">
        <div class="container">

            <!-- Section 1: About Egypt Tour Pro -->
            <div id="about-agency" class="tips-card">
                <div class="brand-spotlight">
                    <h3><i class="la la-award"></i> {{ __('Why Travel With Egypt Tour Pro?') }}</h3>
                    <p style="font-size: 1.05rem; line-height: 1.8; color: rgba(255,255,255,0.92);">
                        {{ __('Egypt Tour Pro is a leading licensed Egyptian travel agency specialized in private luxury packages, tailor-made itineraries, and deluxe Nile cruises across Cairo, Luxor, Aswan, Hurghada, Sharm El Sheikh, Alexandria, and Siwa Oasis. We eliminate stress and middleman costs to offer you an authentic, transparent, and unforgettable Egyptian travel experience.') }}
                    </p>

                    <div class="brand-features-grid">
                        <div class="brand-feature-item">
                            <i class="la la-user-tie"></i>
                            <h4>{{ __('Licensed Egyptologist Guides') }}</h4>
                            <p>{{ __('Private, highly knowledgeable English and multilingual guides dedicated exclusively to your group.') }}
                            </p>
                        </div>
                        <div class="brand-feature-item">
                            <i class="la la-shuttle-van"></i>
                            <h4>{{ __('VIP Private Transfers') }}</h4>
                            <p>{{ __('Modern air-conditioned private vehicles with professional drivers for door-to-door comfort.') }}
                            </p>
                        </div>
                        <div class="brand-feature-item">
                            <i class="la la-glass-cheers"></i>
                            <h4>{{ __('Curated Nile Cruises') }}</h4>
                            <p>{{ __('Handpicked 5-star deluxe Nile cruise ships and boutique Dahabiyas with prime riverfront cabins.') }}
                            </p>
                        </div>
                        <div class="brand-feature-item">
                            <i class="la la-hand-holding-usd"></i>
                            <h4>{{ __('Transparent Pricing') }}</h4>
                            <p>{{ __('No hidden fees, no shopping traps. Clear inclusions with 100% money-value guarantee.') }}
                            </p>
                        </div>
                    </div>

                    <div class="brand-actions">
                        <a href="{{ route('website.travel_packages.index') }}" class="btn-brand-primary">
                            <i class="la la-suitcase"></i> {{ __('Explore Tour Packages') }}
                        </a>
                        <a href="{{ route('website.nile_cruises.index') }}" class="btn-brand-secondary">
                            <i class="la la-ship"></i> {{ __('Browse Nile Cruises') }}
                        </a>
                        <a href="{{ route('website.tailor_made.index') }}" class="btn-brand-secondary">
                            <i class="la la-magic"></i> {{ __('Customize Your Trip') }}
                        </a>
                    </div>
                </div>

                <!-- Text Body Content from DB if available -->
                @if (!empty($pageBody))
                    <div class="static-page-body" style="margin-top: 30px;">
                        {!! $pageBody !!}
                    </div>
                @endif
            </div>

            <!-- Section 2: Visa & Entry Requirements -->
            <div id="visa-entry" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-passport"></i>
                    {{ __('Visa & Entry Requirements to Egypt') }}
                </h2>
                <p style="font-size: 1.04rem; line-height: 1.8; color: #444;">
                    {{ __('Getting your visa for Egypt is simple and straightforward for citizens of over 70 countries including North America, Europe, Australia, and GCC nations.') }}
                </p>

                <div class="tips-grid-2">
                    <div class="info-box">
                        <h4><i class="la la-globe"></i> {{ __('Option A: Official e-Visa Portal') }}</h4>
                        <p>{{ __('Apply online before departure through the official Egyptian Ministry of Interior portal. Your e-Visa will be issued electronically within 3 to 7 business days.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-plane-arrival"></i> {{ __('Option B: Visa on Arrival') }}</h4>
                        <p>{{ __('Eligible passport holders can purchase a single-entry 30-day tourist visa stamp at bank kiosks located inside major airport arrival halls (Cairo, Luxor, Hurghada, Sharm) for $25 USD cash.') }}
                        </p>
                    </div>
                </div>

                <div class="info-box"
                    style="margin-top: 20px; background: #fff8f4; border-color: rgba(243, 107, 10, 0.25);">
                    <h4 style="color: var(--etp-orange-600, #D95600);"><i class="la la-exclamation-triangle"></i>
                        {{ __('Important Entry Checkpoints:') }}</h4>
                    <ul>
                        <li><strong>{{ __('Passport Validity:') }}</strong>
                            {{ __('Your passport must be valid for at least 6 months beyond your scheduled date of entry into Egypt.') }}
                        </li>
                        <li><strong>{{ __('Arrival Meet & Greet by Egypt Tour Pro:') }}</strong>
                            {{ __('When booking with Egypt Tour Pro, our representative meets you inside the terminal before passport control to assist with visa procedures and baggage.') }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Section 3: Weather & Best Time to Visit -->
            <div id="weather-season" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-cloud-sun"></i>
                    {{ __('Best Time to Visit & Weather Guide') }}
                </h2>
                <p style="font-size: 1.04rem; line-height: 1.8; color: #444;">
                    {{ __('Egypt is a year-round destination, but temperatures vary significantly between seasons and geographical regions (Cairo vs Upper Egypt vs Red Sea).') }}
                </p>

                <div class="custom-table-wrapper">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>{{ __('Season') }}</th>
                                <th>{{ __('Months') }}</th>
                                <th>{{ __('Weather Overview') }}</th>
                                <th>{{ __('Recommended Regions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>{{ __('Peak Season') }}</strong></td>
                                <td>{{ __('Oct – Apr') }}</td>
                                <td>{{ __('Sunny & Mild (18°C – 28°C). Cool desert nights.') }}</td>
                                <td>{{ __('Cairo, Pyramids, Luxor, Aswan & Nile Cruises') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Shoulder Season') }}</strong></td>
                                <td>{{ __('May & Sept') }}</td>
                                <td>{{ __('Warm to Hot (28°C – 35°C). Fewer crowds.') }}</td>
                                <td>{{ __('Nile Valley tours early morning, Red Sea Coast') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Summer Season') }}</strong></td>
                                <td>{{ __('Jun – Aug') }}</td>
                                <td>{{ __('Hot in South (35°C – 42°C). Coastal breezes.') }}</td>
                                <td>{{ __('Hurghada, Sharm El Sheikh, Dahab & Resort Cruises') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tips-grid-2" style="margin-top: 24px;">
                    <div class="info-box">
                        <h4><i class="la la-fire"></i> {{ __('Sun Protection in Upper Egypt') }}</h4>
                        <p>{{ __('In Luxor and Aswan, temperatures peak in the afternoon. All Egypt Tour Pro temple visits are scheduled during comfortable morning or sunset hours to avoid the midday sun.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-snowflake"></i> {{ __('Cool Winter Nights') }}</h4>
                        <p>{{ __('Winter nights (December through February) in Cairo and on Nile cruise decks can drop to 10°C (50°F). Remember to pack a warm jacket or sweater for evening strolls.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 4: Money, Currency & Tipping (Baksheesh) -->
            <div id="money-tipping" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-wallet"></i>
                    {{ __('Currency, Payments & Tipping (Baksheesh) Guide') }}
                </h2>

                <div class="tips-grid-2">
                    <div class="info-box">
                        <h4><i class="la la-coins"></i> {{ __('Egyptian Pound (EGP / LE)') }}</h4>
                        <p>{{ __('The official currency is the Egyptian Pound (EGP). Foreign currencies like USD, EUR, and GBP are widely appreciated for major tours, tipping, and hotel bills, but local currency is best for small markets.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-credit-card"></i> {{ __('Cards vs Cash') }}</h4>
                        <p>{{ __('Major credit cards (Visa & MasterCard) are accepted at hotels, high-end restaurants, and official ticket offices. Keep small cash notes for local markets, tipping, and restrooms.') }}
                        </p>
                    </div>
                </div>

                <h3
                    style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #2b2b2b; margin-top: 32px; margin-bottom: 16px;">
                    <i class="la la-hand-holding-heart" style="color: var(--etp-orange-500, #F36B0A);"></i>
                    {{ __('Recommended Tipping (Baksheesh) Matrix') }}
                </h3>
                <p style="font-size: 0.98rem; color: #555; line-height: 1.7;">
                    {{ __('Tipping is a customary part of Egyptian hospitality culture. Below is a realistic guideline for voluntary tipping based on great service:') }}
                </p>

                <div class="custom-table-wrapper">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>{{ __('Service Provider') }}</th>
                                <th>{{ __('Suggested Tip Amount') }}</th>
                                <th>{{ __('Notes / Frequency') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>{{ __('Private Tour Guide') }}</strong></td>
                                <td>$15 – $25 USD {{ __('per day (per group)') }}</td>
                                <td>{{ __('Given at the end of each tour day based on satisfaction') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Private Vehicle Driver') }}</strong></td>
                                <td>$10 – $15 USD {{ __('per day (per group)') }}</td>
                                <td>{{ __('Handed to the driver upon completion of transfers/tours') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Nile Cruise Crew') }}</strong></td>
                                <td>$8 – $12 USD {{ __('per person / night') }}</td>
                                <td>{{ __('Left in a collective tip envelope at reception upon check-out') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Hotel Porters & Bellboys') }}</strong></td>
                                <td>50 – 100 EGP {{ __('($1 – $2 USD) per luggage') }}</td>
                                <td>{{ __('Paid directly after luggage delivery') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Restaurant Waiters') }}</strong></td>
                                <td>10% – 15% {{ __('of total bill') }}</td>
                                <td>{{ __('Often added voluntarily if not included in service fee') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 5: Clothing, Dress Code & Cultural Etiquette -->
            <div id="dress-culture" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-tshirt"></i>
                    {{ __('Clothing, Dress Code & Cultural Etiquette') }}
                </h2>

                <div class="tips-grid-2">
                    <div class="info-box">
                        <h4><i class="la la-landmark"></i> {{ __('Sightseeing at Temples & Pyramids') }}</h4>
                        <p>{{ __('Wear comfortable, breathable cotton or linen clothing. Smart casual shirts, t-shirts, knee-length shorts, or lightweight trousers are ideal. Comfortable walking shoes or sneakers are mandatory for unpaved archaeological sites.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-mosque"></i> {{ __('Visiting Mosques & Religious Sites') }}</h4>
                        <p>{{ __('Modest dress is required when entering active places of worship. Men should cover shoulders and knees. Women should wear loose clothes covering shoulders, chest, and knees, plus carry a light scarf for head coverage.') }}
                        </p>
                    </div>
                </div>

                <div class="tips-grid-2" style="margin-top: 20px;">
                    <div class="info-box">
                        <h4><i class="la la-umbrella-beach"></i> {{ __('Red Sea Resorts & Nile Cruise Decks') }}</h4>
                        <p>{{ __('Swimwear and shorts are completely fine around resort swimming pools, beaches, and Nile cruise sun decks. Smart casual wear is recommended for dinner inside cruise dining rooms.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-camera"></i> {{ __('Photography & Drone Etiquette') }}</h4>
                        <p>{{ __('Photography with smartphones is free at almost all sites and museums. Professional cameras may require a small ticket. Note: Drones are strictly illegal in Egypt without prior military permits.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 6: Nile Cruise Essential Tips -->
            <div id="nile-cruises" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-ship"></i>
                    {{ __('Essential Tips for Egypt Nile Cruises') }}
                </h2>

                <p style="font-size: 1.04rem; line-height: 1.8; color: #444;">
                    {{ __('A Nile Cruise between Luxor and Aswan is the highlight of any Egyptian holiday. Follow these expert recommendations from Egypt Tour Pro to enjoy a seamless voyage:') }}
                </p>

                <div class="tips-grid-2">
                    <div class="info-box">
                        <h4><i class="la la-compass"></i> {{ __('Choosing Your Cruise Itinerary') }}</h4>
                        <ul>
                            <li><strong>{{ __('4 Nights (Luxor to Aswan):') }}</strong>
                                {{ __('Covers Karnak, Valley of the Kings, Edfu, Kom Ombo, and Philae Temple at a relaxed pace.') }}
                            </li>
                            <li><strong>{{ __('3 Nights (Aswan to Luxor):') }}</strong>
                                {{ __('Ideal for travelers with tight schedules wanting key highlights.') }}</li>
                            <li><strong>{{ __('Dahabiya Sailing:') }}</strong>
                                {{ __('Boutique 4-to-10 cabin luxury wooden sailing boats for an intimate, peaceful journey.') }}
                            </li>
                        </ul>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-utensils"></i> {{ __('Meals & Daily Schedule') }}</h4>
                        <ul>
                            <li>{{ __('Nile cruises include Full Board (Breakfast, Lunch, and Dinner served buffet or set menu style).') }}
                            </li>
                            <li>{{ __('Excursions usually take place in the early morning to avoid midday heat and maximize sailing time.') }}
                            </li>
                            <li>{{ __('Tea time is served on the sun deck daily while sailing between towns.') }}</li>
                        </ul>
                    </div>
                </div>

                <div style="margin-top: 24px; text-align: center;">
                    <a href="{{ route('website.nile_cruises.index') }}" class="btn-brand-primary"
                        style="padding: 14px 32px; font-size: 1rem;">
                        <i class="la la-ship"></i> {{ __('View All Egypt Tour Pro Nile Cruise Packages') }}
                    </a>
                </div>
            </div>

            <!-- Section 7: Health, Safety & Food -->
            <div id="health-safety" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-shield-alt"></i>
                    {{ __('Health, Safety & Dining Essentials') }}
                </h2>

                <div class="tips-grid-2">
                    <div class="info-box">
                        <h4><i class="la la-tint"></i> {{ __('Drinking Water') }}</h4>
                        <p>{{ __('Do not drink tap water in Egypt. Always use sealed bottled water or filtered water for drinking and brushing teeth. Egypt Tour Pro private vehicles carry cold bottled water for guests daily.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-user-shield"></i> {{ __('Tourist Safety & Security') }}</h4>
                        <p>{{ __('Egypt is extremely safe for international tourists, solo female travelers, and families. Tourist police officers are stationed across all major monuments, hotels, and highways to guarantee peace of mind.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-utensils"></i> {{ __('Local Food & Specialties') }}</h4>
                        <p>{{ __('Egyptian cuisine is delicious and rich in flavor! Don’t miss trying Koshary, Taameya (Egyptian falafel), Molokhia, Grilled Kebabs, and fresh mango or sugarcane juices at vetted restaurants.') }}
                        </p>
                    </div>

                    <div class="info-box">
                        <h4><i class="la la-first-aid"></i> {{ __('Personal Medication') }}</h4>
                        <p>{{ __('Carry a small personal first-aid pouch with sunblock, lip balm, rehydration salts, antacids, motion sickness tabs for cruises, and any prescription medicine in original containers.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 8: Packing Checklist -->
            <div id="packing-list" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-suitcase"></i>
                    {{ __('Egypt Traveler Packing Checklist') }}
                </h2>

                <div class="checklist-grid">
                    <div class="checklist-item">
                        <i class="la la-check-circle"></i>
                        <div>
                            <strong>{{ __('Travel Documents') }}</strong>
                            <span>{{ __('Passport (6+ mo validity), e-Visa printout, flight tickets, travel insurance copy.') }}</span>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <i class="la la-check-circle"></i>
                        <div>
                            <strong>{{ __('Clothing Essentials') }}</strong>
                            <span>{{ __('Light breathable cotton tops, loose trousers, comfortable walking shoes, sun hat, sunglasses.') }}</span>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <i class="la la-check-circle"></i>
                        <div>
                            <strong>{{ __('Sun & Skincare') }}</strong>
                            <span>{{ __('High SPF sunscreen, lip balm with UV protection, insect repellent, wet wipes.') }}</span>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <i class="la la-check-circle"></i>
                        <div>
                            <strong>{{ __('Electronics') }}</strong>
                            <span>{{ __('Phone, camera, power bank, European dual-pin plug adapter (Type C / F 220V).') }}</span>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <i class="la la-check-circle"></i>
                        <div>
                            <strong>{{ __('Modest Attire') }}</strong>
                            <span>{{ __('Scarf for women for mosque visits, long trousers/skirt covering knees.') }}</span>
                        </div>
                    </div>

                    <div class="checklist-item">
                        <i class="la la-check-circle"></i>
                        <div>
                            <strong>{{ __('Money Essentials') }}</strong>
                            <span>{{ __('Credit cards, small cash bills ($1, $5, $10 USD or EGP notes) for tips & bazaars.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 9: Frequently Asked Questions (FAQ) -->
            <div id="faq-section" class="tips-card">
                <h2 class="tips-card-title">
                    <i class="la la-question-circle"></i>
                    {{ __('Frequently Asked Questions About Traveling to Egypt') }}
                </h2>

                <div class="tips-faq-item">
                    <button type="button" class="tips-faq-header"
                        onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                        <span>{{ __('Is Egypt safe for solo travelers and families?') }}</span>
                        <i class="la la-angle-down"></i>
                    </button>
                    <div class="tips-faq-body" style="display: block;">
                        {{ __('Yes! Egypt is a very safe and welcoming destination. Tens of thousands of international tourists visit safely every month. When traveling with Egypt Tour Pro, you enjoy 24/7 assistance, private AC transfers, and dedicated Egyptologist guides for maximum security and comfort.') }}
                    </div>
                </div>

                <div class="tips-faq-item">
                    <button type="button" class="tips-faq-header"
                        onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                        <span>{{ __('How far in advance should I book my Egypt tour or Nile cruise?') }}</span>
                        <i class="la la-angle-down"></i>
                    </button>
                    <div class="tips-faq-body" style="display: none;">
                        {{ __('We recommend booking 2 to 6 months in advance, especially during the high season (October through April) to secure the best Nile cruise cabins, train tickets, and preferred hotel rooms.') }}
                    </div>
                </div>

                <div class="tips-faq-item">
                    <button type="button" class="tips-faq-header"
                        onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                        <span>{{ __('Can I customize my tour itinerary with Egypt Tour Pro?') }}</span>
                        <i class="la la-angle-down"></i>
                    </button>
                    <div class="tips-faq-body" style="display: none;">
                        {{ __('Absolutely! 100% of our tours can be tailored to your specific preferences, travel dates, budget, and desired pace. You can use our Tailor-made form or message our team directly on WhatsApp.') }}
                    </div>
                </div>

                <div class="tips-faq-item">
                    <button type="button" class="tips-faq-header"
                        onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                        <span>{{ __('Are entry fees to monuments included in Egypt Tour Pro packages?') }}</span>
                        <i class="la la-angle-down"></i>
                    </button>
                    <div class="tips-faq-body" style="display: none;">
                        {{ __('All standard entry tickets to scheduled temples, museums, and historical sites mentioned in your itinerary are included with skip-the-line privileges, unless explicitly requested as optional extras.') }}
                    </div>
                </div>
            </div>

            <!-- CTA Banner -->
            <div class="tips-cta-banner">
                <h3>{{ __('Ready to Experience the Magic of Egypt?') }}</h3>
                <p>
                    {{ __('Let Egypt Tour Pro organize your dream trip with private Egyptologist guides, luxury Nile cruises, VIP transfers, and 24/7 personal care.') }}
                </p>
                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('website.tailor_made.index') }}" class="btn-cta">
                        <i class="la la-magic"></i> {{ __('Plan Your Tailor-Made Trip') }}
                    </a>
                    <a href="https://wa.me/201062217720" target="_blank" rel="noopener noreferrer" class="btn-cta"
                        style="background: #25D366; color: #fff !important;">
                        <i class="lab la-whatsapp"></i> {{ __('Chat with Us on WhatsApp') }}
                    </a>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for jump links
            document.querySelectorAll('.tips-nav-item').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        const navHeight = 140;
                        const elementPosition = targetEl.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - navHeight;
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });

                        document.querySelectorAll('.tips-nav-item').forEach(item => item.classList
                            .remove('active'));
                        this.classList.add('active');
                    }
                });
            });
        });
    </script>
@endsection
