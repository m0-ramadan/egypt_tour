@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('الصفحات الثابتة'))

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

        .panel-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .panel-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .panel-body {
            padding: 30px;
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

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
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

        .form-label {
            color: rgba(255, 255, 255, .85);
            font-weight: 600;
            margin-bottom: 8px;
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
        }

        .item-row {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, .3);
            color: #fff;
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

        .empty-state-text {
            color: rgba(255, 255, 255, .7);
            margin-bottom: 20px;
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
    </style>

@endsection

@section('content')
    @php
        $total = $pages->total() ?? $pages->count();
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الصفحات الثابتة</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-4 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;"><i
                            class="fas fa-file-lines"></i></div>
                    <div class="stats-number">{{ number_format($total) }}</div>
                    <div class="stats-label">إجمالي الصفحات</div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة الصفحات الثابتة</h5>
                    <small class="opacity-75">إدارة محتوى الصفحات العامة</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.static-pages.create') }}" class="btn btn-light">إضافة صفحة</a>
                </div>
            </div>
            <div class="panel-body">
                @forelse($pages as $page)
                    <div class="item-row">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h6 class="mb-2">{{ adminTrans($page->title) ?: 'بدون عنوان' }}</h6>
                                <div class="mb-2">Slug: {{ $page->slug ?? '-' }}</div>
                                <small
                                    class="text-light opacity-75">{{ \Illuminate\Support\Str::limit(adminTrans($page->content) ?: '-', 140) }}</small>
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a href="{{ route('admin.static-pages.show', $page) }}" class="btn btn-sm btn-info">عرض</a>
                                <a href="{{ route('admin.static-pages.edit', $page) }}"
                                    class="btn btn-sm btn-warning">تعديل</a>
                                <a href="{{ route('admin.static-pages.edit-with-ai', $page) }}"
                                    class="btn btn-sm btn-secondary">AI</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-file-lines"></i></div>
                        <h5 class="empty-state-text">لا توجد صفحات</h5>
                    </div>
                @endforelse
                <div class="mt-3">{{ $pages->links() }}</div>
            </div>
        </div>
    </div>
@endsection
