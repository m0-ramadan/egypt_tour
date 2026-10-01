@extends('website.layouts.master')

@section('title', __('Secure Checkout') . ' | ' . $title)
@section('description', __('Complete your booking securely online.'))
@section('robots', 'noindex, nofollow')
@section('preferred_theme', 'light')
@section('body_class', 'checkout-page')

@section('css')
    @vite('resources/css/pages/checkout-show.css')
@endsection

@section('content')
    <section class="checkout-shell">
        <div class="container">
            <div class="checkout-grid">
                <div class="checkout-panel">
                    <header class="checkout-head">
                        <h1><i class="la la-credit-card"></i> {{ __('Secure Checkout') }}</h1>
                        <p>{{ __('Complete your booking details and continue to secure payment.') }}</p>
                    </header>

                    <form class="checkout-form" method="post" action="{{ route('website.checkout.store', $package->slug) }}"
                        id="checkoutForm">
                        @csrf
                        @if ($errors->any())
                            <div class="checkout-alert" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <section class="checkout-section">
                            <h2 class="checkout-section-title"><i
                                    class="la la-calendar-check"></i>{{ __('Booking Details') }}</h2>
                            <div class="checkout-fields">
                                <div class="checkout-field"><label for="travel_date">{{ __('Travel Date') }} *</label><input
                                        id="travel_date" name="travel_date" type="date"
                                        min="{{ today()->toDateString() }}"
                                        value="{{ old('travel_date', request('travel_date')) }}" required></div>
                                @if ($package->package_type === 'day_tour')
                                    <input id="rooms" name="rooms" type="hidden" value="1">
                                @else
                                    <div class="checkout-field"><label
                                            for="rooms">{{ $package->package_type === 'nile_cruise' ? __('Number of Cabins') : __('Number of Rooms') }}
                                            *</label><input id="rooms" name="rooms" type="number" min="1"
                                            max="20" value="{{ old('rooms', request('rooms', 1)) }}" required></div>
                                @endif
                                @if ($isTravelPackage)
                                    <input type="hidden" name="pricing_option" value="travel_package">
                                    @if ($accommodation)
                                        <input type="hidden" name="accommodation" value="{{ $accommodation }}">
                                    @endif
                                    @foreach ($roomsData as $idx => $r)
                                        <input type="hidden" name="room_{{ $idx + 1 }}_adults"
                                            value="{{ $r['adults'] }}">
                                        <input type="hidden" name="room_{{ $idx + 1 }}_children"
                                            value="{{ $r['children'] }}">
                                    @endforeach
                                @else
                                    <div class="field-full price-options" id="priceOptions">
                                        @foreach ($pricingOptions as $option)
                                            <label class="price-option">
                                                <input type="radio" name="pricing_option" value="{{ $option['id'] }}"
                                                    @checked(old('pricing_option', request('pricing_option', $pricingOptions->first()['id'] ?? '')) === $option['id']) required>
                                                <span class="price-option-body">
                                                    <span><span
                                                            class="price-option-title">{{ $option['label'] }}</span><span
                                                            class="price-option-desc">{{ $option['description'] }}
                                                            @if ($option['available_rooms'] !== null)
                                                                ·
                                                                {{ trans_choice(':count cabin available|:count cabins available', $option['available_rooms'], ['count' => $option['available_rooms']]) }}
                                                            @endif
                                                        </span>
                                                    </span>
                                                    <span
                                                        class="price-option-amount">{{ $option['currency_symbol'] }}{{ number_format($option['amount'], 2) }}<span
                                                            class="price-option-unit">{{ match ($option['price_unit']) {'per_room' => __('per room'),'per_booking' => __('per booking'),'category' => __('by traveler'),'per_adult' => __('per adult'),default => __('per person')} }}</span></span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="checkout-alert field-full" id="noPriceForDate" style="display:none">
                                        {{ __('There is no online booking price for the selected date. Please choose another date or submit an enquiry.') }}
                                    </div>
                                @endif
                                <div class="field-full guest-grid"
                                    @if ($isTravelPackage) style="display:none;" @endif>
                                    <div class="guest-box"><label for="adults">{{ __('Adults') }}</label><input
                                            id="adults" name="adults" type="number" min="1" max="40"
                                            value="{{ old('adults', request('adults', 1)) }}" required></div>
                                    <div class="guest-box"><label for="children">{{ __('Children') }}</label><input
                                            id="children" name="children" type="number" min="0" max="40"
                                            value="{{ old('children', request('children', 0)) }}" required></div>
                                    <div class="guest-box"><label for="infants">{{ __('Infants') }}</label><input
                                            id="infants" name="infants" type="number" min="0" max="20"
                                            value="{{ old('infants', request('infants', 0)) }}" required></div>
                                </div>
                            </div>
                        </section>

                        <section class="checkout-section">
                            <h2 class="checkout-section-title"><i class="la la-users"></i>{{ __('Traveler Information') }}
                            </h2>
                            <div id="travelersContainer"></div>
                            <div class="checkout-fields" style="margin-top:18px">
                                <div class="checkout-field"><label for="email">{{ __('Email Address') }} *</label><input
                                        id="email" name="email" type="email" value="{{ old('email') }}"
                                        autocomplete="email" required></div>
                                <div class="checkout-field"><label for="phone">{{ __('Phone Number') }} *</label><input
                                        id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                                        autocomplete="tel" required></div>
                                <div class="checkout-field field-full"><label for="country">{{ __('Country') }}</label>
                                    <select id="country" name="country">
                                        <option value="">{{ __('Select your country') }}</option>
                                        @foreach ($countries as $cName)
                                            <option value="{{ $cName }}" @selected(old('country') === $cName)>
                                                {{ __($cName) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="checkout-section">
                            <h2 class="checkout-section-title"><i class="la la-map-marked"></i>{{ __('Travel Details') }}
                            </h2>
                            <div class="checkout-fields">
                                <div class="checkout-field field-full"><label
                                        for="pickup_location">{{ __('Pickup Location') }} *</label><input
                                        id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}"
                                        placeholder="{{ __('Hotel name, airport, or specific address in Egypt') }}"
                                        required>
                                    <small
                                        style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">{{ __('Please provide your hotel name, airport, or specific pickup address in Egypt') }}</small>
                                </div>
                                <div class="checkout-field field-full"><label
                                        for="special_requests">{{ __('Special Requests') }}</label>
                                    <textarea id="special_requests" name="special_requests"
                                        placeholder="{{ __('Tell us about any dietary requirements, accessibility needs, special occasions, or other requests...') }}">{{ old('special_requests') }}</textarea>
                                </div>
                            </div>
                        </section>

                        <section class="checkout-section">
                            <h2 class="checkout-section-title"><i class="la la-lock"></i>{{ __('Payment Method') }}</h2>
                            @if ($paymentMethods->isEmpty())
                                <div class="checkout-alert">
                                    {{ __('Online payment is temporarily unavailable. Please submit an enquiry and our team will assist you.') }}
                                </div>
                            @else
                                <div class="payment-options">
                                    @foreach ($paymentMethods as $method)
                                        <label class="payment-option">
                                            <input type="radio" name="payment_method"
                                                value="{{ $method['provider'] }}" @checked(old('payment_method', $paymentMethods->first()['provider'] ?? '') === $method['provider']) required>
                                            <span class="payment-card"><span><span
                                                        class="payment-name">{{ $method['name'] }}</span><span
                                                        class="payment-desc">{{ $method['description'] }}</span></span><img
                                                    src="{{ $method['image'] }}" alt="{{ $method['name'] }}"></span>

                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            {{-- @if ($availablePaymentMethods->isEmpty())
                                <div class="checkout-alert mt-3">
                                    {{ __('Paymob and PayPal have been added. Online payment will be enabled after the merchant credentials are configured and the methods are activated.') }}
                                </div>
                            @endif --}}
                            <label class="terms-row"><input type="checkbox" name="terms" value="1"
                                    @checked(old('terms'))
                                    required><span>{{ __('By continuing, you agree to the booking terms and cancellation policy.') }}</span></label>
                            <button class="checkout-submit" type="submit" @disabled($paymentMethods->isEmpty())><i
                                    class="la la-credit-card"></i> {{ __('Continue to Secure Payment') }}</button>

                            <div class="security-strip"><span><i
                                        class="la la-shield-alt"></i>{{ __('SSL Encrypted') }}</span><span><i
                                        class="la la-lock"></i>{{ __('Secure Payment') }}</span><span><i
                                        class="la la-check-circle"></i>{{ __('Instant Confirmation') }}</span></div>
                        </section>
                    </form>
                </div>

                <aside class="booking-summary">
                    <header class="summary-head">
                        <h2>{{ __('Your Booking') }}</h2>
                        <p>{{ $durationText }}</p>
                    </header>
                    <div class="summary-body">
                        <img class="summary-image" src="{{ $heroImage }}" alt="{{ $title }}">
                        <h3 class="summary-title">{{ $title }}</h3>
                        <div class="summary-meta">
                            <div class="summary-line"><i class="la la-calendar"></i>
                                <div><span>{{ __('Travel Date') }}</span><strong
                                        id="summaryDate">{{ request('travel_date') ?: '—' }}</strong></div>
                            </div>
                            @if ($package->package_type === 'day_tour')
                                <div class="summary-line"><i class="la la-compass"></i>
                                    <div><span>{{ __('Tour Type') }}</span><strong>{{ __('Day Tour') }}</strong></div>
                                </div>
                            @else
                                <div class="summary-line"><i class="la la-bed"></i>
                                    <div><span>{{ __('Accommodation') }}</span><strong
                                            id="summaryOption">{{ $travelPackageQuote['accommodation_name'] ?? ($accommodation ?: '—') }}</strong>
                                    </div>
                                </div>
                            @endif
                            <div class="summary-line"><i class="la la-users"></i>
                                <div><span>{{ __('Total Guests') }}</span><strong
                                        id="summaryGuests">{{ (int) request('adults', 1) + (int) request('children', 0) + (int) request('infants', 0) }}</strong>
                                </div>
                            </div>
                        </div>

                        @if ($isTravelPackage && !empty($travelPackageQuote['room_breakdown']))
                            <div class="checkout-room-details mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                                <h4
                                    style="font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #061B3E; margin-bottom: 12px;">
                                    {{ __('Room Details') }}</h4>
                                @foreach ($travelPackageQuote['room_breakdown'] as $r)
                                    <div class="room-summary-box mb-2 p-2 rounded"
                                        style="background: #fdfbf7; border: 1px solid rgba(6, 27, 62, 0.08);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong style="color: #061B3E;">{{ __('Room') }}
                                                {{ $r['room_number'] }}</strong>
                                            <strong style="color: #061B3E;">{{ __('Room') }} {{ $r['room_number'] }}
                                                @if (!empty($r['accommodation']))
                                                    <span class="badge"
                                                        style="background: rgba(243, 107, 10, 0.15); color: #8c6721; font-weight: 600; font-size: 11px;">{{ $r['accommodation'] }}</span>
                                                @endif
                                            </strong>
                                            <span
                                                style="color: var(--rich-gold, var(--etp-orange-500, #F36B0A)); font-weight: 700;">{{ $travelPackageQuote['currency_symbol'] }}{{ number_format($r['price'], 0) }}</span>
                                        </div>
                                        <small class="text-muted"><i class="la la-users"></i> {{ $r['adults'] }}
                                            {{ __('Adults') }}{{ $r['children'] > 0 ? ', ' . $r['children'] . ' ' . __('Children') : '' }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="summary-total">
                            <div class="total-row"><span>{{ __('Package Price') }}</span><strong
                                    id="summarySubtotal">{{ $travelPackageQuote ? $travelPackageQuote['currency_symbol'] . number_format($travelPackageQuote['total'], 0) : '—' }}</strong>
                            </div>
                            <div class="total-row">
                                <span>{{ __('Taxes & Fees') }}</span><strong>{{ __('Included') }}</strong>
                            </div>
                            <div class="total-row grand">
                                <span>{{ $isTravelPackage ? __('Pay Today (50% Deposit)') : __('Pay Today') }}</span><strong
                                    id="summaryTotal">{{ $travelPackageQuote ? $travelPackageQuote['currency_symbol'] . number_format($travelPackageQuote['deposit_amount'], 0) : '—' }}</strong>
                            </div>
                        </div>

                        @if ($isTravelPackage && !empty($travelPackageQuote['remaining_balance']))
                            <div
                                style="background: #e8f5e8; border-radius: 10px; padding: 12px; margin-top: 14px; border-left: 4px solid #28a745;">
                                <small style="color: #28a745; font-weight: 600; display: block; line-height: 1.4;">
                                    💡
                                    {{ __('Remaining balance of :symbol:amount due 30 days before travel', [
                                        'symbol' => $travelPackageQuote['currency_symbol'],
                                        'amount' => number_format($travelPackageQuote['remaining_balance'], 0),
                                    ]) }}
                                </small>
                            </div>
                        @endif

                        <div class="security-strip mt-3"
                            style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #4b5563; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                            <span><i class="la la-shield-alt text-success"
                                    style="font-size: 16px; margin-right: 4px;"></i> {{ __('Secure Booking') }}</span>
                            <span><i class="la la-headset text-primary" style="font-size: 16px; margin-right: 4px;"></i>
                                {{ __('24/7 Support') }}</span>
                            <span><i class="la la-award text-warning" style="font-size: 16px; margin-right: 4px;"></i>
                                {{ __('Licensed Operator') }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script type="application/json" id="checkoutConfig">
        {
            "options": @json($pricingOptions->keyBy('id')),
            "oldTravelers": @json(old('travelers', [])),
            "isTravelPackage": @json($isTravelPackage ?? false),
            "translations": {
                "adult": @json(__('Adult')),
                "child": @json(__('Child')),
                "infant": @json(__('Infant')),
                "leadTravelerInfo": @json(__('Lead Traveler Information')),
                "otherTravelersInfo": @json(__('Other Travelers Information')),
                "otherTravelersDesc": @json(__('Please provide details for the remaining travelers')),
                "leadTraveler": @json(__('Lead Traveler')),
                "traveler": @json(__('Traveler')),
                "title": @json(__('Title')),
                "firstNamePlaceholder": @json(__('First name as shown on passport')),
                "lastNamePlaceholder": @json(__('Last name as shown on passport'))
            }
        }
    </script>
    @vite('resources/js/pages/checkout-show.js')
@endsection
