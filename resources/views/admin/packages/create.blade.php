@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة رحلة جديدة'))

@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $viewErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();

    $selectedAttractionIds = collect(old('attraction_ids', []))
        ->map(fn($id) => (int) $id)
        ->all();
    $durationType = old('duration_type', 'days');
    $itinerary = old('itinerary');
    if (!is_array($itinerary) || $itinerary === []) {
        $itinerary = [['day_number' => 1]];
    }
    $included = old('included', []);
    $excluded = old('excluded', []);
    $prices = old('prices', []);
    $faqItems = old('faq_json', []);
    $adultMinAge = old('adult_min_age', 12);
    $childMinAge = old('child_min_age', 2);
    $childMaxAge = old('child_max_age', 11);
    $infantMinAge = old('infant_min_age', 0);
    $infantMaxAge = old('infant_max_age', 1);

    $steps = [
        1 => [
            'title' => admin_t('البيانات الأساسية'),
            'description' => admin_t('أدخل المعلومات الرئيسية والتصنيف الخاص بالرحلة.'),
        ],
        2 => [
            'title' => admin_t('الوصف والصور'),
            'description' => admin_t('أضف وصف الرحلة والصور التي ستظهر للعملاء.'),
        ],
        3 => [
            'title' => admin_t('المسار والمدة'),
            'description' => admin_t('حدد مدة الرحلة والبرنامج اليومي ومسار الرحلة.'),
        ],
        4 => [
            'title' => admin_t('الأسعار والسياسات'),
            'description' => admin_t('حدد أسعار الرحلة وما هو مشمول وسياسات الحجز.'),
        ],
        5 => [
            'title' => admin_t('النشر وSEO'),
            'description' => admin_t('راجع بيانات الرحلة وحدد إعدادات النشر ومحركات البحث.'),
        ],
    ];

    $errorFieldStepMap = [
        'title' => 1,
        'subtitle' => 1,
        'slug' => 1,
        'category_id' => 1,
        'destination_id' => 1,
        'primary_country_id' => 1,
        'package_type' => 1,
        'nile_cruise_type_id' => 1,
        'nile_cruise_category_id' => 1,
        'tour_type' => 1,
        'currency_id' => 1,
        'booking_mode' => 1,

        'short_description' => 2,
        'description' => 2,
        'featured_image' => 2,
        'gallery_images' => 2,
        'experience.brochure' => 2,
        'experience.og_image' => 2,

        'duration_type' => 3,
        'duration_days' => 3,
        'duration_nights' => 3,
        'duration_hours' => 3,
        'duration_text' => 3,
        'schedule_text' => 3,
        'route_text' => 3,
        'pickup_location' => 3,
        'dropoff_location' => 3,
        'destinations_text' => 3,
        'location_summary' => 3,
        'itinerary' => 3,
        'nile_cruise' => 3,

        'attraction_ids' => 4,
        'facilities' => 4,
        'included' => 4,
        'excluded' => 4,
        'adult_price' => 4,
        'child_price' => 4,
        'infant_price' => 4,
        'adult_min_age' => 4,
        'child_min_age' => 4,
        'child_max_age' => 4,
        'infant_min_age' => 4,
        'infant_max_age' => 4,
        'compare_price' => 4,
        'pricing_information' => 4,
        'children_policy' => 4,
        'pickup_policy' => 4,
        'cancellation_policy' => 4,
        'terms_conditions' => 4,
        'prices' => 4,
        'faq_json' => 4,
        'experience.addons' => 4,
        'experience.group_pricing_tiers' => 4,

        'min_participants' => 5,
        'max_participants' => 5,
        'booking_lead_days' => 5,
        'rating_avg' => 5,
        'reviews_count' => 5,
        'difficulty_level' => 5,
        'video_url' => 5,
        'published_at' => 5,
        'sort_order' => 5,
        'is_active' => 5,
        'is_featured' => 5,
        'is_best_seller' => 5,
        'is_ultra_luxury' => 5,
        'seo_title' => 5,
        'seo_description' => 5,
        'breadcrumb_title' => 5,
        'canonical_url' => 5,
    ];

    $initialStep = 1;
    $errorSteps = [];

    if ($viewErrors->any()) {
        foreach ($viewErrors->keys() as $errorKey) {
            $matchedStep = null;

            foreach ($errorFieldStepMap as $field => $stepNumber) {
                if ($errorKey === $field || str_starts_with($errorKey, $field . '.')) {
                    $matchedStep = $stepNumber;
                    break;
                }
            }

            if ($matchedStep !== null) {
                $errorSteps[$matchedStep] = true;
                if ($initialStep === 1) {
                    $initialStep = $matchedStep;
                }
            } else {
                $errorSteps[1] = true;
            }
        }
    }
@endphp

