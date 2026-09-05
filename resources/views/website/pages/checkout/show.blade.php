@extends('website.layouts.master')

@section('title', __('Secure Checkout') . ' | ' . $title)
@section('description', __('Complete your booking securely online.'))
@section('robots', 'noindex, nofollow')
@section('preferred_theme', 'light')
@section('body_class', 'checkout-page')

@section('css')
    <style>
        .checkout-page .why-choose-section,
        .checkout-page .luxury-cta-section {
            display: none !important
        }

        .checkout-shell {
            background: #f8f6f1;
            padding: 130px 0 80px;
            color: #263b61;
            min-height: 70vh
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(310px, .95fr);
            gap: 26px;
            align-items: start
        }

        .checkout-panel,
        .booking-summary {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(21, 39, 67, .09);
            overflow: hidden;
            border: 1px solid rgba(28, 50, 92, .07)
        }

        .booking-summary {
            position: sticky;
            top: 105px
        }

        .checkout-head,
        .summary-head {
            background: linear-gradient(135deg, #173b63, #26576b);
            color: #fff;
            padding: 26px 32px;
            border-top: 5px solid #e6ba7d
        }

        .checkout-head h1,
        .summary-head h2 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            margin: 0 0 4px;
            font-size: clamp(1.55rem, 3vw, 2.15rem)
        }

        .checkout-head p,
        .summary-head p {
            margin: 0;
            color: rgba(255, 255, 255, .78)
        }

        .checkout-form {
            padding: 30px 32px 36px
        }

        .checkout-section {
            padding-bottom: 30px;
            margin-bottom: 30px;
            border-bottom: 1px solid #eadfce
        }

        .checkout-section:last-of-type {
            border-bottom: 0
        }

        .checkout-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            color: #1c325c;
            margin: 0 0 22px
        }

        .checkout-section-title i {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e7b676;
            color: #1c325c;
            display: grid;
            place-items: center;
            font-size: 1.2rem
        }

        .checkout-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px
        }

        .field-full {
            grid-column: 1/-1
        }

        .checkout-field label {
            display: block;
            font-weight: 700;
            font-size: .82rem;
            margin: 0 0 8px;
            color: #30466e
        }

        .checkout-field input,
        .checkout-field select,
        .checkout-field textarea {
            width: 100%;
            border: 1px solid #dce2e8;
            border-radius: 13px;
            background: #fff;
            color: #243858;
            padding: 13px 15px;
            min-height: 52px;
            outline: none;
            transition: .2s
        }

        .checkout-field textarea {
            min-height: 108px;
            resize: vertical
        }

        .checkout-field input:focus,
        .checkout-field select:focus,
        .checkout-field textarea:focus {
            border-color: #c9934d;
            box-shadow: 0 0 0 3px rgba(201, 147, 77, .13)
        }

        .guest-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px
        }

        .guest-box {
            background: #f7f8fa;
            border: 1px solid #e7eaf0;
            border-radius: 14px;
            padding: 13px
        }

        .guest-box label {
            font-weight: 700;
            font-size: .82rem
        }

        .guest-box input {
            margin-top: 7px;
            width: 100%;
            border: 1px solid #dce2e8;
            border-radius: 10px;
            padding: 10px
        }

        .price-options {
            display: grid;
            gap: 12px
        }

        .price-option {
            display: block;
            position: relative;
            cursor: pointer
        }

        .price-option input {
            position: absolute;
            opacity: 0
        }

        .price-option-body {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 16px 18px;
            transition: .2s
        }

        .price-option input:checked+.price-option-body {
            border-color: #c9934d;
            background: #fffaf3;
            box-shadow: 0 0 0 3px rgba(201, 147, 77, .10)
        }

        .price-option-title {
            font-weight: 800;
            color: #1c325c
        }

        .price-option-desc {
            display: block;
            font-size: .78rem;
            color: #788391;
            margin-top: 4px
        }

        .price-option-amount {
            font-family: 'Playfair Display', serif;
            color: #bd8237;
            font-weight: 800;
            white-space: nowrap
        }

        .price-option-unit {
            display: block;
            font: 500 .68rem Inter, sans-serif;
            color: #7c8795;
            text-align: right
        }

        .traveler-card {
            background: #f7f8fa;
            border: 1px solid #e9edf1;
            border-radius: 15px;
            padding: 17px;
            margin-top: 13px
        }

        .traveler-label {
            font-weight: 800;
            color: #29436e;
            margin-bottom: 12px
        }

        .traveler-fields {
            display: grid;
            grid-template-columns: .65fr 1.35fr 1.35fr;
            gap: 12px
        }

        .traveler-fields select,
        .traveler-fields input {
            width: 100%;
            border: 1px solid #dce2e8;
            border-radius: 11px;
            padding: 11px;
            background: #fff
        }

        .payment-options {
            display: grid;
            gap: 12px
        }

        .payment-option {
            position: relative;
            display: block;
            cursor: pointer
        }

        .payment-option input {
            position: absolute;
            opacity: 0
        }

        .payment-card {
            display: grid;
            grid-template-columns: 1fr 150px;
            align-items: center;
            gap: 16px;
            border: 2px solid #e4e8ed;
            border-radius: 15px;
            padding: 14px 18px;
            transition: .2s
        }

        .payment-option input:checked+.payment-card {
            border-color: #c9934d;
            background: #fffaf3
        }

        .payment-name {
            display: block;
            font-weight: 800;
            color: #1c325c
        }

        .payment-desc {
            display: block;
            font-size: .76rem;
            color: #7b8795;
            margin-top: 3px
        }

        .payment-card img {
            width: 145px;
            height: 42px;
            object-fit: contain;
            justify-self: end
        }

        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: .86rem;
            color: #59677a;
            margin: 22px 0
        }

        .terms-row input {
            margin-top: 3px
        }

        .checkout-submit {
            width: 100%;
            border: 0;
            border-radius: 999px;
            padding: 17px;
            background: linear-gradient(90deg, #ca934e, #e9b879);
            color: #173763;
            font-weight: 900;
            font-size: 1rem;
            box-shadow: 0 12px 27px rgba(194, 139, 69, .28);
            cursor: pointer
        }

        .checkout-submit:disabled {
            opacity: .55;
            cursor: not-allowed
        }

        .checkout-alert {
            border-radius: 13px;
            padding: 13px 16px;
            margin-bottom: 20px;
            background: #fff2f2;
            color: #a12c2c;
            border: 1px solid #f0caca
        }

        .checkout-alert ul {
            margin: 0;
            padding-inline-start: 20px
        }

        .summary-body {
            padding: 25px
        }

        .summary-image {
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: 15px;
            object-fit: cover
        }

        .summary-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            color: #1c325c;
            margin: 16px 0
        }

        .summary-meta {
            background: #faf7f2;
            border-radius: 15px;
            padding: 15px;
            display: grid;
            gap: 11px
        }

        .summary-line {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: .84rem
        }

        .summary-line i {
            color: #c88d43;
            font-size: 1.15rem
        }

        .summary-line span {
            display: block;
            color: #7d8793;
            font-size: .7rem
        }

        .summary-line strong {
            color: #2c4269
        }

        .summary-total {
            border: 1px solid #ead5b5;
            border-radius: 15px;
            padding: 16px;
            margin-top: 18px
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 8px 0;
            color: #344563
        }

        .total-row.grand {
            border-top: 1px solid #ead5b5;
            margin-top: 7px;
            padding-top: 15px;
            font-weight: 900;
            color: #1c325c
        }

        .total-row.grand strong {
            color: #bf8339
        }

        .security-strip {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 18px;
            background: #faf7f2;
            border: 1px solid #ede2d2;
            border-radius: 13px;
            padding: 14px;
            margin-top: 22px;
            font-size: .76rem;
            font-weight: 700
        }

        .security-strip i {
            color: #25b987;
            margin-right: 4px
        }

        html[dir=rtl] .price-option-unit {
            text-align: left
        }

        @media(max-width:991px) {
            .checkout-grid {
                grid-template-columns: 1fr
            }

            .booking-summary {
                order: -1;
                position: static
            }

            .summary-body {
                display: grid;
                grid-template-columns: 180px 1fr;
                gap: 18px
            }

            .summary-image {
                height: 125px
            }

            .summary-title {
                margin-top: 0
            }

            .summary-meta,
            .summary-total {
                grid-column: 2
            }

            .summary-total {
                margin-top: 0
            }
        }

        @media(max-width:650px) {
            .checkout-shell {
                padding: 100px 0 50px
            }

            .checkout-form,
            .checkout-head {
                padding: 23px 18px
            }

            .checkout-fields,
            .guest-grid {
                grid-template-columns: 1fr
            }

            .traveler-fields {
                grid-template-columns: 1fr
            }

            .payment-card {
                grid-template-columns: 1fr 105px;
                padding: 12px
            }

            .payment-card img {
                width: 105px
            }

            .summary-body {
                display: block
            }

            .summary-meta {
                margin-top: 14px
            }

            .summary-total {
                margin-top: 14px
            }

            .price-option-body {
                padding: 13px
            }
        }
    </style>
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
                                <div class="checkout-field"><label
                                        for="rooms">{{ $package->package_type === 'nile_cruise' ? __('Number of Cabins') : __('Number of Rooms') }}
                                        *</label><input id="rooms" name="rooms" type="number" min="1"
                                        max="20" value="{{ old('rooms', request('rooms', 1)) }}" required></div>
                                <div class="field-full price-options" id="priceOptions">
                                    @foreach ($pricingOptions as $option)
                                        <label class="price-option">
                                            <input type="radio" name="pricing_option" value="{{ $option['id'] }}"
                                                @checked(old('pricing_option', request('pricing_option', $pricingOptions->first()['id'] ?? '')) === $option['id']) required>
                                            <span class="price-option-body">
                                                <span><span class="price-option-title">{{ $option['label'] }}</span><span
                                                        class="price-option-desc">{{ $option['description'] }}@if ($option['available_rooms'] !== null)
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
                                <div class="field-full guest-grid">
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
                            </div>
                        </section>

                        <section class="checkout-section">
                            <h2 class="checkout-section-title"><i class="la la-map-marked"></i>{{ __('Travel Details') }}
                            </h2>
                            <div class="checkout-fields">
                                <div class="checkout-field field-full"><label
                                        for="pickup_location">{{ __('Pickup Location') }}</label><input
                                        id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}"
                                        placeholder="{{ __('Hotel name, airport, or specific address') }}"></div>
                                <div class="checkout-field field-full"><label
                                        for="special_requests">{{ __('Special Requests') }}</label>
                                    <textarea id="special_requests" name="special_requests"
                                        placeholder="{{ __('Dietary requirements, accessibility needs, or special occasions') }}">{{ old('special_requests') }}</textarea>
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
                                <div><span>{{ __('Travel Date') }}</span><strong id="summaryDate">—</strong></div>
                            </div>
                            <div class="summary-line"><i class="la la-bed"></i>
                                <div><span>{{ __('Accommodation') }}</span><strong id="summaryOption">—</strong></div>
                            </div>
                            <div class="summary-line"><i class="la la-users"></i>
                                <div><span>{{ __('Total Guests') }}</span><strong id="summaryGuests">1</strong></div>
                            </div>
                        </div>
                        <div class="summary-total">
                            <div class="total-row"><span>{{ __('Package Price') }}</span><strong
                                    id="summarySubtotal">—</strong></div>
                            <div class="total-row">
                                <span>{{ __('Taxes & Fees') }}</span><strong>{{ __('Included') }}</strong>
                            </div>
                            <div class="total-row grand"><span>{{ __('Pay Today') }}</span><strong
                                    id="summaryTotal">—</strong></div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = @json($pricingOptions->keyBy('id'));
            const oldTravelers = @json(old('travelers', []));
            const adults = document.getElementById('adults');
            const children = document.getElementById('children');
            const infants = document.getElementById('infants');
            const rooms = document.getElementById('rooms');
            const date = document.getElementById('travel_date');
            const travelerContainer = document.getElementById('travelersContainer');
            let travelerValues = Array.isArray(oldTravelers) ? oldTravelers : [];

            function selectedOption() {
                const input = document.querySelector('input[name="pricing_option"]:checked');
                return input ? options[input.value] : null;
            }

            function number(input) {
                return Math.max(0, parseInt(input.value || '0', 10));
            }

            function money(value, option) {
                return (option.currency_symbol || option.currency_code + ' ') + Number(value).toLocaleString(
                    undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
            }

            function total(option) {
                if (!option) return 0;
                if (option.price_unit === 'per_booking') return Number(option.amount);
                if (option.price_unit === 'per_room') return Number(option.amount) * Math.max(1, number(rooms));
                if (option.price_unit === 'per_adult') return Number(option.amount) * Math.max(1, number(adults));
                if (option.price_unit === 'category') return Number(option.adult_price || 0) * number(adults) +
                    Number(option.child_price || 0) * number(children) + Number(option.infant_price || 0) *
                    number(infants);
                return Number(option.amount) * Math.max(1, number(adults) + number(children));
            }

            function syncSummary() {
                const option = selectedOption();
                const guests = number(adults) + number(children) + number(infants);
                document.getElementById('summaryDate').textContent = date.value || '—';
                document.getElementById('summaryOption').textContent = option ? option.label : '—';
                document.getElementById('summaryGuests').textContent = guests;
                document.getElementById('summarySubtotal').textContent = option ? money(total(option), option) :
                    '—';
                document.getElementById('summaryTotal').textContent = option ? money(total(option), option) : '—';
            }

            function syncOptionAvailability() {
                const selectedDate = date.value;
                const guests = number(adults) + number(children) + number(infants);
                let firstAvailable = null;
                document.querySelectorAll('.price-option').forEach(label => {
                    const input = label.querySelector('input');
                    const option = options[input.value];
                    const dateValid = !selectedDate || ((!option.valid_from || selectedDate >= option
                        .valid_from) && (!option.valid_to || selectedDate <= option.valid_to));
                    const paxValid = (!option.pax_min || guests >= Number(option.pax_min)) && (!option
                        .pax_max || guests <= Number(option.pax_max));
                    const valid = dateValid && paxValid;
                    label.style.display = valid ? '' : 'none';
                    input.disabled = !valid;
                    if (valid && !firstAvailable) firstAvailable = input;
                });
                const checked = document.querySelector('input[name="pricing_option"]:checked:not(:disabled)');
                if (!checked && firstAvailable) firstAvailable.checked = true;
                document.getElementById('noPriceForDate').style.display = firstAvailable ? 'none' : 'block';
            }

            function snapshotTravelers() {
                travelerValues = Array.from(travelerContainer.querySelectorAll('.traveler-card')).map(card => ({
                    title: card.querySelector('select').value,
                    first_name: card.querySelector('input[data-first]').value,
                    last_name: card.querySelector('input[data-last]').value
                }));
            }

            function renderTravelers() {
                snapshotTravelers();
                const types = [...Array(number(adults)).fill(@json(__('Adult'))), ...Array(number(
                    children)).fill(@json(__('Child'))), ...Array(number(infants)).fill(
                    @json(__('Infant')))];
                travelerContainer.innerHTML = types.map((type, index) => {
                    const value = travelerValues[index] || {};
                    const opts = ['Mr', 'Mrs', 'Ms', 'Miss', 'Dr'].map(v =>
                        `<option value="${v}" ${value.title===v?'selected':''}>${v}</option>`).join('');
                    return `<div class="traveler-card"><div class="traveler-label">${index===0 ? @json(__('Lead Traveler')) : @json(__('Traveler'))+' '+(index+1)} · ${type}</div><div class="traveler-fields"><select name="travelers[${index}][title]" required><option value="">${@json(__('Title'))}</option>${opts}</select><input data-first name="travelers[${index}][first_name]" value="${String(value.first_name||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;')}" placeholder="${@json(__('First name as shown on passport'))}" required><input data-last name="travelers[${index}][last_name]" value="${String(value.last_name||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;')}" placeholder="${@json(__('Last name as shown on passport'))}" required></div></div>`;
                }).join('');
            }
            [adults, children, infants].forEach(input => input.addEventListener('change', () => {
                renderTravelers();
                syncOptionAvailability();
                syncSummary();
            }));
            rooms.addEventListener('change', syncSummary);
            date.addEventListener('change', () => {
                syncOptionAvailability();
                syncSummary();
            });
            document.querySelectorAll('input[name="pricing_option"]').forEach(input => input.addEventListener(
                'change', syncSummary));
            renderTravelers();
            syncOptionAvailability();
            syncSummary();
        });
    </script>
@endsection
