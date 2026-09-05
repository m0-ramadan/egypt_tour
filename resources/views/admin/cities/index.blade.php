@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إدارة المدن'))

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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border-top: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            margin-bottom: 5px;
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

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-right: 40px;
            border-radius: 25px;
            background: rgba(255, 255, 255, .05);
            border-color: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .search-box input:focus {
            background: rgba(255, 255, 255, .1);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, .5);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all .3s ease;
            border-right: 4px solid transparent;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, .3);
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-active {
            background: linear-gradient(135deg, rgba(21, 87, 36, .2) 0%, rgba(32, 201, 151, .2) 100%);
            color: var(--success-color);
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-inactive {
            background: linear-gradient(135deg, rgba(220, 53, 69, .2) 0%, rgba(253, 126, 20, .2) 100%);
            color: var(--danger-color);
            border: 1px solid rgba(253, 126, 20, .3);
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: rgba(255, 255, 255, .8);
            margin-left: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state-icon {
            font-size: 60px;
            color: rgba(255, 255, 255, .1);
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    @php
        $totalCities = $cities->total() ?? $cities->count();
        $activeCities = \App\Models\City::where('is_active', true)->count();
        $inactiveCities = \App\Models\City::where('is_active', false)->count();
        $featuredCities = \App\Models\City::where('is_featured', true)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">المدن</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalCities) }}</div>
                    <div class="stats-label">إجمالي المدن</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activeCities) }}</div>
                    <div class="stats-label">مدن مفعلة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stats-number">{{ number_format($inactiveCities) }}</div>
                    <div class="stats-label">مدن غير مفعلة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-number">{{ number_format($featuredCities) }}</div>
                    <div class="stats-label">مدن مميزة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.cities.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                placeholder="ابحث باسم المدينة أو الدولة">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الدولة</label>
                        <select name="country_id" class="form-select">
                            <option value="">كل الدول</option>
                            @foreach ($countries ?? collect() as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ adminTrans($country->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مفعل</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير مفعل
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة المدن</h5>
                    <small class="opacity-75">إدارة جميع المدن داخل النظام</small>
                </div>
                <a href="{{ route('admin.cities.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>إضافة مدينة
                </a>
            </div>

            <div class="p-4">
                @forelse($cities as $city)
                    <div class="item-card">
                        <div class="item-header">
                            <div>
                                <h6 class="mb-1">{{ adminTrans($city->name) ?: 'بدون اسم' }}</h6>
                                <small class="text-light opacity-75">{{ $city->slug ?? '-' }}</small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span
                                    class="badge-status {{ $city->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                    {{ $city->is_active ?? true ? 'مفعلة' : 'غير مفعلة' }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div>
                                <span class="detail-label">الدولة:</span>
                                <span>{{ adminTrans(optional($city->country)->name) ?: '-' }}</span>
                            </div>

                            <div>
                                <span class="detail-label">الترتيب:</span>
                                <span>{{ $city->sort_order ?? 0 }}</span>
                            </div>

                            <div>
                                <span class="detail-label">مميزة:</span>
                                <span>{{ $city->is_featured ?? false ? 'نعم' : 'لا' }}</span>
                            </div>

                            <div>
                                <span class="detail-label">تاريخ الإنشاء:</span>
                                <span>{{ optional($city->created_at)->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.cities.show', $city) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye me-1"></i>عرض
                            </a>
                            <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i>تعديل
                            </a>
                            @if (Route::has('admin.cities.toggle-status'))
                                <form action="{{ route('admin.cities.toggle-status', $city) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-power-off me-1"></i>تبديل الحالة
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-city"></i>
                        </div>
                        <h5 class="empty-state-text">لا توجد مدن حالياً</h5>
                        <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">
                            إضافة مدينة جديدة
                        </a>
                    </div>
                @endforelse

                @if (method_exists($cities, 'links'))
                    <div class="mt-4">
                        {{ $cities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
