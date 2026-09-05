@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض Redirect'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
            --success-color: #20c997;
            --danger-color: #fd7e14;
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
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.seo-redirects.index') }}">SEO Redirects</a></li>
                <li class="breadcrumb-item active">عرض Redirect</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $seoRedirect->old_path }}</h4>
                    <small class="opacity-75">{{ $seoRedirect->new_path }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.seo-redirects.edit', $seoRedirect) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.seo-redirects.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Old Path</div>
                            <div class="info-value">{{ $seoRedirect->old_path }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">New Path</div>
                            <div class="info-value">{{ $seoRedirect->new_path }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">HTTP Code</div>
                            <div class="info-value">{{ $seoRedirect->http_code }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $seoRedirect->is_active ?? true ? 'فعالة' : 'غير فعالة' }}</div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-box">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                {{ optional($seoRedirect->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
