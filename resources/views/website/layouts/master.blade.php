@ezyIntegrity

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    data-theme="@yield('preferred_theme', 'light')">

<head>
    @php
        $siteName = 'Egypt Tour Pro';
        $siteUrl = rtrim(config('app.url') ?: request()->root(), '/');
        if (request()->isSecure() || request()->header('x-forwarded-proto') === 'https' || \Illuminate\Support\Str::contains(request()->getHost(), 'egypttourpro.com')) {
            $siteUrl = preg_replace('/^http:\/\//i', 'https://', $siteUrl);
        }
        $logoUrl = asset('website/logo/egypt-tour-pro-charcoal.webp');
        if (\Illuminate\Support\Str::startsWith($siteUrl, 'https://') && \Illuminate\Support\Str::startsWith($logoUrl, 'http://')) {
            $logoUrl = 'https://' . \Illuminate\Support\Str::after($logoUrl, 'http://');
        }
        $brandThemeColor = '#1C1C1C';
        $defaultTitle = 'Egypt Tour Pro | Luxury Egypt Tours, Nile Cruises & Handcrafted Journeys';
        $defaultDescription =
            'Experience ancient Egypt with Egypt Tour Pro. Handcrafted private tours, luxury 5-star Nile cruises, and bespoke itineraries led by expert local Egyptologists.';
        $defaultKeywords =
            'Egypt Tour Pro, Egypt tours, luxury Egypt tours, Nile cruises, Egypt travel packages, Cairo tours, Luxor tours, Aswan tours, tailor made Egypt holidays, private Egypt guide';
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
        $rawTwitterDescription = trim(
            preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('twitter_description'))),
        );
        $rawTwitterImage = trim($__env->yieldContent('twitter_image'));

        $toAbsoluteUrl = static function (?string $url) use ($siteUrl): string {
            $url = trim((string) $url);
            if ($url === '') {
                return '';
            }
            if (!\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
                $url = $siteUrl . '/' . ltrim($url, '/');
            }
            if (\Illuminate\Support\Str::startsWith($siteUrl, 'https://') && \Illuminate\Support\Str::startsWith($url, 'http://')) {
                $url = 'https://' . \Illuminate\Support\Str::after($url, 'http://');
            }
            return $url;
        };

        $pageTitle = $rawTitle !== '' ? $rawTitle : $defaultTitle;
        $pageDescription =
            $rawDescription !== '' ? \Illuminate\Support\Str::limit($rawDescription, 170, '...') : $defaultDescription;
        $pageKeywords = $rawKeywords !== '' ? $rawKeywords : $defaultKeywords;
        $pageCanonical = $toAbsoluteUrl($rawCanonical !== '' ? $rawCanonical : url()->current());
        $pageImage = $toAbsoluteUrl($rawImage !== '' ? $rawImage : $logoUrl);
        $pageOgTitle = $rawOgTitle !== '' ? $rawOgTitle : $pageTitle;
        $pageOgDescription =
            $rawOgDescription !== '' ? \Illuminate\Support\Str::limit($rawOgDescription, 200, '...') : $pageDescription;
        $pageRobots = $rawRobots !== '' ? $rawRobots : 'index, follow, max-image-preview:large';
        $pageOgType =
            $rawOgType !== '' ? $rawOgType : (request()->routeIs('website.blogs.show*') ? 'article' : 'website');
        $twitterCard = $rawTwitterCard !== '' ? $rawTwitterCard : 'summary_large_image';
        $twitterTitle = $rawTwitterTitle !== '' ? $rawTwitterTitle : $pageOgTitle;
        $twitterDescription =
            $rawTwitterDescription !== ''
                ? \Illuminate\Support\Str::limit($rawTwitterDescription, 200, '...')
                : $pageOgDescription;
        $twitterImage = $toAbsoluteUrl($rawTwitterImage !== '' ? $rawTwitterImage : $pageImage);
        $ogLocale = app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US';
        $alternateLocale = app()->getLocale() === 'ar' ? 'en_US' : 'ar_AR';

        $imagePathOnly = parse_url($pageImage, PHP_URL_PATH) ?? '';
        $imageExtension = strtolower(pathinfo($imagePathOnly, PATHINFO_EXTENSION));
        $imageMimeType = match ($imageExtension) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
        $imageWidth = 1200;
        $imageHeight = 630;
        $localImagePath = public_path(ltrim($imagePathOnly, '/'));
        if (file_exists($localImagePath) && is_file($localImagePath)) {
            $imageInfo = @getimagesize($localImagePath);
            if ($imageInfo && !empty($imageInfo[0]) && !empty($imageInfo[1])) {
                $imageWidth = (int) $imageInfo[0];
                $imageHeight = (int) $imageInfo[1];
                if (!empty($imageInfo['mime'])) {
                    $imageMimeType = (string) $imageInfo['mime'];
                }
            }
        }

        $preferredThemeValue = trim($__env->yieldContent('preferred_theme', 'light'));
        $preferredTheme = in_array($preferredThemeValue, ['light', 'dark'], true) ? $preferredThemeValue : 'light';
        $bodyClass = trim($__env->yieldContent('body_class'));
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $logoUrl,
            'telephone' => '+20 106 221 7720',
            'email' => 'info@egypttourpro.com',
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
    @hasSection('lcp_preload')
        @yield('lcp_preload')
    @endif
    <link rel="preload" href="{{ asset('website/fonts/website/la-solid-subset.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('website/fonts/website/la-regular-subset.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('website/fonts/website/la-brands-subset.woff2') }}" as="font" type="font/woff2" crossorigin>
    <meta name="theme-color" content="{{ $brandThemeColor }}" data-theme-color-meta>
    <title>{{ $pageTitle }}</title>
    <link rel="canonical" href="{{ $pageCanonical }}">
    <meta name="robots" content="{{ $pageRobots }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <meta name="description" content="{{ $pageDescription }}">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="{{ $pageOgType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:locale:alternate" content="{{ $alternateLocale }}">
    <meta property="og:title" content="{{ $pageOgTitle }}">
    <meta property="og:description" content="{{ $pageOgDescription }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:image:secure_url" content="{{ $pageImage }}">
    <meta property="og:image:type" content="{{ $imageMimeType }}">
    <meta property="og:image:width" content="{{ $imageWidth }}">
    <meta property="og:image:height" content="{{ $imageHeight }}">
    <meta property="og:image:alt" content="{{ $pageOgTitle }}">
    <link rel="image_src" href="{{ $pageImage }}">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $twitterTitle }}">
    <meta name="twitter:description" content="{{ $twitterDescription }}">
    <meta name="twitter:image" content="{{ $twitterImage }}">

    {{-- Temporarily commented out Google Tag Manager for testing
    <!-- Google Tag Manager -->
    @if (request()->routeIs('website.home'))
        <script>
            (function(w, d, s, l, i) {
                var loaded = false;

                function loadTagManager() {
                    if (loaded) return;
                    loaded = true;

                    w[l] = w[l] || [];
                    w[l].push({
                        'gtm.start': new Date().getTime(),
                        event: 'gtm.js'
                    });

                    var f = d.getElementsByTagName(s)[0];
                    var j = d.createElement(s);
                    var dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                }

                ['pointerdown', 'touchstart', 'keydown', 'scroll'].forEach(function(eventName) {
                    w.addEventListener(eventName, loadTagManager, {
                        once: true,
                        passive: true
                    });
                });

                // Preserve analytics for visitors who do not interact while
                // keeping third-party work outside the critical render path.
                w.setTimeout(loadTagManager, 10000);
            })(window, document, 'script', 'dataLayer', 'GTM-P8XJ3D9D');
        </script>
    @else
        <script>
            (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-P8XJ3D9D');
        </script>
    @endif
    <!-- End Google Tag Manager -->
    --}}
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
            var storageKey = 'website-theme';
            var theme = document.documentElement.getAttribute('data-theme') || 'light';
            try {
                var storedTheme = localStorage.getItem(storageKey);
                if (storedTheme === 'dark' || storedTheme === 'light') theme = storedTheme;
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

    @hasSection('home_style_bundle')
        @vite('resources/css/website-home-entry.css')
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/website-home-deferred.css') }}" media="none" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="{{ Vite::asset('resources/css/website-home-deferred.css') }}">
        </noscript>
    @else
        <link rel="preload" href="{{ asset('website/fonts/website/inter-latin-variable.woff2') }}" as="font"
            type="font/woff2" crossorigin>
        @vite('resources/css/website.css')
        @yield('css')
    @endif
    @if (app()->getLocale() === 'ar')
        @vite('resources/css/website-rtl.css')
    @endif
    <meta name="google-site-verification" content="OKwZFMPi1pE0RpnHtt6lJnyE_qPXCNqW8E7-U4BHPRw" />
</head>

<body
    class="website-theme-shell {{ app()->getLocale() === 'ar' ? 'website-rtl' : 'website-ltr' }}{{ $bodyClass !== '' ? ' ' . $bodyClass : '' }}">

    {{-- Temporarily commented out Google Tag Manager (noscript) for testing
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P8XJ3D9D" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    --}}

    <a class="skip-to-content" href="#main-content">{{ __('Skip to main content') }}</a>
    @include('website.layouts.header')

    <main id="main-content" tabindex="-1">
        @yield('content')

        @if (!request()->routeIs('website.home'))
            <!-- Why Travel With Us Section -->
            <section class="why-choose-section" style="background: var(--pearl-luxury); padding: 80px 0;">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="section-heading text-center mb-0">
                                <h2 class="section-header"
                                    style="font-family: 'Playfair Display', serif; color: var(--primary-navy); font-size: clamp(1.5rem, 3vw, 2.2rem); margin-bottom: 20px;">
                                    {{ __('Why travel with Egypt Tour Pro?') }}
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
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                        {{ __('Your entire vacation is designed around your requirements') }}
                                    </div>
                                    <div class="feature-item"
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                        {{ __('Explore your interests at your own speed') }}
                                    </div>
                                    <div class="feature-item"
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
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
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                        {{ __('All our specialists have traveled extensively or lived in their specialist regions, We\'re with you every step of the way') }}
                                    </div>
                                    <div class="feature-item"
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
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
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                        {{ __('Make the difference between a good trip and an outstanding one') }}
                                    </div>
                                    <div class="feature-item"
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
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
                                        style="padding: 12px 0; border-bottom: 1px solid rgba(243, 107, 10, 0.16); color: var(--warm-gray); font-size: 0.95rem; line-height: 1.6;">
                                        {{ __('Secure Payment - Use your debit card or credit card. Your transactions are protected by 3D Secure and SecureCode.') }}
                                    </div>
                                    <div class="feature-item" style="padding: 12px 0; text-align: center;">
                                        <img loading="lazy" decoding="async"
                                            src="{{ asset('website/flags/cybersource-300.webp') }}" height="85"
                                            width="150" alt="{{ __('Cybersource Security') }}"
                                            style="opacity: 0.8;">
                                        <img loading="lazy" decoding="async"
                                            src="{{ asset('website/flags/mpgs-300.webp') }}" height="79"
                                            width="150" alt="{{ __('Cybersource Security') }}"
                                            style="opacity: 0.8;">
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
        @endif

    </main>

    <!-- Fixed WhatsApp Button -->
    <a href="https://wa.me/201062217720" target="_blank" rel="noopener noreferrer" class="whatsapp-fixed"
        aria-label="{{ __('Chat with Egypt Tour Pro on WhatsApp') }}">
        <i class="lab la-whatsapp" aria-hidden="true"></i>
    </a>



    @include('website.layouts.footer')


    @yield('js')
    @vite('resources/js/website.js')
</body>

</html>
