@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إنشاء رحلة بالذكاء الاصطناعي'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
            --dark-input: rgba(255, 255, 255, .05);
            --dark-border: rgba(255, 255, 255, .1);
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .main-card {
            background: var(--dark-card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            border: 1px solid var(--dark-border);
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 24px 28px;
        }

        .form-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            font-size: 18px;
            margin: 30px 0 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--dark-border);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #f1f1f1;
        }

        .form-control,
        .form-select,
        textarea {
            background: var(--dark-input) !important;
            border: 1px solid var(--dark-border) !important;
            color: #fff !important;
            border-radius: 10px !important;
            min-height: 46px;
        }

        .form-control::placeholder,
        textarea::placeholder {
            color: rgba(255, 255, 255, .55);
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25) !important;
        }

        .form-select option {
            color: #000;
        }

        .note-box {
            background: rgba(255, 255, 255, .04);
            border: 1px dashed rgba(255, 255, 255, .15);
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            color: rgba(255, 255, 255, .8);
        }

        .page-loader {
            position: fixed;
            inset: 0;
            background: rgba(30, 30, 45, .92);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .page-loader.active {
            display: flex;
        }

        .loader-box {
            width: 100%;
            max-width: 420px;
            background: var(--dark-card);
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .35);
            border: 1px solid var(--dark-border);
        }

        .loader-spinner {
            width: 65px;
            height: 65px;
            border: 5px solid rgba(255, 255, 255, .15);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        .loader-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }

        .loader-text {
            font-size: 14px;
            color: rgba(255, 255, 255, .75);
            margin-bottom: 20px;
        }

        .progress-wrapper {
            width: 100%;
            height: 14px;
            background: rgba(255, 255, 255, .08);
            border-radius: 30px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-bar-custom {
            width: 0%;
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 30px;
            transition: width .3s ease;
        }

        .progress-percent {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')

    <div class="page-loader" id="pageLoader">
        <div class="loader-box">
            <div class="loader-spinner"></div>
            <div class="loader-title">جاري إنشاء الرحلة...</div>
            <div class="loader-text">يتم توليد العنوان، الوصف، البرنامج، المرافق، الأسعار، وSEO</div>

            <div class="progress-wrapper">
                <div class="progress-bar-custom" id="progressBar"></div>
            </div>

            <div class="progress-percent" id="progressPercent">0%</div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.index') }}">الرئيسية</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.packages.index') }}">الرحلات</a>
                </li>
                <li class="breadcrumb-item active">إنشاء بالذكاء الاصطناعي</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">إنشاء رحلة بالذكاء الاصطناعي</h5>
                    <small class="opacity-75">اكتب فكرة الرحلة وسيتم تجهيز محتوى كامل قابل للتعديل</small>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.packages.create') }}" class="btn btn-light">
                        إنشاء يدوي
                    </a>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-light">
                        رجوع
                    </a>
                </div>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.packages.store-with-ai') }}" method="POST" id="packageAiForm">
                    @csrf

                    <div class="section-title">وصف الرحلة</div>

                    <div class="mb-3">
                        <label class="form-label">Prompt</label>
                        <textarea name="prompt" class="form-control" rows="8"
                            placeholder="مثال: أنشئ رحلة نيلية فاخرة بين الأقصر وأسوان لمدة 5 أيام تشمل المعابد، الإقامة على مركب دهبية، وجبات كاملة، أسعار حسب الموسم، سياسة أطفال، وSEO مناسب">{{ old('prompt') }}</textarea>
                        @error('prompt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="note-box mb-3">
                        الأفضل تكتب: نوع الرحلة، المدن، عدد الأيام، مستوى الفخامة، نوع العميل المستهدف، البرنامج اليومي،
                        ونوع الأسعار المطلوبة.
                    </div>

                    <div class="section-title">إعدادات التوليد</div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">نوع الرحلة</label>
                            <select name="package_type" class="form-select">
                                <option value="travel_package"
                                    {{ old('package_type') == 'travel_package' ? 'selected' : '' }}>Travel Package</option>
                                <option value="nile_cruise"
                                    {{ old('package_type', 'nile_cruise') == 'nile_cruise' ? 'selected' : '' }}>Nile Cruise
                                </option>
                                <option value="day_tour" {{ old('package_type') == 'day_tour' ? 'selected' : '' }}>Day Tour
                                </option>
                                <option value="shore_excursion"
                                    {{ old('package_type') == 'shore_excursion' ? 'selected' : '' }}>Shore Excursion
                                </option>
                                <option value="custom" {{ old('package_type') == 'custom' ? 'selected' : '' }}>
                                    Tailor Made</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">عدد الأيام</label>
                            <input type="number" name="duration_days" class="form-control"
                                value="{{ old('duration_days', 5) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">عدد الليالي</label>
                            <input type="number" name="duration_nights" class="form-control"
                                value="{{ old('duration_nights', 4) }}">
                        </div>

                        <div class="col-md-4 mb-3" id="ai_destination_wrapper">
                            <label class="form-label">المدينة</label>
                            <select id="destination_selector" name="destination_id" class="form-select">
                                <option value="">اختر المدينة</option>
                                @foreach ($destinations ?? collect() as $destination)
                                    <option value="{{ $destination->id }}"
                                        data-country-id="{{ $destination->country_id }}"
                                        {{ old('destination_id') == $destination->id ? 'selected' : '' }}>
                                        {{ adminTrans($destination->name) }}
                                    </option>
                                @endforeach
                            </select>

                            <input type="hidden" name="primary_country_id" id="primary_country_id"
                                value="{{ old('primary_country_id') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">التصنيف</label>
                            <select name="category_id" class="form-select">
                                <option value="">اختر التصنيف</option>
                                @foreach ($categories ?? collect() as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ adminTrans($category->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">العملة</label>
                            <select name="currency_id" class="form-select">
                                <option value="">اختر العملة</option>
                                @foreach ($currencies ?? collect() as $currency)
                                    <option value="{{ $currency->id }}"
                                        {{ old('currency_id') == $currency->id || $currency->code == 'USD' ? 'selected' : '' }}>
                                        {{ $currency->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">نوع الجولة</label>
                            <select name="tour_type" class="form-select">
                                <option value="private" {{ old('tour_type') == 'private' ? 'selected' : '' }}>Private
                                </option>
                                <option value="group" {{ old('tour_type', 'group') == 'group' ? 'selected' : '' }}>Small
                                    Group Tour</option>
                                <option value="shared" {{ old('tour_type') == 'shared' ? 'selected' : '' }}>Shared</option>
                                <option value="custom" {{ old('tour_type') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">مستوى الصعوبة</label>
                            <select name="difficulty_level" class="form-select">
                                <option value="easy" {{ old('difficulty_level', 'easy') == 'easy' ? 'selected' : '' }}>
                                    Easy</option>
                                <option value="moderate" {{ old('difficulty_level') == 'moderate' ? 'selected' : '' }}>
                                    Moderate</option>
                                <option value="hard" {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>Hard
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">نظام الحجز</label>
                            <select name="booking_mode" class="form-select">
                                <option value="request"
                                    {{ old('booking_mode', 'request') == 'request' ? 'selected' : '' }}>Request</option>
                                <option value="instant" {{ old('booking_mode') == 'instant' ? 'selected' : '' }}>Instant
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">تفاصيل اختيارية تساعد الذكاء الاصطناعي</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">المسار المطلوب</label>
                            <input type="text" name="route_text" class="form-control"
                                value="{{ old('route_text') }}" placeholder="مثال: Luxor / Edfu / Kom Ombo / Aswan">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الجدول</label>
                            <input type="text" name="schedule_text" class="form-control"
                                value="{{ old('schedule_text') }}" placeholder="مثال: Every Monday from Luxor">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">مستوى الفخامة</label>
                            <select name="luxury_level" class="form-select">
                                <option value="standard" {{ old('luxury_level') == 'standard' ? 'selected' : '' }}>
                                    Standard</option>
                                <option value="luxury" {{ old('luxury_level', 'luxury') == 'luxury' ? 'selected' : '' }}>
                                    Luxury</option>
                                <option value="ultra_luxury"
                                    {{ old('luxury_level') == 'ultra_luxury' ? 'selected' : '' }}>Ultra Luxury</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">لغة المحتوى</label>
                            <select name="content_language" class="form-select">
                                <option value="en" {{ old('content_language', 'en') == 'en' ? 'selected' : '' }}>
                                    English</option>
                                <option value="ar" {{ old('content_language') == 'ar' ? 'selected' : '' }}>Arabic
                                </option>
                                <option value="both" {{ old('content_language') == 'both' ? 'selected' : '' }}>Arabic +
                                    English</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">تعليمات إضافية</label>
                            <textarea name="extra_instructions" rows="4" class="form-control"
                                placeholder="مثال: اجعل المحتوى مناسبًا للسياح الأجانب، أضف سياسة أطفال، أسعار موسمية، وبرنامج يومي مفصل">{{ old('extra_instructions') }}</textarea>
                        </div>
                    </div>

                    <div class="section-title">ماذا يتم توليده؟</div>

                    <div class="row">
                        @php
                            $generateOptions = [
                                'generate_description' => 'الوصف الكامل',
                                'generate_itinerary' => 'برنامج الرحلة',
                                'generate_facilities' => 'المرافق',
                                'generate_inclusions' => 'المشمول وغير المشمول',
                                'generate_prices' => 'الأسعار',
                                'generate_policies' => 'السياسات',
                                'generate_seo' => 'SEO',
                            ];
                        @endphp

                        @foreach ($generateOptions as $key => $label)
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" value="1"
                                        name="{{ $key }}" id="{{ $key }}"
                                        {{ old($key, true) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            توليد الرحلة
                        </button>

                        <a href="{{ route('admin.packages.create') }}" class="btn btn-outline-secondary">
                            إنشاء يدوي
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('packageAiForm');
            const submitBtn = document.getElementById('submitBtn');
            const pageLoader = document.getElementById('pageLoader');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            const destinationSelector = document.getElementById('destination_selector');
            const primaryCountryInput = document.getElementById('primary_country_id');

            function syncCountryFromDestination() {
                if (!destinationSelector || !primaryCountryInput) return;

                const selected = destinationSelector.options[destinationSelector.selectedIndex];
                const countryId = selected ? selected.getAttribute('data-country-id') : '';
                primaryCountryInput.value = countryId || '';
            }

            const packageTypeSelect = document.querySelector('select[name="package_type"]');
            const aiDestinationWrapper = document.getElementById('ai_destination_wrapper');

            function syncAiDestinationVisibility() {
                if (!packageTypeSelect || !aiDestinationWrapper) return;
                if (packageTypeSelect.value === 'nile_cruise') {
                    aiDestinationWrapper.style.display = 'none';
                } else {
                    aiDestinationWrapper.style.display = '';
                }
            }
            if (packageTypeSelect) {
                packageTypeSelect.addEventListener('change', syncAiDestinationVisibility);
                syncAiDestinationVisibility();
            }

            if (destinationSelector) {
                destinationSelector.addEventListener('change', syncCountryFromDestination);
                syncCountryFromDestination();
            }

            let progress = 0;
            let interval = null;
            let submitted = false;

            form.addEventListener('submit', function(event) {
                if (submitted) {
                    event.preventDefault();
                    return false;
                }

                submitted = true;
                submitBtn.disabled = true;
                pageLoader.classList.add('active');

                interval = setInterval(() => {
                    if (progress < 90) {
                        progress += Math.floor(Math.random() * 10) + 3;
                        if (progress > 90) progress = 90;

                        progressBar.style.width = progress + '%';
                        progressPercent.textContent = progress + '%';
                    }
                }, 220);
            });

            window.addEventListener('pageshow', function() {
                clearInterval(interval);
                progress = 0;

                if (progressBar) progressBar.style.width = '0%';
                if (progressPercent) progressPercent.textContent = '0%';
                if (pageLoader) pageLoader.classList.remove('active');
                if (submitBtn) submitBtn.disabled = false;

                submitted = false;
            });
        });
    </script>
@endsection
