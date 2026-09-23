@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('View Trip'))

@section('css')
    <style>
        .package-detail {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
            --dark-box: rgba(255, 255, 255, .05);
            --dark-border: rgba(255, 255, 255, .1);
        }

        .package-detail .profile-card {
            background: var(--dark-card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            border: 1px solid var(--dark-border);
        }

        .package-detail .profile-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 30px;
        }

        .package-detail .profile-body {
            padding: 30px;
        }

        .package-detail .section-title {
            font-weight: 700;
            font-size: 18px;
            margin: 30px 0 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--dark-border);
        }

        .package-detail .info-box {
            background: var(--dark-box);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            height: calc(100% - 15px);
        }

        .package-detail .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .package-detail .info-value {
            color: #fff;
            font-weight: 600;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .package-detail .badge-soft {
            display: inline-block;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #fff;
            padding: 7px 10px;
            border-radius: 999px;
            margin: 3px;
            font-size: 13px;
        }

        .package-detail .image-preview {
            width: 180px;
            max-width: 100%;
            height: 135px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid var(--dark-border);
        }

        .package-detail .table-dark-custom {
            color: #fff;
            border-color: var(--dark-border);
        }

        .package-detail .table-dark-custom th,
        .package-detail .table-dark-custom td {
            color: #fff;
            border-color: var(--dark-border);
            vertical-align: middle;
        }

        .package-detail .timeline-item {
            background: var(--dark-box);
            border: 1px solid var(--dark-border);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 14px;
        }

        .package-detail .trip-section-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 18px 30px;
            border-bottom: 1px solid var(--dark-border);
        }

        .package-detail .trip-section-nav a {
            color: #e5ddff;
            background: var(--dark-box);
            padding: 8px 14px;
            border-radius: 10px;
        }

        .package-detail .trip-section-nav a:hover,
        .package-detail .trip-section-nav a:focus-visible {
            background: #667eea;
            color: white;
        }

        .package-detail .section-title {
            scroll-margin-top: 100px;
            color: #fff;
        }

        .package-detail .profile-header h4 {
            color: #fff;
            overflow-wrap: anywhere;
        }

        @media (max-width: 767px) {

            .package-detail .profile-body,
            .package-detail .profile-header {
                padding: 18px;
            }

            .package-detail .trip-section-nav {
                padding: 14px 18px;
            }

            .package-detail .image-preview {
                width: 140px;
                height: 105px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $packageTitle = adminTrans($package->title ?? ($package->name ?? '')) ?: 'No Name';
        $isHourly = $package->package_type === 'day_tour' || $package->duration_type === 'hours';
        $packageTypeLabel = match ($package->package_type) {
            'day_tour' => 'Day Tour',
            'travel_package' => 'Travel Package',
            'nile_cruise' => 'Nile Cruise',
            'shore_excursion' => 'Shore Excursion',
            'multi_country' => 'Multi-country',
            'deal' => 'Deal',
            'custom' => 'Custom',
            default => $package->package_type ?: '-',
        };
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y package-detail">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.index') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.packages.index') }}">Trips</a>
                </li>
                <li class="breadcrumb-item active">View Trip</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">{{ $packageTitle }}</h4>
                    <small class="opacity-75">{{ $package->slug ?? '-' }}</small>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-light">Edit</a>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-light">Back</a>
                </div>
            </div>

            <nav class="trip-section-nav" aria-label="Trip sections">
                <a href="#trip-media">{{ admin_t('Description and Images') }}</a>
                <a href="#trip-basics">{{ admin_t('Basic Information') }}</a>
                <a href="#trip-itinerary">{{ admin_t('Itinerary') }}</a>
                <a href="#trip-pricing">{{ admin_t('Prices') }}</a>
                <a href="#trip-policies">{{ admin_t('Policies And Terms') }}</a>
                <a href="#trip-seo">{{ admin_t('SEO') }}</a>
            </nav>

            <div class="profile-body">

                {{-- Images --}}
                <h2 class="section-title" id="trip-media">Description and Images</h2>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Main Image</div>

                            @if (!empty($package->featured_image))
                                <img src="{{ $savedFeaturedUrl }}" class="image-preview" alt="{{ $packageTitle }}">
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="info-box">
                            <div class="info-label">Gallery Images</div>

                            @if ($savedGalleryUrls)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($savedGalleryUrls as $imageUrl)
                                        <a href="{{ $imageUrl }}" target="_blank" rel="noopener">
                                            <img src="{{ $imageUrl }}" class="image-preview" alt="{{ $packageTitle }}"
                                                loading="lazy">
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Basic Information --}}
                <h2 class="section-title" id="trip-basics">Basic Information</h2>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Category</div>
                            <div class="info-value">{{ adminTrans(optional($package->category)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">City</div>
                            <div class="info-value">
                                {{ adminTrans(optional(optional($package->destination)->city)->name) ?: (adminTrans(optional($package->destination)->name) ?: '-') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Country Basic</div>
                            <div class="info-value">{{ adminTrans(optional($package->primaryCountry)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Trip Type</div>
                            <div class="info-value">{{ $packageTypeLabel }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Tour Type</div>
                            <div class="info-value">{{ $package->tour_type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Booking System</div>
                            <div class="info-value">{{ $package->booking_mode ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Currency</div>
                            <div class="info-value">{{ optional($package->currency)->code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{ $package->is_active ?? true ? 'Enabled' : 'Inactive' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Sort Order</div>
                            <div class="info-value">{{ $package->sort_order ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <h2 class="section-title" id="trip-description">Text and Description</h2>

                <div class="row">
                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Subtitle</div>
                            <div class="info-value">{{ adminTrans($package->subtitle ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Short Description</div>
                            <div class="info-value">{{ adminTrans($package->short_description ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Full Description</div>
                            <div class="info-value">{{ adminTrans($package->description ?? '') ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Duration and Itinerary --}}
                <h2 class="section-title" id="trip-duration">Duration and Itinerary</h2>

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">
                                {{ $isHourly ? admin_t('Number of Hours') : admin_t('Number of Days') }}</div>
                            <div class="info-value">
                                {{ ($isHourly ? $package->duration_hours : $package->duration_days) ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">
                                {{ $isHourly ? admin_t('Type Duration') : admin_t('Number of Nights') }}</div>
                            <div class="info-value">
                                {{ $isHourly ? admin_t('Hours') : $package->duration_nights ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Text Duration</div>
                            <div class="info-value">{{ $package->duration_text ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Schedule</div>
                            <div class="info-value">{{ adminTrans($package->schedule_text ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Itinerary</div>
                            <div class="info-value">{{ $package->route_text ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Pickup Location</div>
                            <div class="info-value">{{ adminTrans($package->pickup_location ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Drop-off Location</div>
                            <div class="info-value">{{ adminTrans($package->dropoff_location ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Destinations</div>
                            <div class="info-value">{{ adminTrans($package->destinations_text ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Location Summary</div>
                            <div class="info-value">{{ adminTrans($package->location_summary ?? '') ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                <h2 class="section-title" id="trip-facilities">Trip Facilities</h2>

                <div class="info-box">
                    @if (isset($package->packageAttractions) && $package->packageAttractions->count())
                        @foreach ($package->packageAttractions as $packageAttraction)
                            <span class="badge-soft">
                                {{ $packageAttraction->display_title ?: $packageAttraction->attraction?->display_name }}
                            </span>
                        @endforeach
                    @elseif (isset($package->facilities) && $package->facilities->count())
                        @foreach ($package->facilities as $facility)
                            <span class="badge-soft">{{ $facility->title }}</span>
                        @endforeach
                    @else
                        <div class="info-value">-</div>
                    @endif
                </div>

                {{-- Itinerary --}}
                <h2 class="section-title" id="trip-itinerary">Itinerary</h2>

                @if (isset($package->itineraries) && $package->itineraries->count())
                    @foreach ($package->itineraries as $day)
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                <h6 class="mb-0">
                                    {{ $isHourly ? 'Stop' : 'Day' }} {{ $day->day_number ?? '-' }} -
                                    {{ adminTrans($day->title ?? '') ?: '-' }}
                                </h6>
                                <span class="badge-soft">{{ $day->duration ?? '-' }}</span>
                            </div>

                            <div class="info-value mb-2">{{ adminTrans($day->description ?? '') ?: '-' }}</div>

                            @php
                                $dayMeals = [];
                                if (
                                    !empty($day->meals) &&
                                    (is_array($day->meals) || $day->meals instanceof \Illuminate\Support\Collection)
                                ) {
                                    $dayMeals = is_array($day->meals) ? $day->meals : $day->meals->toArray();
                                }
                                if (!empty($day->meals_breakfast) && !in_array('breakfast', $dayMeals)) {
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
                                    style="background-color: #fff7ed; border-left: 4px solid #F36B0A;">
                                    <div class="fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">
                                        {{ admin_t('Meals Included') }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($dayMeals as $m)
                                            @php
                                                $mLower = strtolower((string) $m);
                                                if (in_array($mLower, ['breakfast', 'bf'])) {
                                                    $mealText = admin_t('Breakfast');
                                                } elseif (in_array($mLower, ['lunch', 'Lunch'])) {
                                                    $mealText = admin_t('Lunch');
                                                } elseif (in_array($mLower, ['dinner', 'Dinner'])) {
                                                    $mealText = admin_t('Dinner');
                                                } else {
                                                    $mealText = admin_t(ucfirst($mLower));
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
                    @endforeach
                @else
                    <div class="info-box">
                        <div class="info-value">-</div>
                    </div>
                @endif

                {{-- Included and Excluded --}}
                <h2 class="section-title" id="trip-inclusions">Included and Excluded</h2>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Included</div>

                            @if (isset($package->inclusions) && $package->inclusions->where('type', 'included')->count())
                                <ul class="mb-0">
                                    @foreach ($package->inclusions->where('type', 'included') as $item)
                                        <li>{{ $item->title }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Not Included</div>

                            @if (isset($package->inclusions) && $package->inclusions->where('type', 'excluded')->count())
                                <ul class="mb-0">
                                    @foreach ($package->inclusions->where('type', 'excluded') as $item)
                                        <li>{{ $item->title }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Prices --}}
                <h2 class="section-title" id="trip-pricing">Prices</h2>

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Adult Price</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->adult_price ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Child Price</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->child_price ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Infant Price</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->infant_price ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Compare-at Price</div>
                            <div class="info-value">
                                {{ $package->compare_price ? number_format($package->compare_price, 2) : '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Displayed Price Range</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->price_from ?? 0), 2) }}
                                {{ optional($package->currency)->code }} -
                                {{ number_format((float) ($package->price_to ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>
                </div>

                @if (isset($package->prices) && $package->prices->count())
                    @if ($package->package_type === 'day_tour' && !empty($package->group_pricing_tiers))
                        <div class="mt-4">
                            <h4 class="mb-3 text-white">{{ admin_t('Group-Size Pricing Tiers') }}</h4>
                            <div class="table-responsive">
                                <table class="table table-dark-custom">
                                    <thead>
                                        <tr>
                                            <th>{{ admin_t('Tier Title') }}</th>
                                            <th>{{ admin_t('Persons') }}</th>
                                            <th>{{ admin_t('Price Per Person') }}</th>
                                            <th>{{ admin_t('Badge') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ((array) $package->group_pricing_tiers as $tier)
                                            @php
                                                $tMin = $tier['min'] ?? null;
                                                $tMax = $tier['max'] ?? null;
                                                $pLabel =
                                                    $tMin && $tMax
                                                        ? ($tMin == $tMax
                                                            ? "$tMin Pax"
                                                            : "$tMin–$tMax Pax")
                                                        : ($tMin
                                                            ? "$tMin+ Pax"
                                                            : ($tMax
                                                                ? "Up to $tMax Pax"
                                                                : '-'));
                                            @endphp
                                            <tr>
                                                <td>{{ $tier['title'] ?? ($tier['label'] ?? '-') }}</td>
                                                <td>{{ $pLabel }}</td>
                                                <td><strong
                                                        class="text-warning">${{ number_format((float) ($tier['price_per_person'] ?? 0), 2) }}</strong>
                                                </td>
                                                <td>{{ $tier['badge_label'] ?? ($tier['badge'] ?? '-') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @elseif (isset($package->prices) && $package->prices->count())
                        <div class="table-responsive">
                            <table class="table table-dark-custom">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Season</th>
                                        <th>Price Type</th>
                                        <th>Room</th>
                                        <th>Number of Guests</th>
                                        <th>Amount</th>
                                        <th>Currency</th>
                                        <th>Valid From</th>
                                        <th>Valid To</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($package->prices as $price)
                                        <tr>
                                            <td>{{ adminTrans($price->label ?? '') ?: '-' }}</td>
                                            <td>{{ adminTrans($price->season_name ?? '') ?: '-' }}</td>
                                            <td>{{ $price->price_type ?? '-' }}</td>
                                            <td>{{ $price->room_type ?? '-' }}</td>
                                            <td>
                                                @if ($price->pax_min && $price->pax_max && $price->pax_min === $price->pax_max)
                                                    {{ $price->pax_min }}
                                                @elseif ($price->pax_min && $price->pax_max)
                                                    {{ $price->pax_min }} - {{ $price->pax_max }}
                                                @elseif ($price->pax_min)
                                                    {{ $price->pax_min }}+
                                                @elseif ($price->pax_max)
                                                    1 - {{ $price->pax_max }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ number_format($price->amount ?? 0, 2) }}</td>
                                            <td>{{ optional($price->currency)->code ?? (optional($package->currency)->code ?? '-') }}
                                            </td>
                                            <td>{{ $price->valid_from ?? '-' }}</td>
                                            <td>{{ $price->valid_to ?? '-' }}</td>
                                            <td>{{ adminTrans($price->notes ?? '') ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="info-box">
                            <div class="info-value">-</div>
                        </div>
                    @endif

                    <div class="info-box mt-3">
                        <div class="info-label">Pricing Notes</div>
                        <div class="info-value">{{ adminTrans($package->pricing_information ?? '') ?: '-' }}</div>
                    </div>

                    {{-- Policies --}}
                    <h2 class="section-title" id="trip-policies">Policies And Terms</h2>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-box">
                                <div class="info-label">{{ __('trips.age_policy') }}</div>
                                <div class="info-value">
                                    Adults: {{ (int) ($package->adult_min_age ?? 12) }}+<br>
                                    Children: {{ (int) ($package->child_min_age ?? 2) }} -
                                    {{ (int) ($package->child_max_age ?? 11) }}<br>
                                    Infants: {{ (int) ($package->infant_min_age ?? 0) }} -
                                    {{ (int) ($package->infant_max_age ?? 1) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!empty($package->faq_json) && is_array($package->faq_json))
                        <h2 class="section-title" id="trip-faq">FAQs</h2>

                        @foreach ($package->faq_json as $faq)
                            <div class="info-box">
                                <div class="info-label">
                                    {{ is_array($faq['question'] ?? null) ? $faq['question'][app()->getLocale()] ?? ($faq['question']['en'] ?? '-') : $faq['question'] ?? '-' }}
                                </div>
                                <div class="info-value">
                                    {{ is_array($faq['answer'] ?? null) ? $faq['answer'][app()->getLocale()] ?? ($faq['answer']['en'] ?? '-') : $faq['answer'] ?? '-' }}
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">Children Policy</div>
                                <div class="info-value">{{ adminTrans($package->children_policy ?? '') ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">Pickup Policy</div>
                                <div class="info-value">{{ adminTrans($package->pickup_policy ?? '') ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">Cancellation Policy</div>
                                <div class="info-value">{{ adminTrans($package->cancellation_policy ?? '') ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">Terms & Conditions</div>
                                <div class="info-value">{{ adminTrans($package->terms_conditions ?? '') ?: '-' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Participants and Rating --}}
                    <h2 class="section-title" id="trip-participants">Participants and Rating</h2>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Minimum Participants</div>
                                <div class="info-value">{{ $package->min_participants ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Maximum Participants</div>
                                <div class="info-value">{{ $package->max_participants ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Advance Booking Days</div>
                                <div class="info-value">{{ $package->booking_lead_days ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Rating</div>
                                <div class="info-value">{{ $package->rating_avg ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Review Count</div>
                                <div class="info-value">{{ $package->reviews_count ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Difficulty Level</div>
                                <div class="info-value">{{ $package->difficulty_level ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">Video URL</div>
                                <div class="info-value">
                                    @if (!empty($package->video_url))
                                        <a href="{{ $package->video_url }}" target="_blank" class="text-white"
                                            rel="noopener">
                                            {{ $package->video_url }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Publishing --}}
                    <h2 class="section-title" id="trip-publishing">Publishing and Settings</h2>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Publish Date</div>
                                <div class="info-value">{{ optional($package->published_at)->format('Y-m-d') ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Featured</div>
                                <div class="info-value">{{ $package->is_featured ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Best Seller</div>
                                <div class="info-value">{{ $package->is_best_seller ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box">
                                <div class="info-label">Ultra Luxury</div>
                                <div class="info-value">{{ $package->is_ultra_luxury ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- SEO --}}
                    <h2 class="section-title" id="trip-seo">SEO</h2>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">SEO Title</div>
                                <div class="info-value">{{ adminTrans($package->seo_title ?? '') ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">Breadcrumb Title</div>
                                <div class="info-value">{{ adminTrans($package->breadcrumb_title ?? '') ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="info-box">
                                <div class="info-label">SEO Description</div>
                                <div class="info-value">{{ adminTrans($package->seo_description ?? '') ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-label">Canonical URL</div>
                                <div class="info-value">{{ $package->canonical_url ?? '-' }}</div>
                            </div>
                        </div>
                    </div>


            </div>
        </div>
    </div>
@endsection
