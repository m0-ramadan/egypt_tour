@extends('website.layouts.master')

@section('title', __('Contact Us') . ' - Etro Tours')
@section('description', __('Contact Etro Tours for luxury Egypt tours, tailor-made itineraries, Nile cruises, and fast support from our travel specialists.'))
@section('keywords', 'contact Etro Tours, Egypt travel experts, luxury travel support, Nile cruise booking, tailor made Egypt tours')
@section('image', asset('website/photos/home2.webp'))

@section('css')
    <style>
        .contact-hero {
            position: relative;
            overflow: hidden;
            margin-top: -85px;
            padding: 150px 0 95px;
            background:
                linear-gradient(rgba(16, 33, 63, 0.7), rgba(21, 53, 91, 0.72)),
                url('{{ asset('website/photos/home2.webp') }}') center/cover no-repeat;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            inset: auto -10% -80px;
            height: 180px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12), transparent 65%);
            filter: blur(24px);
        }

        .contact-hero .container,
        .contact-main-section .container,
        .contact-office-section .container {
            position: relative;
            z-index: 1;
        }

        .contact-hero-content {
            max-width: 860px;
            margin: 0 auto;
            text-align: center;
            color: #fff;
        }

        .contact-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 999px;
            margin-bottom: 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            font-weight: 600;
        }

        .contact-badge i {
            color: #ffd27d;
        }

        .contact-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.6rem, 6vw, 4.4rem);
            line-height: 1.08;
            margin-bottom: 18px;
            color: #fff;
        }

        .contact-subtitle {
            max-width: 700px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.92);
            font-size: 1.1rem;
            line-height: 1.85;
        }

        .hero-stats {
            margin-top: 42px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .hero-stat {
            padding: 24px 18px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(14px);
            box-shadow: 0 20px 40px rgba(10, 20, 40, 0.18);
        }

        .hero-stat i {
            display: block;
            margin-bottom: 14px;
            font-size: 2rem;
            color: #ffd27d;
        }

        .hero-stat h4 {
            margin: 0 0 8px;
            font-size: 1.05rem;
            color: #fff;
        }

        .hero-stat p {
            margin: 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.92rem;
        }

        .contact-main-section {
            padding: 80px 0 36px;
            background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .section-heading {
            text-align: center;
            max-width: 760px;
            margin: 0 auto 40px;
        }

        .section-heading h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 4vw, 2.8rem);
            color: #1c325c;
            margin-bottom: 14px;
        }

        .section-heading p {
            margin: 0;
            color: #617189;
            line-height: 1.8;
            font-size: 1.02rem;
        }

        .contact-methods {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 36px;
        }

        .contact-method {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 32px 28px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 14px 38px rgba(20, 41, 74, 0.08);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .contact-method:hover {
            transform: translateY(-8px);
            border-color: rgba(197, 149, 91, 0.28);
            box-shadow: 0 24px 52px rgba(20, 41, 74, 0.14);
        }

        .contact-method-icon {
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(197, 149, 91, 0.15), rgba(66, 153, 225, 0.16));
            color: #c5955b;
            font-size: 2rem;
            box-shadow: 0 10px 24px rgba(197, 149, 91, 0.12);
        }

        .contact-method-title {
            font-family: 'Playfair Display', serif;
            margin-bottom: 12px;
            font-size: 1.45rem;
            color: #1c325c;
        }

        .contact-method-description {
            margin: 0 0 12px;
            color: #5f6f86;
            line-height: 1.75;
            flex: 1;
        }

        .contact-method-highlight {
            display: inline-flex;
            width: fit-content;
            margin-bottom: 18px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(197, 149, 91, 0.12);
            color: #9b6a2c;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .contact-method-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1c325c;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.25s ease, transform 0.25s ease;
        }

        .contact-method-link:hover {
            color: #c5955b;
            transform: translateY(-1px);
        }

        .contact-content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.8fr);
            gap: 28px;
            align-items: start;
        }

        .contact-form-card,
        .contact-side-card,
        .office-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid rgba(26, 54, 93, 0.08);
            box-shadow: 0 18px 44px rgba(20, 41, 74, 0.08);
        }

        .contact-form-card {
            padding: 34px;
        }

        .contact-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            color: #1c325c;
            margin-bottom: 10px;
        }

        .contact-card-subtitle {
            margin: 0 0 24px;
            color: #617189;
            line-height: 1.75;
        }

        .alert-success,
        .error-container {
            padding: 18px 20px;
            border-radius: 18px;
            margin-bottom: 22px;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.12);
            border: 1px solid rgba(22, 163, 74, 0.2);
            color: #166534;
        }

        .error-container {
            background: rgba(220, 38, 38, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.16);
            color: #b91c1c;
        }

        .error-container h4 {
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .error-container ul {
            margin: 0;
            padding-inline-start: 18px;
        }

        .contact-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #1c325c;
            font-weight: 700;
            font-size: 0.96rem;
        }

        .form-control,
        .form-select,
        .form-textarea {
            width: 100%;
            min-height: 58px;
            border: 1px solid rgba(26, 54, 93, 0.12);
            border-radius: 18px;
            background: #fff;
            color: #1f2f46;
            padding: 15px 18px;
            box-shadow: 0 8px 24px rgba(20, 41, 74, 0.04);
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .form-textarea {
            min-height: 168px;
            resize: vertical;
        }

        .form-control:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: rgba(197, 149, 91, 0.58);
            box-shadow: 0 0 0 4px rgba(197, 149, 91, 0.12);
            transform: translateY(-1px);
        }

        .field-error {
            margin-top: 8px;
            font-size: 0.88rem;
            color: #dc2626;
            font-weight: 600;
        }

        .submit-wrap {
            display: flex;
            justify-content: center;
            margin-top: 8px;
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 58px;
            width: min(100%, 320px);
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #c5955b 0%, #b8860b 100%);
            color: #10213f;
            font-weight: 800;
            box-shadow: 0 14px 30px rgba(197, 149, 91, 0.24);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(197, 149, 91, 0.32);
        }

        .contact-side-card,
        .office-card {
            padding: 30px;
        }

        .contact-side-card {
            position: sticky;
            top: 100px;
        }

        .side-card-title,
        .office-title {
            font-family: 'Playfair Display', serif;
            color: #1c325c;
            font-size: 1.65rem;
            margin-bottom: 12px;
        }

        .side-card-copy,
        .office-intro {
            margin: 0 0 22px;
            color: #617189;
            line-height: 1.75;
        }

        .support-list,
        .office-details {
            display: grid;
            gap: 16px;
        }

        .office-details {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .support-item,
        .office-detail {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px;
            border-radius: 20px;
            background: linear-gradient(135deg, #f8fbff 0%, #f2f6fb 100%);
            border: 1px solid rgba(26, 54, 93, 0.08);
        }

        .support-item i,
        .office-detail i {
            width: 46px;
            height: 46px;
            min-width: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: rgba(197, 149, 91, 0.14);
            color: #c5955b;
            font-size: 1.35rem;
        }

        .support-item strong,
        .office-detail strong {
            display: block;
            margin-bottom: 6px;
            color: #1c325c;
        }

        .support-item span,
        .office-detail-text,
        .office-detail-text a {
            color: #5f6f86;
            line-height: 1.7;
            text-decoration: none;
        }

        .office-detail-text a:hover {
            color: #c5955b;
        }

        .contact-office-section {
            padding: 0 0 90px;
            background: linear-gradient(180deg, #eef4fb 0%, #f8fbff 100%);
        }

        html[data-theme='dark'] .contact-main-section,
        html[data-theme='dark'] .contact-office-section {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
        }

        html[data-theme='dark'] .contact-method,
        html[data-theme='dark'] .contact-form-card,
        html[data-theme='dark'] .contact-side-card,
        html[data-theme='dark'] .office-card,
        html[data-theme='dark'] .support-item,
        html[data-theme='dark'] .office-detail {
            background: #111827 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        html[data-theme='dark'] .support-item,
        html[data-theme='dark'] .office-detail {
            background: #172033 !important;
        }

        html[data-theme='dark'] .section-heading h2,
        html[data-theme='dark'] .contact-method-title,
        html[data-theme='dark'] .contact-card-title,
        html[data-theme='dark'] .side-card-title,
        html[data-theme='dark'] .office-title,
        html[data-theme='dark'] .form-label,
        html[data-theme='dark'] .support-item strong,
        html[data-theme='dark'] .office-detail strong {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .section-heading p,
        html[data-theme='dark'] .contact-method-description,
        html[data-theme='dark'] .contact-card-subtitle,
        html[data-theme='dark'] .side-card-copy,
        html[data-theme='dark'] .office-intro,
        html[data-theme='dark'] .support-item span,
        html[data-theme='dark'] .office-detail-text,
        html[data-theme='dark'] .office-detail-text a {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .contact-method-highlight {
            background: rgba(244, 195, 106, 0.12) !important;
            color: #f7d488 !important;
        }

        html[data-theme='dark'] .contact-method-link {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .contact-method-link:hover,
        html[data-theme='dark'] .office-detail-text a:hover {
            color: var(--rich-gold) !important;
        }

        html[data-theme='dark'] .form-control,
        html[data-theme='dark'] .form-select,
        html[data-theme='dark'] .form-textarea {
            background: #0f172a !important;
            color: var(--charcoal-deep) !important;
            border-color: rgba(148, 163, 184, 0.24) !important;
        }

        html[data-theme='dark'] .form-control::placeholder,
        html[data-theme='dark'] .form-textarea::placeholder {
            color: #94a3b8 !important;
        }

        html[data-theme='dark'] .alert-success {
            background: rgba(22, 163, 74, 0.14) !important;
            border-color: rgba(22, 163, 74, 0.22) !important;
            color: #86efac !important;
        }

        html[data-theme='dark'] .error-container {
            background: rgba(220, 38, 38, 0.12) !important;
            border-color: rgba(248, 113, 113, 0.18) !important;
            color: #fca5a5 !important;
        }

        html[dir="rtl"] .contact-method-link,
        html[dir="rtl"] .submit-btn,
        html[dir="rtl"] .support-item,
        html[dir="rtl"] .office-detail {
            flex-direction: row-reverse;
        }

        html[dir="rtl"] .section-heading,
        html[dir="rtl"] .contact-form-card,
        html[dir="rtl"] .contact-side-card,
        html[dir="rtl"] .office-card,
        html[dir="rtl"] .contact-method {
            text-align: right;
        }

        html[dir="rtl"] .contact-method-link:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 1199px) {
            .hero-stats,
            .contact-methods {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .office-details {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .contact-content-grid {
                grid-template-columns: 1fr;
            }

            .contact-side-card {
                position: static;
            }
        }

        @media (max-width: 767px) {
            .contact-hero {
                padding: 135px 0 75px;
            }

            .hero-stats,
            .contact-methods,
            .contact-form-grid,
            .office-details {
                grid-template-columns: 1fr;
            }

            .contact-form-card,
            .contact-side-card,
            .office-card {
                padding: 24px 20px;
                border-radius: 24px;
            }

            .contact-main-section {
                padding-top: 60px;
            }

            .contact-office-section {
                padding-bottom: 70px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <div class="contact-badge">
                    <i class="la la-headset"></i>
                    <span>{{ __('Travel Assistance That Feels Personal') }}</span>
                </div>

                <h1 class="contact-title">{{ __('Get in Touch') }}</h1>
                <p class="contact-subtitle">
                    {{ __('Ready to plan your Egyptian adventure? Our travel specialists are here to help you shape the perfect journey with clear answers, thoughtful guidance, and fast support.') }}
                </p>

                <div class="hero-stats">
                    @foreach ($heroStats as $stat)
                        <div class="hero-stat">
                            <i class="{{ $stat['icon'] }}"></i>
                            <h4>{{ $stat['title'] }}</h4>
                            <p>{{ $stat['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="contact-main-section">
        <div class="container">
            <div class="section-heading">
                <h2>{{ __('How Can We Help You?') }}</h2>
                <p>{{ __('Choose the contact method that suits you best, or send us a detailed message and our team will get back to you shortly.') }}</p>
            </div>

            <div class="contact-methods">
                @foreach ($contactMethods as $method)
                    <article class="contact-method">
                        <div class="contact-method-icon">
                            <i class="{{ $method['icon'] }}"></i>
                        </div>
                        <h3 class="contact-method-title">{{ $method['title'] }}</h3>
                        <p class="contact-method-description">{{ $method['description'] }}</p>
                        <span class="contact-method-highlight">{{ $method['highlight'] }}</span>
                        <a href="{{ $method['url'] }}" class="contact-method-link"
                            @if (!empty($method['external'])) target="_blank" rel="noopener" @endif>
                            <i class="{{ $method['icon'] }}"></i>
                            <span>{{ $method['label'] }}</span>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="contact-content-grid">
                <div class="contact-form-card">
                    <h2 class="contact-card-title">{{ __('Send Us a Message') }}</h2>
                    <p class="contact-card-subtitle">
                        {{ __('Tell us what you need and we will reply with the most helpful next step, whether you are planning a new trip or following up on an existing booking.') }}
                    </p>

                    @if (session('success'))
                        <div class="alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="error-container">
                            <h4>{{ __('Please review the highlighted fields below.') }}</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('website.contact.store') }}">
                        @csrf
                        <input type="hidden" name="form_start_time" id="contactFormStartTime" value="{{ old('form_start_time') }}">

                        <div style="display:none;">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                            <input type="text" name="url" tabindex="-1" autocomplete="off">
                            <input type="text" name="company_name" tabindex="-1" autocomplete="off">
                            <input type="text" name="subject_line" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="contact-form-grid">
                            <div>
                                <label class="form-label" for="first_name">{{ __('First Name') }} *</label>
                                <input id="first_name" type="text" name="first_name"
                                    class="form-control @error('first_name') error @enderror"
                                    value="{{ old('first_name') }}"
                                    placeholder="{{ __('Enter your first name') }}" required>
                                @error('first_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="last_name">{{ __('Last Name') }} *</label>
                                <input id="last_name" type="text" name="last_name"
                                    class="form-control @error('last_name') error @enderror"
                                    value="{{ old('last_name') }}"
                                    placeholder="{{ __('Enter your last name') }}" required>
                                @error('last_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="email">{{ __('Email Address') }} *</label>
                                <input id="email" type="email" name="email"
                                    class="form-control @error('email') error @enderror"
                                    value="{{ old('email') }}"
                                    placeholder="{{ __('your.email@example.com') }}" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="phone">{{ __('Phone Number') }}</label>
                                <input id="phone" type="tel" name="phone"
                                    class="form-control @error('phone') error @enderror"
                                    value="{{ old('phone') }}"
                                    placeholder="{{ __('Phone Number') }}">
                                @error('phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>



                            <div>
                                <label class="form-label" for="inquiry_type">{{ __('Inquiry Type') }}</label>
                                <select id="inquiry_type" name="inquiry_type"
                                    class="form-select @error('inquiry_type') error @enderror">
                                    <option value="">{{ __('Select inquiry type') }}</option>
                                    @foreach ($inquiryTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('inquiry_type') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inquiry_type')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group-full">
                                <label class="form-label" for="subject">{{ __('Subject') }} *</label>
                                <input id="subject" type="text" name="subject"
                                    class="form-control @error('subject') error @enderror"
                                    value="{{ old('subject') }}"
                                    placeholder="{{ __('Brief description of your inquiry') }}" required>
                                @error('subject')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group-full">
                                <label class="form-label" for="message">{{ __('Message') }} *</label>
                                <textarea id="message" name="message" class="form-textarea @error('message') error @enderror"
                                    placeholder="{{ __('Please provide details about your travel plans, dates, preferences, or any support you need.') }}"
                                    required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="submit-wrap">
                            <button type="submit" class="submit-btn">
                                <i class="la la-paper-plane"></i>
                                <span>{{ __('Send Message') }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="contact-side-card">
                    <h3 class="side-card-title">{{ __('Why Contact Etro Tours?') }}</h3>
                    <p class="side-card-copy">
                        {{ __('We do more than answer questions. We help shape the right trip, solve uncertainty quickly, and guide you with local expertise from the first message.') }}
                    </p>

                    <div class="support-list">
                        <div class="support-item">
                            <i class="la la-bolt"></i>
                            <div>
                                <strong>{{ __('Fast Replies') }}</strong>
                                <span>{{ __('Clear answers from a real travel specialist, not generic auto replies.') }}</span>
                            </div>
                        </div>

                        <div class="support-item">
                            <i class="la la-map"></i>
                            <div>
                                <strong>{{ __('Tailored Guidance') }}</strong>
                                <span>{{ __('Recommendations matched to your travel dates, interests, and budget.') }}</span>
                            </div>
                        </div>

                        <div class="support-item">
                            <i class="la la-shield-alt"></i>
                            <div>
                                <strong>{{ __('Reliable Support') }}</strong>
                                <span>{{ __('We stay available before, during, and after booking whenever you need help.') }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="contact-office-section">
        <div class="container">
            <div class="office-card">
                <h3 class="office-title">{{ __('Visit Our Office') }}</h3>
                <p class="office-intro">
                    {{ __('Located in the heart of Luxor, our office is perfectly placed to help you plan unforgettable journeys across Egypt with local knowledge and responsive support.') }}
                </p>

                <div class="office-details">
                    @foreach ($officeDetails as $detail)
                        <div class="office-detail">
                            <i class="{{ $detail['icon'] }}"></i>
                            <div class="office-detail-text">
                                <strong>{{ $detail['title'] }}</strong>
                                @foreach ($detail['lines'] as $line)
                                    @if (($detail['type'] ?? null) === 'email')
                                        <div><a href="mailto:{{ $line }}">{{ $line }}</a></div>
                                    @else
                                        <div>{{ $line }}</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formStartTime = document.getElementById('contactFormStartTime');
            if (formStartTime && !formStartTime.value) {
                formStartTime.value = Math.floor(Date.now() / 1000);
            }
        });
    </script>
@endsection
