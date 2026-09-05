@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل Redirect'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .form-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: 10px;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.seo-redirects.index') }}">SEO Redirects</a></li>
                <li class="breadcrumb-item active">تعديل Redirect</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">تعديل Redirect</h5>
                    <small class="opacity-75">{{ $seoRedirect->old_path }}</small>
                </div>
                <a href="{{ route('admin.seo-redirects.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.seo-redirects.update', $seoRedirect) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="section-title">بيانات التحويل</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Old Path</label>
                            <input type="text" name="old_path" class="form-control"
                                value="{{ old('old_path', $seoRedirect->old_path) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Path</label>
                            <input type="text" name="new_path" class="form-control"
                                value="{{ old('new_path', $seoRedirect->new_path) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">HTTP Code</label>
                            <select name="http_code" class="form-select">
                                <option value="301"
                                    {{ old('http_code', $seoRedirect->http_code) == '301' ? 'selected' : '' }}>301</option>
                                <option value="302"
                                    {{ old('http_code', $seoRedirect->http_code) == '302' ? 'selected' : '' }}>302</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active" {{ old('is_active', $seoRedirect->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">فعالة</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.seo-redirects.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
