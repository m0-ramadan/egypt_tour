@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('أسعار الباقات'))

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
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $totalPrices = $packagePrices->total() ?? $packagePrices->count();
        $activePrices = \App\Models\PackagePrice::where(function ($q) {
            $q->whereNull('valid_to')->orWhere('valid_to', '>=', now()->toDateString());
        })->count();
        $currenciesCount = \App\Models\PackagePrice::distinct('currency_id')->count('currency_id');
        $avgPrice = \App\Models\PackagePrice::avg('amount');
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">أسعار الباقات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalPrices) }}</div>
                    <div class="stats-label">إجمالي الأسعار</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activePrices) }}</div>
                    <div class="stats-label">أسعار فعالة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stats-number">{{ number_format($currenciesCount) }}</div>
                    <div class="stats-label">عدد العملات</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-money-bill-trend-up"></i>
                    </div>
                    <div class="stats-number">{{ number_format($avgPrice ?? 0, 2) }}</div>
                    <div class="stats-label">متوسط السعر</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.package-prices.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">الباقة</label>
                        <select name="package_id" class="form-select">
                            <option value="">كل الباقات</option>
                            @foreach ($packages ?? collect() as $package)
                                <option value="{{ $package->id }}"
                                    {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">نوع السعر</label>
                        <input type="text" class="form-control" name="price_type" value="{{ request('price_type') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الغرفة</label>
                        <input type="text" class="form-control" name="room_type" value="{{ request('room_type') }}">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.package-prices.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة أسعار الباقات</h5>
                    <small class="opacity-75">إدارة جميع الأسعار الموسمية والأساسية</small>
                </div>
                <a href="{{ route('admin.package-prices.create') }}" class="btn btn-light">إضافة سعر</a>
            </div>

            <div class="p-4">
                @forelse($packagePrices as $price)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $price->package->name ?? '-' }}</h6>
                                <small class="text-light opacity-75">{{ $price->label ?? '-' }}</small>
                            </div>

                            <span class="badge-status">
                                {{ number_format($price->amount ?? 0, 2) }} {{ $price->currency->code ?? '-' }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Season:</strong> {{ $price->season_name ?? '-' }}</div>
                            <div class="col-md-3"><strong>Type:</strong> {{ $price->price_type ?? '-' }}</div>
                            <div class="col-md-3"><strong>Room:</strong> {{ $price->room_type ?? '-' }}</div>
                            <div class="col-md-3"><strong>Pax:</strong> {{ $price->pax_min ?? '-' }} -
                                {{ $price->pax_max ?? '-' }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"><strong>Valid From:</strong>
                                {{ optional($price->valid_from)->translatedFormat('d M Y') ?? '-' }}</div>
                            <div class="col-md-4"><strong>Valid To:</strong>
                                {{ optional($price->valid_to)->translatedFormat('d M Y') ?? '-' }}</div>
                            <div class="col-md-4"><strong>Group:</strong> {{ $price->group_size_min ?? '-' }} -
                                {{ $price->group_size_max ?? '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <strong>Notes:</strong> {{ $price->notes ?: '-' }}
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.package-prices.show', $price) }}" class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.package-prices.edit', $price) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                            <form action="{{ route('admin.package-prices.destroy', $price) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد أسعار حالياً</div>
                @endforelse

                @if (method_exists($packagePrices, 'links'))
                    <div class="mt-4">{{ $packagePrices->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
