@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('SEO Meta حسب العنصر'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
            --info-color: #0c63e4;
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

        .content-body {
            padding: 30px;
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .info-label {
            font-weight: 600;
            color: rgba(255, 255, 255, .8);
            margin-left: 5px;
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.seo-meta.index') }}">SEO Meta</a></li>
                <li class="breadcrumb-item active">حسب العنصر</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">SEO Meta حسب العنصر</h5>
                    <small class="opacity-75">Type: {{ $type }} | ID: {{ $id }}</small>
                </div>
                <a href="{{ route('admin.seo-meta.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="content-body">
                @forelse($seoMeta as $item)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1">{{ $item->meta_title ?: 'بدون عنوان' }}</h6>
                                <small class="text-light opacity-75">{{ $item->canonical_url ?: '-' }}</small>
                            </div>
                            @if ($item->locale)
                                <span class="badge-status">{{ $item->locale }}</span>
                            @endif
                        </div>

                        <div class="mb-2">
                            <span class="info-label">الوصف:</span>
                            <span>{{ $item->meta_description ?: '-' }}</span>
                        </div>

                        <div class="mb-2">
                            <span class="info-label">الكلمات المفتاحية:</span>
                            <span>{{ $item->meta_keywords ?: '-' }}</span>
                        </div>

                        <div class="mb-2">
                            <span class="info-label">OG Title:</span>
                            <span>{{ $item->og_title ?: '-' }}</span>
                        </div>

                        <div>
                            <span class="info-label">OG Description:</span>
                            <span>{{ $item->og_description ?: '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        لا توجد بيانات SEO لهذا العنصر
                    </div>
                @endforelse

                @if (method_exists($seoMeta, 'links'))
                    <div class="mt-4">
                        {{ $seoMeta->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
