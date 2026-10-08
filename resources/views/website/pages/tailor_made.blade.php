@extends('website.layouts.master')

@section('title', __('Tailor-Made Travel Experiences') . ' - Egypt Tour Pro')
@section('description',
    __('Plan a tailor-made journey with Egypt Tour Pro and get a custom itinerary designed around your
    budget, interests, travel style, and dream destinations.'))
@section('keywords',
    'tailor made Egypt tours, custom travel itinerary, private Egypt holidays, luxury bespoke travel,
    Egypt Tour Pro')
@section('image', asset('website/photos/home2.webp'))

@section('css')
    @vite('resources/css/pages/tailor-made.css')
@endsection

@section('content')
    <section class="tailor-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">{{ __('Tailor-Made Travel Experiences') }}</h1>
                <p class="hero-subtitle">
                    {{ __('Create your perfect Egyptian adventure with our expert travel specialists. Every detail crafted to match your dreams.') }}
                </p>

                <div class="hero-features">
                    @foreach ($heroFeatures as $feature)
                        <div class="hero-feature">
                            <i class="{{ $feature['icon'] }}"></i>
                            <h4>{{ __($feature['title']) }}</h4>
                            <p>{{ __($feature['description']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="tailor-form-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ __('Plan Your Dream Journey') }}</h2>
                <p class="section-subtitle">
                    {{ __('Share your travel preferences and let our experts craft the perfect Egyptian adventure tailored just for you.') }}
                </p>
            </div>

            <div class="form-container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="main-form">
                            <h3 class="form-title">{{ __('Tell Us About Your Dream Trip') }}</h3>

                            @if (session('success'))
                                <div class="alert-success">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert-danger">
                                    <strong>{{ __('Please review the highlighted fields and try again.') }}</strong>
                                </div>
                            @endif

                            <form action="{{ route('website.tailor_made.store') }}" method="POST" id="tailorMadeForm">
                                @csrf

                                <input type="hidden" name="website" value="{{ old('website') }}">
                                <input type="hidden" name="url" value="{{ old('url') }}">
                                <input type="hidden" name="company_name" value="{{ old('company_name') }}">
                                <input type="hidden" name="subject_line" value="{{ old('subject_line') }}">
                                <input type="hidden" name="form_start_time" id="formStartTime"
                                    value="{{ old('form_start_time') }}">

                                <div class="form-step">
                                    <div class="step-header">
                                        <div class="step-number">1</div>
                                        <h4 class="step-title">{{ __('Personal Information') }}</h4>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Full Name *') }}</label>
                                            <input type="text" class="form-control @error('name') error @enderror"
                                                name="name" value="{{ old('name') }}"
                                                placeholder="{{ __('Enter your full name') }}" required>
                                            @error('name')
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">{{ __('Email Address *') }}</label>
                                            <input type="email" class="form-control @error('email') error @enderror"
                                                name="email" value="{{ old('email') }}"
                                                placeholder="{{ __('your.email@example.com') }}" required>
                                            @error('email')
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Phone Number') }}</label>
                                            <input type="tel" class="form-control @error('phone') error @enderror"
                                                name="phone" value="{{ old('phone') }}"
                                                placeholder="{{ __('+1 (555) 123-4567') }}">
                                            @error('phone')
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">{{ __('Country of Residence') }}</label>
                                            <select class="form-control form-select @error('nationality') error @enderror"
                                                name="nationality">
                                                <option value="">{{ __('Select your country') }}</option>
                                                @foreach ($countries ?? [] as $country)
                                                    <option value="{{ $country }}" @selected(old('nationality') === $country)>
                                                        {{ __($country) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('nationality')
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-step">
                                    <div class="step-header">
                                        <div class="step-number">2</div>
                                        <h4 class="step-title">{{ __('Travel Details') }}</h4>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('Travel Dates') }}</label>
                                        <div class="date-range-group">
                                            <div>
                                                <input type="date"
                                                    class="form-control @error('start_date') error @enderror"
                                                    name="start_date" value="{{ old('start_date') }}"
                                                    min="{{ now()->toDateString() }}">
                                                @error('start_date')
                                                    <div class="field-error">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="date-separator">{{ __('to') }}</div>
                                            <div>
                                                <input type="date"
                                                    class="form-control @error('end_date') error @enderror"
                                                    name="end_date" value="{{ old('end_date') }}"
                                                    min="{{ old('start_date', now()->toDateString()) }}">
                                                @error('end_date')
                                                    <div class="field-error">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Trip Duration') }}</label>
                                            <input type="number" class="form-control @error('days') error @enderror"
                                                name="days" value="{{ old('days') }}"
                                                placeholder="{{ __('Number of days') }}" min="1" max="60">
                                            @error('days')
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">{{ __('Accommodation Preference') }}</label>
                                            <select class="form-control form-select @error('acommodation') error @enderror"
                                                name="acommodation">
                                                <option value="">{{ __('Select preference') }}</option>
                                                @foreach ($accommodationOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('acommodation') === $value)>
                                                        {{ __($label) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('acommodation')
                                                <div class="field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('Number of Travelers') }}</label>
                                        <div class="travelers-grid">
                                            @php
                                                $travelerFields = [
                                                    'adults' => ['label' => 'Adults *', 'default' => 2, 'min' => 1],
                                                    'children' => ['label' => 'Children', 'default' => 0, 'min' => 0],
                                                    'infants' => ['label' => 'Infants', 'default' => 0, 'min' => 0],
                                                ];
                                            @endphp

                                            @foreach ($travelerFields as $field => $meta)
                                                <div>
                                                    <label class="form-label">{{ __($meta['label']) }}</label>
                                                    <div class="quantity-input">
                                                        <button type="button" class="qty-btn"
                                                            data-target="{{ $field }}" data-change="-1">-</button>
                                                        <div class="qty-display" id="{{ $field }}-qty">
                                                            {{ old($field, $meta['default']) }}
                                                        </div>
                                                        <button type="button" class="qty-btn"
                                                            data-target="{{ $field }}" data-change="1">+</button>
                                                    </div>
                                                    <input type="hidden" name="{{ $field }}"
                                                        id="{{ $field }}-input"
                                                        value="{{ old($field, $meta['default']) }}"
                                                        data-min="{{ $meta['min'] }}" data-max="20">
                                                    @error($field)
                                                        <div class="field-error">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="form-step">
                                    <div class="step-header">
                                        <div class="step-number">3</div>
                                        <h4 class="step-title">{{ __('Travel Preferences') }}</h4>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('Budget Range (Per Person)') }}</label>
                                        <div class="budget-range">
                                            <select class="form-control form-select @error('budget_min') error @enderror"
                                                name="budget_min">
                                                <option value="">{{ __('Min Budget') }}</option>
                                                @foreach ($budgetMinOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('budget_min') === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <select class="form-control form-select @error('budget_max') error @enderror"
                                                name="budget_max">
                                                <option value="">{{ __('Max Budget') }}</option>
                                                @foreach ($budgetMaxOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('budget_max') === $value)>
                                                        {{ __($label) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('Special Occasions') }}</label>
                                        <select class="form-control form-select @error('occasion') error @enderror"
                                            name="occasion">
                                            <option value="">{{ __('Select if applicable') }}</option>
                                            @foreach ($occasionOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('occasion') === $value)>
                                                    {{ __($label) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            class="form-label">{{ __('Interests & Activities (Select all that apply)') }}</label>
                                        <div class="checkbox-group">
                                            @foreach ($interestOptions as $value => $label)
                                                <div class="custom-checkbox">
                                                    <input type="checkbox" id="{{ $value }}" name="interests[]"
                                                        value="{{ $value }}" @checked(in_array($value, old('interests', []), true))>
                                                    <label for="{{ $value }}">{{ __($label) }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="form-step">
                                    <div class="step-header">
                                        <div class="step-number">4</div>
                                        <h4 class="step-title">{{ __('Special Requirements') }}</h4>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('Dietary Requirements') }}</label>
                                        <input type="text" class="form-control @error('dietary') error @enderror"
                                            name="dietary" value="{{ old('dietary') }}"
                                            placeholder="{{ __('e.g., Vegetarian, Halal, Gluten-free, Allergies') }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">{{ __('Mobility Requirements') }}</label>
                                        <input type="text" class="form-control @error('mobility') error @enderror"
                                            name="mobility" value="{{ old('mobility') }}"
                                            placeholder="{{ __('e.g., Wheelchair accessible, Limited walking') }}">
                                    </div>

                                    <div class="form-group">
                                        <label
                                            class="form-label">{{ __('Additional Comments & Special Requests *') }}</label>
                                        <textarea class="form-control form-textarea @error('comment') error @enderror" name="comment" rows="5"
                                            placeholder="{{ __('Tell us anything else that would help us create your perfect trip...') }}" required>{{ old('comment') }}</textarea>
                                        @error('comment')
                                            <div class="field-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="recaptcha-holder">
                                        {{ __('reCAPTCHA can be added later if needed.') }}
                                    </div>
                                </div>

                                <div class="submit-section">
                                    <button type="submit" class="submit-btn">
                                        <i class="la la-paper-plane"></i>
                                        {{ __('Send My Travel Request') }}
                                    </button>

                                    <p style="margin-top: 20px; color: var(--warm-gray); font-size: 0.9rem;">
                                        {{ __('Our travel specialists will contact you within 24 hours with a personalized itinerary proposal.') }}
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4 pt-3 pt-lg-0">
                        <div class="form-sidebar">
                            <div class="sidebar-card">
                                <h4 class="sidebar-title">{{ __('Why Choose Our Tailor-Made Service?') }}</h4>

                                @foreach ($sidebarFeatures as $feature)
                                    <div class="sidebar-feature">
                                        <i class="{{ $feature['icon'] }}"></i>
                                        <span>{{ __($feature['label']) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="contact-card">
                                <div class="contact-content">
                                    <div class="contact-icon">
                                        <i class="la la-headset"></i>
                                    </div>

                                    <h4 class="contact-title">{{ __('Need Help?') }}</h4>

                                    <div class="contact-info">
                                        <p>{{ __('Speak with our travel experts') }}</p>
                                        <p><a href="mailto:reservations@egypttourpro.com">reservations@egypttourpro.com</a>
                                        </p>
                                    </div>

                                    <p style="font-size: 0.9rem; opacity: 0.9; margin: 0;">
                                        {{ __('Available 24/7 to assist with your travel planning') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    @vite('resources/js/pages/tailor-made.js')
@endsection
