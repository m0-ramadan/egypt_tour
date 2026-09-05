@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('المدفوعات'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
            --warning-color: #ffc107;
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

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
            border-top: 4px solid var(--primary-color);
            transition: transform .3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .filter-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card:hover {
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-paid {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-pending {
            background: rgba(255, 193, 7, .2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, .3);
        }

        .status-failed {
            background: rgba(253, 126, 20, .2);
            color: #fd7e14;
            border: 1px solid rgba(253, 126, 20, .3);
        }

        .status-refunded {
            background: rgba(220, 53, 69, .2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $total = $payments->total() ?? $payments->count();
        $paid = \App\Models\Payment::where('status', 'paid')->count();
        $pending = \App\Models\Payment::where('status', 'pending')->count();
        $sumPaid = \App\Models\Payment::where('status', 'paid')->sum('amount');
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">المدفوعات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;"><i
                            class="fas fa-credit-card"></i></div>
                    <div class="stats-number">{{ number_format($total) }}</div>
                    <div class="stats-label">إجمالي المدفوعات</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);"><i
                            class="fas fa-circle-check"></i></div>
                    <div class="stats-number">{{ number_format($paid) }}</div>
                    <div class="stats-label">مدفوعات ناجحة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);"><i
                            class="fas fa-hourglass-half"></i></div>
                    <div class="stats-number">{{ number_format($pending) }}</div>
                    <div class="stats-label">مدفوعات معلقة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);"><i
                            class="fas fa-money-bill-wave"></i></div>
                    <div class="stats-number">{{ number_format($sumPaid, 2) }}</div>
                    <div class="stats-label">إجمالي المحصل</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.payments.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" class="form-control" name="q" value="{{ request('q') }}"
                            placeholder="بحث بالمرجع أو الحالة أو العملة">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>paid</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>pending</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>failed</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>refunded
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">العملة</label>
                        <input type="text" class="form-control" name="currency_code"
                            value="{{ request('currency_code') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">نوع الدفع</label>
                        <input type="text" class="form-control" name="payment_type"
                            value="{{ request('payment_type') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة المدفوعات</h5>
                    <small class="opacity-75">إدارة جميع العمليات المالية</small>
                </div>
                <a href="{{ route('admin.payments.create') }}" class="btn btn-light">إضافة دفعة</a>
            </div>

            <div class="p-4">
                @forelse($payments as $payment)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $payment->transaction_reference ?: 'بدون مرجع' }}</h6>
                                <small class="text-light opacity-75">{{ $payment->gateway_reference ?: '-' }}</small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge-status status-{{ $payment->status }}">
                                    {{ $payment->status }}
                                </span>
                                <span class="badge-status"
                                    style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                                    {{ number_format($payment->amount, 2) }} {{ $payment->currency_code }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Booking:</strong> {{ $payment->booking_id ?? '-' }}</div>
                            <div class="col-md-3"><strong>Method:</strong> {{ $payment->payment_method_id ?? '-' }}</div>
                            <div class="col-md-3"><strong>Type:</strong> {{ $payment->payment_type ?? '-' }}</div>
                            <div class="col-md-3"><strong>Paid At:</strong>
                                {{ optional($payment->paid_at)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-warning btn-sm">تعديل</a>

                            @if (Route::has('admin.payments.refund'))
                                <form action="{{ route('admin.payments.refund', $payment) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-secondary btn-sm" type="submit">Refund</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد مدفوعات حالياً</div>
                @endforelse

                @if (method_exists($payments, 'links'))
                    <div class="mt-4">{{ $payments->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
