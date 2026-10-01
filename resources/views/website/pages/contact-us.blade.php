@extends('website.layouts.master')

@section('title', __('Contact Us') . ' - Egypt Tour Pro')
@section('description', __('Contact Egypt Tour Pro for luxury Egypt tours, tailor-made itineraries, Nile cruises, and fast
    support from our travel specialists.'))
@section('keywords', 'contact Egypt Tour Pro, Egypt travel experts, luxury travel support, Nile cruise booking, tailor made
    Egypt tours')
@section('image', asset('website/photos/home2.webp'))

@section('css')
    @vite('resources/css/pages/contact-us.css')
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
                <p>{{ __('Choose the contact method that suits you best, or send us a detailed message and our team will get back to you shortly.') }}
                </p>
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
                        <input type="hidden" name="form_start_time" id="contactFormStartTime"
                            value="{{ old('form_start_time') }}">

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
                                    value="{{ old('first_name') }}" placeholder="{{ __('Enter your first name') }}"
                                    required>
                                @error('first_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="last_name">{{ __('Last Name') }} *</label>
                                <input id="last_name" type="text" name="last_name"
                                    class="form-control @error('last_name') error @enderror"
                                    value="{{ old('last_name') }}" placeholder="{{ __('Enter your last name') }}"
                                    required>
                                @error('last_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="email">{{ __('Email Address') }} *</label>
                                <input id="email" type="email" name="email"
                                    class="form-control @error('email') error @enderror" value="{{ old('email') }}"
                                    placeholder="{{ __('your.email@example.com') }}" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="phone">{{ __('Phone Number') }}</label>
                                <input id="phone" type="tel" name="phone"
                                    class="form-control @error('phone') error @enderror" value="{{ old('phone') }}"
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
                                    class="form-control @error('subject') error @enderror" value="{{ old('subject') }}"
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
                    <h3 class="side-card-title">{{ __('Why Contact Egypt Tour Pro?') }}</h3>
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
    @vite('resources/js/pages/contact-us.js')
@endsection
