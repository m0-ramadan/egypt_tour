<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    data-theme="@yield('preferred_theme', 'dark')">

<head>
    @php
        $siteName = 'Etro Tours';
        $siteUrl = rtrim(config('app.url') ?: request()->root(), '/');
        $logoUrl = asset('website/logo/logo-lat.png');
        $brandThemeColor = '#1f5fbf';
        $defaultTitle = 'Etro Tours | Luxury Egypt Tours, Nile Cruises & Tailor-Made Travel';
        $defaultDescription =
            'Plan luxury Egypt tours, Nile cruises, private day trips, and tailor-made holidays with Etro Tours. Explore Cairo, Luxor, Aswan, and beyond with expert local travel specialists.';
        $defaultKeywords =
            'Etro Tours, Egypt tours, luxury Egypt tours, Nile cruises, Egypt travel packages, Cairo tours, Luxor tours, Aswan tours, tailor made Egypt holidays';
        $rawTitle = trim($__env->yieldContent('title'));
        $rawDescription = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('description'))));
        $rawKeywords = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('keywords'))));
        $rawCanonical = trim($__env->yieldContent('canonical'));
        $rawRobots = trim($__env->yieldContent('robots'));
        $rawOgType = trim($__env->yieldContent('og_type'));
        $rawImage = trim($__env->yieldContent('image'));
        $rawOgTitle = trim($__env->yieldContent('og_title'));
        $rawOgDescription = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('og_description'))));
        $rawTwitterCard = trim($__env->yieldContent('twitter_card'));
        $rawTwitterTitle = trim($__env->yieldContent('twitter_title'));
        $rawTwitterDescription = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('twitter_description'))));
        $rawTwitterImage = trim($__env->yieldContent('twitter_image'));
        $pageTitle = $rawTitle !== '' ? $rawTitle : $defaultTitle;
        $pageDescription =
            $rawDescription !== '' ? \Illuminate\Support\Str::limit($rawDescription, 170, '...') : $defaultDescription;
        $pageKeywords = $rawKeywords !== '' ? $rawKeywords : $defaultKeywords;
        $pageCanonical = $rawCanonical !== '' ? $rawCanonical : url()->current();
        $pageImage = $rawImage !== '' ? $rawImage : $logoUrl;
        $pageOgTitle = $rawOgTitle !== '' ? $rawOgTitle : $pageTitle;
        $pageOgDescription = $rawOgDescription !== '' ? \Illuminate\Support\Str::limit($rawOgDescription, 200, '...') : $pageDescription;
        $pageRobots = $rawRobots !== '' ? $rawRobots : 'index, follow, max-image-preview:large';
        $pageOgType =
            $rawOgType !== '' ? $rawOgType : (request()->routeIs('website.blogs.show*') ? 'article' : 'website');
        $twitterCard = $rawTwitterCard !== '' ? $rawTwitterCard : 'summary_large_image';
        $twitterTitle = $rawTwitterTitle !== '' ? $rawTwitterTitle : $pageOgTitle;
        $twitterDescription = $rawTwitterDescription !== '' ? \Illuminate\Support\Str::limit($rawTwitterDescription, 200, '...') : $pageOgDescription;
        $twitterImage = $rawTwitterImage !== '' ? $rawTwitterImage : $pageImage;
        $ogLocale = app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US';
        $alternateLocale = app()->getLocale() === 'ar' ? 'en_US' : 'ar_AR';
        $preferredThemeValue = trim($__env->yieldContent('preferred_theme', 'dark'));
        $preferredTheme = in_array($preferredThemeValue, ['light', 'dark'], true) ? $preferredThemeValue : 'dark';
        $bodyClass = trim($__env->yieldContent('body_class'));
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $logoUrl,
            'image' => $logoUrl,
            'telephone' => '+1-917-267-8628',
            'email' => 'info@etrotours.com',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Luxor',
                'addressCountry' => 'EG',
            ],
        ];
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
            'inLanguage' => app()->getLocale(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('website.search.index') . '?keyword={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="{{ $brandThemeColor }}" data-theme-color-meta>
    <title>{{ $pageTitle }}</title>
    <link rel="canonical" href="{{ $pageCanonical }}">
    <meta name="robots" content="{{ $pageRobots }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:type" content="{{ $pageOgType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:locale:alternate" content="{{ $alternateLocale }}">
    <meta property="og:title" content="{{ $pageOgTitle }}">
    <meta property="og:description" content="{{ $pageOgDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $twitterTitle }}">
    <meta name="twitter:description" content="{{ $twitterDescription }}">
    <meta name="twitter:image" content="{{ $twitterImage }}">
    @hasSection('published_time')
        <meta property="article:published_time" content="@yield('published_time')">
    @endif
    @hasSection('modified_time')
        <meta property="article:modified_time" content="@yield('modified_time')">
    @endif
    <script type="application/ld+json">
        {!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @hasSection('schema')
        @yield('schema')
    @endif

    <script>
        (function() {
            const storageKey = 'website-theme';
            let theme = @json($preferredTheme);

            try {
                const storedTheme = localStorage.getItem(storageKey);
                if (storedTheme === 'dark' || storedTheme === 'light') {
                    theme = storedTheme;
                }
            } catch (e) {}

            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;
        })();
    </script>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon-512x512.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @hasSection('lcp_preload')
        @yield('lcp_preload')
    @endif


    <link rel="preload" href="{{ asset('website/fonts/website/inter-latin-variable.woff2') }}" as="font"
        type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('website/fonts/website/playfair-display-latin-variable.woff2') }}" as="font"
        type="font/woff2" crossorigin>
    @if (app()->getLocale() === 'ar')
        <link rel="preload" href="{{ asset('website/fonts/website/cairo-arabic-variable.woff2') }}" as="font"
            type="font/woff2" crossorigin>
    @endif

    @vite('resources/css/website.css')
    @vite('resources/css/website-base.css')

    @yield('css')
    @vite('resources/css/website-after.css')
    @vite('resources/css/website-theme.css')
    @if (app()->getLocale() === 'ar')
        @vite('resources/css/website-rtl.css')
    @endif
    <link rel="stylesheet" href="{{ asset('website/vendor/intl-tel-input/css/intlTelInput.min.css') }}">
    <style>
        /* Custom styled searchable country code flag picker */
        .iti {
            width: 100%;
            display: block;
        }
        .iti__country-container {
            z-index: 5;
        }
        .iti__selected-country {
            padding: 0 12px !important;
            border-radius: 14px 0 0 14px !important;
            background: transparent !important;
        }
        html[dir="rtl"] .iti__selected-country {
            border-radius: 0 14px 14px 0 !important;
        }
        .iti__selected-dial-code {
            font-weight: 700;
            color: #1c325c;
            font-size: 0.95rem;
            margin-left: 6px;
        }
        html[dir="rtl"] .iti__selected-dial-code {
            margin-left: 0;
            margin-right: 6px;
        }
        .iti__dropdown-content {
            background-color: #ffffff !important;
            border-radius: 18px !important;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.22) !important;
            border: 1px solid rgba(26, 54, 93, 0.12) !important;
            padding: 8px !important;
            z-index: 99999 !important;
        }
        .iti__search-input {
            width: 100% !important;
            padding: 10px 14px !important;
            border-radius: 12px !important;
            border: 1px solid rgba(26, 54, 93, 0.15) !important;
            font-size: 0.9rem !important;
            outline: none !important;
            margin-bottom: 8px !important;
            background: #f8fbff !important;
            color: #1c325c !important;
        }
        .iti__country-list {
            border-top: 1px solid rgba(26, 54, 93, 0.08) !important;
            border-radius: 0 0 14px 14px !important;
            max-height: 240px !important;
        }
        .iti__country {
            padding: 10px 12px !important;
            border-radius: 10px !important;
            font-size: 0.92rem !important;
            color: #1c325c !important;
            transition: background-color 0.15s ease;
        }
        .iti__country:hover,
        .iti__country.iti__highlight {
            background-color: rgba(197, 149, 91, 0.14) !important;
        }
        .iti input.form-control,
        .iti input.iti__tel-input {
            width: 100% !important;
            min-height: 58px !important;
            border-radius: 18px !important;
        }
        /* Dark theme support */
        html[data-theme='dark'] .iti__selected-dial-code {
            color: #f8fafc !important;
        }
        html[data-theme='dark'] .iti__dropdown-content {
            background-color: #111827 !important;
            border-color: rgba(148, 163, 184, 0.2) !important;
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.45) !important;
        }
        html[data-theme='dark'] .iti__search-input {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, 0.24) !important;
        }
        html[data-theme='dark'] .iti__country {
            color: #e2e8f0 !important;
        }
        html[data-theme='dark'] .iti__country:hover,
        html[data-theme='dark'] .iti__country.iti__highlight {
            background-color: rgba(197, 149, 91, 0.22) !important;
        }
    </style>
    <meta name="google-site-verification" content="OKwZFMPi1pE0RpnHtt6lJnyE_qPXCNqW8E7-U4BHPRw" />
