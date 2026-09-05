@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('وسائل التواصل الاجتماعي'))

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
    </style>
@endsection

@section('content')
    @php
        $totalLinks = $socialMedia->count() ?? 0;
        $activeLinks = collect($socialMedia ?? [])
            ->where('is_active', true)
            ->count();
        $inactiveLinks = collect($socialMedia ?? [])
            ->where('is_active', false)
            ->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">وسائل التواصل الاجتماعي</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalLinks) }}</div>
                    <div class="stats-label">إجمالي المنصات</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activeLinks) }}</div>
                    <div class="stats-label">روابط مفعلة</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stats-number">{{ number_format($inactiveLinks) }}</div>
                    <div class="stats-label">روابط غير مفعلة</div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة وسائل التواصل</h5>
                    <small class="opacity-75">إدارة روابط المنصات الاجتماعية للموقع</small>
                </div>

                <a href="{{ route('admin.social-media.create') }}" class="btn btn-light">
                    <i class="fas fa-plus"></i> إضافة جديد
                </a>
            </div>
            <div class="p-4">
                @forelse($socialMedia as $item)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $item->platform ?? ($item->name ?? 'بدون اسم') }}</h6>
                                <small class="text-light opacity-75">{{ $item->url ?? '-' }}</small>
                            </div>

                            <span class="badge-status {{ $item->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                {{ $item->is_active ?? true ? 'مفعل' : 'غير مفعل' }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"><strong>الأيقونة:</strong> {{ $item->icon ?? '-' }}</div>
                            <div class="col-md-4"><strong>الترتيب:</strong> {{ $item->sort_order ?? 0 }}</div>
                            <div class="col-md-4"><strong>آخر تحديث:</strong>
                                {{ optional($item->updated_at)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.social-media.edit', $item->id ?? $item->getKey()) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد روابط تواصل حالياً</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
