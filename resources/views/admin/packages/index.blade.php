@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إدارة الباقات'))

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
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card:hover {
            transform: translateX(-5px);
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
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

        .status-featured {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
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
        $totalPackages = $packages->total() ?? $packages->count();
        $activePackages = \App\Models\Package::where('is_active', true)->count();
        $featuredPackages = \App\Models\Package::where('is_featured', true)->count();
        $draftPackages = \App\Models\Package::where('is_active', false)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الباقات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-suitcase"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalPackages) }}</div>
                    <div class="stats-label">إجمالي الباقات</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activePackages) }}</div>
                    <div class="stats-label">باقات مفعلة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-number">{{ number_format($featuredPackages) }}</div>
                    <div class="stats-label">باقات مميزة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-file-circle-xmark"></i>
                    </div>
                    <div class="stats-number">{{ number_format($draftPackages) }}</div>
                    <div class="stats-label">غير مفعلة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.packages.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                placeholder="ابحث باسم الرحلة أو الـ slug">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select">
                            <option value="">كل التصنيفات</option>
                            @foreach ($categories ?? collect() as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ adminTrans($category->name) }}
                                </option>
                            @endforeach
                        </select>
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

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة الباقات</h5>
                    <small class="opacity-75">إدارة جميع باقات السفر</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.packages.create-with-ai') }}" class="btn btn-light">
                        <i class="fas fa-wand-magic-sparkles me-2"></i>إنشاء بالذكاء الاصطناعي
                    </a>
                    <a href="{{ route('admin.packages.create') }}" class="btn btn-light">
                        <i class="fas fa-plus me-2"></i>إضافة رحلة
                    </a>
                </div>
            </div>

            <div class="p-4">
                @forelse($packages as $package)
                    <div class="item-card">
                        <div class="item-header">
                            <div>
                                <h6 class="mb-1">{{ adminTrans($package->name) ?: 'بدون اسم' }}</h6>
                                <small class="text-light opacity-75">{{ $package->slug ?? '-' }}</small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span
                                    class="badge-status {{ $package->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                    {{ $package->is_active ?? true ? 'مفعلة' : 'غير مفعلة' }}
                                </span>
                                @if ($package->is_featured ?? false)
                                    <span class="badge-status status-featured">مميزة</span>
                                @endif
                            </div>
                        </div>

                        <div class="detail-row">
                            <div>
                                <span class="detail-label">التصنيف:</span>
                                <span>{{ adminTrans(optional($package->category)->name) ?: '-' }}</span>
                            </div>

                            <div>
                                <span class="detail-label">المدة:</span>
                                <span>{{ $package->duration_days ?? '-' }} يوم</span>
                            </div>

                            <div>
                                <span class="detail-label">العملة:</span>
                                <span>{{ $package->currency->code ?? '-' }}</span>
                            </div>

                            <div>
                                <span class="detail-label">السعر الأساسي:</span>
                                <span>{{ number_format($package->base_price ?? 0, 2) }}</span>
                            </div>

                            <div>
                                <span class="detail-label">المدينة:</span>
                                <span>{{ adminTrans(optional(optional($package->destination)->city)->name) ?: (adminTrans(optional($package->destination)->name) ?: '-') }}</span>
                            </div>

                            <div>
                                <span class="detail-label">الترتيب:</span>
                                <span>{{ $package->sort_order ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="detail-label">الوصف:</span>
                            <span>{{ \Illuminate\Support\Str::limit(adminTrans($package->short_description) ?: (adminTrans($package->description) ?: '-'), 180) }}</span>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-warning btn-sm">تعديل</a>

                            @if (Route::has('admin.package-prices.by-package'))
                                <a href="{{ route('admin.package-prices.by-package', $package) }}"
                                    class="btn btn-secondary btn-sm">
                                    الأسعار
                                </a>
                            @endif

                            @if (Route::has('admin.packages.toggle-status'))
                                <form action="{{ route('admin.packages.toggle-status', $package) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-dark btn-sm" type="submit">تبديل الحالة</button>
                                </form>
                            @endif

                            @if (Route::has('admin.packages.duplicate'))
                                <form action="{{ route('admin.packages.duplicate', $package) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-primary btn-sm" type="submit">نسخ</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.packages.destroy', $package) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-suitcase"></i>
                        </div>
                        <h5 class="empty-state-text">لا توجد باقات حالياً</h5>
                        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">إضافة رحلة جديدة</a>
                    </div>
                @endforelse

                @include('admin.layout.pagination', ['paginator' => $packages])

            </div>
        </div>
    </div>
@endsection
