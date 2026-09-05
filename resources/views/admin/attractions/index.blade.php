@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('المعالم السياحية'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
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

        .filter-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }

        .form-label {
            color: rgba(255, 255, 255, .85);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            transition: all .3s ease;
        }

        .item-card:hover {
            transform: translateY(-3px);
            background: rgba(105, 108, 255, .08);
            border-color: var(--primary-color);
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .thumb {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
        }

        .status-active {
            background: rgba(32, 201, 151, .15);
            color: var(--success-color);
            border: 1px solid rgba(32, 201, 151, .25);
        }

        .status-inactive {
            background: rgba(253, 126, 20, .15);
            color: var(--danger-color);
            border: 1px solid rgba(253, 126, 20, .25);
        }

        .status-featured {
            background: rgba(12, 99, 228, .15);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .25);
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 15px;
        }

        .detail-label {
            color: rgba(255, 255, 255, .75);
            font-weight: 600;
            margin-left: 4px;
        }

        .excerpt {
            color: rgba(255, 255, 255, .76);
            line-height: 1.8;
            margin-bottom: 15px;
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
        $totalAttractions = $attractions->total() ?? $attractions->count();
        $activeAttractions = \App\Models\Attraction::where('is_active', true)->count();
        $inactiveAttractions = \App\Models\Attraction::where('is_active', false)->count();
        $featuredAttractions = \App\Models\Attraction::where('is_featured', true)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">المعالم السياحية</h4>
            <a href="{{ route('admin.attractions.create') }}" class="btn btn-primary">إضافة معلم</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="main-card p-4">
                    <div class="fs-4 fw-bold">{{ number_format($totalAttractions) }}</div>
                    <div class="text-light opacity-75">إجمالي المعالم</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="main-card p-4">
                    <div class="fs-4 fw-bold text-success">{{ number_format($activeAttractions) }}</div>
                    <div class="text-light opacity-75">معالم مفعلة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="main-card p-4">
                    <div class="fs-4 fw-bold text-danger">{{ number_format($inactiveAttractions) }}</div>
                    <div class="text-light opacity-75">معالم غير مفعلة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="main-card p-4">
                    <div class="fs-4 fw-bold text-info">{{ number_format($featuredAttractions) }}</div>
                    <div class="text-light opacity-75">معالم مميزة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.attractions.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" name="q" class="form-control" placeholder="ابحث بالاسم أو الوصف"
                            value="{{ request('q') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">المدينة</label>
                        <select name="city_id" class="form-select">
                            <option value="">كل المدن</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ adminTrans($city->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>مفعل</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير مفعل
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة المعالم</h5>
                    <small class="opacity-75">إدارة جميع المعالم السياحية</small>
                </div>
            </div>

            <div class="p-4">
                @forelse ($attractions as $attraction)
                    <div class="item-card">
                        <div class="item-header">
                            <div class="d-flex gap-3">
                                @if ($attraction->image)
                                    <img src="{{ asset('storage/' . $attraction->image) }}" class="thumb"
                                        alt="attraction">
                                @endif

                                <div>
                                    <h6 class="mb-1">{{ adminTrans($attraction->name) ?: 'بدون اسم' }}</h6>
                                    <small class="text-light opacity-75">{{ $attraction->slug ?: '-' }}</small>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span
                                    class="badge-status {{ $attraction->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $attraction->is_active ? 'مفعل' : 'غير مفعل' }}
                                </span>

                                @if ($attraction->is_featured)
                                    <span class="badge-status status-featured">مميز</span>
                                @endif
                            </div>
                        </div>

                        <div class="excerpt">
                            {{ adminTrans($attraction->short_description) ?: 'لا يوجد وصف مختصر' }}
                        </div>

                        <div class="detail-row">
                            <div>
                                <span class="detail-label">المدينة:</span>
                                <span>{{ adminTrans(optional($attraction->city)->name) ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="detail-label">الترتيب:</span>
                                <span>{{ $attraction->sort_order ?? 0 }}</span>
                            </div>
                            <div>
                                <span class="detail-label">مميز:</span>
                                <span>{{ $attraction->is_featured ? 'نعم' : 'لا' }}</span>
                            </div>
                            <div>
                                <span class="detail-label">تاريخ الإنشاء:</span>
                                <span>{{ optional($attraction->created_at)->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.attractions.show', $attraction) }}"
                                class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.attractions.edit', $attraction) }}"
                                class="btn btn-warning btn-sm">تعديل</a>

                            <form action="{{ route('admin.attractions.destroy', $attraction) }}" method="POST"
                                onsubmit="return confirm('متأكد من الحذف؟')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">🏛️</div>
                        <h5 class="mb-3">لا توجد معالم حالياً</h5>
                        <a href="{{ route('admin.attractions.create') }}" class="btn btn-primary">إضافة معلم جديد</a>
                    </div>
                @endforelse

                @include('admin.layout.pagination', ['paginator' => $attractions])

            </div>
        </div>
    </div>
@endsection
