@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل دفعة'))

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

        .main-card {
            background: var(--dark-card);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .form-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: 10px;
        }

        .form-control,
        .form-select,
        textarea {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">المدفوعات</a></li>
                <li class="breadcrumb-item active">تعديل دفعة</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">تعديل بيانات الدفع</h5>
                    <small class="opacity-75">{{ $payment->transaction_reference ?: 'بدون مرجع' }}</small>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="section-title">بيانات الدفع</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Booking ID</label>
                            <input type="number" name="booking_id" class="form-control"
                                value="{{ old('booking_id', $payment->booking_id) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method ID</label>
                            <input type="number" name="payment_method_id" class="form-control"
                                value="{{ old('payment_method_id', $payment->payment_method_id) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transaction Reference</label>
                            <input type="text" name="transaction_reference" class="form-control"
                                value="{{ old('transaction_reference', $payment->transaction_reference) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gateway Reference</label>
                            <input type="text" name="gateway_reference" class="form-control"
                                value="{{ old('gateway_reference', $payment->gateway_reference) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control"
                                value="{{ old('amount', $payment->amount) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency Code</label>
                            <input type="text" name="currency_code" class="form-control"
                                value="{{ old('currency_code', $payment->currency_code) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending"
                                    {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>pending</option>
                                <option value="paid" {{ old('status', $payment->status) == 'paid' ? 'selected' : '' }}>
                                    paid</option>
                                <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>
                                    failed</option>
                                <option value="refunded"
                                    {{ old('status', $payment->status) == 'refunded' ? 'selected' : '' }}>refunded</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Type</label>
                            <input type="text" name="payment_type" class="form-control"
                                value="{{ old('payment_type', $payment->payment_type) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Paid At</label>
                            <input type="date" name="paid_at" class="form-control"
                                value="{{ old('paid_at', optional($payment->paid_at)->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Gateway Payload</label>
                            <textarea name="gateway_payload" class="form-control" rows="4">{{ old('gateway_payload', is_array($payment->gateway_payload) ? json_encode($payment->gateway_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $payment->gateway_payload) }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="4">{{ old('notes', $payment->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
