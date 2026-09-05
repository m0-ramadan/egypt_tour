@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل عميل'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .order-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .order-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .form-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: 10px;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }

        .form-label {
            color: rgba(255, 255, 255, .85);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">العملاء</a></li>
                <li class="breadcrumb-item active">تعديل عميل</li>
            </ol>
        </nav>

        <div class="order-card">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">تعديل عميل جديد</h5>
                        <small class="opacity-75">إدخال بيانات العميل الأساسية</small>
                    </div>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                </div>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.clients.update', $client) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="section-title">البيانات الأساسية</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم الأول</label>
                            <input type="text" name="first_name" class="form-control"
                                value="{{ old('first_name', $client->first_name) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم الأخير</label>
                            <input type="text" name="last_name" class="form-control"
                                value="{{ old('last_name', $client->last_name) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $client->email) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الهاتف</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $client->phone) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الجنسية</label>
                            <input type="text" name="nationality" class="form-control"
                                value="{{ old('nationality', $client->nationality) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ الميلاد</label>
                            <input type="date" name="date_of_birth" class="form-control"
                                value="{{ old('date_of_birth', optional($client->date_of_birth)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="section-title mt-4">بيانات السفر</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم جواز السفر</label>
                            <input type="text" name="passport_number" class="form-control"
                                value="{{ old('passport_number', $client->passport_number) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ انتهاء الجواز</label>
                            <input type="date" name="passport_expiry" class="form-control"
                                value="{{ old('passport_expiry', optional($client->passport_expiry)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="section-title mt-4">إعدادات إضافية</div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="5">{{ old('notes', $client->notes) }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" id="newsletter_subscribed"
                                    name="newsletter_subscribed"
                                    {{ old('newsletter_subscribed', $client->newsletter_subscribed) ? 'checked' : '' }}>
                                <label class="form-check-label" for="newsletter_subscribed">
                                    الاشتراك في النشرة البريدية
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ
                        </button>
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
