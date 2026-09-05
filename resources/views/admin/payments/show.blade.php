@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض دفعة'))

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
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">المدفوعات</a></li>
                <li class="breadcrumb-item active">عرض دفعة</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $payment->transaction_reference ?: 'بدون مرجع' }}</h4>
                    <small class="opacity-75">{{ $payment->gateway_reference ?: '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Booking ID</div>
                            <div class="info-value">{{ $payment->booking_id ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Payment Method ID</div>
                            <div class="info-value">{{ $payment->payment_method_id ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Amount</div>
                            <div class="info-value">{{ number_format($payment->amount, 2) }} {{ $payment->currency_code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{ $payment->status }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Payment Type</div>
                            <div class="info-value">{{ $payment->payment_type ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Paid At</div>
                            <div class="info-value">
                                {{ optional($payment->paid_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-box">
                            <div class="info-label">Gateway Payload</div>
                            <div class="info-value" style="white-space: pre-wrap;">
                                {{ is_array($payment->gateway_payload) ? json_encode($payment->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($payment->gateway_payload ?: '-') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-box">
                            <div class="info-label">Notes</div>
                            <div class="info-value">{{ $payment->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