</head>

<body
    class="website-theme-shell {{ app()->getLocale() === 'ar' ? 'website-rtl' : 'website-ltr' }}{{ $bodyClass !== '' ? ' ' . $bodyClass : '' }}">

    <a class="skip-to-content" href="#main-content">{{ __('Skip to main content') }}</a>
    @include('website.layouts.header')

    <main id="main-content" tabindex="-1">
    @yield('content')

    <!-- Include Footer -->
    <!-- Why Travel With Us Section -->
    <section class="why-choose-section" style="background: var(--pearl-luxury); padding: 80px 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading text-center mb-0">
                        <h2 class="section-header"
                            style="font-family: 'Playfair Display', serif; color: var(--primary-navy); font-size: clamp(1.5rem, 3vw, 2.2rem); margin-bottom: 20px;">
                            {{ __('Why travel with Etro Tours?') }}
                        </h2>
                        <p class="section-subtitle"
                            style="color: var(--warm-gray); font-size: 1.2rem; max-width: 700px; margin: 0 auto 60px; line-height: 1.6;">
                            {{ __('Your entire vacation is designed around your requirements with expert guidance every step of the way.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card"
                        style="background: white; border-radius: 25px; padding: 40px 30px; text-align: center; box-shadow: var(--shadow-medium); border: 2px solid transparent; transition: all 0.4s ease; height: 100%; position: relative; overflow: hidden;"
                        data-cf-modified-bbfb53b5999c6c3f61fbade4-="">
                        <div class="choose-icon"
                            style="width: 80px; height: 80px;  border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2.2rem; color: white; box-shadow: var(--shadow-gold); transition: all 0.3s ease;">
                            <i class="la la-cog"></i>
                        </div>
                        <h3 class="choose-title"
                            style="font-family: 'Playfair Display', serif; color: var(--primary-navy); font-size: 1.4rem; font-weight: 600; margin-bottom: 20px;">
                            {{ __('100% Tailor made') }}</h3>
                        <div class="choose-features">
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Your entire vacation is designed around your requirements') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Explore your interests at your own speed') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Select your preferred style of accommodations') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Create the perfect trip with the help of our specialists') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card"
                        style="background: white; border-radius: 25px; padding: 40px 30px; text-align: center; box-shadow: var(--shadow-medium); border: 2px solid transparent; transition: all 0.4s ease; height: 100%; position: relative; overflow: hidden;"
                        data-cf-modified-bbfb53b5999c6c3f61fbade4-="">
                        <div class="choose-icon"
                            style="width: 80px; height: 80px;  border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2.2rem; color: white; box-shadow: var(--shadow-gold); transition: all 0.3s ease;">
                            <i class="la la-lightbulb"></i>
                        </div>
                        <h3 class="choose-title"
                            style="font-family: 'Playfair Display', serif; color: var(--primary-navy); font-size: 1.4rem; font-weight: 600; margin-bottom: 20px;">
                            {{ __('Expert knowledge') }}</h3>
                        <div class="choose-features">
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('All our specialists have traveled extensively or lived in their specialist regions, We\'re with you every step of the way') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('The same specialist will handle your trip from start to finish') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Make the most of your time and budget') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card"
                        style="background: white; border-radius: 25px; padding: 40px 30px; text-align: center; box-shadow: var(--shadow-medium); border: 2px solid transparent; transition: all 0.4s ease; height: 100%; position: relative; overflow: hidden;"
                        data-cf-modified-bbfb53b5999c6c3f61fbade4-="">
                        <div class="choose-icon"
                            style="width: 80px; height: 80px;  border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2.2rem; color: white; box-shadow: var(--shadow-gold); transition: all 0.3s ease;">
                            <i class="la la-user-graduate"></i>
                        </div>
                        <h3 class="choose-title"
                            style="font-family: 'Playfair Display', serif; color: var(--primary-navy); font-size: 1.4rem; font-weight: 600; margin-bottom: 20px;">
                            {{ __('The best guides') }}</h3>
                        <div class="choose-features">
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Make the difference between a good trip and an outstanding one') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Our leaders will be there to ensure your safety and wellbeing is the number one priority') }}
                            </div>
                            <div class="feature-item"
                                style="padding: 12px 0; color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Offering more than just dates and names, they strive to offer real insight into their country') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card"
                        style="background: white; border-radius: 25px; padding: 40px 30px; text-align: center; box-shadow: var(--shadow-medium); border: 2px solid transparent; transition: all 0.4s ease; height: 100%; position: relative; overflow: hidden;"
                        data-cf-modified-bbfb53b5999c6c3f61fbade4-="">
                        <div class="choose-icon"
                            style="width: 80px; height: 80px;  border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2.2rem; color: white; box-shadow: var(--shadow-gold); transition: all 0.3s ease;">
                            <i class="la la-shield-alt"></i>
                        </div>
                        <h3 class="choose-title"
                            style="font-family: 'Playfair Display', serif; color: var(--primary-navy); font-size: 1.4rem; font-weight: 600; margin-bottom: 20px;">
                            {{ __('Fully protected') }}</h3>
                        <div class="choose-features">
                            <div class="feature-item"
                                style="padding: 12px 0; border-bottom: 1px solid rgba(197, 149, 91, 0.2); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                {{ __('Secure Payment - Use your debit card or credit card. Your transactions are protected by 3D Secure and SecureCode.') }}
                            </div>
                            <div class="feature-item" style="padding: 12px 0; text-align: center;">
                                <img loading="lazy" src="{{ asset('website/flags/cybersource.png') }}"
                                    height="100" width="150" alt="{{ __('Cybersource Security') }}"
                                    style="opacity: 0.8;">
                                <img loading="lazy" src="{{ asset('website/flags/mpgs.webp') }}" height="100"
                                    width="150" alt="{{ __('Cybersource Security') }}" style="opacity: 0.8;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Minimal Enhanced Luxury CTA Section -->
    <section class="luxury-cta-section">
        <div class="container">
            <div class="luxury-cta-content">
                <div class="cta-content-wrapper">
                    <div class="cta-text-content">
                        <h2 class="cta-title">{{ __('Ready to Plan Your Dream Cruise?') }}</h2>
                        <p class="cta-subtitle">
                            {{ __('Speak with our Egypt specialists for your perfect luxury journey.') }}</p>

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

                    <div class="cta-actions">
                        <div class="cta-icon-container">
                            <i class="la la-phone"></i>
                        </div>

                        <a href="{{ route('website.contact.index') }}" class="luxury-cta-btn">
                            <i class="la la-calendar-check"></i>
                            {{ __('Start Planning') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </main>

    <!-- Fixed WhatsApp Button -->
    <a href="https://wa.me/201553383000" target="_blank" rel="noopener noreferrer" class="whatsapp-fixed"
        aria-label="{{ __('Chat with Etro Tours on WhatsApp') }}">
        <i class="lab la-whatsapp" aria-hidden="true"></i>
    </a>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/6a722c3be998931d47ff34ee/1jv6vpiou';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->

    @include('website.layouts.footer')


    <script src="{{ asset('website/vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.intlTelInput === 'function') {
                const locale = @json(app()->getLocale());
                const searchPlaceholder = locale === 'ar' ? 'ابحث عن الدولة أو الكود...' : 'Search country or code...';
                const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="phone"], #phone');

                phoneInputs.forEach(function(input) {
                    if (input.dataset.itiInitialized) return;
                    input.dataset.itiInitialized = 'true';

                    const iti = window.intlTelInput(input, {
                        initialCountry: "auto",
                        geoIpLookup: function(callback) {
                            var cached = sessionStorage.getItem('user_country_code');
                            if (cached) {
                                callback(cached);
                                return;
                            }
                            fetch('https://ipapi.co/json/')
                                .then(function(res) { return res.json(); })
                                .then(function(data) {
                                    var countryCode = (data && data.country_code) ? data.country_code.toLowerCase() : 'eg';
                                    sessionStorage.setItem('user_country_code', countryCode);
                                    callback(countryCode);
                                })
                                .catch(function() {
                                    callback('eg');
                                });
                        },
                        separateDialCode: true,
                        allowDropdown: true,
                        autoPlaceholder: "polite",
                        preferredCountries: ["eg", "sa", "ae", "kw", "qa", "om", "us", "gb", "de", "fr"],
                        utilsScript: "{{ asset('website/vendor/intl-tel-input/js/utils.js') }}",
                        i18n: {
                            searchPlaceholder: searchPlaceholder
                        }
                    });

                    const form = input.closest('form');
                    if (form) {
                        form.addEventListener('submit', function() {
                            const fullNumber = iti.getNumber();
                            if (fullNumber && fullNumber.trim() !== '') {
                                input.value = fullNumber;
                            }
                        });
                    }
                });
            }
        });
    </script>

    @yield('js')
    @vite('resources/js/website.js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeStorageKey = 'website-theme';
            const themeColorMeta = document.querySelector('[data-theme-color-meta]');

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.style.colorScheme = theme;
                if (themeColorMeta) {
                    themeColorMeta.setAttribute('content', '{{ $brandThemeColor }}');
                }
                updateThemeButtons(theme);
            }

            function updateThemeButtons(theme) {
                document.querySelectorAll('[data-theme-toggle]').forEach(function(button) {
                    const icon = button.querySelector('i');
                    const isDark = theme === 'dark';
                    const nextLabel = isDark ? button.dataset.lightLabel : button.dataset.darkLabel;

                    button.setAttribute('aria-label', nextLabel);
                    button.setAttribute('title', nextLabel);
                    button.setAttribute('aria-pressed', isDark ? 'true' : 'false');

                    if (icon) {
                        icon.className = 'la ' + (isDark ? 'la-sun' : 'la-moon');
                    }
                });
            }

            function toggleTheme() {
                const currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' :
                    'light';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

                try {
                    localStorage.setItem(themeStorageKey, nextTheme);
                } catch (e) {}

                applyTheme(nextTheme);
            }

            document.querySelectorAll('[data-theme-toggle]').forEach(function(button) {
                button.addEventListener('click', function() {
                    toggleTheme();
                });
            });

            const savedTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            updateThemeButtons(savedTheme);

            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', function(event) {
                    try {
                        if (localStorage.getItem(themeStorageKey)) {
                            return;
                        }
                    } catch (e) {}

                    applyTheme(event.matches ? 'dark' : 'light');
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Navbar Dropdown Fix
            |--------------------------------------------------------------------------
            | This keeps desktop dropdowns working even if Bootstrap dropdown JS
            | is not initializing correctly or custom CSS is hiding the menu.
            */

            document.querySelectorAll('.navbar [data-navbar-dropdown-toggle]').forEach(function(toggle) {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const dropdown = this.closest('.dropdown');
                    const menu = dropdown ? dropdown.querySelector('.dropdown-menu') : null;

                    if (!dropdown || !menu) {
                        return;
                    }

                    document.querySelectorAll('.navbar .dropdown').forEach(function(item) {
                        if (item !== dropdown) {
                            item.classList.remove('show');

                            const otherMenu = item.querySelector('.dropdown-menu');
                            const otherToggle = item.querySelector(
                                '[data-navbar-dropdown-toggle]');

                            if (otherMenu) {
                                otherMenu.classList.remove('show');
                            }

                            if (otherToggle) {
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });

                    dropdown.classList.toggle('show');
                    menu.classList.toggle('show');

                    const isOpen = menu.classList.contains('show');
                    this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            });

            document.addEventListener('click', function(event) {
                if (event.target.closest('.navbar .dropdown')) {
                    return;
                }

                document.querySelectorAll('.navbar .dropdown').forEach(function(dropdown) {
                    dropdown.classList.remove('show');

                    const menu = dropdown.querySelector('.dropdown-menu');
                    const toggle = dropdown.querySelector('[data-navbar-dropdown-toggle]');

                    if (menu) {
                        menu.classList.remove('show');
                    }

                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    </script>
</body>

</html>
