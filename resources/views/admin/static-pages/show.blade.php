@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض الصفحة'))

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

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: 10px;
        }

        .info-box {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            height: 100%;
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 8px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
            line-height: 1.8;
            word-break: break-word;
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
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

        .status-home {
            background: rgba(12, 99, 228, .2);
            color: #9ec5fe;
            border: 1px solid rgba(12, 99, 228, .35);
        }

        .featured-image-box {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 14px;
            overflow: hidden;
        }

        .featured-image-box img {
            width: 100%;
            max-height: 380px;
            object-fit: cover;
            display: block;
        }

        .featured-image-empty {
            padding: 40px 20px;
            text-align: center;
            color: rgba(255, 255, 255, .6);
        }

        .page-content-box {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 14px;
            padding: 24px;
        }

        .page-rendered-content {
            color: #fff;
            line-height: 1.9;
            font-size: 15px;
        }

        .page-rendered-content h1,
        .page-rendered-content h2,
        .page-rendered-content h3,
        .page-rendered-content h4,
        .page-rendered-content h5,
        .page-rendered-content h6 {
            color: #fff;
            margin-top: 20px;
            margin-bottom: 12px;
        }

        .page-rendered-content p,
        .page-rendered-content li,
        .page-rendered-content blockquote {
            color: rgba(255, 255, 255, .92);
        }

        .page-rendered-content a {
            color: #9ec5fe;
        }

        .page-rendered-content table {
            width: 100%;
            margin-bottom: 1rem;
            color: #fff;
            border-collapse: collapse;
        }

        .page-rendered-content table th,
        .page-rendered-content table td {
            border: 1px solid rgba(255, 255, 255, .12);
            padding: 10px;
        }

        .page-rendered-content blockquote {
            border-inline-start: 4px solid var(--primary-color);
            padding: 10px 15px;
            background: rgba(255, 255, 255, .04);
            border-radius: 8px;
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
    </style>
@endsection

@section('content')
    @php
        $pageTitle = adminTrans($page->title ?? null);
        $pageBody = adminTrans($page->body ?? ($page->content ?? null));
        $pageSeoTitle = adminTrans($page->seo_title ?? ($page->meta_title ?? null));
        $pageSeoDescription = adminTrans($page->seo_description ?? ($page->meta_description ?? null));
        $pageTemplate = $page->template ?? 'default';
        $pageFeaturedImage = asset('storage/' . $page->featured_image) ?? null;
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.static-pages.index') }}">الصفحات الثابتة</a></li>
                <li class="breadcrumb-item active">عرض الصفحة</li>
            </ol>
        </nav>

        <div class="panel-card mb-4">
            <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">{{ $pageTitle ?: 'بدون عنوان' }}</h5>
                    <small class="opacity-75">Slug: {{ $page->slug ?? '-' }}</small>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.static-pages.edit', $page) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.static-pages.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="panel-body">
                <div class="section-title">معلومات الصفحة</div>

                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <div class="info-label">العنوان</div>
                            <div class="info-value">{{ $pageTitle ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <div class="info-label">الرابط المختصر</div>
                            <div class="info-value">{{ $page->slug ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <div class="info-label">القالب</div>
                            <div class="info-value">{{ $pageTemplate ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">
                                @if ($page->is_active ?? false)
                                    <span class="badge-status status-active">مفعلة</span>
                                @else
                                    <span class="badge-status status-inactive">غير مفعلة</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <div class="info-label">الصفحة الرئيسية</div>
                            <div class="info-value">
                                @if ($page->is_home ?? false)
                                    <span class="badge-status status-home">نعم</span>
                                @else
                                    <span class="badge-status status-inactive">لا</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-box">
                            <div class="info-label">تاريخ النشر</div>
                            <div class="info-value">
                                {{ !empty($page->published_at) ? \Carbon\Carbon::parse($page->published_at)->format('Y-m-d h:i A') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-title">الصورة البارزة</div>

                <div class="featured-image-box mb-4">
                    @if (!empty($pageFeaturedImage))
                        <img src="{{ $pageFeaturedImage }}" alt="{{ $pageTitle ?: 'Page image' }}">
                    @else
                        <div class="featured-image-empty">
                            لا توجد صورة بارزة
                        </div>
                    @endif
                </div>

                <div class="section-title">المحتوى</div>

                <div class="page-content-box mb-4">
                    <div class="page-rendered-content">
                        {!! $pageBody ?: '<p>-</p>' !!}
                    </div>
                </div>

                <div class="section-title">SEO</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-box">
                            <div class="info-label">عنوان الميتا</div>
                            <div class="info-value">{{ $pageSeoTitle ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="info-box">
                            <div class="info-label">وصف الميتا</div>
                            <div class="info-value">{{ $pageSeoDescription ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="section-title">معلومات إضافية</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-box">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                {{ !empty($page->created_at) ? $page->created_at->format('Y-m-d h:i A') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="info-box">
                            <div class="info-label">آخر تحديث</div>
                            <div class="info-value">
                                {{ !empty($page->updated_at) ? $page->updated_at->format('Y-m-d h:i A') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
