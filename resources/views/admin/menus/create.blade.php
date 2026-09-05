@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة قائمة'))

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
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">القوائم</a></li>
                <li class="breadcrumb-item active">إضافة قائمة</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">إضافة قائمة جديدة</h5>
                    <small class="opacity-75">إدخال بيانات القائمة</small>
                </div>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.menus.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم القائمة</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الموقع</label>
                            <select name="location" class="form-select">
                                <option value="">اختر الموقع</option>
                                <option value="header" {{ old('location') == 'header' ? 'selected' : '' }}>header</option>
                                <option value="footer" {{ old('location') == 'footer' ? 'selected' : '' }}>footer</option>
                                <option value="sidebar" {{ old('location') == 'sidebar' ? 'selected' : '' }}>sidebar
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">اللغة</label>
                            <select name="language_id" class="form-select">
                                <option value="">اختر اللغة</option>
                                @foreach ($languages ?? collect() as $language)
                                    <option value="{{ $language->id }}"
                                        {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                        {{ $language->native_name ?? $language->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', 0) }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">مفعل</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
