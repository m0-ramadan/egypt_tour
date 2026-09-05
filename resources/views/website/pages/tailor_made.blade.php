@extends('website.layouts.master')

@section('title', __('Tailor-Made Travel Experiences') . ' - Etro Tours')
@section('description', __('Plan a tailor-made journey with Etro Tours and get a custom itinerary designed around your
    budget, interests, travel style, and dream destinations.'))
@section('keywords', 'tailor made Egypt tours, custom travel itinerary, private Egypt holidays, luxury bespoke travel,
    Etro Tours')
@section('image', asset('website/photos/home2.webp'))

@section('css')

    <style>
        /* Hero Section */
        .tailor-hero {
            background: var(--gradient-hero);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .tailor-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="none"><path d="M0 10L10 0L20 10L30 0L40 10L50 0L60 10L70 0L80 10L90 0L100 10V20H0V10Z" fill="rgba(197,149,91,0.1)"/></svg>') repeat-x;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 3rem);
            /* Updated responsive sizing */
            font-weight: 700;
            margin-bottom: 25px;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.3rem);
            /* Updated responsive sizing */
            opacity: 0.95;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .hero-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .hero-feature {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(197, 149, 91, 0.3);
            transition: all 0.3s ease;
        }

        .hero-feature:hover {
            transform: translateY(-5px);
            background: rgba(197, 149, 91, 0.2);
        }

        .hero-feature i {
            font-size: clamp(2rem, 3.5vw, 2.5rem);
            /* Updated responsive sizing */
            color: var(--rich-gold);
            margin-bottom: 15px;
        }

        .hero-feature h4 {
            font-size: clamp(0.95rem, 1.8vw, 1.1rem);
            /* Updated responsive sizing */
            margin-bottom: 10px;
            font-weight: 600;
        }

        .hero-feature p {
            font-size: clamp(0.8rem, 1.5vw, 0.9rem);
            /* Updated responsive sizing */
            opacity: 0.9;
            margin: 0;
        }

        /* Main Form Section */
        .tailor-form-section {
            padding: 80px 0;
            background: var(--gradient-elegant);
            position: relative;
        }

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: clamp(1.8rem, 3.2vw, 2.5rem);
            /* Updated responsive sizing */
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-gold);
            border-radius: 2px;
        }

        .section-subtitle {
            color: var(--warm-gray);
            font-size: clamp(1rem, 1.8vw, 1.2rem);
            /* Updated responsive sizing */
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Form Layout */
        .form-layout {
            display: block;
        }

        .main-form {
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: var(--shadow-dramatic);
            border: 2px solid rgba(197, 149, 91, 0.1);
            position: relative;
            overflow: hidden;
        }

        .main-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient-gold);
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: clamp(1.4rem, 2.5vw, 1.8rem);
            /* Updated responsive sizing */
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-step {
            margin-bottom: 40px;
        }

        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-sand);
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: var(--gradient-gold);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            box-shadow: var(--shadow-gold);
            font-size: clamp(0.9rem, 1.6vw, 1rem);
            /* Updated responsive sizing */
        }

        .step-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: clamp(1.1rem, 2vw, 1.3rem);
            /* Updated responsive sizing */
            font-weight: 600;
            margin: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--primary-navy);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: clamp(0.85rem, 1.5vw, 0.95rem);
            /* Updated responsive sizing */
        }

        .form-control {
            width: 100%;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: clamp(0.9rem, 1.6vw, 1rem);
            /* Updated responsive sizing */
            transition: all 0.3s ease;
            background: white;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            border-color: var(--rich-gold);
            box-shadow: 0 0 0 0.25rem rgba(197, 149, 91, 0.15);
            outline: none;
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23c5955b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 12px;
            cursor: pointer;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Special Input Groups */
        .date-range-group {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 15px;
            align-items: end;
        }

        .date-separator {
            color: var(--rich-gold);
            font-weight: 600;
            padding-bottom: 15px;
            text-align: center;
            font-size: clamp(0.9rem, 1.6vw, 1rem);
            /* Updated responsive sizing */
        }

        .budget-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .travelers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            background: var(--light-sand);
            border-radius: 12px;
            padding: 10px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .quantity-input:hover {
            border-color: var(--rich-gold);
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: var(--rich-gold);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: clamp(0.9rem, 1.6vw, 1rem);
            /* Updated responsive sizing */
        }

        .qty-btn:hover {
            background: var(--warm-bronze);
            transform: scale(1.1);
        }

        .qty-display {
            flex: 1;
            text-align: center;
            font-weight: 600;
            color: var(--primary-navy);
            font-size: clamp(1rem, 1.8vw, 1.1rem);
            /* Updated responsive sizing */
        }

        /* Checkbox and Radio Styles */
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--light-sand);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .custom-checkbox:hover {
            background: rgba(197, 149, 91, 0.1);
            border-color: var(--rich-gold);
        }

        .custom-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            accent-color: var(--rich-gold);
        }

        .custom-checkbox label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            font-size: clamp(0.85rem, 1.5vw, 0.95rem);
            /* Updated responsive sizing */
        }

        /* reCAPTCHA styling */
        .recaptcha-holder {
            margin: 25px 0;
            text-align: center;
        }

        /* Submit Button */
        .submit-section {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--light-sand);
        }

        .submit-btn {
            background: var(--gradient-gold);
            color: var(--primary-navy);
            border: none;
            padding: clamp(14px, 2.5vw, 18px) clamp(30px, 6vw, 50px);
            border-radius: clamp(25px, 4vw, 50px);
            font-weight: 700;
            font-size: clamp(1rem, 1.8vw, 1.2rem);
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-gold);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            max-width: 400px;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(197, 149, 91, 0.4);
            background: linear-gradient(135deg, #d4a574 0%, #c5955b 100%);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn i {
            font-size: clamp(1rem, 1.8vw, 1.3rem);
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .submit-btn {
                width: 100%;
                max-width: 300px;
                padding: 16px 24px;
                border-radius: 25px;
            }
        }

        /* Sidebar */
        .form-sidebar {
            /* Removed sticky positioning */
        }

        .sidebar-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: var(--shadow-medium);
            border: 2px solid rgba(197, 149, 91, 0.1);
            margin-bottom: 30px;
        }

        .sidebar-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy);
            font-size: clamp(1.2rem, 2.2vw, 1.4rem);
            /* Updated responsive sizing */
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar-feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px;
            background: var(--light-sand);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar-feature:hover {
            transform: translateX(5px);
            background: rgba(197, 149, 91, 0.1);
        }

        .sidebar-feature i {
            color: var(--rich-gold);
            margin-right: 12px;
            font-size: clamp(1rem, 1.8vw, 1.2rem);
            /* Updated responsive sizing */
            width: 24px;
        }

        .sidebar-feature span {
            font-weight: 500;
            color: var(--charcoal-deep);
            font-size: clamp(0.85rem, 1.5vw, 0.95rem);
            /* Updated responsive sizing */
        }

        .contact-card {
            background: var(--gradient-hero);
            color: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="none"><path d="M0 10L10 0L20 10L30 0L40 10L50 0L60 10L70 0L80 10L90 0L100 10V20H0V10Z" fill="rgba(197,149,91,0.1)"/></svg>') repeat-x;
            opacity: 0.3;
        }

        .contact-content {
            position: relative;
            z-index: 2;
        }

        .contact-icon {
            width: clamp(50px, 8vw, 60px);
            /* Updated responsive sizing */
            height: clamp(50px, 8vw, 60px);
            /* Updated responsive sizing */
            background: var(--rich-gold);
            color: var(--primary-navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: clamp(1.2rem, 2vw, 1.5rem);
            /* Updated responsive sizing */
            box-shadow: var(--shadow-gold);
        }

        .contact-title {
            font-size: clamp(1rem, 1.8vw, 1.2rem);
            /* Updated responsive sizing */
            font-weight: 600;
            margin-bottom: 15px;
        }

        .contact-info {
            margin-bottom: 15px;
            font-size: clamp(0.85rem, 1.5vw, 0.95rem);
            /* Updated responsive sizing */
        }

        .contact-info a {
            color: var(--rich-gold);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .contact-info a:hover {
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .main-form {
                padding: 30px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .date-range-group {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .date-separator {
                padding: 0;
                text-align: left;
            }

            .travelers-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .checkbox-group {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .tailor-hero {
                padding: 130px 0 60px;
            }

            .hero-features {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .tailor-form-section {
                padding: 60px 0;
            }

            .main-form {
                padding: 25px;
            }

            .sidebar-card {
                padding: 25px;
            }

            .travelers-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <style>
        .alert-success,
        .alert-danger {
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }

        .alert-success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        /* Enhanced error styles */
        .form-control.error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .field-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }


        .date-section-error {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }


        .highlight-error {
            animation: highlightError 2s ease-in-out;
        }

        @keyframes highlightError {

            0%,
            100% {
                background-color: transparent;
            }

            50% {
                background-color: rgba(220, 53, 69, 0.1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }
    </style>
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
                                        <p><a href="tel:+201553383000">+20 15 53383000</a></p>
                                        <p><a href="mailto:reservations@etrotours.com">reservations@etrotours.com</a></p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formStartTime = document.getElementById('formStartTime');
            if (formStartTime && !formStartTime.value) {
                formStartTime.value = Math.floor(Date.now() / 1000);
            }

            document.querySelectorAll('.qty-btn[data-target]').forEach((button) => {
                button.addEventListener('click', function() {
                    const target = this.dataset.target;
                    const change = parseInt(this.dataset.change, 10);
                    const input = document.getElementById(target + '-input');
                    const display = document.getElementById(target + '-qty');

                    if (!input || !display) {
                        return;
                    }

                    const min = parseInt(input.dataset.min || '0', 10);
                    const max = parseInt(input.dataset.max || '20', 10);
                    const current = parseInt(input.value || '0', 10);
                    const next = Math.min(max, Math.max(min, current + change));

                    input.value = next;
                    display.textContent = next;
                });
            });
        });
    </script>
@endsection
