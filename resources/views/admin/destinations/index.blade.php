@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إدارة الوجهات'))

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
            color: white;
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

        .status-featured {
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
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
        $totalDestinations = $destinations->total() ?? $destinations->count();
        $activeDestinations = \App\Models\Destination::where('is_active', true)->count();
        $featuredDestinations = \App\Models\Destination::where('is_featured', true)->count();
        $inactiveDestinations = \App\Models\Destination::where('is_active', false)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الوجهات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;"><i
                            class="fas fa-map-marked-alt"></i></div>
                    <div class="stats-number">{{ number_format($totalDestinations) }}</div>
                    <div class="stats-label">إجمالي الوجهات</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);"><i
                            class="fas fa-check-circle"></i></div>
                    <div class="stats-number">{{ number_format($activeDestinations) }}</div>
                    <div class="stats-label">وجهات مفعلة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);"><i
                            class="fas fa-star"></i></div>
                    <div class="stats-number">{{ number_format($featuredDestinations) }}</div>
                    <div class="stats-label">وجهات مميزة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);"><i
                            class="fas fa-ban"></i></div>
                    <div class="stats-number">{{ number_format($inactiveDestinations) }}</div>
                    <div class="stats-label">وجهات غير مفعلة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.destinations.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                placeholder="ابحث باسم الوجهة أو الـ slug">
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
                        <a href="{{ route('admin.destinations.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة الوجهات</h5>
                    <small class="opacity-75">إدارة جميع وجهات السفر</small>
                </div>
                <a href="{{ route('admin.destinations.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>إضافة وجهة
                </a>
            </div>

            <div class="p-4">
                @forelse($destinations as $destination)
                    <div class="item-card">
                        <div class="item-header">
                            <div>
                                <h6 class="mb-1">{{ adminTrans($destination->name) ?: 'بدون اسم' }}</h6>
                                <small class="text-light opacity-75">{{ $destination->slug ?? '-' }}</small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span
                                    class="badge-status {{ $destination->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                    {{ $destination->is_active ?? true ? 'مفعلة' : 'غير مفعلة' }}
                                </span>
                                @if ($destination->is_featured ?? false)
                                    <span class="badge-status status-featured">مميزة</span>
                                @endif
                            </div>
                        </div>

                        <div class="detail-row">
                            <div>
                                <span class="detail-label">الدولة:</span>
                                <span>{{ adminTrans(optional($destination->country)->name) ?: '-' }}</span>
                            </div>

                            <div>
                                <span class="detail-label">المدينة:</span>
                                <span>{{ adminTrans(optional($destination->city)->name) ?: '-' }}</span>
                            </div>

                            <div>
                                <span class="detail-label">الترتيب:</span>
                                <span>{{ $destination->sort_order ?? 0 }}</span>
                            </div>

                            <div>
                                <span class="detail-label">الإنشاء:</span>
                                <span>{{ optional($destination->created_at)->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.destinations.show', $destination) }}"
                                class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.destinations.edit', $destination) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                            @if (Route::has('admin.destinations.toggle-status'))
                                <form action="{{ route('admin.destinations.toggle-status', $destination) }}"
                                    method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm">تبديل الحالة</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-map-marked-alt"></i></div>
                        <h5 class="empty-state-text">لا توجد وجهات حالياً</h5>
                        <a href="{{ route('admin.destinations.create') }}" class="btn btn-primary">إضافة وجهة جديدة</a>
                    </div>
                @endforelse
                <!-- Pagination -->
                @include('admin.layout.pagination', ['paginator' => $destinations])


            </div>
        </div>
    </div>
@endsection
