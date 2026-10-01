@php
    $isCruiseEnquiry = ($package->package_type ?? null) === 'nile_cruise';
    $suffix = $formSuffix ?? 'form';
    $adultMinAge = (int) ($package->adult_min_age ?? 12);
    $childMinAge = (int) ($package->child_min_age ?? 2);
    $childMaxAge = (int) ($package->child_max_age ?? 11);
    $infantMinAge = (int) ($package->infant_min_age ?? 0);
    $infantMaxAge = (int) ($package->infant_max_age ?? 1);
    $currencySymbol = $package->currency?->symbol ?? '$';
@endphp
<div class="js-enquiry-form-wrapper" data-group-tiers="@json(collect($package->group_pricing_tiers)->values())" data-currency-symbol="{{ $currencySymbol }}" data-suffix="{{ $suffix }}">
<form class="{{ $isCruiseEnquiry ? 'nile-cruise-enquiry' : '' }}" method="post"
    action="{{ route('website.inquiries.store') }}">
    @csrf
    <input type="hidden" name="package_id" value="{{ $package->id }}">
    <input type="hidden" name="title" value="{{ $title }}">
    @unless ($isCruiseEnquiry)
        <input type="hidden" name="selected_pricing_tier" value="2_persons">
        <input type="hidden" name="price_per_person" value="">
        <input type="hidden" name="calculated_total" value="">
    @endunless

    <div class="input-box">
        <label class="label-text" for="enquiry_name_{{ $suffix }}">{{ __('Your Name *') }}</label>
        <div class="form-group">
            <span class="la la-user form-icon"></span>
            <input class="form-control" type="text" id="enquiry_name_{{ $suffix }}" name="name" required
                placeholder="{{ $isCruiseEnquiry ? __('Enter your full name') : __('Your name') }}"
                value="{{ old('name') }}">
        </div>
        @error('name')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-box">
        <label class="label-text" for="enquiry_email_{{ $suffix }}">{{ __('Your Email *') }}</label>
        <div class="form-group">
            <span class="la la-envelope-o form-icon"></span>
            <input class="form-control" type="email" id="enquiry_email_{{ $suffix }}" name="email" required
                placeholder="{{ $isCruiseEnquiry ? 'your.email@example.com' : __('Email address') }}"
                value="{{ old('email') }}">
        </div>
        @error('email')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    @if ($isCruiseEnquiry)
        <div class="input-box">
            <label class="label-text" for="enquiry_country_{{ $suffix }}">{{ __('Country') }} *</label>
            <div class="form-group">
                <select class="form-control" id="enquiry_country_{{ $suffix }}" name="nationality" required>
                    <option value="">{{ __('Select your country') }}</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected(old('nationality') === $country)>{{ __($country) }}</option>
                    @endforeach
                </select>
            </div>
            @error('nationality')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
    @endif

    <div class="input-box">
        <label class="label-text" for="enquiry_phone_{{ $suffix }}">{{ __('Phone Number') }}</label>
        <div class="form-group">
            <input class="form-control" type="tel" id="enquiry_phone_{{ $suffix }}" name="phone"
                placeholder="{{ $isCruiseEnquiry ? '+1 (555) 123-4567' : __('Phone Number') }}"
                value="{{ old('phone') }}">
        </div>
        @error('phone')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-box">
        <label class="label-text" for="enquiry_travel_date_{{ $suffix }}">{{ __('Date *') }}</label>
        <div class="form-group">
            <span class="la la-calendar form-icon"></span>
            <input id="enquiry_travel_date_{{ $suffix }}" name="travel_date" class="form-control" type="date"
                required value="{{ old('travel_date', $isCruiseEnquiry ? now()->toDateString() : null) }}">
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
                    value="{{ old('adults', $isCruiseEnquiry ? 2 : 1) }}" min="1" readonly>
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

        @unless ($isCruiseEnquiry)
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
        @endunless
    </div>



    <div class="input-box">
        <label class="label-text" for="enquiry_comment_{{ $suffix }}">{{ __('Message') }}</label>
        <div class="form-group">
            <span class="la la-pencil form-icon" style="top:24px"></span>
            <textarea class="message-control form-control" id="enquiry_comment_{{ $suffix }}" name="comment"
                placeholder="{{ __('Please advise your tour requirements') }}">{{ old('comment') }}</textarea>
        </div>
        @error('comment')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    @unless ($isCruiseEnquiry)
        {{-- غير مربوط: recaptcha-holder متساب لأنك محتاج تضيف site key + secret key لو هتشغل Google reCAPTCHA --}}
        <div class="input-box">
            <div class="form-group">
                <div class="recaptcha-holder"></div>
            </div>
        </div>
    @endunless

    <div class="btn-box">
        <button type="submit" class="submit-btn" style="width:100%">
            @if ($isCruiseEnquiry)
                <i class="la la-paper-plane" aria-hidden="true"></i>
            @endif{{ __('Submit Enquiry') }}
        </button>
    </div>

    @unless ($isCruiseEnquiry)
        <div class="trust-indicators">
            <div class="trust-item-small"><i class="la la-shield-alt"></i><span>{{ __('Secure Enquiry') }}</span></div>
            <div class="trust-item-small"><i class="la la-clock"></i><span>{{ __('24/7 Support') }}</span></div>
            <div class="trust-item-small"><i class="la la-award"></i><span>{{ __('Best Price Guarantee') }}</span></div>
        </div>
    @endunless
</form>

@if ($isCruiseEnquiry)
    @once
        @vite('resources/css/pages/enquiry-form.css')
    @endonce
@endif

@vite('resources/js/pages/enquiry-form.js')

</div>