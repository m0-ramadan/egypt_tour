@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض القائمة'))

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
            white-space: pre-wrap;
        }

        .item-list {
            background: rgba(255, 255, 255, .04);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, .08);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">القوائم</a></li>
                <li class="breadcrumb-item active">عرض القائمة</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ adminTrans($menu->name) ?: 'بدون اسم' }}</h4>
                    <small class="opacity-75">{{ $menu->slug ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-light">تعديل</a>
                    @if (Route::has('admin.menus.items'))
                        <a href="{{ route('admin.menus.items', $menu) }}" class="btn btn-light">العناصر</a>
                    @endif
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">اسم القائمة</div>
                            <div class="info-value">{{ adminTrans($menu->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Slug</div>
                            <div class="info-value">{{ $menu->slug ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الموقع</div>
                            <div class="info-value">{{ $menu->location ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">اللغة</div>
                            <div class="info-value">{{ $menu->language->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $menu->is_active ?? true ? 'مفعل' : 'غير مفعل' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $menu->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف</div>
                            <div class="info-value">{{ adminTrans($menu->description) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <h5 class="mb-3">عناصر القائمة</h5>

                        @forelse($menu->items ?? [] as $item)
                            <div class="item-list">
                                <div><strong>العنوان:</strong> {{ adminTrans($item->title) ?: '-' }}</div>
                                <div><strong>الرابط:</strong> {{ $item->url ?? '-' }}</div>
                                <div><strong>الترتيب:</strong> {{ $item->sort_order ?? 0 }}</div>
                            </div>
                        @empty
                            <div class="info-box">
                                <div class="info-value">لا توجد عناصر لهذه القائمة</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
