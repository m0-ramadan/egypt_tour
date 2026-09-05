@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('وسائل الدفع'))

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
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
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
            transition: all .3s ease;
        }

        .item-card:hover {
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
            transform: translateX(-5px);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-active {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-inactive {
            background: rgba(253, 126, 20, .2);
            color: #fd7e14;
            border: 1px solid rgba(253, 126, 20, .3);
        }

        .status-default {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $totalMethods = $paymentMethods->total() ?? $paymentMethods->count();
        $activeMethods = \App\Models\PaymentMethod::where('is_active', true)->count();
        $inactiveMethods = \App\Models\PaymentMethod::where('is_active', false)->count();
        $defaultMethods = \App\Models\PaymentMethod::where('is_default', true)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">وسائل الدفع</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalMethods) }}</div>
                    <div class="stats-label">إجمالي الوسائل</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activeMethods) }}</div>
                    <div class="stats-label">مفعلة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stats-number">{{ number_format($inactiveMethods) }}</div>
                    <div class="stats-label">غير مفعلة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-number">{{ number_format($defaultMethods) }}</div>
                    <div class="stats-label">افتراضية</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.payment-methods.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="ابحث بالاسم أو النوع">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مفعل</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير مفعل
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">افتراضي</label>
                        <select name="default" class="form-select">
                            <option value="">الكل</option>
                            <option value="1" {{ request('default') == '1' ? 'selected' : '' }}>نعم</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة وسائل الدفع</h5>
                    <small class="opacity-75">إدارة بوابات وطرق الدفع</small>
                </div>
                <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>إضافة وسيلة
                </a>
            </div>

            <div class="p-4">
                @forelse($paymentMethods as $paymentMethod)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ adminTrans($paymentMethod->name) ?: 'بدون اسم' }}</h6>
                                <small class="text-light opacity-75">{{ $paymentMethod->type ?? '-' }}</small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span
                                    class="badge-status {{ $paymentMethod->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                    {{ $paymentMethod->is_active ?? true ? 'مفعل' : 'غير مفعل' }}
                                </span>

                                @if ($paymentMethod->is_default ?? false)
                                    <span class="badge-status status-default">افتراضي</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3"><strong>النوع:</strong> {{ $paymentMethod->type ?? '-' }}</div>
                            <div class="col-md-3"><strong>العملة:</strong> {{ $paymentMethod->currency_code ?? '-' }}</div>
                            <div class="col-md-3"><strong>الترتيب:</strong> {{ $paymentMethod->sort_order ?? 0 }}</div>
                            <div class="col-md-3"><strong>آخر تحديث:</strong>
                                {{ optional($paymentMethod->updated_at)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.payment-methods.show', $paymentMethod) }}"
                                class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.payment-methods.edit', $paymentMethod) }}"
                                class="btn btn-warning btn-sm">تعديل</a>

                            @if (Route::has('admin.payment-methods.toggle-status'))
                                <form action="{{ route('admin.payment-methods.toggle-status', $paymentMethod) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-dark btn-sm" type="submit">تبديل الحالة</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.payment-methods.destroy', $paymentMethod) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد وسائل دفع حالياً</div>
                @endforelse

                @if (method_exists($paymentMethods, 'links'))
                    <div class="mt-4">{{ $paymentMethods->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
