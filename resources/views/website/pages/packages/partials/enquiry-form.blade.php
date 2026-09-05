<form method="post" action="{{ route('website.inquiries.store') }}">
    @csrf
    @php
        $suffix = $formSuffix ?? 'form';
        $adultMinAge = (int) ($package->adult_min_age ?? 12);
        $childMinAge = (int) ($package->child_min_age ?? 2);
        $childMaxAge = (int) ($package->child_max_age ?? 11);
        $infantMinAge = (int) ($package->infant_min_age ?? 0);
        $infantMaxAge = (int) ($package->infant_max_age ?? 1);
        $currencySymbol = $package->currency?->symbol ?? '$';
    @endphp
    <input type="hidden" name="package_id" value="{{ $package->id }}">
    <input type="hidden" name="title" value="{{ $title }}">
    <input type="hidden" name="selected_pricing_tier" value="2_persons">
    <input type="hidden" name="price_per_person" value="">
    <input type="hidden" name="calculated_total" value="">

    <div class="input-box">
        <label class="label-text">{{ __('Your Name *') }}</label>
        <div class="form-group">
            <span class="la la-user form-icon"></span>
            <input class="form-control" type="text" name="name" required placeholder="{{ __('Your name') }}"
                value="{{ old('name') }}">
        </div>
        @error('name')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-box">
        <label class="label-text">{{ __('Your Email *') }}</label>
        <div class="form-group">
            <span class="la la-envelope-o form-icon"></span>
            <input class="form-control" type="email" name="email" required placeholder="{{ __('Email address') }}"
                value="{{ old('email') }}">
        </div>
        @error('email')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-box">
        <label class="label-text">{{ __('Phone Number') }}</label>
        <div class="form-group">
            <input class="form-control" type="tel" name="phone" placeholder="{{ __('Phone Number') }}"
                value="{{ old('phone') }}">
        </div>
        @error('phone')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-box">
        <label class="label-text">{{ __('Date *') }}</label>
        <div class="form-group">
            <span class="la la-calendar form-icon"></span>
            <input name="travel_date" class="form-control" type="date" required value="{{ old('travel_date') }}">
        </div>
        @error('travel_date')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="sidebar-widget-item">
        <div class="quantity-control">
            <label for="adults_book_{{ $suffix }}">
                {{ __('trips.adults') }}
                {{ __('trips.adults_age', ['age' => $adultMinAge]) }}
            </label>
            <div class="qty-buttons">
                <button type="button" class="qty-btn" data-qty-target="adults_book_{{ $suffix }}"
                    data-qty-step="-1">-</button>
                <input type="number" id="adults_book_{{ $suffix }}" name="adults" class="qty-input"
                    value="{{ old('adults', 1) }}" min="1" readonly>
                <button type="button" class="qty-btn" data-qty-target="adults_book_{{ $suffix }}"
                    data-qty-step="1">+</button>
            </div>
            @error('adults')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        <div class="quantity-control">
            <label for="children_book_{{ $suffix }}">
                {{ __('trips.children') }}
                {{ __('trips.children_age', ['from' => $childMinAge, 'to' => $childMaxAge]) }}
            </label>
            <div class="qty-buttons">
                <button type="button" class="qty-btn" data-qty-target="children_book_{{ $suffix }}"
                    data-qty-step="-1">-</button>
                <input type="number" id="children_book_{{ $suffix }}" name="children" class="qty-input"
                    value="{{ old('children', 0) }}" min="0" readonly>
                <button type="button" class="qty-btn" data-qty-target="children_book_{{ $suffix }}"
                    data-qty-step="1">+</button>
            </div>
            @error('children')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        <div class="quantity-control">
            <label for="infants_book_{{ $suffix }}">
                {{ __('trips.infants') }}
                {{ __('trips.infants_age', ['from' => $infantMinAge, 'to' => $infantMaxAge]) }}
            </label>
            <div class="qty-buttons">
                <button type="button" class="qty-btn" data-qty-target="infants_book_{{ $suffix }}"
                    data-qty-step="-1">-</button>
                <input type="number" id="infants_book_{{ $suffix }}" name="infants" class="qty-input"
                    value="{{ old('infants', 0) }}" min="0" readonly>
                <button type="button" class="qty-btn" data-qty-target="infants_book_{{ $suffix }}"
                    data-qty-step="1">+</button>
            </div>
            @error('infants')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
    </div>

    @if($hasBookablePrice ?? false)
        <div class="input-box">
            <label class="label-text">{{ __('Estimated total') }}</label>
            <div class="form-group">
                <span class="la la-calculator form-icon"></span>
                <input class="form-control js-booking-total-display" type="text" id="booking_total_{{ $suffix }}"
                    value="{{ $currencySymbol }}0.00" readonly>
            </div>
        </div>
    @endif

    <div class="input-box">
        <label class="label-text">{{ __('Message') }}</label>
        <div class="form-group">
            <span class="la la-pencil form-icon" style="top:24px"></span>
            <textarea class="message-control form-control" name="comment" placeholder="{{ __('Please advise your tour requirements') }}">{{ old('comment') }}</textarea>
        </div>
        @error('comment')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    {{-- غير مربوط: recaptcha-holder متساب لأنك محتاج تضيف site key + secret key لو هتشغل Google reCAPTCHA --}}
    <div class="input-box">
        <div class="form-group">
            <div class="recaptcha-holder"></div>
        </div>
    </div>

    <div class="btn-box">
        <button type="submit" class="submit-btn" style="width:100%">{{ __('Submit Enquiry') }}</button>
    </div>

    <div class="trust-indicators">
        <div class="trust-item-small"><i class="la la-shield-alt"></i><span>{{ __('Secure Enquiry') }}</span></div>
        <div class="trust-item-small"><i class="la la-clock"></i><span>{{ __('24/7 Support') }}</span></div>
        <div class="trust-item-small"><i class="la la-award"></i><span>{{ __('Best Price Guarantee') }}</span></div>
    </div>
