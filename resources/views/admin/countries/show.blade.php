@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض الدولة'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .profile-card {
            background: var(--dark-card);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
        }

        .profile-header {
            background: var(--primary-gradient);
            padding: 30px;
            color: #fff;
        }

        .profile-body {
            padding: 30px;
        }

        .info-box {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
        }

        .country-flag {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }

        .country-flag:hover {
            transform: scale(1.05);
        }

        .no-image {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            margin: auto;
            font-weight: 600;
        }

        .mini-flag {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.countries.index') }}">الدول</a></li>
                <li class="breadcrumb-item active">عرض الدولة</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if ($country->flag)
                        <img src="{{ asset($country->flag) }}" alt="{{ adminTrans($country->name) }}" class="mini-flag">
                    @endif

                    <div>
                        <h4 class="mb-1">{{ $country->display_name ?? 'بدون اسم' }}</h4>
                        <small class="opacity-75">{{ $country->code ?? '-' }}</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        @if ($country->flag)
                            <img src="{{ asset($country->flag) }}" alt="{{ adminTrans($country->name) }}"
                                class="country-flag">
                        @else
                            <div class="no-image">لا توجد صورة</div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الكود</div>
                            <div class="info-value">{{ $country->code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $country->is_active ?? true ? 'مفعلة' : 'غير مفعلة' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">مميزة</div>
                            <div class="info-value">{{ $country->is_featured ?? false ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $country->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">عدد المدن</div>
                            <div class="info-value">{{ $country->cities_count ?? ($country->cities->count() ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف</div>
                            <div class="info-value">{{ adminTrans($country->description) ?: 'لا يوجد وصف' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
