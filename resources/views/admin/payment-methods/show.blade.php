@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض وسيلة الدفع'))

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
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .profile-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 30px;
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
            white-space: pre-wrap;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payment-methods.index') }}">وسائل الدفع</a></li>
                <li class="breadcrumb-item active">عرض وسيلة الدفع</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ adminTrans($paymentMethod->name) ?: 'بدون اسم' }}</h4>
                    <small class="opacity-75">{{ $paymentMethod->type ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.payment-methods.edit', $paymentMethod) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ adminTrans($paymentMethod->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">النوع</div>
                            <div class="info-value">{{ $paymentMethod->type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">العملة</div>
                            <div class="info-value">{{ $paymentMethod->currency_code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $paymentMethod->is_active ?? true ? 'مفعل' : 'غير مفعل' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">افتراضي</div>
                            <div class="info-value">{{ $paymentMethod->is_default ?? false ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $paymentMethod->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف</div>
                            <div class="info-value">{{ adminTrans($paymentMethod->description) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الإعدادات / Data</div>
                            <div class="info-value">
                                {{ is_array($paymentMethod->config ?? null) ? json_encode($paymentMethod->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $paymentMethod->config ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