@section('css')
    <style>
        :root {
            --wizard-primary: #7c3aed;
            --wizard-primary-soft: rgba(124, 58, 237, 0.16);
            --wizard-page: #1e1b2e;
            --wizard-card: #2a3148;
            --wizard-card-soft: #323b56;
            --wizard-border: rgba(255, 255, 255, 0.08);
            --wizard-muted: rgba(255, 255, 255, 0.68);
            --wizard-input: rgba(255, 255, 255, 0.05);
            --wizard-success: #10b981;
            --wizard-danger: #ef4444;
            --wizard-warning: #f59e0b;
        }

        body {
            background: var(--wizard-page);
        }

        .package-wizard {
            color: #fff;
        }

        .wizard-shell {
            background: linear-gradient(180deg, rgba(124, 58, 237, 0.18), rgba(42, 49, 72, 0.96) 22%);
            border: 1px solid var(--wizard-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
        }

        .wizard-hero {
            padding: 28px 28px 18px;
            border-bottom: 1px solid var(--wizard-border);
            background:
                radial-gradient(circle at top {{ $isRtl ? 'right' : 'left' }}, rgba(167, 139, 250, 0.35), transparent 30%),
                linear-gradient(135deg, rgba(124, 58, 237, 0.24), rgba(42, 49, 72, 0.88));
        }

        .wizard-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #f4ecff;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .wizard-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 10px;
        }

        .wizard-subtitle {
            color: var(--wizard-muted);
            max-width: 760px;
            margin: 0;
        }

        .wizard-top-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-end;
        }

        .wizard-top-actions .btn {
            border-radius: 12px;
            min-height: 46px;
        }

        .wizard-stepper {
            padding: 22px 28px 10px;
            border-bottom: 1px solid var(--wizard-border);
        }

        .wizard-steps {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
        }

        .wizard-step {
            position: relative;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid var(--wizard-border);
            background: rgba(255, 255, 255, 0.02);
            cursor: pointer;
            transition: .25s ease;
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        .wizard-step:hover {
            border-color: rgba(124, 58, 237, 0.5);
            transform: translateY(-2px);
        }

        .wizard-step.is-disabled {
            opacity: .62;
            cursor: not-allowed;
        }

        .wizard-step.is-active {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.28), rgba(124, 58, 237, 0.08));
            border-color: rgba(167, 139, 250, 0.55);
            box-shadow: inset 0 0 0 1px rgba(167, 139, 250, 0.18);
        }

        .wizard-step.is-complete {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.28);
        }

        .wizard-step-badge {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-weight: 700;
        }

        .wizard-step.is-active .wizard-step-badge {
            background: #fff;
            color: var(--wizard-primary);
        }

        .wizard-step.is-complete .wizard-step-badge {
            background: var(--wizard-success);
        }

        .wizard-step-title {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .wizard-step-description {
            margin: 0;
            color: var(--wizard-muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .wizard-mobile-progress {
            display: none;
        }

        .wizard-body {
            padding: 28px;
        }

        .wizard-panel {
            display: none;
        }

        .wizard-panel.is-active {
            display: block;
        }

        .wizard-panel-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .wizard-panel-title {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 800;
        }

        .wizard-panel-copy {
            color: var(--wizard-muted);
            margin: 0;
        }

        .wizard-panel-pill {
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--wizard-border);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            white-space: nowrap;
        }

        .wizard-grid {
            display: grid;
            gap: 18px;
        }

        .wizard-grid.two-columns {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-section-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02));
            border: 1px solid var(--wizard-border);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .section-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--wizard-border);
            background: rgba(255, 255, 255, 0.03);
        }

        .section-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--wizard-primary-soft);
            color: #e9dcff;
            flex: 0 0 44px;
        }

        .section-header h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .section-header p {
            margin: 0;
            color: var(--wizard-muted);
            font-size: 13px;
        }

        .section-body {
            padding: 20px;
        }

        .fields-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .fields-grid.two-up {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-span-2 {
            grid-column: span 2;
        }

        .field-span-3 {
            grid-column: span 3;
        }

        .form-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            font-weight: 700;
            color: #f5f3ff;
        }

        .required-mark {
            color: #fb7185;
            font-size: 15px;
        }

        .field-help {
            display: block;
            color: var(--wizard-muted);
            font-size: 12px;
            margin-top: 7px;
        }

        .form-control,
        .form-select,
        textarea {
            min-height: 48px;
            border-radius: 13px !important;
            border: 1px solid var(--wizard-border) !important;
            background: var(--wizard-input) !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        textarea.form-control {
            min-height: auto;
        }

        .form-control::placeholder,
        textarea::placeholder {
            color: rgba(255, 255, 255, 0.46);
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: rgba(167, 139, 250, 0.7) !important;
            box-shadow: 0 0 0 .22rem rgba(124, 58, 237, 0.18) !important;
        }

        .form-select option {
            /* color: #111827; */
        }

        .form-control.is-invalid,
        .form-select.is-invalid,
        textarea.is-invalid,
        .field-error {
            border-color: rgba(239, 68, 68, 0.92) !important;
            box-shadow: 0 0 0 .18rem rgba(239, 68, 68, 0.12) !important;
        }

        .invalid-feedback {
            display: block;
            font-size: 12px;
            margin-top: 8px;
            color: #fecaca;
        }

        .counter-line {
            display: flex;
            justify-content: flex-end;
            margin-top: 7px;
            color: var(--wizard-muted);
            font-size: 12px;
        }

        .choice-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        .choice-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--wizard-border);
            background: rgba(255, 255, 255, 0.03);
        }

        .repeat-box {
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid var(--wizard-border);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .stack-list {
            display: grid;
            gap: 14px;
        }

        .editor-card {
            margin-bottom: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.045), rgba(255, 255, 255, 0.025));
        }

        .editor-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .editor-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .editor-card-title strong {
            font-size: 15px;
            color: #fff;
        }

        .editor-card-badge {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 36px;
            background: rgba(124, 58, 237, 0.16);
            color: #f4ecff;
        }

        .editor-card-body {
            display: grid;
            gap: 16px;
        }

        .editor-inline-input {
            flex: 1 1 auto;
        }

        .editor-textarea {
            min-height: 120px !important;
        }

        .repeat-box {
            transition: opacity .25s ease, transform .25s ease, height .25s ease, margin .25s ease, padding .25s ease;
            will-change: opacity, transform, height;
        }

        .repeat-box.is-entering {
            opacity: 0;
            transform: translateY(-10px);
        }

        .repeat-box.is-removing {
            opacity: 0;
            transform: translateY(-6px);
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            overflow: hidden;
        }

        .dynamic-section-shell {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 20px;
            background:
                radial-gradient(circle at top, rgba(124, 92, 255, 0.08), transparent 36%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.025), rgba(255, 255, 255, 0.015));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        .dynamic-section-shell.is-ltr {
            direction: ltr !important;
            text-align: left !important;
            unicode-bidi: isolate;
        }

        .dynamic-section-shell.is-ltr input,
        .dynamic-section-shell.is-ltr select,
        .dynamic-section-shell.is-ltr textarea,
        .dynamic-section-shell.is-ltr button,
        .dynamic-section-shell.is-ltr label {
            direction: ltr !important;
            text-align: left !important;
        }

        .dynamic-section-shell.is-ltr .btn-icon-text {
            flex-direction: row !important;
        }

        .dynamic-section-head {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .dynamic-section-icon {
            width: 64px;
            height: 64px;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 64px;
            background: linear-gradient(180deg, rgba(124, 92, 255, 0.36), rgba(124, 92, 255, 0.22));
            color: #fff;
            box-shadow: 0 18px 30px rgba(124, 92, 255, 0.18);
        }

        .dynamic-section-head h4 {
            margin: 0 0 6px;
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
        }

        .dynamic-section-head p {
            margin: 0;
            color: rgba(255, 255, 255, 0.62);
            font-size: 13px;
        }

        .faq-list,
        .itinerary-list {
            display: grid;
            gap: 12px;
        }

        .faq-item-card,
        .itinerary-item-card {
            direction: ltr;
            min-width: 0;
            margin-bottom: 0;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.02));
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .faq-item-grid {
            display: grid;
            grid-template-columns: 64px minmax(0, .9fr) minmax(0, 1.1fr) 48px;
            grid-template-areas: "order question answer remove";
            gap: 16px;
            align-items: start;
        }

        .itinerary-item-grid {
            display: grid;
            grid-template-columns: 64px minmax(0, .85fr) minmax(0, 1.15fr) 48px;
            grid-template-areas:
                "order date place remove"
                "order meals activities .";
            gap: 16px;
            align-items: start;
        }

        .dynamic-order-column {
            grid-area: order;
        }

        .faq-question-field {
            grid-area: question;
        }

        .faq-answer-field {
            grid-area: answer;
        }

        .itinerary-date-field {
            grid-area: date;
        }

        .itinerary-hour-fields {
            grid-area: date;
        }

        .itinerary-place-field {
            grid-area: place;
        }

        .itinerary-meals-field {
            grid-area: meals;
        }

        .meal-options-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            padding-top: 4px;
        }

        .meal-pill-checkbox {
            cursor: pointer;
            user-select: none;
            margin: 0;
        }

        .meal-pill-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .meal-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .meal-pill-checkbox input[type="checkbox"]:checked + .meal-pill {
            background: linear-gradient(135deg, rgba(124, 92, 255, 0.35), rgba(124, 92, 255, 0.18));
            border-color: rgba(124, 92, 255, 0.6);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(124, 92, 255, 0.25);
        }

        .meal-pill-checkbox:hover .meal-pill {
            border-color: rgba(124, 92, 255, 0.4);
            color: #ffffff;
        }

        .itinerary-activities-field {
            grid-area: activities;
        }

        .itinerary-tour-package-fields,
        .itinerary-tour-package-advanced {
            grid-column: 2 / 4;
            width: 100%;
        }

        .fields-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .dynamic-remove-control {
            grid-area: remove;
        }

        .item-order-badge {
            width: 60px;
            min-height: 58px;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, rgba(124, 92, 255, 0.45), rgba(124, 92, 255, 0.24));
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .04em;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .item-order-label {
            display: block;
            font-size: 9px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .72);
            margin-bottom: 5px;
        }

        .item-order-number {
            line-height: 1;
        }

        .field-block {
            display: grid;
            gap: 10px;
            min-width: 0;
        }

        .field-block-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #f4f1ff;
        }

        .field-shell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 48px;
            width: 100%;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: #2e3055;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .dynamic-section-shell.is-ltr .field-shell {
            direction: ltr !important;
            flex-direction: row;
        }

        .field-shell:focus-within {
            border-color: rgba(124, 92, 255, 0.95);
            box-shadow: 0 0 0 2px rgba(124, 92, 255, 0.2), 0 10px 24px rgba(124, 92, 255, 0.14);
        }

        .field-shell-icon {
            color: rgba(196, 172, 255, 0.95);
            font-size: 18px;
            flex: 0 0 auto;
        }

        .field-shell input,
        .field-shell select,
        .field-shell textarea {
            direction: ltr !important;
            text-align: left !important;
            width: 100%;
            min-height: 46px;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #fff !important;
        }

        .field-shell input[type="date"] {
            min-width: 0;
            color-scheme: dark;
        }

        .field-shell textarea {
            min-height: 120px;
            padding-top: 14px !important;
            padding-bottom: 14px !important;
            resize: vertical;
        }

        .field-shell select option {
            color: #111827;
        }

        .field-shell input::placeholder,
        .field-shell textarea::placeholder {
            color: rgba(255, 255, 255, 0.45);
        }

        .field-shell-textarea {
            align-items: flex-start;
        }

        .field-shell-textarea .field-shell-icon {
            margin-top: 16px;
        }

        .hover-delete-btn {
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid rgba(248, 113, 113, 0.14);
            background: rgba(239, 68, 68, 0.09);
            color: #fb7185;
            opacity: .28;
            transition: opacity .2s ease, background .2s ease, border-color .2s ease, transform .2s ease;
        }

        .faq-item-card:hover .hover-delete-btn,
        .itinerary-item-card:hover .hover-delete-btn {
            opacity: 1;
        }

        .hover-delete-btn:hover {
            color: #fecaca;
            background: rgba(239, 68, 68, 0.16);
            border-color: rgba(248, 113, 113, 0.26);
            transform: translateY(-1px);
        }

        .dynamic-add-btn {
            width: fit-content;
            min-height: 56px;
            margin-top: 20px;
            padding-inline: 22px;
            border-radius: 14px;
            border: 1px solid rgba(124, 92, 255, 0.55);
            background: rgba(124, 92, 255, 0.08);
            color: #b79bff;
            box-shadow: 0 14px 28px rgba(124, 92, 255, 0.08);
        }

        .dynamic-add-btn:hover {
            color: #d5c6ff;
            background: rgba(124, 92, 255, 0.14);
            transform: translateY(-1px);
        }

        .dynamic-section-shell.is-ltr .dynamic-add-btn {
            margin-right: auto;
            margin-left: 0;
        }

        .empty-dynamic-state {
            display: grid;
            place-items: center;
            gap: 10px;
            min-height: 180px;
            text-align: center;
        }

        .empty-dynamic-state strong {
            font-size: 34px;
            line-height: 1;
        }

        .icon-remove-btn {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            flex: 0 0 42px;
        }

        .repeat-box-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .repeat-box-title strong {
            font-size: 15px;
        }

        .empty-state {
            border: 1px dashed rgba(255, 255, 255, 0.14);
            border-radius: 16px;
            padding: 26px 18px;
            text-align: center;
            color: var(--wizard-muted);
            background: rgba(255, 255, 255, 0.02);
        }

        .facility-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .facility-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(167, 139, 250, 0.24);
            background: rgba(124, 58, 237, 0.10);
            color: #f4ecff;
            border-radius: 999px;
            padding: 9px 14px;
            cursor: pointer;
            transition: .2s ease;
        }

        .facility-chip:hover {
            transform: translateY(-1px);
            background: rgba(124, 58, 237, 0.18);
        }

        .attractions-picker-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .attractions-search {
            position: relative;
            flex: 1 1 420px;
        }

        .attractions-search i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: var(--wizard-muted);
            pointer-events: none;
        }

        .attractions-search .form-control {
            padding-left: 44px;
        }

        .attractions-selected-count {
            flex: 0 0 auto;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(124, 92, 255, 0.35);
            background: rgba(124, 92, 255, 0.10);
            color: #d8ccff;
            font-size: 13px;
            font-weight: 700;
        }

        .attractions-picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 12px;
            max-height: 430px;
            overflow-y: auto;
            padding: 4px;
        }

        .attraction-choice {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 14px;
            border: 1px solid var(--wizard-border);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.025);
            cursor: pointer;
            transition: border-color .2s ease, background .2s ease, transform .2s ease;
        }

        .attraction-choice:hover {
            transform: translateY(-1px);
            border-color: rgba(167, 139, 250, 0.48);
        }

        .attraction-choice:has(input:checked) {
            border-color: rgba(124, 92, 255, 0.85);
            background: rgba(124, 92, 255, 0.14);
            box-shadow: inset 0 0 0 1px rgba(124, 92, 255, 0.18);
        }

        .attraction-choice input {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            accent-color: var(--wizard-primary);
        }

        .attraction-choice-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            border-radius: 13px;
            background: var(--wizard-primary-soft);
            color: #e9dcff;
            font-size: 19px;
        }

        .attraction-choice-copy {
            min-width: 0;
        }

        .attraction-choice-copy strong,
        .attraction-choice-copy small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .attraction-choice-copy strong {
            color: #fff;
            font-size: 14px;
        }

        .attraction-choice-copy small {
            margin-top: 4px;
            color: var(--wizard-muted);
        }

        .attraction-choice.is-filtered-out {
            display: none;
        }

        .upload-zone {
            position: relative;
            display: grid;
            place-items: center;
            min-height: 220px;
            border: 1px dashed rgba(167, 139, 250, 0.34);
            border-radius: 18px;
            background: rgba(124, 58, 237, 0.07);
            text-align: center;
            padding: 22px;
            cursor: pointer;
            overflow: hidden;
        }

        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-zone h4 {
            font-size: 18px;
            font-weight: 700;
            margin: 14px 0 8px;
        }

        .upload-zone p {
            margin: 0;
            color: var(--wizard-muted);
            max-width: 360px;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .preview-card {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--wizard-border);
            background: rgba(255, 255, 255, 0.04);
        }

        .preview-card img {
            width: 100%;
            height: 138px;
            object-fit: cover;
            display: block;
        }

        .preview-card-footer {
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            font-size: 12px;
        }

        .preview-remove {
            border: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.16);
            color: #fecaca;
        }

        .split-card {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .summary-item {
            border: 1px solid var(--wizard-border);
            border-radius: 15px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.03);
        }

        .summary-label {
            display: block;
            color: var(--wizard-muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: 700;
        }

        .review-list {
            display: grid;
            gap: 12px;
        }

        .review-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px;
            border: 1px solid var(--wizard-border);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.03);
        }

        .review-meta small {
            color: var(--wizard-muted);
            display: block;
            margin-top: 3px;
        }

        .wizard-actions {
            position: sticky;
            bottom: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 26px;
            padding: 18px 22px;
            border-radius: 18px;
            border: 1px solid var(--wizard-border);
            background: rgba(25, 28, 43, 0.92);
            backdrop-filter: blur(10px);
        }

        .wizard-actions-meta {
            color: var(--wizard-muted);
            font-size: 13px;
        }

        .wizard-actions-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .wizard-actions .btn {
            min-height: 46px;
            border-radius: 12px;
            padding-inline: 18px;
        }

        .btn-wizard-primary {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            border: 0;
            color: #fff;
        }

        .btn-wizard-primary:hover {
            color: #fff;
            opacity: .95;
        }

        .btn-wizard-outline {
            background: transparent;
            border: 1px solid var(--wizard-border);
            color: #fff;
        }

        .btn-wizard-outline:hover {
            border-color: rgba(167, 139, 250, 0.55);
            color: #fff;
        }

        .btn-icon-text {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .d-none-force {
            display: none !important;
        }

        @media (max-width: 1199px) {

            .wizard-steps,
            .fields-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .field-span-3 {
                grid-column: span 2;
            }
        }

        @media (max-width: 991px) {

            .wizard-grid.two-columns,
            .split-card,
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .wizard-steps {
                display: none;
            }

            .wizard-mobile-progress {
                display: block;
                margin-top: 4px;
            }

            .wizard-mobile-bar {
                height: 8px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.08);
                overflow: hidden;
                margin-top: 10px;
            }

            .wizard-mobile-bar>span {
                display: block;
                height: 100%;
                border-radius: inherit;
                background: linear-gradient(90deg, #8b5cf6, #7c3aed);
                transition: width .25s ease;
            }

            .faq-item-grid {
                grid-template-columns: 56px minmax(0, 1fr) 48px;
                grid-template-areas:
                    "order question remove"
                    ". answer .";
            }

            .itinerary-item-grid {
                grid-template-columns: 56px minmax(0, 1fr) 48px;
                grid-template-areas:
                    "order date remove"
                    ". place ."
                    ". meals ."
                    ". activities .";
            }

            .itinerary-tour-package-fields,
            .itinerary-tour-package-advanced {
                grid-column: 2;
            }
        }

        @media (max-width: 767px) {

            .wizard-hero,
            .wizard-stepper,
            .wizard-body {
                padding-inline: 18px;
            }

            .wizard-title {
                font-size: 24px;
            }

            .wizard-panel-header {
                flex-direction: column;
            }

            .fields-grid,
            .fields-grid.two-up,
            .field-span-2,
            .field-span-3 {
                grid-template-columns: 1fr;
                grid-column: span 1;
            }

            .wizard-actions {
                padding: 16px;
            }

            .wizard-actions-group {
                width: 100%;
            }

            .wizard-actions-group .btn {
                flex: 1 1 auto;
            }

            .editor-card-head {
                align-items: stretch;
            }

            .editor-card-title {
                flex: 1 1 auto;
            }

            .editor-inline-input {
                width: 100%;
            }

            .faq-item-grid,
            .itinerary-item-grid {
                grid-template-columns: 1fr;
            }

            .faq-item-grid {
                grid-template-areas:
                    "order"
                    "question"
                    "answer"
                    "remove";
            }

            .itinerary-item-grid {
                grid-template-areas:
                    "order"
                    "date"
                    "place"
                    "meals"
                    "activities"
                    "remove";
            }

            .itinerary-tour-package-fields,
            .itinerary-tour-package-advanced {
                grid-column: 1 / -1;
            }

            .item-order-badge {
                width: 48px;
                height: 48px;
            }

            .hover-delete-btn {
                opacity: 1;
                width: 100%;
            }

            .dynamic-section-shell {
                padding: 16px;
            }
        }
    
        .nile-choice-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:10px}.nile-choice-card{position:relative;display:block;border:1px solid var(--wizard-border);background:var(--wizard-input);border-radius:14px;padding:14px;cursor:pointer;transition:.18s}.nile-choice-card:hover{border-color:rgba(124,58,237,.65);transform:translateY(-1px)}.nile-choice-card.is-selected{border-color:var(--wizard-primary);background:var(--wizard-primary-soft);box-shadow:0 0 0 1px var(--wizard-primary)}.nile-choice-card input{position:absolute;opacity:0;pointer-events:none}.nile-choice-card strong{display:block;font-size:14px;margin-bottom:4px}.nile-choice-card small{display:block;color:var(--wizard-muted);font-size:11px;line-height:1.5}.nile-choice-card img{width:100%;height:72px;object-fit:cover;border-radius:9px;margin-bottom:10px}.nile-choice-select{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important}.nile-cruise-advanced-host{grid-column:1/-1}.nile-cruise-advanced-host .nile-cruise-extended-section{margin-top:0}@media(max-width:900px){.nile-choice-grid{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y package-wizard" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.index') }}">{{ admin_t('الرئيسية') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.packages.index') }}">{{ admin_t('الرحلات') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ admin_t('إضافة رحلة جديدة') }}</li>
            </ol>
        </nav>

        <div class="wizard-shell">
            <div class="wizard-hero">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <span class="wizard-eyebrow">
                            <i class="ti ti-route"></i>
                            {{ admin_t('مراجعة ورفع') }}
                        </span>
                        <h1 class="wizard-title">{{ admin_t('إضافة رحلة جديدة') }}</h1>
                        <p class="wizard-subtitle">{{ admin_t('أضف جميع تفاصيل الرحلة خطوة بخطوة ثم قم بنشرها.') }}</p>
                    </div>

                    <div class="wizard-top-actions">
                        @if (Route::has('admin.packages.create-with-ai'))
                            <a href="{{ route('admin.packages.create-with-ai') }}" class="btn btn-light">
                                <span class="btn-icon-text">
                                    <i class="ti ti-sparkles"></i>
                                    {{ admin_t('إنشاء بالذكاء الاصطناعي') }}
                                </span>
                            </a>
                        @endif

                        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-light" id="cancelWizardBtn">
                            <span class="btn-icon-text">
                                <i class="ti ti-arrow-back-up"></i>
                                {{ admin_t('الرجوع لقائمة الرحلات') }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="wizard-stepper">
                <div class="wizard-steps" id="wizardSteps">
                    @foreach ($steps as $number => $step)
                        @php $hasStepError = !empty($errorSteps[$number]); @endphp
                        <button type="button" class="wizard-step {{ $hasStepError ? 'border-danger' : '' }}" data-step-trigger="{{ $number }}">
                            <span class="wizard-step-badge {{ $hasStepError ? 'bg-danger text-white' : '' }}">
                                @if($hasStepError) <i class="ti ti-alert-triangle"></i> @else {{ $number }} @endif
                            </span>
                            <span>
                                <span class="wizard-step-title {{ $hasStepError ? 'text-danger fw-bold' : '' }}">
                                    {{ $step['title'] }}
                                    @if($hasStepError) <span class="badge bg-danger-subtle text-danger ms-1" style="font-size:10px;">{{ admin_t('خطأ') }}</span> @endif
                                </span>
                                <span class="wizard-step-description">{{ $step['description'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="wizard-mobile-progress">
                    <strong id="mobileStepTitle">{{ $steps[$initialStep]['title'] }}</strong>
                    <div class="wizard-actions-meta mt-1" id="mobileStepCounter"></div>
                    <div class="wizard-mobile-bar">
                        <span id="mobileStepBar" style="width: {{ ($initialStep / count($steps)) * 100 }}%"></span>
                    </div>
                </div>
            </div>

            <div class="wizard-body">
                @if ($viewErrors->any())
                    <div class="alert alert-danger mx-4 mt-3 mb-3 shadow-sm" style="border-radius: 16px; background: rgba(239, 68, 68, 0.18); border: 1px solid rgba(239, 68, 68, 0.5); color: #ffffff;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-alert-triangle-filled text-danger fs-3"></i>
                            <h5 class="mb-0 text-white fw-bold">
                                {{ admin_t('تعذر حفظ الرحلة! يرجى مراجعة الأخطاء الموضحة أدناه واستكمال الحقول المطلوب:') }}
                            </h5>
                        </div>
                        <ul class="mb-0 ps-4" style="line-height: 1.8;">
                            @foreach ($viewErrors->all() as $error)
                                <li style="font-weight: 600;">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="packageWizardForm" action="{{ route('admin.packages.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="wizard-panel" data-step-panel="1">
                        <div class="wizard-panel-header">
                            <div>
                                <h2 class="wizard-panel-title">{{ admin_t('البيانات الأساسية') }}</h2>
                                <p class="wizard-panel-copy">
                                    {{ admin_t('أدخل المعلومات الرئيسية والتصنيف الخاص بالرحلة.') }}</p>
                            </div>
                            <div class="wizard-panel-pill">
                                {{ admin_t('الخطوة :current من :total', ['current' => 1, 'total' => count($steps)]) }}
                            </div>
                        </div>

                        <div class="wizard-grid">
                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-briefcase"></i></div>
                                    <div>
                                        <h3>{{ admin_t('معلومات الرحلة الأساسية') }}</h3>
                                        <p>{{ admin_t('عرّف هوية الرحلة والجهة المرتبطة بها وإعدادات الحجز الأساسية.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="fields-grid">
                                        <div class="field-span-3 mb-2">
                                            @include('admin.packages.partials.tour-type-selector')

                                            <!-- Nile Cruise Fields -->
                                            <div id="nile_cruise_type_wrapper" data-tour-type-section="nile_cruise" style="display: {{ old('package_type') == 'nile_cruise' ? 'block' : 'none' }};" class="mt-3">
                                                <label class="form-label" for="nile_cruise_type_id">
                                                    {{ admin_t('Nile Cruise Type') }}
                                                    <span class="required-mark">*</span>
                                                </label>
                                                <select id="nile_cruise_type_id" name="nile_cruise_type_id"
                                                    class="form-select nile-choice-select @error('nile_cruise_type_id') is-invalid @enderror">
                                                    <option value="">{{ admin_t('Select Nile Cruise Type') }}</option>
                                                    @foreach($nileCruiseTypes ?? [] as $nType)
                                                        <option value="{{ $nType->id }}" data-slug="{{ $nType->slug }}" data-has-categories="{{ $nType->categories->count() > 0 ? 'true' : 'false' }}"
                                                            {{ old('nile_cruise_type_id') == $nType->id ? 'selected' : '' }}>
                                                            {{ $nType->display_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="nile-choice-grid" id="nileCruiseTypeCards">
                                                    @foreach($nileCruiseTypes ?? [] as $nType)
                                                        <button type="button" class="nile-choice-card text-start" data-nile-type-card="{{ $nType->id }}" data-type-has-categories="{{ $nType->categories->count() > 0 ? 'true' : 'false' }}">
                                                            <img src="{{ $nType->image_url }}" alt="{{ $nType->display_name }}">
                                                            <strong>{{ $nType->display_name }}</strong>
                                                            <small>{{ $nType->display_short_description ?: admin_t('اختر هذا النوع للرحلة النيلية') }}</small>
                                                        </button>
                                                    @endforeach
                                                </div>
                                                @error('nile_cruise_type_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div id="nile_cruise_category_wrapper" data-tour-type-section="nile_cruise" style="display: {{ (old('package_type') == 'nile_cruise' && old('nile_cruise_type_id')) ? 'block' : 'none' }};" class="mt-3">
                                                <label class="form-label" for="nile_cruise_category_id">
                                                    {{ admin_t('Nile Cruise Category') }}
                                                    <span class="required-mark">*</span>
                                                </label>
                                                <select id="nile_cruise_category_id" name="nile_cruise_category_id"
                                                    class="form-select nile-choice-select @error('nile_cruise_category_id') is-invalid @enderror">
                                                    <option value="">{{ admin_t('Select Nile Cruise Category') }}</option>
                                                    @foreach($nileCruiseTypes ?? [] as $nType)
                                                        @foreach($nType->categories as $nCat)
                                                            <option value="{{ $nCat->id }}" data-type-id="{{ $nType->id }}" class="nile-cat-option nile-cat-type-{{ $nType->id }}"
                                                                {{ old('nile_cruise_category_id') == $nCat->id ? 'selected' : '' }}>
                                                                {{ $nCat->display_name }}
                                                            </option>
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                                <div class="nile-choice-grid" id="nileCruiseCategoryCards">
                                                    @foreach($nileCruiseTypes ?? [] as $nType)
                                                        @foreach($nType->categories as $nCat)
                                                            <button type="button" class="nile-choice-card text-start" data-nile-category-card="{{ $nCat->id }}" data-type-id="{{ $nType->id }}">
                                                                <img src="{{ $nCat->image_url }}" alt="{{ $nCat->display_name }}">
                                                                <strong>{{ $nCat->display_name }}</strong>
                                                                <small>{{ $nCat->display_short_description ?: admin_t('تصنيف الرحلة النيلية') }}</small>
                                                            </button>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                                @error('nile_cruise_category_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form-label" for="title">
                                                {{ admin_t('عنوان الرحلة') }}
                                                <span class="required-mark">*</span>
                                            </label>
                                            <input id="title" type="text" name="title"
                                                class="form-control @error('title') is-invalid @enderror"
                                                value="{{ old('title') }}"
                                                placeholder="{{ admin_t('اكتب عنوانًا واضحًا للرحلة') }}"
                                                data-required-step="1">
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="subtitle">{{ admin_t('العنوان الفرعي') }}</label>
                                            <input id="subtitle" type="text" name="subtitle"
                                                class="form-control @error('subtitle') is-invalid @enderror"
                                                value="{{ old('subtitle') }}"
                                                placeholder="{{ admin_t('أضف سطرًا تعريفيا قصيرًا') }}">
                                            @error('subtitle')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label" for="slug">{{ admin_t('Slug') }}</label>
                                            <input id="slug" type="text" name="slug"
                                                class="form-control @error('slug') is-invalid @enderror"
                                                value="{{ old('slug') }}"
                                                placeholder="{{ admin_t('يتم توليده تلقائيًا إذا تركته فارغًا') }}">
                                            @error('slug')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label" for="category_id">
                                                {{ admin_t('Content Category / Theme') }} <small class="text-muted">({{ admin_t('optional') }})</small>
                                            </label>
                                            <select id="category_id" name="category_id"
                                                class="form-select @error('category_id') is-invalid @enderror">
                                                <option value="">{{ admin_t('Select optional content category') }}</option>
                                                @foreach ($categories ?? collect() as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ adminTrans($category->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="tour-type-conditional" data-tour-type-section="travel_package,day_tour,shore_excursion,custom" style="display: {{ old('package_type') == 'nile_cruise' ? 'none' : 'block' }};">
                                            <label class="form-label" for="destination_selector">
                                                {{ admin_t('المدينة') }}
                                                <span class="required-mark">*</span>
                                            </label>
                                            <select id="destination_selector" name="destination_id"
                                                class="form-select @error('destination_id') is-invalid @enderror"
                                                data-required-step="1">
                                                <option value="">{{ admin_t('اختر المدينة') }}</option>
                                                @foreach ($destinations ?? collect() as $destination)
                                                    <option value="{{ $destination->id }}"
                                                        data-country-id="{{ $destination->country_id }}"
                                                        data-destination-name="{{ adminTrans($destination->name) }}"
                                                        {{ old('destination_id') == $destination->id ? 'selected' : '' }}>
                                                        {{ adminTrans($destination->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="primary_country_id" id="primary_country_id"
                                                value="{{ old('primary_country_id') }}">
                                            @error('destination_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            @include('admin.packages.partials.tour-package-cities')
                                        </div>

                                        <div>
                                            <label class="form-label" for="tour_type">{{ admin_t('Tour Style / Group Style') }}</label>
                                            <select id="tour_type" name="tour_type"
                                                class="form-select @error('tour_type') is-invalid @enderror">
                                                <option value="">{{ admin_t('اختر نوع الجولة') }}</option>
                                                <option value="private"
                                                    {{ old('tour_type') == 'private' ? 'selected' : '' }}>
                                                    {{ admin_t('خاصة') }}
                                                </option>
                                                <option value="group"
                                                    {{ old('tour_type') == 'group' ? 'selected' : '' }}>
                                                    {{ admin_t('مجموعة صغيرة') }}
                                                </option>
                                                <option value="shared"
                                                    {{ old('tour_type') == 'shared' ? 'selected' : '' }}>
                                                    {{ admin_t('مشتركة') }}
                                                </option>
                                                <option value="custom"
                                                    {{ old('tour_type') == 'custom' ? 'selected' : '' }}>
                                                    {{ admin_t('مخصصة') }}
                                                </option>
                                            </select>
                                            @error('tour_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label" for="currency_id">{{ admin_t('العملة') }}</label>
                                            <select id="currency_id" name="currency_id"
                                                class="form-select @error('currency_id') is-invalid @enderror">
                                                <option value="">{{ admin_t('اختر العملة') }}</option>
                                                @foreach ($currencies ?? collect() as $currency)
                                                    <option value="{{ $currency->id }}"
                                                        {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                        {{ $currency->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('currency_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="booking_mode">{{ admin_t('نظام الحجز') }}</label>
                                            <select id="booking_mode" name="booking_mode"
                                                class="form-select @error('booking_mode') is-invalid @enderror">
                                                <option value="">{{ admin_t('اختر نظام الحجز') }}</option>
                                                <option value="request"
                                                    {{ old('booking_mode') == 'request' ? 'selected' : '' }}>
                                                    {{ admin_t('طلب') }}
                                                </option>
                                                <option value="instant"
                                                    {{ old('booking_mode') == 'instant' ? 'selected' : '' }}>
                                                    {{ admin_t('فوري') }}
                                                </option>
                                            </select>
                                            @error('booking_mode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="difficulty_level">{{ admin_t('مستوى الصعوبة') }}</label>
                                            <select id="difficulty_level" name="difficulty_level"
                                                class="form-select @error('difficulty_level') is-invalid @enderror">
                                                <option value="">{{ admin_t('اختر المستوى') }}</option>
                                                <option value="easy"
                                                    {{ old('difficulty_level') == 'easy' ? 'selected' : '' }}>
                                                    {{ admin_t('سهل') }}
                                                </option>
                                                <option value="moderate"
                                                    {{ old('difficulty_level') == 'moderate' ? 'selected' : '' }}>
                                                    {{ admin_t('متوسط') }}
                                                </option>
                                                <option value="hard"
                                                    {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>
                                                    {{ admin_t('صعب') }}
                                                </option>
                                            </select>
                                            @error('difficulty_level')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-panel" data-step-panel="2">
                        <div class="wizard-panel-header">
                            <div>
                                <h2 class="wizard-panel-title">{{ admin_t('الوصف والصور') }}</h2>
                                <p class="wizard-panel-copy">{{ admin_t('أضف وصف الرحلة والصور التي ستظهر للعملاء.') }}
                                </p>
                            </div>
                            <div class="wizard-panel-pill">
                                {{ admin_t('الخطوة :current من :total', ['current' => 2, 'total' => count($steps)]) }}
                            </div>
                        </div>

                        <div class="wizard-grid">
                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-writing"></i></div>
                                    <div>
                                        <h3>{{ admin_t('الوصف والنصوص') }}</h3>
                                        <p>{{ admin_t('اكتب المحتوى الذي سيظهر للعميل في صفحة الرحلة.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="fields-grid two-up">
                                        <div class="field-span-2">
                                            <label class="form-label"
                                                for="short_description">{{ admin_t('وصف مختصر') }}</label>
                                            <textarea id="short_description" name="short_description" rows="4"
                                                class="form-control @error('short_description') is-invalid @enderror"
                                                placeholder="{{ admin_t('الوصف المختصر يظهر في القوائم ونتائج البحث.') }}" data-counter-max="150">{{ old('short_description') }}</textarea>
                                            <div class="counter-line"><span data-counter-for="short_description">0 /
                                                    150</span></div>
                                            @error('short_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="field-span-2">
                                            <label class="form-label"
                                                for="description">{{ admin_t('الوصف الكامل') }}</label>
                                            <textarea id="description" name="description" rows="8"
                                                class="form-control @error('description') is-invalid @enderror"
                                                placeholder="{{ admin_t('أضف وصفًا تفصيليًا غنيًا يساعد العميل على اتخاذ القرار.') }}">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-photo"></i></div>
                                    <div>
                                        <h3>{{ admin_t('الصور والمعرض') }}</h3>
                                        <p>{{ admin_t('ارفع الصورة الرئيسية وصور المعرض مع معاينة مباشرة قبل الحفظ.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="split-card">
                                        <div>
                                            <label class="form-label">{{ admin_t('الصورة الرئيسية') }}</label>
                                            <label class="upload-zone" for="featured_image">
                                                <input id="featured_image" type="file" name="featured_image"
                                                    accept="image/*">
                                                <div>
                                                    <i class="ti ti-cloud-upload" style="font-size: 42px;"></i>
                                                    <h4>{{ admin_t('اسحب الصور هنا أو اضغط للاختيار') }}</h4>
                                                    <p>{{ admin_t('الامتدادات المسموحة: JPG, PNG, WEBP - الحد الأقصى 5MB لكل صورة.') }}
                                                    </p>
                                                </div>
                                            </label>
                                            @error('featured_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="preview-grid" id="featuredPreview"></div>
                                        </div>

                                        <div>
                                            <label class="form-label">{{ admin_t('صور المعرض') }}</label>
                                            <label class="upload-zone" for="gallery_images">
                                                <input id="gallery_images" type="file" name="gallery_images[]"
                                                    accept="image/*" multiple>
                                                <div>
                                                    <i class="ti ti-photos" style="font-size: 42px;"></i>
                                                    <h4>{{ admin_t('اسحب الصور هنا أو اضغط للاختيار') }}</h4>
                                                    <p>{{ admin_t('الامتدادات المسموحة: JPG, PNG, WEBP - الحد الأقصى 5MB لكل صورة.') }}
                                                    </p>
                                                </div>
                                            </label>
                                            @error('gallery_images')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            @error('gallery_images.*')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="preview-grid" id="galleryPreview">
                                                <div class="empty-state" id="galleryEmptyState">
                                                    {{ admin_t('لا توجد صور في المعرض حتى الآن.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('admin.packages.partials.common-experience-details')
                            @include('admin.packages.partials.common-media-extra')
                        </div>
                    </div>

                    <div class="wizard-panel" data-step-panel="3">
                        <div class="wizard-panel-header">
                            <div>
                                <h2 class="wizard-panel-title">{{ admin_t('المسار والمدة') }}</h2>
                                <p class="wizard-panel-copy">
                                    {{ admin_t('حدد مدة الرحلة والبرنامج اليومي ومسار الرحلة.') }}</p>
                            </div>
                            <div class="wizard-panel-pill">
                                {{ admin_t('الخطوة :current من :total', ['current' => 3, 'total' => count($steps)]) }}
                            </div>
                        </div>

                        <div class="wizard-grid">
                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-clock-hour-4"></i></div>
                                    <div>
                                        <h3>{{ admin_t('المدة والمعلومات الزمنية') }}</h3>
                                        <p>{{ admin_t('حدد شكل المدة وطريقة عرضها داخل الموقع.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="mb-4">
                                        <label class="form-label">{{ admin_t('نوع المدة') }}</label>
                                        <div class="choice-row">
                                            <label class="choice-pill">
                                                <input type="radio" name="duration_type" value="days"
                                                    {{ $durationType === 'days' ? 'checked' : '' }}>
                                                <span>{{ admin_t('أيام / ليالي') }}</span>
                                            </label>
                                            <label class="choice-pill">
                                                <input type="radio" name="duration_type" value="hours"
                                                    {{ $durationType === 'hours' ? 'checked' : '' }}>
                                                <span>{{ admin_t('ساعات') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="fields-grid">
                                        <div id="daysFieldWrapper">
                                            <label class="form-label"
                                                for="duration_days">{{ admin_t('عدد الأيام') }}</label>
                                            <input id="duration_days" type="number" name="duration_days"
                                                class="form-control" value="{{ old('duration_days') }}">
                                        </div>

                                        <div id="nightsFieldWrapper">
                                            <label class="form-label"
                                                for="duration_nights">{{ admin_t('عدد الليالي') }}</label>
                                            <input id="duration_nights" type="number" name="duration_nights"
                                                class="form-control" value="{{ old('duration_nights') }}">
                                        </div>

                                        <div id="hoursFieldWrapper">
                                            <label class="form-label"
                                                for="duration_hours">{{ admin_t('عدد الساعات') }}</label>
                                            <input id="duration_hours" type="number" name="duration_hours"
                                                class="form-control" value="{{ old('duration_hours') }}">
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="duration_text">{{ admin_t('نص المدة المعروض') }}</label>
                                            <input id="duration_text" type="text" name="duration_text"
                                                class="form-control" value="{{ old('duration_text') }}"
                                                placeholder="{{ admin_t('مثال: 5 أيام / 4 ليالٍ') }}">
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="schedule_text">{{ admin_t('الجدول') }}</label>
                                            <input id="schedule_text" type="text" name="schedule_text"
                                                class="form-control" value="{{ old('schedule_text') }}"
                                                placeholder="{{ admin_t('مثال: يوميًا / كل سبت / حسب الطلب') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-map-route"></i></div>
                                    <div>
                                        <h3>{{ admin_t('المسار والتنقل') }}</h3>
                                        <p>{{ admin_t('أضف نقاط البداية والوصول ومسار الرحلة بشكل واضح.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="fields-grid">
                                        <div>
                                            <label class="form-label" for="route_text">{{ admin_t('المسار') }}</label>
                                            <input id="route_text" type="text" name="route_text" class="form-control"
                                                value="{{ old('route_text') }}">
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="pickup_location">{{ admin_t('مكان الاستلام') }}</label>
                                            <input id="pickup_location" type="text" name="pickup_location"
                                                class="form-control" value="{{ old('pickup_location') }}">
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="dropoff_location">{{ admin_t('مكان الانتهاء') }}</label>
                                            <input id="dropoff_location" type="text" name="dropoff_location"
                                                class="form-control" value="{{ old('dropoff_location') }}">
                                        </div>

                                        <div class="field-span-2">
                                            <label class="form-label"
                                                for="destinations_text">{{ admin_t('الوجهات') }}</label>
                                            <input id="destinations_text" type="text" name="destinations_text"
                                                class="form-control" value="{{ old('destinations_text') }}"
                                                placeholder="{{ admin_t('افصل بين الوجهات بفاصلة') }}">
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="location_summary">{{ admin_t('ملخص الموقع') }}</label>
                                            <input id="location_summary" type="text" name="location_summary"
                                                class="form-control" value="{{ old('location_summary') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-calendar-event"></i></div>
                                    <div>
                                        <h3>{{ admin_t('البرنامج اليومي') }}</h3>
                                        <p>{{ admin_t('قسّم الرحلة إلى أيام أو محطات مع تفاصيل الوجبات والنشاطات.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="dynamic-section-shell is-ltr" dir="ltr" lang="en">
                                        <div class="dynamic-section-head">
                                            <span class="dynamic-section-icon"><i class="ti ti-calendar-event"></i></span>
                                            <div>
                                                <h4 id="itinerarySectionTitle">Daily Itinerary</h4>
                                                <p id="itinerarySectionCopy">Split the trip into days with meal and activity details.</p>
                                            </div>
                                        </div>

                                        <div id="itinerary-wrapper" class="itinerary-list">
                                            @forelse ($itinerary as $i => $day)
                                                <div class="repeat-box itinerary-item itinerary-item-card">
                                                    <div class="itinerary-item-grid">
                                                        <div class="dynamic-order-column">
                                                            <span class="item-order-badge">
                                                                <small class="item-order-label">{{ $durationType === 'hours' ? 'Step' : 'Day' }}</small>
                                                                <span class="item-order-number">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                                            </span>
                                                        </div>

                                                        <div class="field-block itinerary-date-field">
                                                            <label class="field-block-label" data-itinerary-duration-label>Date / Day label</label>
                                                            <div class="field-shell">
                                                                <span class="field-shell-icon"><i class="ti ti-clock"></i></span>
                                                                <input type="text" data-itinerary-duration-input
                                                                    placeholder="Optional date or day label" name="itinerary[{{ $i }}][duration]"
                                                                    value="{{ $day['duration'] ?? '' }}">
                                                            </div>
                                                            <input type="hidden" name="itinerary[{{ $i }}][day_number]"
                                                                value="{{ $day['day_number'] ?? $i + 1 }}">
                                                        </div>

                                                        <div class="field-block itinerary-hour-fields" data-itinerary-hour-fields>
                                                            <label class="field-block-label">Activity time</label>
                                                            <div class="fields-grid fields-grid-2">
                                                                <div class="field-shell">
                                                                    <span class="field-shell-icon"><i class="ti ti-clock-play"></i></span>
                                                                    <input type="time" name="itinerary[{{ $i }}][start_time]" value="{{ $day['start_time'] ?? '' }}" aria-label="Start time">
                                                                </div>
                                                                <div class="field-shell">
                                                                    <span class="field-shell-icon"><i class="ti ti-clock-stop"></i></span>
                                                                    <input type="time" name="itinerary[{{ $i }}][end_time]" value="{{ $day['end_time'] ?? '' }}" aria-label="End time">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="field-block itinerary-place-field">
                                                            <label class="field-block-label">Place / Stop</label>
                                                            <div class="field-shell">
                                                                <span class="field-shell-icon"><i class="ti ti-map-pin"></i></span>
                                                                <input type="text" name="itinerary[{{ $i }}][title]"
                                                                    value="{{ $day['title'] ?? '' }}"
                                                                    placeholder="Enter place or stop">
                                                            </div>
                                                        </div>

                                                        <div class="field-block itinerary-meals-field" data-itinerary-day-fields>
                                                            <label class="field-block-label">Meals Included</label>
                                                            @php
                                                                $mealsList = [];
                                                                if (!empty($day['meals']) && is_array($day['meals'])) {
                                                                    $mealsList = $day['meals'];
                                                                } else {
                                                                    if (!empty($day['meals_breakfast'])) $mealsList[] = 'breakfast';
                                                                    if (!empty($day['meals_lunch'])) $mealsList[] = 'lunch';
                                                                    if (!empty($day['meals_dinner'])) $mealsList[] = 'dinner';
                                                                }
                                                            @endphp
                                                            <div class="meal-options-pills">
                                                                <label class="meal-pill-checkbox">
                                                                    <input type="checkbox" name="itinerary[{{ $i }}][meals][]" value="breakfast"
                                                                        {{ in_array('breakfast', $mealsList) ? 'checked' : '' }} class="js-meal-checkbox">
                                                                    <span class="meal-pill"><i class="ti ti-coffee"></i> Breakfast</span>
                                                                </label>
                                                                <label class="meal-pill-checkbox">
                                                                    <input type="checkbox" name="itinerary[{{ $i }}][meals][]" value="lunch"
                                                                        {{ in_array('lunch', $mealsList) ? 'checked' : '' }} class="js-meal-checkbox">
                                                                    <span class="meal-pill"><i class="ti ti-soup"></i> Lunch</span>
                                                                </label>
                                                                <label class="meal-pill-checkbox">
                                                                    <input type="checkbox" name="itinerary[{{ $i }}][meals][]" value="dinner"
                                                                        {{ in_array('dinner', $mealsList) ? 'checked' : '' }} class="js-meal-checkbox">
                                                                    <span class="meal-pill"><i class="ti ti-glass-full"></i> Dinner</span>
                                                                </label>
                                                            </div>
                                                            <input type="hidden" name="itinerary[{{ $i }}][meals_breakfast]"
                                                                value="{{ in_array('breakfast', $mealsList) ? '1' : '0' }}" class="js-meal-hidden-breakfast">
                                                            <input type="hidden" name="itinerary[{{ $i }}][meals_lunch]"
                                                                value="{{ in_array('lunch', $mealsList) ? '1' : '0' }}" class="js-meal-hidden-lunch">
                                                            <input type="hidden" name="itinerary[{{ $i }}][meals_dinner]"
                                                                value="{{ in_array('dinner', $mealsList) ? '1' : '0' }}" class="js-meal-hidden-dinner">
                                                        </div>

                                                        <div class="field-block itinerary-activities-field">
                                                            <label class="field-block-label">Activities / Day description</label>
                                                            <div class="field-shell field-shell-textarea">
                                                                <span class="field-shell-icon"><i class="ti ti-route"></i></span>
                                                                <textarea name="itinerary[{{ $i }}][description]" rows="4"
                                                                    placeholder="Enter activities">{{ $day['description'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>

                                                        <div class="field-block itinerary-tour-package-fields" data-itinerary-tour-package-fields>
                                                            <label class="field-block-label">Tour Package day details</label>
                                                            <div class="fields-grid fields-grid-2">
                                                                <div>
                                                                    <label class="field-block-label">Overnight location</label>
                                                                    <input class="form-control" type="text" name="itinerary[{{ $i }}][overnight_location]" value="{{ $day['overnight_location'] ?? '' }}" placeholder="e.g. Luxor">
                                                                </div>
                                                                <div>
                                                                    <label class="field-block-label">Accommodation</label>
                                                                    <input class="form-control" type="text" name="itinerary[{{ $i }}][accommodation]" value="{{ $day['accommodation'] ?? '' }}" placeholder="e.g. 5-star hotel in Luxor">
                                                                </div>
                                                            </div>
                                                            <div class="mt-2">
                                                                <label class="field-block-label">Transport notes</label>
                                                                <textarea class="form-control" name="itinerary[{{ $i }}][transport_notes]" rows="2" placeholder="Transfers, domestic flights, train, private vehicle...">{{ $day['transport_notes'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>

                                                        <div class="field-block itinerary-tour-package-advanced" data-itinerary-tour-package-advanced>
                                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                                <label class="field-block-label mb-0">Advanced day activities</label>
                                                                <button type="button" class="btn btn-sm btn-outline-primary js-add-tour-package-activity" data-itinerary-index="{{ $i }}">+ Add Activity</button>
                                                            </div>
                                                            <div class="tour-package-activities-list" data-tour-package-activities-list>
                                                                @foreach ((array) ($day['activities'] ?? []) as $activityIndex => $activity)
                                                                    <div class="repeat-box tour-package-activity-row" data-tour-package-activity-row>
                                                                        <div class="row g-2">
                                                                            <div class="col-md-2"><input class="form-control" type="time" name="itinerary[{{ $i }}][activities][{{ $activityIndex }}][time]" value="{{ $activity['time'] ?? '' }}" aria-label="Activity time"></div>
                                                                            <div class="col-md-3"><input class="form-control" type="text" name="itinerary[{{ $i }}][activities][{{ $activityIndex }}][title]" value="{{ $activity['title'] ?? '' }}" placeholder="Activity title"></div>
                                                                            <div class="col-md-3"><input class="form-control" type="text" name="itinerary[{{ $i }}][activities][{{ $activityIndex }}][location]" value="{{ $activity['location'] ?? '' }}" placeholder="Location"></div>
                                                                            <div class="col-md-2"><input class="form-control" type="text" name="itinerary[{{ $i }}][activities][{{ $activityIndex }}][duration]" value="{{ $activity['duration'] ?? '' }}" placeholder="Duration"></div>
                                                                            <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 js-remove-tour-package-activity">Remove</button></div>
                                                                            <div class="col-12"><textarea class="form-control" rows="2" name="itinerary[{{ $i }}][activities][{{ $activityIndex }}][description]" placeholder="Activity details">{{ $activity['description'] ?? '' }}</textarea></div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <button type="button"
                                                            class="btn js-remove hover-delete-btn icon-remove-btn dynamic-remove-control"
                                                            aria-label="{{ admin_t('حذف') }}">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="empty-state empty-dynamic-state" id="itineraryEmptyState">
                                                    <strong>🗓️</strong>
                                                    <span>No itinerary added yet.</span>
                                                </div>
                                            @endforelse
                                        </div>

                                        <button type="button" class="btn dynamic-add-btn" id="addItineraryBtn">
                                            <span class="btn-icon-text">
                                                <i class="ti ti-plus"></i>
                                                <span id="addItineraryText">Add New Day</span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="nile-cruise-advanced-host">
                                @include('admin.packages.partials.nile-cruise-extended-fields')
                            </div>
                        </div>
                    </div>

                    <div class="wizard-panel" data-step-panel="4">
                        <div class="wizard-panel-header">
                            <div>
                                <h2 class="wizard-panel-title">{{ admin_t('Pricing & Policies') }}</h2>
                                <p class="wizard-panel-copy">
                                    {{ admin_t('Set package pricing, included items, and booking policies.') }}</p>
                            </div>
                            <div class="wizard-panel-pill">
                                {{ admin_t('Step :current of :total', ['current' => 4, 'total' => count($steps)]) }}
                            </div>
                        </div>

                        <div class="wizard-grid">
                            @include('admin.packages.partials.common-operations')

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-cash"></i></div>
                                    <div>
                                        <h3>{{ admin_t('Pricing & Packages') }}</h3>
                                        <p>{{ admin_t('Add flexible pricing based on group size, counts, or room types.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    @include('admin.packages.partials.pricing-day-tour')
                                    @include('admin.packages.partials.pricing-travel-package')
                                    @include('admin.packages.partials.pricing-nile-cruise')

                                    <div class="fields-grid">
                                        <div>
                                            <label class="form-label" for="adult_price">{{ admin_t('Adult Price (Default)') }}</label>
                                            <input id="adult_price" type="number" step="0.01" min="0"
                                                name="adult_price" class="form-control"
                                                value="{{ old('adult_price', '') }}">
                                            @error('adult_price')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label" for="child_price">{{ admin_t('Child Price') }}</label>
                                            <input id="child_price" type="number" step="0.01" min="0"
                                                name="child_price" class="form-control"
                                                value="{{ old('child_price', '') }}">
                                            @error('child_price')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label" for="infant_price">{{ admin_t('Infant Price') }}</label>
                                            <input id="infant_price" type="number" step="0.01" min="0"
                                                name="infant_price" class="form-control"
                                                value="{{ old('infant_price', '') }}">
                                            @error('infant_price')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="compare_price">{{ admin_t('Compare Price') }}</label>
                                            <input id="compare_price" type="number" step="0.01" name="compare_price"
                                                class="form-control" value="{{ old('compare_price') }}">
                                        </div>
                                    </div>

                                    <div class="repeat-box mt-3">
                                        <div class="repeat-box-title">
                                            <strong>{{ __('trips.age_policy') }}</strong>
                                        </div>
                                        <div class="fields-grid">
                                            <div>
                                                <label class="form-label" for="adult_min_age">{{ admin_t('Adult Min Age') }}</label>
                                                <input id="adult_min_age" type="number" min="0" name="adult_min_age"
                                                    class="form-control" value="{{ $adultMinAge }}">
                                                @error('adult_min_age')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="form-label" for="child_min_age">{{ admin_t('Child Min Age') }}</label>
                                                <input id="child_min_age" type="number" min="0" name="child_min_age"
                                                    class="form-control" value="{{ $childMinAge }}">
                                                @error('child_min_age')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="form-label" for="child_max_age">{{ admin_t('Child Max Age') }}</label>
                                                <input id="child_max_age" type="number" min="0" name="child_max_age"
                                                    class="form-control" value="{{ $childMaxAge }}">
                                                @error('child_max_age')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="form-label" for="infant_min_age">{{ admin_t('Infant Min Age') }}</label>
                                                <input id="infant_min_age" type="number" min="0" name="infant_min_age"
                                                    class="form-control" value="{{ $infantMinAge }}">
                                                @error('infant_min_age')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="form-label" for="infant_max_age">{{ admin_t('Infant Max Age') }}</label>
                                                <input id="infant_max_age" type="number" min="0" name="infant_max_age"
                                                    class="form-control" value="{{ $infantMaxAge }}">
                                                @error('infant_max_age')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div id="prices-wrapper" class="stack-list mt-3">
                                        @forelse ($prices as $i => $price)
                                            <div class="repeat-box editor-card price-item">
                                                <div class="editor-card-head">
                                                    <div class="editor-card-title editor-inline-input">
                                                        <span class="editor-card-badge">
                                                            <i class="ti ti-cash-banknote"></i>
                                                        </span>
                                                        <input type="text" name="prices[{{ $i }}][label]"
                                                            class="form-control" value="{{ $price['label'] ?? '' }}"
                                                            placeholder="{{ admin_t('Price or package title') }}">
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-outline-danger js-remove icon-remove-btn"
                                                        aria-label="{{ admin_t('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="editor-card-body">
                                                    <div class="fields-grid">
                                                        <div>
                                                            <label class="form-label">{{ admin_t('Season') }}</label>
                                                            <input type="text" name="prices[{{ $i }}][season_name]"
                                                                class="form-control"
                                                                value="{{ $price['season_name'] ?? '' }}"
                                                                placeholder="{{ admin_t('e.g. Summer Season') }}">
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Amount') }}</label>
                                                            <input type="number" step="0.01"
                                                                name="prices[{{ $i }}][amount]"
                                                                class="form-control" value="{{ $price['amount'] ?? '' }}"
                                                                placeholder="{{ admin_t('Amount') }}">
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Currency') }}</label>
                                                            <select name="prices[{{ $i }}][currency_id]"
                                                                class="form-select">
                                                                <option value="">{{ admin_t('Currency') }}</option>
                                                                @foreach ($currencies ?? collect() as $currency)
                                                                    <option value="{{ $currency->id }}"
                                                                        {{ ($price['currency_id'] ?? old('currency_id')) == $currency->id ? 'selected' : '' }}>
                                                                        {{ $currency->code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="fields-grid">
                                                        <div>
                                                            <label class="form-label">{{ admin_t('Price Type') }}</label>
                                                            <select name="prices[{{ $i }}][price_type]"
                                                                class="form-select">
                                                                <option value="from"
                                                                    {{ ($price['price_type'] ?? '') === 'from' ? 'selected' : '' }}>
                                                                    {{ admin_t('Starts From') }}</option>
                                                                <option value="fixed"
                                                                    {{ ($price['price_type'] ?? '') === 'fixed' ? 'selected' : '' }}>
                                                                    {{ admin_t('Fixed') }}</option>
                                                                <option value="seasonal"
                                                                    {{ ($price['price_type'] ?? '') === 'seasonal' ? 'selected' : '' }}>
                                                                    {{ admin_t('Seasonal') }}</option>
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Room Type') }}</label>
                                                            <input type="text"
                                                                name="prices[{{ $i }}][room_type]"
                                                                class="form-control"
                                                                value="{{ $price['room_type'] ?? '' }}"
                                                                placeholder="{{ admin_t('e.g. Double Room') }}">
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Min Pax') }}</label>
                                                            <input type="number" min="1"
                                                                name="prices[{{ $i }}][pax_min]"
                                                                class="form-control"
                                                                value="{{ $price['pax_min'] ?? '' }}"
                                                                placeholder="{{ admin_t('e.g. 1') }}">
                                                            @error("prices.$i.pax_min")
                                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Max Pax') }}</label>
                                                            <input type="number" min="1"
                                                                name="prices[{{ $i }}][pax_max]"
                                                                class="form-control"
                                                                value="{{ $price['pax_max'] ?? '' }}"
                                                                placeholder="{{ admin_t('e.g. 4') }}">
                                                            @error("prices.$i.pax_max")
                                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Valid From') }}</label>
                                                            <input type="date"
                                                                name="prices[{{ $i }}][valid_from]"
                                                                class="form-control"
                                                                value="{{ $price['valid_from'] ?? '' }}">
                                                        </div>
                                                    </div>

                                                    <div class="fields-grid two-up">
                                                        <div>
                                                            <label class="form-label">{{ admin_t('Valid To') }}</label>
                                                            <input type="date"
                                                                name="prices[{{ $i }}][valid_to]"
                                                                class="form-control"
                                                                value="{{ $price['valid_to'] ?? '' }}">
                                                        </div>

                                                        <div>
                                                            <label class="form-label">{{ admin_t('Notes') }}</label>
                                                            <textarea name="prices[{{ $i }}][notes]" rows="3" class="form-control"
                                                                placeholder="{{ admin_t('Add notes or clarifications for this price') }}">{{ $price['notes'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="empty-state" id="pricesEmptyState">
                                                {{ admin_t('No prices added yet.') }}</div>
                                        @endforelse
                                    </div>

                                    <button type="button" class="btn btn-wizard-outline mt-2" id="addPriceBtn">
                                        <span class="btn-icon-text">
                                            <i class="ti ti-plus"></i>
                                            {{ admin_t('+ Add Price') }}
                                        </span>
                                    </button>

                                    <div class="mt-4">
                                        <label class="form-label"
                                            for="pricing_information">{{ admin_t('Pricing Notes') }}</label>
                                        <textarea id="pricing_information" name="pricing_information" rows="4" class="form-control">{{ old('pricing_information') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            @php
                                $facilityPresets = \App\Services\NileCruisePackageService::facilityPresets();
                                $oldFacilityTitles = collect(old('facilities', []))
                                    ->pluck('title')
                                    ->filter()
                                    ->map(fn ($title) => trim((string) $title))
                                    ->all();
                                $normalizeFacility = function (string $str): string {
                                    $c = strtolower(trim($str));
                                    $c = str_replace(['conditioning', 'condition'], 'ac', $c);
                                    $c = str_replace(['swimming pool', 'pool'], 'pool', $c);
                                    $c = str_replace(['bathroom', 'bath'], 'bath', $c);
                                    $c = str_replace(['gymnasium', 'gym'], 'gym', $c);
                                    $c = str_replace(['satellite', 'tv'], 'tv', $c);
                                    return preg_replace('/[^a-z0-9]/', '', $c);
                                };
                                $normalizedOld = array_map($normalizeFacility, $oldFacilityTitles);
                            @endphp
                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-layout-grid-add"></i></div>
                                    <div>
                                        <h3>{{ admin_t('Trip & Cruise Facilities') }}</h3>
                                        <p>{{ admin_t('Select the facilities and amenities that appear on the trip or cruise page.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="attractions-picker-grid">
                                        @foreach ($facilityPresets as $facilityIndex => $facilityTitle)
                                            @php
                                                $isChecked = in_array($facilityTitle, $oldFacilityTitles, true)
                                                    || in_array($normalizeFacility($facilityTitle), $normalizedOld, true);
                                            @endphp
                                            <label class="attraction-choice">
                                                <input type="checkbox"
                                                    name="facilities[{{ $facilityIndex }}][title]"
                                                    value="{{ $facilityTitle }}"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                                <input type="hidden" name="facilities[{{ $facilityIndex }}][sort_order]"
                                                    value="{{ $facilityIndex }}">
                                                <span class="attraction-choice-icon"><i class="ti ti-check"></i></span>
                                                <span class="attraction-choice-copy">
                                                    <strong>{{ admin_t($facilityTitle) }}</strong>
                                                    <small>{{ admin_t('Will appear in trip facilities') }}</small>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-map-pin-star"></i></div>
                                    <div>
                                        <h3>Places / Attractions</h3>
                                        <p>Select the places associated with this trip from the existing list.</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="attractions-picker-toolbar d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                        <div class="attractions-search flex-grow-1" style="max-width: 400px;">
                                            <i class="ti ti-search"></i>
                                            <input type="search" class="form-control" id="attractionSearch"
                                                placeholder="Search attractions by name or city..." autocomplete="off">
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="attractions-selected-count" id="attractionsSelectedCount">0 selected</span>
                                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#quickAddAttractionModal">
                                                <i class="ti ti-plus"></i> {{ __('Add New Facility / Place') }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="attractions-picker-grid" id="attractionsPicker">
                                        @forelse ($attractions ?? collect() as $attraction)
                                            @php
                                                $attractionName = adminTrans($attraction->name) ?: 'Attraction #' . $attraction->id;
                                                $cityName = adminTrans($attraction->city?->name) ?: 'No city';
                                                $searchText = mb_strtolower($attractionName . ' ' . $cityName);
                                            @endphp
                                            <label class="attraction-choice" data-attraction-choice
                                                data-attraction-search="{{ $searchText }}">
                                                <input type="checkbox" name="attraction_ids[]"
                                                    value="{{ $attraction->id }}"
                                                    {{ in_array((int) $attraction->id, $selectedAttractionIds, true) ? 'checked' : '' }}>
                                                <span class="attraction-choice-icon"><i class="ti ti-map-pin"></i></span>
                                                <span class="attraction-choice-copy">
                                                    <strong>{{ $attractionName }}</strong>
                                                    <small>{{ $cityName }}</small>
                                                </span>
                                            </label>
                                        @empty
                                            <div class="empty-state field-span-3">
                                                No active attractions are available. Add attractions first, then return to this page.
                                            </div>
                                        @endforelse
                                    </div>

                                    @error('attraction_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('attraction_ids.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-list-check"></i></div>
                                    <div>
                                        <h3>{{ admin_t('المشمول وغير المشمول') }}</h3>
                                        <p>{{ admin_t('قسّم ما يحصل عليه العميل وما لا يشمله السعر.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="split-card">
                                        <div>
                                            <h5 class="mb-3">{{ admin_t('المشمول في الرحلة') }}</h5>
                                            <div id="included-wrapper">
                                                @forelse ($included as $i => $item)
                                                    <div class="repeat-box included-item">
                                                        <div class="d-flex gap-2">
                                                            <input type="text"
                                                                name="included[{{ $i }}][title]"
                                                                class="form-control" value="{{ $item['title'] ?? '' }}">
                                                            <button type="button"
                                                                class="btn btn-outline-danger js-remove">{{ admin_t('حذف') }}</button>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="empty-state" id="includedEmptyState">
                                                        {{ admin_t('لا يوجد عناصر مشمولة حتى الآن.') }}</div>
                                                @endforelse
                                            </div>
                                            <button type="button" class="btn btn-wizard-outline" id="addIncludedBtn">
                                                <span class="btn-icon-text">
                                                    <i class="ti ti-plus"></i>
                                                    {{ admin_t('+ إضافة بند') }}
                                                </span>
                                            </button>
                                        </div>

                                        <div>
                                            <h5 class="mb-3">{{ admin_t('غير المشمول') }}</h5>
                                            <div id="excluded-wrapper">
                                                @forelse ($excluded as $i => $item)
                                                    <div class="repeat-box excluded-item">
                                                        <div class="d-flex gap-2">
                                                            <input type="text"
                                                                name="excluded[{{ $i }}][title]"
                                                                class="form-control" value="{{ $item['title'] ?? '' }}">
                                                            <button type="button"
                                                                class="btn btn-outline-danger js-remove">{{ admin_t('حذف') }}</button>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="empty-state" id="excludedEmptyState">
                                                        {{ admin_t('لا يوجد عناصر غير مشمولة حتى الآن.') }}</div>
                                                @endforelse
                                            </div>
                                            <button type="button" class="btn btn-wizard-outline" id="addExcludedBtn">
                                                <span class="btn-icon-text">
                                                    <i class="ti ti-plus"></i>
                                                    {{ admin_t('+ إضافة بند') }}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-shield-check"></i></div>
                                    <div>
                                        <h3>{{ admin_t('الشروط والسياسات') }}</h3>
                                        <p>{{ admin_t('وضح السياسات المهمة قبل الحجز لتقليل الاستفسارات.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="fields-grid two-up">
                                        <div>
                                            <label class="form-label"
                                                for="children_policy">{{ admin_t('سياسة الأطفال') }}</label>
                                            <textarea id="children_policy" name="children_policy" rows="5" class="form-control">{{ old('children_policy') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="pickup_policy">{{ admin_t('سياسة الاستلام والتوصيل') }}</label>
                                            <textarea id="pickup_policy" name="pickup_policy" rows="5" class="form-control">{{ old('pickup_policy') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="cancellation_policy">{{ admin_t('سياسة الإلغاء') }}</label>
                                            <textarea id="cancellation_policy" name="cancellation_policy" rows="5" class="form-control">{{ old('cancellation_policy') }}</textarea>
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="terms_conditions">{{ admin_t('الشروط والأحكام') }}</label>
                                            <textarea id="terms_conditions" name="terms_conditions" rows="5" class="form-control">{{ old('terms_conditions') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-help-hexagon"></i></div>
                                    <div>
                                        <h3>{{ admin_t('الأسئلة الشائعة') }}</h3>
                                        <p>{{ admin_t('أضف أسئلة وإجابات خاصة بهذه الرحلة لتظهر في الموقع.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="dynamic-section-shell is-ltr" dir="ltr" lang="en">
                                        <div class="dynamic-section-head">
                                            <span class="dynamic-section-icon"><i class="ti ti-help-hexagon"></i></span>
                                            <div>
                                                <h4>FAQs</h4>
                                                <p>Add questions and answers specific to this trip.</p>
                                            </div>
                                        </div>

                                        <div id="faq-wrapper" class="faq-list">
                                            @forelse ($faqItems as $i => $faq)
                                                <div class="repeat-box faq-item faq-item-card">
                                                    <div class="faq-item-grid">
                                                        <div class="dynamic-order-column">
                                                            <span class="item-order-badge">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                                        </div>

                                                        <div class="field-block faq-question-field">
                                                            <label class="field-block-label">Question</label>
                                                            <div class="field-shell">
                                                                <span class="field-shell-icon"><i class="ti ti-help-circle"></i></span>
                                                                <input type="text" name="faq_json[{{ $i }}][question]"
                                                                    value="{{ is_array($faq['question'] ?? null) ? ($faq['question'][app()->getLocale()] ?? $faq['question']['en'] ?? '') : ($faq['question'] ?? '') }}"
                                                                    placeholder="Enter question...">
                                                            </div>
                                                        </div>

                                                        <div class="field-block faq-answer-field">
                                                            <label class="field-block-label">Answer</label>
                                                            <div class="field-shell field-shell-textarea">
                                                                <span class="field-shell-icon"><i class="ti ti-edit"></i></span>
                                                                <textarea name="faq_json[{{ $i }}][answer]" rows="4"
                                                                    placeholder="Enter answer...">{{ is_array($faq['answer'] ?? null) ? ($faq['answer'][app()->getLocale()] ?? $faq['answer']['en'] ?? '') : ($faq['answer'] ?? '') }}</textarea>
                                                            </div>
                                                        </div>

                                                        <button type="button"
                                                            class="btn js-remove hover-delete-btn icon-remove-btn dynamic-remove-control"
                                                            aria-label="{{ admin_t('حذف') }}">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="empty-state empty-dynamic-state" id="faqEmptyState">
                                                    <strong>💬</strong>
                                                    <span>No FAQs added yet.</span>
                                                </div>
                                            @endforelse
                                        </div>

                                        <button type="button" class="btn dynamic-add-btn" id="addFaqBtn">
                                            <span class="btn-icon-text">
                                                <i class="ti ti-plus"></i>
                                                Add New FAQ
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-panel" data-step-panel="5">
                        <div class="wizard-panel-header">
                            <div>
                                <h2 class="wizard-panel-title">{{ admin_t('النشر وSEO') }}</h2>
                                <p class="wizard-panel-copy">
                                    {{ admin_t('راجع بيانات الرحلة وحدد إعدادات النشر ومحركات البحث.') }}</p>
                            </div>
                            <div class="wizard-panel-pill">
                                {{ admin_t('الخطوة :current من :total', ['current' => 5, 'total' => count($steps)]) }}
                            </div>
                        </div>

                        <div class="wizard-grid">
                            <div class="wizard-grid two-columns">
                                <div class="form-section-card">
                                    <div class="section-header">
                                        <div class="section-icon"><i class="ti ti-users"></i></div>
                                        <div>
                                            <h3>{{ admin_t('المشاركون والتقييم') }}</h3>
                                            <p>{{ admin_t('راجع أرقام السعة والتقييم قبل النشر.') }}</p>
                                        </div>
                                    </div>

                                    <div class="section-body">
                                        <div class="fields-grid">
                                            <div>
                                                <label class="form-label"
                                                    for="min_participants">{{ admin_t('الحد الأدنى للمشاركين') }}</label>
                                                <input id="min_participants" type="number" name="min_participants"
                                                    class="form-control" value="{{ old('min_participants') }}">
                                            </div>

                                            <div>
                                                <label class="form-label"
                                                    for="max_participants">{{ admin_t('الحد الأقصى للمشاركين') }}</label>
                                                <input id="max_participants" type="number" name="max_participants"
                                                    class="form-control" value="{{ old('max_participants') }}">
                                            </div>

                                            <div>
                                                <label class="form-label"
                                                    for="booking_lead_days">{{ admin_t('أيام الحجز المسبق') }}</label>
                                                <input id="booking_lead_days" type="number" name="booking_lead_days"
                                                    class="form-control" value="{{ old('booking_lead_days') }}">
                                            </div>

                                            <div>
                                                <label class="form-label"
                                                    for="rating_avg">{{ admin_t('التقييم') }}</label>
                                                <input id="rating_avg" type="number" step="0.01" name="rating_avg"
                                                    class="form-control" value="{{ old('rating_avg') }}">
                                            </div>

                                            <div>
                                                <label class="form-label"
                                                    for="reviews_count">{{ admin_t('عدد المراجعات') }}</label>
                                                <input id="reviews_count" type="number" name="reviews_count"
                                                    class="form-control" value="{{ old('reviews_count') }}">
                                            </div>

                                            <div class="field-span-2">
                                                <label class="form-label"
                                                    for="video_url">{{ admin_t('رابط الفيديو') }}</label>
                                                <input id="video_url" type="text" name="video_url"
                                                    class="form-control" value="{{ old('video_url') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section-card">
                                    <div class="section-header">
                                        <div class="section-icon"><i class="ti ti-settings"></i></div>
                                        <div>
                                            <h3>{{ admin_t('إعدادات النشر') }}</h3>
                                            <p>{{ admin_t('تحكم في حالة الظهور والتمييز وتاريخ النشر.') }}</p>
                                        </div>
                                    </div>

                                    <div class="section-body">
                                        <div class="fields-grid two-up">
                                            <div>
                                                <label class="form-label"
                                                    for="published_at">{{ admin_t('تاريخ النشر') }}</label>
                                                <input id="published_at" type="date" name="published_at"
                                                    class="form-control" value="{{ old('published_at') }}">
                                            </div>

                                            <div>
                                                <label class="form-label"
                                                    for="sort_order">{{ admin_t('الترتيب') }}</label>
                                                <input id="sort_order" type="number" name="sort_order"
                                                    class="form-control" value="{{ old('sort_order') }}">
                                            </div>
                                        </div>

                                        <div class="choice-row mt-3">
                                            <label class="choice-pill">
                                                <input type="checkbox" name="is_active" value="1"
                                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                                <span>{{ admin_t('مفعلة') }}</span>
                                            </label>
                                            <label class="choice-pill">
                                                <input type="checkbox" name="is_featured" value="1"
                                                    {{ old('is_featured') ? 'checked' : '' }}>
                                                <span>{{ admin_t('مميزة') }}</span>
                                            </label>
                                            <label class="choice-pill">
                                                <input type="checkbox" name="is_best_seller" value="1"
                                                    {{ old('is_best_seller') ? 'checked' : '' }}>
                                                <span>{{ admin_t('الأكثر مبيعًا') }}</span>
                                            </label>
                                            <label class="choice-pill">
                                                <input type="checkbox" name="is_ultra_luxury" value="1"
                                                    {{ old('is_ultra_luxury') ? 'checked' : '' }}>
                                                <span>{{ admin_t('فاخرة جدًا') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-world-search"></i></div>
                                    <div>
                                        <h3>{{ admin_t('إعدادات SEO') }}</h3>
                                        <p>{{ admin_t('حسّن ظهور الرحلة في محركات البحث ومنصات المشاركة.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="fields-grid">
                                        <div class="field-span-2">
                                            <label class="form-label" for="seo_title">{{ admin_t('عنوان SEO') }}</label>
                                            <input id="seo_title" type="text" name="seo_title" class="form-control"
                                                value="{{ old('seo_title') }}" data-counter-max="60">
                                            <div class="counter-line"><span data-counter-for="seo_title">0 / 60</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="breadcrumb_title">{{ admin_t('عنوان مسار التنقل') }}</label>
                                            <input id="breadcrumb_title" type="text" name="breadcrumb_title"
                                                class="form-control" value="{{ old('breadcrumb_title') }}">
                                        </div>

                                        <div class="field-span-2">
                                            <label class="form-label"
                                                for="seo_description">{{ admin_t('وصف SEO') }}</label>
                                            <textarea id="seo_description" name="seo_description" rows="4" class="form-control" data-counter-max="160">{{ old('seo_description') }}</textarea>
                                            <div class="counter-line"><span data-counter-for="seo_description">0 /
                                                    160</span></div>
                                        </div>

                                        <div>
                                            <label class="form-label"
                                                for="canonical_url">{{ admin_t('Canonical URL') }}</label>
                                            <input id="canonical_url" type="text" name="canonical_url"
                                                class="form-control" value="{{ old('canonical_url') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('admin.packages.partials.common-seo-extra')

                            <div class="form-section-card">
                                <div class="section-header">
                                    <div class="section-icon"><i class="ti ti-checklist"></i></div>
                                    <div>
                                        <h3>{{ admin_t('مراجعة سريعة') }}</h3>
                                        <p>{{ admin_t('ملخص نهائي قبل حفظ الرحلة ونشرها.') }}</p>
                                    </div>
                                </div>

                                <div class="section-body">
                                    <div class="summary-grid mb-4">
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('العنوان') }}</span>
                                            <span class="summary-value" data-summary="title">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('المدينة') }}</span>
                                            <span class="summary-value" data-summary="destination">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('المدة') }}</span>
                                            <span class="summary-value" data-summary="duration">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('السعر') }}</span>
                                            <span class="summary-value" data-summary="price">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('التصنيف') }}</span>
                                            <span class="summary-value" data-summary="category">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('الحالة') }}</span>
                                            <span class="summary-value" data-summary="status">-</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('عدد الصور') }}</span>
                                            <span class="summary-value" data-summary="images">0</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">{{ admin_t('عدد الأيام') }}</span>
                                            <span class="summary-value" data-summary="daysCount">0</span>
                                        </div>
                                    </div>

                                    <div class="summary-grid mb-4" data-day-trip-review-summary style="display:none;">
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Tour Type') }}</span><span class="summary-value">🗺️ Day Trip</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Timeline Stops') }}</span><span class="summary-value" data-summary="dayTripStops">0</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Departure Times') }}</span><span class="summary-value" data-summary="dayTripDepartureTimes">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Operating Days') }}</span><span class="summary-value" data-summary="operatingDays">-</span></div>
                                    </div>

                                    <div class="summary-grid mb-4" data-tour-package-review-summary style="display:none;">
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Tour Type') }}</span><span class="summary-value">📦 Tour Package</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Cities') }}</span><span class="summary-value" data-summary="tourPackageCities">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Accommodation') }}</span><span class="summary-value" data-summary="tourPackageAccommodation">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Meals') }}</span><span class="summary-value" data-summary="tourPackageMeals">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Flexible Itinerary') }}</span><span class="summary-value" data-summary="tourPackageFlexible">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Itinerary Mode') }}</span><span class="summary-value" data-summary="tourPackageItineraryMode">-</span></div>
                                    </div>

                                    <div class="summary-grid mb-4" data-nile-review-summary style="display:none;">
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Nile Cruise Type') }}</span><span class="summary-value" data-summary="nileType">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Nile Cruise Category') }}</span><span class="summary-value" data-summary="nileCategory">-</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Cabins / Suites') }}</span><span class="summary-value" data-summary="nileCabins">0</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Duration Variants') }}</span><span class="summary-value" data-summary="nileDurations">0</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Sailing Schedules') }}</span><span class="summary-value" data-summary="nileSchedules">0</span></div>
                                        <div class="summary-item"><span class="summary-label">{{ admin_t('Route Stops') }}</span><span class="summary-value" data-summary="nileRouteStops">0</span></div>
                                    </div>

                                    <div class="review-list">
                                        <div class="review-row">
                                            <div class="review-meta">
                                                <strong>{{ admin_t('البيانات الأساسية') }}</strong>
                                                <small>{{ admin_t('تأكد من العنوان والمدينة ونوع الرحلة قبل النشر.') }}</small>
                                            </div>
                                            <button type="button" class="btn btn-wizard-outline"
                                                data-jump-step="1">{{ admin_t('تعديل') }}</button>
                                        </div>
                                        <div class="review-row">
                                            <div class="review-meta">
                                                <strong>{{ admin_t('الوصف والصور') }}</strong>
                                                <small>{{ admin_t('تحقق من الوصف المختصر والصورة الرئيسية.') }}</small>
                                            </div>
                                            <button type="button" class="btn btn-wizard-outline"
                                                data-jump-step="2">{{ admin_t('تعديل') }}</button>
                                        </div>
                                        <div class="review-row">
                                            <div class="review-meta">
                                                <strong>{{ admin_t('المسار والمدة') }}</strong>
                                                <small>{{ admin_t('راجع مدة الرحلة وبرنامجها اليومي.') }}</small>
                                            </div>
                                            <button type="button" class="btn btn-wizard-outline"
                                                data-jump-step="3">{{ admin_t('تعديل') }}</button>
                                        </div>
                                        <div class="review-row">
                                            <div class="review-meta">
                                                <strong>{{ admin_t('الأسعار والسياسات') }}</strong>
                                                <small>{{ admin_t('تأكد من الأسعار والعناصر المشمولة والسياسات.') }}</small>
                                            </div>
                                            <button type="button" class="btn btn-wizard-outline"
                                                data-jump-step="4">{{ admin_t('تعديل') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wizard-actions">
                        <div>
                            <div class="wizard-actions-meta" id="wizardStepLabel"></div>
                            <div class="wizard-actions-meta mt-1">
                                {{ admin_t('استخدم هذا الزر لحفظ نسخة محلية مؤقتة داخل المتصفح.') }}</div>
                        </div>

                        <div class="wizard-actions-group">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-wizard-outline"
                                id="cancelActionBtn">{{ admin_t('إلغاء') }}</a>
                            <button type="button" class="btn btn-wizard-outline"
                                id="saveDraftBtn">{{ admin_t('حفظ كمسودة') }}</button>
                            <button type="button" class="btn btn-wizard-outline"
                                id="prevStepBtn">{{ admin_t('السابق') }}</button>
                            <button type="button" class="btn btn-wizard-primary" id="nextStepBtn">
                                <span class="btn-icon-text">
                                    <span>{{ admin_t('التالي') }}</span>
                                    <i class="ti ti-arrow-{{ $isRtl ? 'left' : 'right' }}"></i>
                                </span>
                            </button>
                            <button type="submit" class="btn btn-wizard-primary d-none-force" id="submitWizardBtn">
                                <span class="btn-icon-text">
                                    <i class="ti ti-device-floppy"></i>
                                    <span>{{ admin_t('حفظ ونشر الرحلة') }}</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('packageWizardForm');
            const stepButtons = Array.from(document.querySelectorAll('[data-step-trigger]'));
            const stepPanels = Array.from(document.querySelectorAll('[data-step-panel]'));
            const prevBtn = document.getElementById('prevStepBtn');
            const nextBtn = document.getElementById('nextStepBtn');
            const submitBtn = document.getElementById('submitWizardBtn');
            const saveDraftBtn = document.getElementById('saveDraftBtn');
            const stepLabel = document.getElementById('wizardStepLabel');
            const mobileStepTitle = document.getElementById('mobileStepTitle');
            const mobileStepCounter = document.getElementById('mobileStepCounter');
            const mobileStepBar = document.getElementById('mobileStepBar');
            const packageTypeSelect = document.getElementById('package_type');
            const nileTypeWrapper = document.getElementById('nile_cruise_type_wrapper');
            const nileTypeSelect = document.getElementById('nile_cruise_type_id');
            const nileCatWrapper = document.getElementById('nile_cruise_category_wrapper');
            const nileCatSelect = document.getElementById('nile_cruise_category_id');

            function syncNileCruiseEditorMode() {
                const isNile = packageTypeSelect?.value === 'nile_cruise';
                const genericItineraryWrapper = document.getElementById('itinerary-wrapper');
                const genericItineraryCard = genericItineraryWrapper?.closest('.form-section-card');
                const itineraryTitle = document.getElementById('itinerary-section-title');
                const itineraryCopy = document.getElementById('itinerary-section-copy');
                const addGenericItinerary = document.getElementById('addItineraryBtn') || document.getElementById('add-itinerary-btn');
                const pricesWrapper = document.getElementById('prices-wrapper');
                const addGenericPrice = document.getElementById('addPriceBtn') || document.getElementById('add-price-btn');
                const groupPricingCard = document.querySelector('[name="price_1_person"]')?.closest('.card');

                if (genericItineraryCard) {
                    genericItineraryCard.style.display = isNile ? 'none' : '';
                    genericItineraryCard.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = isNile);
                } else {
                    [genericItineraryWrapper, itineraryTitle, itineraryCopy, addGenericItinerary].forEach(el => {
                        if (el) el.style.display = isNile ? 'none' : '';
                    });
                    genericItineraryWrapper?.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = isNile);
                    if (addGenericItinerary) addGenericItinerary.disabled = isNile;
                }

                if (pricesWrapper) {
                    pricesWrapper.style.display = isNile ? 'none' : '';
                    pricesWrapper.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = isNile);
                }
                if (addGenericPrice) {
                    addGenericPrice.style.display = isNile ? 'none' : '';
                    addGenericPrice.disabled = isNile;
                }
                if (groupPricingCard) {
                    groupPricingCard.style.display = isNile ? 'none' : '';
                    groupPricingCard.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = isNile);
                }
            }

            function updateNileCruiseFields() {
                if (!packageTypeSelect || !nileTypeWrapper || !nileCatWrapper) return;

                if (packageTypeSelect.value === 'nile_cruise') {
                    nileTypeWrapper.style.display = 'block';

                    const selectedTypeOpt = nileTypeSelect && nileTypeSelect.selectedIndex >= 0 ? nileTypeSelect.options[nileTypeSelect.selectedIndex] : null;
                    const hasCategories = selectedTypeOpt && selectedTypeOpt.getAttribute('data-has-categories') === 'true';
                    const typeId = selectedTypeOpt ? selectedTypeOpt.value : null;

                    if (hasCategories && typeId) {
                        nileCatWrapper.style.display = 'block';
                        if (nileCatSelect) {
                            Array.from(nileCatSelect.options).forEach(opt => {
                                if (!opt.value) return;
                                const optTypeId = opt.getAttribute('data-type-id');
                                if (optTypeId === typeId) {
                                    opt.style.display = 'block';
                                    opt.disabled = false;
                                } else {
                                    opt.style.display = 'none';
                                    opt.disabled = true;
                                }
                            });
                            const currentCatOpt = nileCatSelect.selectedIndex >= 0 ? nileCatSelect.options[nileCatSelect.selectedIndex] : null;
                            if (currentCatOpt && currentCatOpt.disabled) {
                                nileCatSelect.value = '';
                            }
                        }
                    } else {
                        nileCatWrapper.style.display = 'none';
                        if (nileCatSelect) nileCatSelect.value = '';
                    }
                } else {
                    nileTypeWrapper.style.display = 'none';
                    nileCatWrapper.style.display = 'none';
                    if (nileTypeSelect) nileTypeSelect.value = '';
                    if (nileCatSelect) nileCatSelect.value = '';
                }
                syncNileCruiseEditorMode();
            }

            function syncNileChoiceCards() {
                document.querySelectorAll('[data-nile-type-card]').forEach(card => {
                    card.classList.toggle('is-selected', String(card.dataset.nileTypeCard) === String(nileTypeSelect?.value || ''));
                });
                document.querySelectorAll('[data-nile-category-card]').forEach(card => {
                    const sameType = String(card.dataset.typeId) === String(nileTypeSelect?.value || '');
                    card.style.display = sameType ? 'block' : 'none';
                    card.classList.toggle('is-selected', String(card.dataset.nileCategoryCard) === String(nileCatSelect?.value || ''));
                });
            }
            document.querySelectorAll('[data-nile-type-card]').forEach(card => card.addEventListener('click', () => {
                if (!nileTypeSelect) return; nileTypeSelect.value = card.dataset.nileTypeCard; nileTypeSelect.dispatchEvent(new Event('change', {bubbles:true})); syncNileChoiceCards();
            }));
            document.querySelectorAll('[data-nile-category-card]').forEach(card => card.addEventListener('click', () => {
                if (!nileCatSelect) return; nileCatSelect.value = card.dataset.nileCategoryCard; nileCatSelect.dispatchEvent(new Event('change', {bubbles:true})); syncNileChoiceCards();
            }));
            if (packageTypeSelect) packageTypeSelect.addEventListener('change', () => { updateNileCruiseFields(); syncNileChoiceCards(); });
            if (nileTypeSelect) nileTypeSelect.addEventListener('change', () => { updateNileCruiseFields(); syncNileChoiceCards(); });
            if (nileCatSelect) nileCatSelect.addEventListener('change', syncNileChoiceCards);
            updateNileCruiseFields();
            syncNileChoiceCards();

            const featuredInput = document.getElementById('featured_image');
            const galleryInput = document.getElementById('gallery_images');
            const galleryPreview = document.getElementById('galleryPreview');
            const featuredPreview = document.getElementById('featuredPreview');
            const destinationSelector = document.getElementById('destination_selector');
            const primaryCountryInput = document.getElementById('primary_country_id');
            const attractionSearch = document.getElementById('attractionSearch');
            const attractionsPicker = document.getElementById('attractionsPicker');
            const attractionsSelectedCount = document.getElementById('attractionsSelectedCount');
            const addItineraryBtn = document.getElementById('addItineraryBtn');
            const addIncludedBtn = document.getElementById('addIncludedBtn');
            const addExcludedBtn = document.getElementById('addExcludedBtn');
            const addPriceBtn = document.getElementById('addPriceBtn');
            const addFaqBtn = document.getElementById('addFaqBtn');
            const totalSteps = {{ count($steps) }};
            const initialStep = {{ $initialStep }};
            const draftKey = 'travelnest-package-create-draft';
            let currentStep = initialStep;
            let highestStep = initialStep;
            let isDirty = {{ $viewErrors->any() || old() ? 'true' : 'false' }};
            let isSubmitting = false;
            let featuredFile = null;
            let galleryFiles = [];

            const texts = {
                complete: @json(admin_t('مكتملة')),
                incomplete: @json(admin_t('غير مكتملة')),
                requiredMessage: @json(admin_t('يرجى استكمال الحقول المطلوبة.')),
                saveDraftSuccess: @json(admin_t('تم حفظ المسودة محليًا.')),
                saveDraftError: @json(admin_t('تعذر حفظ المسودة.')),
                restoreDraft: @json(admin_t('يوجد نموذج غير مكتمل محفوظ مسبقًا. هل تريد استكماله؟')),
                leavePage: @json(admin_t('لديك تغييرات غير محفوظة. هل تريد مغادرة الصفحة؟')),
                saving: @json(admin_t('جارٍ حفظ الرحلة...')),
                noData: '-',
                active: @json(admin_t('مفعلة')),
                inactive: @json(admin_t('غير مفعلة')),
                dayFormat: @json(admin_t('الخطوة :current من :total')),
                imagePreview: @json(admin_t('معاينة الصورة الرئيسية')),
                galleryPreview: @json(admin_t('معاينة المعرض')),
                noGallery: @json(admin_t('لا توجد صور في المعرض حتى الآن.')),
                noItinerary: @json(admin_t('لا يوجد برنامج يومي حتى الآن.')),
                noIncluded: @json(admin_t('لا يوجد عناصر مشمولة حتى الآن.')),
                noExcluded: @json(admin_t('لا يوجد عناصر غير مشمولة حتى الآن.')),
                noPrices: @json(admin_t('لا توجد أسعار مضافة حتى الآن.')),
                remove: @json(admin_t('إزالة')),
                dayTitle: @json(admin_t('يوم رقم :number')),
            };

            const stepTitles = @json(array_column($steps, 'title'));
            const requiredFieldsByStep = {
                1: ['title', 'category_id', 'destination_id', 'package_type'],
                2: [],
                3: [],
                4: [],
                5: []
            };

            function notify(message, type = 'success') {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2200,
                        timerProgressBar: true,
                        icon: type,
                        title: message
                    });
                    return;
                }

                window.alert(message);
            }

            function replacePlaceholders(text, values) {
                return Object.entries(values).reduce((result, [key, value]) => {
                    return result.replace(':' + key, value);
                }, text);
            }

            function scrollToTopOfWizard() {
                if (form) {
                    form.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }

            function markStepState() {
                stepButtons.forEach((button, index) => {
                    const step = index + 1;
                    button.classList.toggle('is-active', step === currentStep);
                    button.classList.toggle('is-complete', step < currentStep || (step <= highestStep && step !== currentStep));
                    button.classList.toggle('is-disabled', step > highestStep + 1);
                    const badge = button.querySelector('.wizard-step-badge');
                    if (badge) {
                        badge.innerHTML = step < currentStep || (step < highestStep && step !== currentStep) ? '<i class="ti ti-check"></i>' : step;
                    }
                });
            }

            function updateActionState() {
                if (stepLabel) {
                    stepLabel.textContent = replacePlaceholders(texts.dayFormat, {
                        current: currentStep,
                        total: totalSteps
                    }) + ' - ' + (stepTitles[currentStep - 1] || '');
                }

                if (mobileStepTitle) mobileStepTitle.textContent = stepTitles[currentStep - 1] || '';
                if (mobileStepCounter) {
                    mobileStepCounter.textContent = replacePlaceholders(texts.dayFormat, {
                        current: currentStep,
                        total: totalSteps
                    });
                }
                if (mobileStepBar) mobileStepBar.style.width = `${(currentStep / totalSteps) * 100}%`;

                if (prevBtn) prevBtn.disabled = currentStep === 1;
                if (nextBtn) nextBtn.classList.toggle('d-none-force', currentStep === totalSteps);
                if (submitBtn) submitBtn.classList.toggle('d-none-force', currentStep !== totalSteps);
            }

            function showStep(step) {
                currentStep = step;
                if (currentStep > highestStep) {
                    highestStep = currentStep;
                }

                stepPanels.forEach(panel => {
                    panel.classList.toggle('is-active', Number(panel.dataset.stepPanel) === currentStep);
                });

                markStepState();
                updateActionState();
                updateSummary();
                scrollToTopOfWizard();
            }

            function focusFirstInvalid(step) {
                const panel = document.querySelector(`[data-step-panel="${step}"]`);
                if (!panel) return;
                const invalidField = panel.querySelector('.field-error, .is-invalid');
                if (invalidField) {
                    invalidField.focus({
                        preventScroll: true
                    });
                }
            }

            function validateStep(step) {
                if (!form) return true;
                const panel = document.querySelector(`[data-step-panel="${step}"]`);
                if (!panel) return true;

                let requiredFields = requiredFieldsByStep[step] ? [...requiredFieldsByStep[step]] : [];
                const packageType = document.getElementById('package_type')?.value || '';

                if (step === 1) {
                    if (packageType === 'nile_cruise') {
                        requiredFields = requiredFields.filter(field => field !== 'destination_id');
                        requiredFields.push('nile_cruise_type_id');
                    }
                } else if (step === 3) {
                    if (packageType === 'day_tour') {
                        requiredFields.push('duration_hours');
                    } else if (packageType === 'travel_package') {
                        requiredFields.push('duration_days');
                    }
                }

                let isValid = true;
                let firstInvalid = null;

                panel.querySelectorAll('.field-error').forEach(field => field.classList.remove('field-error'));

                requiredFields.forEach(name => {
                    const field = form.querySelector(`[name="${name}"]`);
                    if (!field) {
                        return;
                    }

                    const value = field.value ? field.value.trim() : '';
                    const filled = field.type === 'checkbox' ? field.checked : value !== '';

                    if (!filled) {
                        field.classList.add('field-error');
                        isValid = false;
                        if (!firstInvalid) {
                            firstInvalid = field;
                        }
                    }
                });

                if (!isValid) {
                    notify(texts.requiredMessage, 'error');
                    if (firstInvalid) {
                        firstInvalid.focus({
                            preventScroll: true
                        });
                    }
                }

                return isValid;
            }

            function serializeDraft() {
                if (!form) return {};
                const data = {};
                Array.from(form.elements).forEach(element => {
                    if (!element.name || element.type === 'file' || element.type === 'password' || element.disabled) {
                        return;
                    }

                    if (element.type === 'checkbox') {
                        if (element.name.endsWith('[]')) {
                            data[element.name] = data[element.name] || [];
                            if (element.checked) {
                                data[element.name].push(element.value);
                            }
                            return;
                        }

                        data[element.name] = element.checked;
                        return;
                    }

                    if (element.type === 'radio') {
                        if (element.checked) {
                            data[element.name] = element.value;
                        }
                        return;
                    }

                    data[element.name] = element.value;
                });

                return data;
            }

            function restoreDraft(data) {
                if (!form) return;
                Object.entries(data).forEach(([name, value]) => {
                    const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
                    if (!field) {
                        return;
                    }

                    if (field.type === 'checkbox') {
                        if (name.endsWith('[]') && Array.isArray(value)) {
                            const selectedValues = value.map(String);
                            form.querySelectorAll(`[name="${CSS.escape(name)}"]`).forEach(checkbox => {
                                checkbox.checked = selectedValues.includes(checkbox.value);
                            });
                            return;
                        }

                        field.checked = Boolean(value);
                        return;
                    }

                    if (field.type === 'radio') {
                        const selected = form.querySelector(
                            `[name="${CSS.escape(name)}"][value="${value}"]`);
                        if (selected) {
                            selected.checked = true;
                        }
                        return;
                    }

                    field.value = value;
                });
            }

            function saveDraft() {
                try {
                    localStorage.setItem(draftKey, JSON.stringify(serializeDraft()));
                    notify(texts.saveDraftSuccess, 'success');
                } catch (error) {
                    notify(texts.saveDraftError, 'error');
                }
            }

            function maybeRestoreDraft() {
                if ({{ old() ? 'true' : 'false' }}) {
                    return;
                }

                const storedDraft = localStorage.getItem(draftKey);
                if (!storedDraft) {
                    return;
                }

                if (!window.confirm(texts.restoreDraft)) {
                    localStorage.removeItem(draftKey);
                    return;
                }

                try {
                    restoreDraft(JSON.parse(storedDraft));
                } catch (error) {
                    localStorage.removeItem(draftKey);
                }
            }

            function syncCountryFromDestination() {
                if (!destinationSelector || !primaryCountryInput) {
                    return;
                }

                const selected = destinationSelector.options[destinationSelector.selectedIndex];
                const countryId = selected ? selected.getAttribute('data-country-id') : '';
                primaryCountryInput.value = countryId || '';
            }

            function updateDurationFields() {
                const selected = form?.querySelector('input[name="duration_type"]:checked');
                const type = selected ? selected.value : 'days';
                document.getElementById('daysFieldWrapper')?.classList.toggle('d-none-force', type !== 'days');
                document.getElementById('nightsFieldWrapper')?.classList.toggle('d-none-force', type !== 'days');
                document.getElementById('hoursFieldWrapper')?.classList.toggle('d-none-force', type !== 'hours');
                updateItineraryMode();
            }

            function updateItineraryMode() {
                const packageType = document.getElementById('package_type')?.value || '';
                const type = form?.querySelector('input[name="duration_type"]:checked')?.value || 'days';
                const isHourly = packageType === 'day_tour' || type === 'hours';
                const isTourPackage = packageType === 'travel_package';
                const itineraryMode = document.querySelector('[data-tour-package-itinerary-mode]')?.value || 'simple';
                const isAdvancedPackage = isTourPackage && itineraryMode === 'advanced';

                const titleEl = document.getElementById('itinerarySectionTitle');
                if (titleEl) titleEl.textContent = isHourly ? 'Activity Timeline' : 'Daily Itinerary';

                const copyEl = document.getElementById('itinerarySectionCopy');
                if (copyEl) {
                    copyEl.textContent = isHourly
                        ? 'Build the Day Trip hour-by-hour / stop-by-stop with real start and end times.'
                        : (isTourPackage
                            ? 'Build the Tour Package day-by-day. Advanced mode supports multiple ordered activities inside each day.'
                            : 'Split the trip into days with meal and activity details.');
                }

                const addBtnTextEl = document.getElementById('addItineraryText');
                if (addBtnTextEl) addBtnTextEl.textContent = isHourly ? 'Add Stop' : 'Add New Day';

                document.querySelectorAll('.itinerary-item').forEach(item => {
                    const label = item.querySelector('.item-order-label');
                    const durationLabel = item.querySelector('[data-itinerary-duration-label]');
                    const durationInput = item.querySelector('[data-itinerary-duration-input]');

                    if (label) label.textContent = isHourly ? 'Step' : 'Day';
                    if (durationLabel) durationLabel.textContent = isHourly ? 'Time / Duration label' : 'Date / Day label';
                    if (durationInput) {
                        durationInput.placeholder = isHourly
                            ? 'Example: Morning / 2 hours (optional label)'
                            : 'Optional date or day label';
                    }

                    item.querySelectorAll('[data-itinerary-hour-fields]').forEach(section => {
                        section.style.display = isHourly ? '' : 'none';
                        section.querySelectorAll('input,select,textarea').forEach(el => el.disabled = !isHourly);
                    });

                    item.querySelectorAll('[data-itinerary-day-fields]').forEach(section => {
                        section.style.display = isHourly ? 'none' : '';
                        section.querySelectorAll('input,select,textarea').forEach(el => el.disabled = isHourly);
                    });

                    item.querySelectorAll('[data-itinerary-tour-package-fields]').forEach(section => {
                        section.style.display = isTourPackage ? '' : 'none';
                        section.querySelectorAll('input,select,textarea').forEach(el => el.disabled = !isTourPackage);
                    });

                    item.querySelectorAll('[data-itinerary-tour-package-advanced]').forEach(section => {
                        section.style.display = isAdvancedPackage ? '' : 'none';
                        // Preserve advanced activity data while toggling simple/advanced mode.
                        // Disable only when the selected top-level Tour Type is not Tour Package.
                        section.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = !isTourPackage);
                    });
                });

                const emptyMessage = document.querySelector('#itineraryEmptyState span');
                if (emptyMessage) {
                    emptyMessage.textContent = isHourly ? 'No activity timeline stops added yet.' : 'No itinerary days added yet.';
                }
            }

            function updateCounter(input) {
                if (!input) return;
                const max = Number(input.dataset.counterMax || 0);
                if (!max) {
                    return;
                }

                const counter = document.querySelector(`[data-counter-for="${input.id}"]`);
                if (!counter) {
                    return;
                }

                counter.textContent = `${input.value.length} / ${max}`;
            }

            function renderFeaturedPreview() {
                if (!featuredPreview) return;
                featuredPreview.innerHTML = '';

                if (!featuredFile) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    featuredPreview.innerHTML = `
                        <div class="preview-card">
                            <img src="${event.target.result}" alt="">
                            <div class="preview-card-footer">
                                <span>${texts.imagePreview}</span>
                                <button type="button" class="preview-remove" data-remove-featured>
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(featuredFile);
            }

            function syncGalleryInput() {
                if (!galleryInput) return;
                try {
                    const dataTransfer = new DataTransfer();
                    galleryFiles.forEach(file => dataTransfer.items.add(file));
                    galleryInput.files = dataTransfer.files;
                } catch (e) {
                    console.warn('DataTransfer not fully supported:', e);
                }
            }

            function renderGalleryPreview() {
                if (!galleryPreview) return;
                galleryPreview.innerHTML = '';

                if (!galleryFiles.length) {
                    galleryPreview.innerHTML =
                        `<div class="empty-state" id="galleryEmptyState">${texts.noGallery}</div>`;
                    return;
                }

                galleryFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const card = document.createElement('div');
                        card.className = 'preview-card';
                        card.innerHTML = `
                            <img src="${event.target.result}" alt="">
                            <div class="preview-card-footer">
                                <span>${file.name}</span>
                                <button type="button" class="preview-remove" data-gallery-index="${index}">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        `;
                        galleryPreview.appendChild(card);
                    };
                    reader.readAsDataURL(file);
                });
            }

            function ensureEmptyState(wrapperSelector, itemSelector, emptyId, emptyText) {
                const wrapper = document.querySelector(wrapperSelector);
                if (!wrapper) {
                    return;
                }

                const hasItems = wrapper.querySelectorAll(itemSelector).length > 0;
                const empty = wrapper.querySelector(`#${emptyId}`);

                if (!hasItems && !empty) {
                    const div = document.createElement('div');
                    div.className = 'empty-state';
                    div.id = emptyId;

                    const dynamicState = {
                        itineraryEmptyState: {
                            icon: '🗓️',
                            text: 'No itinerary added yet.'
                        },
                        faqEmptyState: {
                            icon: '💬',
                            text: 'No FAQs added yet.'
                        }
                    }[emptyId];

                    if (dynamicState) {
                        const icon = document.createElement('strong');
                        const message = document.createElement('span');
                        div.classList.add('empty-dynamic-state');
                        icon.textContent = dynamicState.icon;
                        message.textContent = dynamicState.text;
                        div.append(icon, message);
                    } else {
                        div.textContent = emptyText;
                    }

                    wrapper.appendChild(div);
                }

                if (hasItems && empty) {
                    empty.remove();
                }
            }

            function createRemoveButton() {
                return `<button type="button" class="btn js-remove hover-delete-btn icon-remove-btn dynamic-remove-control" aria-label="${@json(admin_t('حذف'))}"><i class="ti ti-trash"></i></button>`;
            }

            function appendAnimatedItem(wrapperId, markup) {
                const wrapper = document.getElementById(wrapperId);
                if (!wrapper) return null;
                wrapper.querySelector('.empty-state')?.remove();
                wrapper.insertAdjacentHTML('beforeend', markup);
                const item = wrapper.lastElementChild;
                isDirty = true;
                if (item) {
                    item.classList.add('is-entering');
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => item.classList.remove('is-entering'));
                    });

                    item.querySelector('input, select, textarea')?.focus({
                        preventScroll: true
                    });
                }
                return item;
            }

            function renumberDynamicItems(wrapperId, itemSelector, syncDayNumbers = false) {
                const wrapper = document.getElementById(wrapperId);
                if (!wrapper) {
                    return;
                }

                Array.from(wrapper.querySelectorAll(itemSelector)).forEach((item, index) => {
                    const badge = item.querySelector('.item-order-badge');
                    if (badge) {
                        const number = badge.querySelector('.item-order-number');
                        if (number) {
                            number.textContent = String(index + 1).padStart(2, '0');
                        } else {
                            badge.textContent = String(index + 1).padStart(2, '0');
                        }
                    }

                    if (syncDayNumbers) {
                        const dayNumber = item.querySelector('input[name*="[day_number]"]');
                        if (dayNumber) {
                            dayNumber.value = index + 1;
                        }
                    }
                });
            }

            function syncMealInputs(element) {
                if (!element) return;
                const card = element.closest('.itinerary-item');
                if (!card) return;

                const checkedMeals = Array.from(card.querySelectorAll('.js-meal-checkbox:checked')).map(cb => cb.value);

                const bHidden = card.querySelector('.js-meal-hidden-breakfast');
                const lHidden = card.querySelector('.js-meal-hidden-lunch');
                const dHidden = card.querySelector('.js-meal-hidden-dinner');

                if (bHidden) bHidden.value = checkedMeals.includes('breakfast') ? '1' : '0';
                if (lHidden) lHidden.value = checkedMeals.includes('lunch') ? '1' : '0';
                if (dHidden) dHidden.value = checkedMeals.includes('dinner') ? '1' : '0';
            }

            function updateAttractionsPicker() {
                if (!attractionsPicker) {
                    return;
                }

                const query = (attractionSearch?.value || '').trim().toLocaleLowerCase();
                const choices = Array.from(attractionsPicker.querySelectorAll('[data-attraction-choice]'));

                choices.forEach(choice => {
                    const searchText = (choice.dataset.attractionSearch || '').toLocaleLowerCase();
                    choice.classList.toggle('is-filtered-out', query !== '' && !searchText.includes(query));
                });

                const selectedCount = choices.filter(choice => choice.querySelector('input:checked')).length;
                if (attractionsSelectedCount) {
                    attractionsSelectedCount.textContent = `${selectedCount} selected`;
                }
            }

            let itineraryIndex = {{ count($itinerary) }};
            let includedIndex = {{ count($included) }};
            let excludedIndex = {{ count($excluded) }};
            let priceIndex = {{ count($prices) }};
            let faqIndex = {{ count($faqItems) }};

            function addItinerary() {
                const packageType = document.getElementById('package_type')?.value || '';
                const isHourly = packageType === 'day_tour' || form?.querySelector('input[name="duration_type"]:checked')?.value === 'hours';
                appendAnimatedItem('itinerary-wrapper', `
                    <div class="repeat-box itinerary-item itinerary-item-card">
                        <div class="itinerary-item-grid">
                            <div class="dynamic-order-column">
                                <span class="item-order-badge">
                                    <small class="item-order-label">${isHourly ? 'Stop' : 'Day'}</small>
                                    <span class="item-order-number">${String(itineraryIndex + 1).padStart(2, '0')}</span>
                                </span>
                            </div>
                            <div class="field-block itinerary-date-field">
                                <label class="field-block-label" data-itinerary-duration-label>${isHourly ? 'Time / Duration label' : 'Date / Day label'}</label>
                                <div class="field-shell">
                                    <span class="field-shell-icon"><i class="ti ti-clock"></i></span>
                                    <input type="text" data-itinerary-duration-input name="itinerary[${itineraryIndex}][duration]" placeholder="${isHourly ? 'Example: Morning / 2 hours (optional label)' : 'Optional date or day label'}">
                                </div>
                                <input type="hidden" name="itinerary[${itineraryIndex}][day_number]" value="${itineraryIndex + 1}">
                            </div>
                            <div class="field-block itinerary-hour-fields" data-itinerary-hour-fields>
                                <label class="field-block-label">Activity time</label>
                                <div class="fields-grid fields-grid-2">
                                    <div class="field-shell">
                                        <span class="field-shell-icon"><i class="ti ti-clock-play"></i></span>
                                        <input type="time" name="itinerary[${itineraryIndex}][start_time]" aria-label="Start time">
                                    </div>
                                    <div class="field-shell">
                                        <span class="field-shell-icon"><i class="ti ti-clock-stop"></i></span>
                                        <input type="time" name="itinerary[${itineraryIndex}][end_time]" aria-label="End time">
                                    </div>
                                </div>
                            </div>
                            <div class="field-block itinerary-place-field">
                                <label class="field-block-label">Place / Stop</label>
                                <div class="field-shell">
                                    <span class="field-shell-icon"><i class="ti ti-map-pin"></i></span>
                                    <input type="text" name="itinerary[${itineraryIndex}][title]" placeholder="Enter place or stop">
                                </div>
                            </div>
                            <div class="field-block itinerary-meals-field" data-itinerary-day-fields>
                                <label class="field-block-label">Meals Included</label>
                                <div class="meal-options-pills">
                                    <label class="meal-pill-checkbox">
                                        <input type="checkbox" name="itinerary[${itineraryIndex}][meals][]" value="breakfast" class="js-meal-checkbox">
                                        <span class="meal-pill"><i class="ti ti-coffee"></i> Breakfast</span>
                                    </label>
                                    <label class="meal-pill-checkbox">
                                        <input type="checkbox" name="itinerary[${itineraryIndex}][meals][]" value="lunch" class="js-meal-checkbox">
                                        <span class="meal-pill"><i class="ti ti-soup"></i> Lunch</span>
                                    </label>
                                    <label class="meal-pill-checkbox">
                                        <input type="checkbox" name="itinerary[${itineraryIndex}][meals][]" value="dinner" class="js-meal-checkbox">
                                        <span class="meal-pill"><i class="ti ti-glass-full"></i> Dinner</span>
                                    </label>
                                </div>
                                <input type="hidden" name="itinerary[${itineraryIndex}][meals_breakfast]" value="0" class="js-meal-hidden-breakfast">
                                <input type="hidden" name="itinerary[${itineraryIndex}][meals_lunch]" value="0" class="js-meal-hidden-lunch">
                                <input type="hidden" name="itinerary[${itineraryIndex}][meals_dinner]" value="0" class="js-meal-hidden-dinner">
                            </div>
                            <div class="field-block itinerary-activities-field">
                                <label class="field-block-label">Activities / Day description</label>
                                <div class="field-shell field-shell-textarea">
                                    <span class="field-shell-icon"><i class="ti ti-route"></i></span>
                                    <textarea name="itinerary[${itineraryIndex}][description]" rows="4" placeholder="Enter activities"></textarea>
                                </div>
                            </div>
                            <div class="field-block itinerary-tour-package-fields" data-itinerary-tour-package-fields>
                                <label class="field-block-label">Tour Package day details</label>
                                <div class="fields-grid fields-grid-2">
                                    <div>
                                        <label class="field-block-label">Overnight location</label>
                                        <input class="form-control" type="text" name="itinerary[${itineraryIndex}][overnight_location]" placeholder="e.g. Luxor">
                                    </div>
                                    <div>
                                        <label class="field-block-label">Accommodation</label>
                                        <input class="form-control" type="text" name="itinerary[${itineraryIndex}][accommodation]" placeholder="e.g. 5-star hotel in Luxor">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="field-block-label">Transport notes</label>
                                    <textarea class="form-control" name="itinerary[${itineraryIndex}][transport_notes]" rows="2" placeholder="Transfers, domestic flights, train, private vehicle..."></textarea>
                                </div>
                            </div>
                            <div class="field-block itinerary-tour-package-advanced" data-itinerary-tour-package-advanced>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <label class="field-block-label mb-0">Advanced day activities</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary js-add-tour-package-activity" data-itinerary-index="${itineraryIndex}">+ Add Activity</button>
                                </div>
                                <div class="tour-package-activities-list" data-tour-package-activities-list></div>
                            </div>
                            ${createRemoveButton()}
                        </div>
                    </div>
                `);
                itineraryIndex++;
                renumberDynamicItems('itinerary-wrapper', '.itinerary-item', true);
                ensureEmptyState('#itinerary-wrapper', '.itinerary-item', 'itineraryEmptyState', texts.noItinerary);
                updateItineraryMode();
            }

            function addTourPackageActivity(button) {
                const itineraryItem = button?.closest('.itinerary-item');
                const list = itineraryItem?.querySelector('[data-tour-package-activities-list]');
                if (!itineraryItem || !list) return;

                const itineraryInput = itineraryItem.querySelector('input[name*="[day_number]"]');
                const itineraryMatch = itineraryInput?.name?.match(/^itinerary\[(\d+)\]/);
                const itineraryKey = button.dataset.itineraryIndex || itineraryMatch?.[1];
                if (itineraryKey === undefined) return;

                let nextActivityIndex = 0;
                list.querySelectorAll('[name*="[activities]["]').forEach(input => {
                    const match = input.name.match(/\[activities\]\[(\d+)\]/);
                    if (match) nextActivityIndex = Math.max(nextActivityIndex, Number(match[1]) + 1);
                });

                list.insertAdjacentHTML('beforeend', `
                    <div class="repeat-box tour-package-activity-row" data-tour-package-activity-row>
                        <div class="row g-2">
                            <div class="col-md-2"><input class="form-control" type="time" name="itinerary[${itineraryKey}][activities][${nextActivityIndex}][time]" aria-label="Activity time"></div>
                            <div class="col-md-3"><input class="form-control" type="text" name="itinerary[${itineraryKey}][activities][${nextActivityIndex}][title]" placeholder="Activity title"></div>
                            <div class="col-md-3"><input class="form-control" type="text" name="itinerary[${itineraryKey}][activities][${nextActivityIndex}][location]" placeholder="Location"></div>
                            <div class="col-md-2"><input class="form-control" type="text" name="itinerary[${itineraryKey}][activities][${nextActivityIndex}][duration]" placeholder="Duration"></div>
                            <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 js-remove-tour-package-activity">Remove</button></div>
                            <div class="col-12"><textarea class="form-control" rows="2" name="itinerary[${itineraryKey}][activities][${nextActivityIndex}][description]" placeholder="Activity details"></textarea></div>
                        </div>
                    </div>
                `);
                isDirty = true;
            }

            function addInclusion(type) {
                const wrapper = document.getElementById(`${type}-wrapper`);
                if (!wrapper) return;
                const index = type === 'included' ? includedIndex++ : excludedIndex++;
                appendAnimatedItem(`${type}-wrapper`, `
                    <div class="repeat-box ${type}-item">
                        <div class="d-flex gap-2">
                            <input type="text" name="${type}[${index}][title]" class="form-control" placeholder="${type === 'included' ? 'Included item' : 'Excluded item'}">
                            ${createRemoveButton()}
                        </div>
                    </div>
                `);
                ensureEmptyState(`#${type}-wrapper`, `.${type}-item`, `${type}EmptyState`, type === 'included' ?
                    texts.noIncluded : texts.noExcluded);
            }

            function addPrice() {
                appendAnimatedItem('prices-wrapper', `
                    <div class="repeat-box editor-card price-item">
                        <div class="editor-card-head">
                            <div class="editor-card-title editor-inline-input">
                                <span class="editor-card-badge">
                                    <i class="ti ti-cash-banknote"></i>
                                </span>
                                <input type="text" name="prices[${priceIndex}][label]" class="form-control" placeholder="${@json(admin_t('عنوان السعر أو الباقة'))}">
                            </div>
                            ${createRemoveButton()}
                        </div>
                        <div class="editor-card-body">
                            <div class="fields-grid">
                                <div>
                                    <label class="form-label">${@json(admin_t('الموسم'))}</label>
                                    <input type="text" name="prices[${priceIndex}][season_name]" class="form-control" placeholder="${@json(admin_t('مثال: موسم الصيف'))}">
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('المبلغ'))}</label>
                                    <input type="number" step="0.01" name="prices[${priceIndex}][amount]" class="form-control" placeholder="${@json(admin_t('المبلغ'))}">
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('العملة'))}</label>
                                    <select name="prices[${priceIndex}][currency_id]" class="form-select">
                                        <option value="">${@json(admin_t('العملة'))}</option>
                                        @foreach ($currencies ?? collect() as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="fields-grid">
                                <div>
                                    <label class="form-label">${@json(admin_t('نوع السعر'))}</label>
                                    <select name="prices[${priceIndex}][price_type]" class="form-select">
                                        <option value="from">${@json(admin_t('يبدأ من'))}</option>
                                        <option value="fixed">${@json(admin_t('ثابت'))}</option>
                                        <option value="seasonal">${@json(admin_t('موسمي'))}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('نوع الغرفة'))}</label>
                                    <input type="text" name="prices[${priceIndex}][room_type]" class="form-control" placeholder="${@json(admin_t('مثال: غرفة مزدوجة'))}">
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('عدد الأفراد من'))}</label>
                                    <input type="number" min="1" name="prices[${priceIndex}][pax_min]" class="form-control" placeholder="${@json(admin_t('مثال: 1'))}">
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('عدد الأفراد إلى'))}</label>
                                    <input type="number" min="1" name="prices[${priceIndex}][pax_max]" class="form-control" placeholder="${@json(admin_t('مثال: 4'))}">
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('من تاريخ'))}</label>
                                    <input type="date" name="prices[${priceIndex}][valid_from]" class="form-control">
                                </div>
                            </div>
                            <div class="fields-grid two-up">
                                <div>
                                    <label class="form-label">${@json(admin_t('إلى تاريخ'))}</label>
                                    <input type="date" name="prices[${priceIndex}][valid_to]" class="form-control">
                                </div>
                                <div>
                                    <label class="form-label">${@json(admin_t('ملاحظات'))}</label>
                                    <textarea name="prices[${priceIndex}][notes]" rows="3" class="form-control" placeholder="${@json(admin_t('أضف أي توضيح متعلق بهذا السعر'))}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                priceIndex++;
                ensureEmptyState('#prices-wrapper', '.price-item', 'pricesEmptyState', texts.noPrices);
            }

            function addFaq() {
                appendAnimatedItem('faq-wrapper', `
                    <div class="repeat-box faq-item faq-item-card">
                        <div class="faq-item-grid">
                            <div class="dynamic-order-column">
                                <span class="item-order-badge">${String(faqIndex + 1).padStart(2, '0')}</span>
                            </div>
                            <div class="field-block faq-question-field">
                                <label class="field-block-label">Question</label>
                                <div class="field-shell">
                                    <span class="field-shell-icon"><i class="ti ti-help-circle"></i></span>
                                    <input type="text" name="faq_json[${faqIndex}][question]" placeholder="Enter question...">
                                </div>
                            </div>
                            <div class="field-block faq-answer-field">
                                <label class="field-block-label">Answer</label>
                                <div class="field-shell field-shell-textarea">
                                    <span class="field-shell-icon"><i class="ti ti-edit"></i></span>
                                    <textarea name="faq_json[${faqIndex}][answer]" rows="4" placeholder="Enter answer..."></textarea>
                                </div>
                            </div>
                            ${createRemoveButton()}
                        </div>
                    </div>
                `);
                faqIndex++;
                renumberDynamicItems('faq-wrapper', '.faq-item');
                ensureEmptyState('#faq-wrapper', '.faq-item', 'faqEmptyState', @json(admin_t('لا توجد أسئلة شائعة مضافة حتى الآن.')));
            }

            function updateSummary() {
                const destinationOption = destinationSelector && destinationSelector.selectedIndex >= 0 ? destinationSelector.options[destinationSelector.selectedIndex] : null;
                const categorySelect = document.getElementById('category_id');
                const categoryOption = categorySelect && categorySelect.selectedIndex >= 0 ? categorySelect.options[categorySelect.selectedIndex] : null;
                
                const durationTextEl = document.getElementById('duration_text');
                const durationDaysEl = document.getElementById('duration_days');
                const durationNightsEl = document.getElementById('duration_nights');
                const durationHoursEl = document.getElementById('duration_hours');

                const durationText = (durationTextEl?.value || '').trim() || [
                    durationDaysEl?.value ? durationDaysEl.value + ' {{ admin_t('يوم') }}' : '',
                    durationNightsEl?.value ? durationNightsEl.value + ' {{ admin_t('ليلة') }}' : '',
                    durationHoursEl?.value ? durationHoursEl.value + ' {{ admin_t('ساعة') }}' : ''
                ].filter(Boolean).join(' / ');
                const imagesCount = (featuredFile ? 1 : 0) + galleryFiles.length;
                const itineraryCount = document.querySelectorAll('.itinerary-item').length;

                const selectedPackageType = packageTypeSelect?.value || '';
                const nileReview = document.querySelector('[data-nile-review-summary]');
                const dayTripReview = document.querySelector('[data-day-trip-review-summary]');
                const tourPackageReview = document.querySelector('[data-tour-package-review-summary]');
                const isNileReview = selectedPackageType === 'nile_cruise';
                const isDayTripReview = selectedPackageType === 'day_tour';
                const isTourPackageReview = selectedPackageType === 'travel_package';
                if (nileReview) nileReview.style.display = isNileReview ? '' : 'none';
                if (dayTripReview) dayTripReview.style.display = isDayTripReview ? '' : 'none';
                if (tourPackageReview) tourPackageReview.style.display = isTourPackageReview ? '' : 'none';
                const nileTypeOption = nileTypeSelect && nileTypeSelect.selectedIndex >= 0 ? nileTypeSelect.options[nileTypeSelect.selectedIndex] : null;
                const nileCategoryOption = nileCatSelect && nileCatSelect.selectedIndex >= 0 ? nileCatSelect.options[nileCatSelect.selectedIndex] : null;

                const summary = {
                    title: document.getElementById('title')?.value || texts.noData,
                    destination: destinationOption && destinationOption.value ? destinationOption.textContent.trim() : texts.noData,
                    duration: durationText || texts.noData,
                    price: document.getElementById('adult_price')?.value || texts.noData,
                    category: categoryOption && categoryOption.value ? categoryOption.textContent.trim() : texts.noData,
                    status: form?.querySelector('input[name="is_active"]')?.checked ? texts.active : texts.inactive,
                    images: imagesCount,
                    daysCount: itineraryCount,
                    nileType: isNileReview && nileTypeOption?.value ? nileTypeOption.textContent.trim() : texts.noData,
                    nileCategory: isNileReview && nileCategoryOption?.value ? nileCategoryOption.textContent.trim() : texts.noData,
                    nileCabins: isNileReview ? document.querySelectorAll('#nileCruiseExtendedSection [data-nc-cabin]').length : 0,
                    nileDurations: isNileReview ? document.querySelectorAll('#nileCruiseExtendedSection [data-nc-duration]').length : 0,
                    nileSchedules: isNileReview ? document.querySelectorAll('#nileCruiseExtendedSection [data-nc-schedule]').length : 0,
                    nileRouteStops: isNileReview ? document.querySelectorAll('#nileCruiseExtendedSection [data-nc-route-row]').length : 0,
                    dayTripStops: isDayTripReview ? itineraryCount : 0,
                    dayTripDepartureTimes: isDayTripReview
                        ? ((form?.querySelector('[name="experience[departure_times]"]')?.value || '').split(/[\n,]+/).map(v => v.trim()).filter(Boolean).join(' · ') || texts.noData)
                        : texts.noData,
                    operatingDays: Array.from(form?.querySelectorAll('input[name="experience[operating_days][]"]:checked') || []).map(el => el.value).join(' · ') || texts.noData,
                    tourPackageCities: isTourPackageReview
                        ? (Array.from(form?.querySelectorAll('select[name="tour_city_ids[]"] option:checked') || []).map(el => el.textContent.trim()).filter(Boolean).join(' / ') || texts.noData)
                        : texts.noData,
                    tourPackageAccommodation: isTourPackageReview ? (form?.querySelector('[name="tour_package[accommodation_standard]"]')?.value || texts.noData) : texts.noData,
                    tourPackageMeals: isTourPackageReview
                        ? (Array.from(form?.querySelectorAll('input[name="tour_package[meals_included][]"]:checked') || []).map(el => el.value).join(' · ') || texts.noData)
                        : texts.noData,
                    tourPackageFlexible: isTourPackageReview ? (form?.querySelector('[name="tour_package[flexible_itinerary]"]')?.checked ? 'Yes' : 'No') : texts.noData,
                    tourPackageItineraryMode: isTourPackageReview ? (form?.querySelector('[data-tour-package-itinerary-mode]')?.value || 'simple') : texts.noData
                };

                Object.entries(summary).forEach(([key, value]) => {
                    const target = document.querySelector(`[data-summary="${key}"]`);
                    if (target) {
                        target.textContent = value;
                    }
                });
            }

            maybeRestoreDraft();
            syncCountryFromDestination();
            updateDurationFields();
            updateAttractionsPicker();
            renderGalleryPreview();
            renderFeaturedPreview();
            form?.querySelectorAll('[data-meal-select]').forEach(syncMealInputs);
            document.querySelectorAll('[data-counter-max]').forEach(updateCounter);
            showStep(currentStep);
            ensureEmptyState('#itinerary-wrapper', '.itinerary-item', 'itineraryEmptyState', texts.noItinerary);
            ensureEmptyState('#included-wrapper', '.included-item', 'includedEmptyState', texts.noIncluded);
            ensureEmptyState('#excluded-wrapper', '.excluded-item', 'excludedEmptyState', texts.noExcluded);
            ensureEmptyState('#prices-wrapper', '.price-item', 'pricesEmptyState', texts.noPrices);
            ensureEmptyState('#faq-wrapper', '.faq-item', 'faqEmptyState', @json(admin_t('لا توجد أسئلة شائعة مضافة حتى الآن.')));
            renumberDynamicItems('itinerary-wrapper', '.itinerary-item', true);
            renumberDynamicItems('faq-wrapper', '.faq-item');

            document.querySelectorAll('[data-counter-max]').forEach(input => {
                input.addEventListener('input', () => updateCounter(input));
            });

            attractionSearch?.addEventListener('input', updateAttractionsPicker);

            stepButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetStep = Number(button.dataset.stepTrigger);
                    if (targetStep > highestStep + 1) {
                        return;
                    }
                    if (targetStep > currentStep && !validateStep(currentStep)) {
                        return;
                    }
                    showStep(targetStep);
                });
            });

            prevBtn?.addEventListener('click', () => {
                if (currentStep > 1) {
                    showStep(currentStep - 1);
                }
            });

            nextBtn?.addEventListener('click', () => {
                if (!validateStep(currentStep)) {
                    focusFirstInvalid(currentStep);
                    return;
                }
                if (currentStep < totalSteps) {
                    showStep(currentStep + 1);
                }
            });

            saveDraftBtn?.addEventListener('click', saveDraft);

            destinationSelector?.addEventListener('change', syncCountryFromDestination);

            form?.querySelectorAll('input[name="duration_type"]').forEach(radio => {
                radio.addEventListener('change', updateDurationFields);
            });

            form?.addEventListener('input', function(event) {
                isDirty = true;
                const input = event.target;
                if (input.matches('[data-counter-max]')) {
                    updateCounter(input);
                }
                updateSummary();
            });

            form?.addEventListener('change', function(event) {
                isDirty = true;
                if (event.target.matches('[data-meal-select]')) {
                    syncMealInputs(event.target);
                }
                if (event.target.matches('input[name="attraction_ids[]"]')) {
                    updateAttractionsPicker();
                }
                if (event.target.matches('#package_type, [data-tour-package-itinerary-mode]')) {
                    updateItineraryMode();
                }
                updateSummary();
            });

            featuredInput?.addEventListener('change', function() {
                featuredFile = this.files ? this.files[0] || null : null;
                renderFeaturedPreview();
                updateSummary();
            });

            galleryInput?.addEventListener('change', function() {
                galleryFiles = Array.from(this.files ? this.files : []);
                renderGalleryPreview();
                updateSummary();
            });

            featuredPreview?.addEventListener('click', function(event) {
                const removeButton = event.target.closest('[data-remove-featured]');
                if (!removeButton) {
                    return;
                }
                featuredFile = null;
                if (featuredInput) featuredInput.value = '';
                renderFeaturedPreview();
                updateSummary();
            });

            galleryPreview?.addEventListener('click', function(event) {
                const removeButton = event.target.closest('[data-gallery-index]');
                if (!removeButton) {
                    return;
                }
                const index = Number(removeButton.dataset.galleryIndex);
                galleryFiles.splice(index, 1);
                syncGalleryInput();
                renderGalleryPreview();
                updateSummary();
            });

            addItineraryBtn?.addEventListener('click', addItinerary);
            addIncludedBtn?.addEventListener('click', () => addInclusion('included'));
            addExcludedBtn?.addEventListener('click', () => addInclusion('excluded'));
            addPriceBtn?.addEventListener('click', addPrice);
            addFaqBtn?.addEventListener('click', addFaq);

            document.addEventListener('change', function(event) {
                if (event.target && event.target.classList.contains('js-meal-checkbox')) {
                    syncMealInputs(event.target);
                    isDirty = true;
                    updateSummary();
                }
            });

            document.addEventListener('click', function(event) {
                const addActivityButton = event.target.closest('.js-add-tour-package-activity');
                if (addActivityButton) {
                    addTourPackageActivity(addActivityButton);
                    return;
                }

                const removeActivityButton = event.target.closest('.js-remove-tour-package-activity');
                if (removeActivityButton) {
                    removeActivityButton.closest('[data-tour-package-activity-row]')?.remove();
                    isDirty = true;
                    updateSummary();
                    return;
                }

                const removeButton = event.target.closest('.js-remove');
                if (!removeButton) {
                    return;
                }

                const box = removeButton.closest('.repeat-box');
                if (box) {
                    isDirty = true;
                    box.style.height = `${box.offsetHeight}px`;
                    requestAnimationFrame(() => {
                        box.classList.add('is-removing');
                        box.style.height = '0px';
                    });

                    setTimeout(() => {
                        box.remove();

                        renumberDynamicItems('itinerary-wrapper', '.itinerary-item', true);
                        renumberDynamicItems('faq-wrapper', '.faq-item');

                        ensureEmptyState('#itinerary-wrapper', '.itinerary-item', 'itineraryEmptyState', texts.noItinerary);
                        ensureEmptyState('#included-wrapper', '.included-item', 'includedEmptyState', texts.noIncluded);
                        ensureEmptyState('#excluded-wrapper', '.excluded-item', 'excludedEmptyState', texts.noExcluded);
                        ensureEmptyState('#prices-wrapper', '.price-item', 'pricesEmptyState', texts.noPrices);
                        ensureEmptyState('#faq-wrapper', '.faq-item', 'faqEmptyState', @json(admin_t('لا توجد أسئلة شائعة مضافة حتى الآن.')));
                        updateItineraryMode();
                        updateSummary();
                    }, 260);

                    return;
                }
            });

            document.querySelectorAll('[data-jump-step]').forEach(button => {
                button.addEventListener('click', function() {
                    showStep(Number(this.dataset.jumpStep));
                });
            });

            form?.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA' && currentStep !== totalSteps) {
                    event.preventDefault();
                }
            });

            form?.addEventListener('submit', function(event) {
                let firstInvalidStep = null;
                for (let s = 1; s <= totalSteps; s++) {
                    if (!validateStep(s)) {
                        if (!firstInvalidStep) firstInvalidStep = s;
                    }
                }

                if (firstInvalidStep !== null) {
                    event.preventDefault();
                    showStep(firstInvalidStep);
                    focusFirstInvalid(firstInvalidStep);
                    notify(texts.requiredMessage + ` (${stepTitles[firstInvalidStep - 1] || ''})`, 'error');
                    return;
                }

                isSubmitting = true;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="btn-icon-text"><span class="spinner-border spinner-border-sm"></span><span>${texts.saving}</span></span>`;
                }
                localStorage.removeItem(draftKey);
            });

            window.addEventListener('beforeunload', function(event) {
                if (!isDirty || isSubmitting) {
                    return;
                }

                event.preventDefault();
                event.returnValue = texts.leavePage;
            });

            ['cancelWizardBtn', 'cancelActionBtn'].forEach(id => {
                document.getElementById(id)?.addEventListener('click', function(event) {
                    if (!isDirty || isSubmitting) {
                        return;
                    }

                    if (!window.confirm(texts.leavePage)) {
                        event.preventDefault();
                    }
                });
            });

            if ({{ $viewErrors->any() ? 'true' : 'false' }}) {
                focusFirstInvalid(initialStep);
            }

            // Quick Add Attraction Handler (Prevent any page reload)
            const handleQuickAddAttraction = function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.stopImmediatePropagation) e.stopImmediatePropagation();
                }
                
                const form = document.getElementById('quickAddAttractionForm');
                const nameInput = form?.querySelector('[name="name"]');
                const saveBtn = document.getElementById('saveQuickAttractionBtn');
                const alertBox = document.getElementById('quickAttractionAlert');
                
                if (!nameInput || !nameInput.value.trim()) {
                    if (alertBox) {
                        alertBox.className = 'alert alert-danger mt-2';
                        alertBox.textContent = '{{ __("Please enter the place/facility name.") }}';
                        alertBox.classList.remove('d-none');
                    }
                    if (nameInput) nameInput.focus();
                    return false;
                }

                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Saving...") }}';
                }
                if (alertBox) alertBox.classList.add('d-none');

                const formData = new FormData(form);

                fetch('{{ route("admin.attractions.quick-store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="ti ti-check"></i> {{ __("Save & Select") }}';
                    }

                    if (data.status === 'success' && data.attraction) {
                        const attr = data.attraction;
                        const picker = document.getElementById('attractionsPicker');
                        
                        if (picker) {
                            const emptyState = picker.querySelector('.empty-state, .field-span-3, .text-white-50');
                            if (emptyState) emptyState.remove();

                            const newCard = document.createElement('label');
                            newCard.className = 'attraction-choice';
                            newCard.dataset.attractionChoice = '';
                            newCard.dataset.attractionSearch = attr.search_text;
                            newCard.innerHTML = `
                                <input type="checkbox" name="attraction_ids[]" value="${attr.id}" checked>
                                <span class="attraction-choice-icon"><i class="ti ti-map-pin"></i></span>
                                <span class="attraction-choice-copy">
                                    <strong>${attr.name}</strong>
                                    <small>${attr.city_name}</small>
                                </span>
                            `;

                            picker.prepend(newCard);
                            const chk = newCard.querySelector('input');
                            if (chk) chk.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        if (form) form.reset();
                        const modalEl = document.getElementById('quickAddAttractionModal');
                        if (modalEl) {
                            const modal = (typeof bootstrap !== 'undefined' && bootstrap.Modal) ? (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)) : null;
                            if (modal) {
                                modal.hide();
                            } else if (window.jQuery) {
                                window.jQuery(modalEl).modal('hide');
                            }
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __("Added!") }}',
                                text: data.message || 'Facility added and selected successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        if (alertBox) {
                            alertBox.className = 'alert alert-danger mt-2';
                            alertBox.textContent = data.message || 'Error adding facility.';
                            alertBox.classList.remove('d-none');
                        }
                    }
                })
                .catch(err => {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="ti ti-check"></i> {{ __("Save & Select") }}';
                    }
                    if (alertBox) {
                        alertBox.className = 'alert alert-danger mt-2';
                        alertBox.textContent = 'An unexpected error occurred.';
                        alertBox.classList.remove('d-none');
                    }
                });

                return false;
            };

            document.getElementById('saveQuickAttractionBtn')?.addEventListener('click', handleQuickAddAttraction);
            document.getElementById('quickAddAttractionForm')?.addEventListener('submit', handleQuickAddAttraction);
        });
    </script>

    <!-- Quick Add Attraction Modal -->
    <div class="modal fade" id="quickAddAttractionModal" tabindex="-1" aria-labelledby="quickAddAttractionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: var(--dark-card, #2b3b4c); color: #fff; border: 1px solid rgba(255,255,255,0.15);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2" id="quickAddAttractionModalLabel">
                        <i class="ti ti-map-pin-plus"></i> {{ __('Add New Facility / Place') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickAddAttractionForm" action="javascript:void(0);" onsubmit="event.preventDefault(); return false;">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white fw-bold">{{ __('Place / Facility Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control text-white" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2);" placeholder="e.g. Pyramids of Giza / Karnak Temple" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white fw-bold">{{ __('City / Destination') }}</label>
                            <select name="city_id" class="form-select text-white" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2);">
                                <option value="">-- {{ __('Select City (Optional)') }} --</option>
                                @foreach ($cities ?? collect() as $city)
                                    <option value="{{ $city->id }}">{{ adminTrans($city->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white fw-bold">{{ __('Description / Details') }}</label>
                            <textarea name="description" rows="3" class="form-control text-white" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2);" placeholder="Brief details about this location..."></textarea>
                        </div>
                        <div id="quickAttractionAlert" class="alert d-none mt-2"></div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" id="saveQuickAttractionBtn">
                            <i class="ti ti-check"></i> {{ __('Save & Select') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('admin/js/unified-pricing.js') }}"></script>
@endsection