</form>

<script>
    (function() {
        @php
            $groupTiers = $package->group_pricing_tiers;
        @endphp

        const groupTiers = @json(collect($groupTiers)->values());
        const tierRates = {};
        groupTiers.forEach((tier, index) => tierRates[index + 1] = Number(tier.price_per_person || 0));

        const tierKeys = {
            1: '1_person',
            2: '2_persons',
            3: '3_persons',
            4: '4_persons',
            5: '5_persons',
            6: '6_plus_persons'
        };

        const currencySymbol = @json($currencySymbol);
        const suffix = @json($suffix);
        const adultsInput = document.getElementById(`adults_book_${suffix}`);
        const totalInput = document.getElementById(`booking_total_${suffix}`);

        function getTierRate(adultsCount) {
            if (adultsCount <= 1) return tierRates[1];
            if (adultsCount === 2) return tierRates[2];
            if (adultsCount === 3) return tierRates[3];
            if (adultsCount === 4) return tierRates[4];
            if (adultsCount === 5) return tierRates[5];
            return tierRates[6] || 0;
        }

        function getTierKey(adultsCount) {
            if (adultsCount <= 1) return tierKeys[1];
            if (adultsCount === 2) return tierKeys[2];
            if (adultsCount === 3) return tierKeys[3];
            if (adultsCount === 4) return tierKeys[4];
            if (adultsCount === 5) return tierKeys[5];
            return tierKeys[6];
        }

        function formatMoney(amount) {
            return currencySymbol + Number(amount || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateBookingTotal() {
            if (!adultsInput || !totalInput) return;

            const form = adultsInput.closest('form');
            const adultsCount = Math.max(parseInt(adultsInput.value || '1', 10), 1);
            const pricePerPerson = getTierRate(adultsCount);
            const calculatedTotal = pricePerPerson * adultsCount;

            totalInput.value = formatMoney(calculatedTotal);

            if (form) {
                const selectedTierInput = form.querySelector('input[name="selected_pricing_tier"]');
                const pricePerPersonInput = form.querySelector('input[name="price_per_person"]');
                const calculatedTotalInput = form.querySelector('input[name="calculated_total"]');

                if (selectedTierInput) selectedTierInput.value = getTierKey(adultsCount);
                if (pricePerPersonInput) pricePerPersonInput.value = pricePerPerson;
                if (calculatedTotalInput) calculatedTotalInput.value = calculatedTotal;
            }
        }

        document.querySelectorAll(`[data-qty-target$="_${suffix}"]`).forEach((button) => {
            button.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.qtyTarget);
                if (!input) return;

                const min = parseInt(input.getAttribute('min') || '0', 10);
                const step = parseInt(this.dataset.qtyStep || '0', 10);
                const current = parseInt(input.value || min, 10);

                input.value = Math.max(min, current + step);
                updateBookingTotal();
            });
        });

        updateBookingTotal();
    })();
</script>
