@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض الاستفسار'))

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
        }

        .message-box {
            white-space: pre-wrap;
            line-height: 1.9;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.inquiries.index') }}">الاستفسارات</a></li>
                <li class="breadcrumb-item active">عرض الاستفسار</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $inquiry->subject ?? 'بدون عنوان' }}</h4>
                    <small class="opacity-75">{{ $inquiry->full_name ?? $inquiry->name ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.inquiries.edit', $inquiry) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.inquiries.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ $inquiry->full_name ?? $inquiry->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">البريد الإلكتروني</div>
                            <div class="info-value">
                                @if(!empty($inquiry->email))
                                    <a href="mailto:{{ $inquiry->email }}" class="text-white text-decoration-none me-2">
                                        {{ $inquiry->email }}
                                    </a>
                                    <a href="mailto:{{ $inquiry->email }}" class="btn btn-sm btn-primary rounded-circle px-2 py-1" title="مراسلة عبر البريد">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الهاتف</div>
                            <div class="info-value">
                                @if(!empty($inquiry->phone))
                                    @php($cleanIPhone = preg_replace('/[^0-9]/', '', $inquiry->phone))
                                    <span class="dir-ltr d-inline-block font-monospace me-2">{{ $inquiry->phone }}</span>
                                    <a href="https://wa.me/{{ $cleanIPhone }}" target="_blank" class="btn btn-sm btn-success rounded-circle px-2 py-1 me-1" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp fs-6"></i>
                                    </a>
                                    <a href="tel:{{ $inquiry->phone }}" class="btn btn-sm btn-info rounded-circle px-2 py-1" title="اتصال هاتفي">
                                        <i class="fas fa-phone-alt fs-6"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الباقة</div>
                            <div class="info-value">{{ $inquiry->package->title ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $inquiry->status ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">عدد الأفراد</div>
                            <div class="info-value">{{ $inquiry->travellers_count ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">تاريخ السفر</div>
                            <div class="info-value">{{ optional($inquiry->travel_date)->translatedFormat('d M Y') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                {{ optional($inquiry->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الرسالة</div>
                            <div class="info-value message-box">{{ $inquiry->message ?? '-' }}</div>
                        </div>
                    </div>

                    @if ($inquiry->tailorMadeRequest)
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">الإقامة</div>
                                <div class="info-value">{{ $inquiry->tailorMadeRequest->accommodation_preference ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">بلد الإقامة</div>
                                <div class="info-value">{{ $inquiry->tailorMadeRequest->country_of_residence ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">تاريخ العودة</div>
                                <div class="info-value">
                                    {{ optional($inquiry->tailorMadeRequest->end_date)->translatedFormat('d M Y') ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label">الرضع</div>
                                <div class="info-value">{{ $inquiry->tailorMadeRequest->infants ?? 0 }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
