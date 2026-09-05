@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل وسيلة تواصل'))

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
                <li class="breadcrumb-item"><a href="{{ route('admin.social-media.index') }}">وسائل التواصل</a></li>
                <li class="breadcrumb-item active">تعديل</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">تعديل وسيلة التواصل</h5>
                    <small class="opacity-75">{{ $socialMedium->platform ?? ($socialMedium->name ?? '') }}</small>
                </div>
                <a href="{{ route('admin.social-media.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.social-media.update', $socialMedium->id ?? $socialMedium->getKey()) }}"
                    method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم المنصة</label>
                            <input type="text" name="platform" class="form-control"
                                value="{{ old('platform', $socialMedium->platform ?? $socialMedium->name) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الرابط</label>
                            <input type="text" name="url" class="form-control"
                                value="{{ old('url', $socialMedium->url) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الأيقونة</label>
                            <input type="text" name="icon" class="form-control"
                                value="{{ old('icon', $socialMedium->icon) }}" placeholder="fab fa-facebook-f">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $socialMedium->sort_order ?? 0) }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active"
                                    {{ old('is_active', $socialMedium->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">مفعل</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.social-media.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
