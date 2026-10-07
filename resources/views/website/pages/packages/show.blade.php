@extends('website.layouts.master')

@php
    $ncSeoDetail = $package->package_type === 'nile_cruise' ? $package->nileCruiseDetail : null;
    $sharedSocialPath = $package->og_image_path ?: $ncSeoDetail?->social_image_path ?? null;
    if ($sharedSocialPath) {
        $socialImage = asset('storage/' . ltrim($sharedSocialPath, '/'));
    } elseif (!empty($heroImage)) {
        $socialImage = \Illuminate\Support\Str::startsWith($heroImage, ['http://', 'https://'])
            ? $heroImage
            : asset(ltrim($heroImage, '/'));
    } else {
        $socialImage = asset('website/photos/home2.webp');
    }
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
    @vite('resources/css/pages/packages-show.css')
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
                                            @if (
                                                $highlight->display_description &&
                                                    trim(mb_strtolower($highlight->display_description)) !== trim(mb_strtolower($highlight->display_title ?? '')))
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
                            in_array($package->package_type, ['nile_cruise', 'travel_package'], true)
                                ? []
                                : (array) ($package->group_pricing_tiers ?? []),
                        )->filter(fn($tier) => is_array($tier) && (float) ($tier['price_per_person'] ?? 0) > 0);
                        $hasAccommodations =
                            $package->tourPackageAccommodations &&
                            $package->tourPackageAccommodations
                                ->where('is_active', true)
                                ->contains(
                                    fn($accommodation) => $accommodation->seasons
                                        ->where('is_active', true)
                                        ->contains(
                                            fn($season) => $season->items
                                                ->where('is_active', true)
                                                ->contains(fn($item) => (float) $item->price > 0),
                                        ),
                                );
                        $package->package_type !== 'day_tour' &&
                            $package->tourPackageAccommodations &&
                            $package->tourPackageAccommodations->isNotEmpty();
                        $showStandardPriceTable = $prices->isNotEmpty();
                        $hasAnyStandardPricing =
                            $showStandardPriceTable ||
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
                                @include('website.pages.packages.partials.nile_cruise.imported_pricing')
                            @endif

                            @if ($hasAccommodations && false)
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

                            @if ($showStandardPriceTable)
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
    <script type="application/json" id="packageConfig">
        {
            "matrixData": @json($travelPackageMatrix['matrix'] ?? []),
            "accommodationsList": @json($travelPackageMatrix['accommodations'] ?? []),
            "currencySymbol": @json($currencySymbol ?? '$'),
            "galleryImages": @json(array_values($gallery ?? [])),
            "translations": {
                "room": @json(__('Room')),
                "accommodationsType": @json(__('Accommodations Type')),
                "adults": @json(__('Adults')),
                "children": @json(__('Children'))
            }
        }
    </script>
    @vite('resources/js/pages/packages-show.js')
@endsection
