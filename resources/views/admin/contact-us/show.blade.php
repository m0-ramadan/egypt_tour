@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض رسالة تواصل'))

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
                <li class="breadcrumb-item"><a href="{{ route('admin.contact-us.index') }}">تواصل معنا</a></li>
                <li class="breadcrumb-item active">عرض الرسالة</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $contactUs->subject ?? 'بدون عنوان' }}</h4>
                    <small class="opacity-75">{{ $contactUs->name ?? '-' }}</small>
                </div>
                <a href="{{ route('admin.contact-us.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ $contactUs->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">البريد الإلكتروني</div>
                            <div class="info-value">
                                @if(!empty($contactUs->email))
                                    <a href="mailto:{{ $contactUs->email }}" class="text-white text-decoration-none me-2">
                                        {{ $contactUs->email }}
                                    </a>
                                    <a href="mailto:{{ $contactUs->email }}" class="btn btn-sm btn-primary rounded-circle px-2 py-1" title="مراسلة عبر البريد">
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
                                @if(!empty($contactUs->phone))
                                    @php($cleanCPhone = preg_replace('/[^0-9]/', '', $contactUs->phone))
                                    <span class="dir-ltr d-inline-block font-monospace me-2">{{ $contactUs->phone }}</span>
                                    <a href="https://wa.me/{{ $cleanCPhone }}" target="_blank" class="btn btn-sm btn-success rounded-circle px-2 py-1 me-1" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp fs-6"></i>
                                    </a>
                                    <a href="tel:{{ $contactUs->phone }}" class="btn btn-sm btn-info rounded-circle px-2 py-1" title="اتصال هاتفي">
                                        <i class="fas fa-phone-alt fs-6"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $contactUs->status ?? 'new' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">تاريخ الإرسال</div>
                            <div class="info-value">
                                {{ optional($contactUs->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الرسالة</div>
                            <div class="info-value message-box">{{ $contactUs->message ?? '-' }}</div>
                        </div>
                    </div>

                    @if (!empty($contactUs->reply_message))
                        <div class="col-12">
                            <div class="info-box">
                                <div class="info-label">الرد</div>
                                <div class="info-value message-box">{{ $contactUs->reply_message }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if (Route::has('admin.contact-us.reply'))
                    <div class="mt-4">
                        <form action="{{ route('admin.contact-us.reply', $contactUs) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الرد على الرسالة</label>
                                <textarea name="reply_message" class="form-control" rows="5">{{ old('reply_message') }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit">إرسال الرد</button>
                                <a href="{{ route('admin.contact-us.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
