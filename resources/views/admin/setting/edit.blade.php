@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل الإعدادات'))

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
                <li class="breadcrumb-item"><a href="{{ route('admin.setting.edit') }}">الإعدادات</a></li>
                <li class="breadcrumb-item active">تعديل</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">تعديل إعدادات الموقع</h5>
                <small class="opacity-75">الإعدادات العامة للموقع السياحي</small>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.setting.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم الموقع</label>
                            <input type="text" name="site_name" class="form-control"
                                value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="site_email" class="form-control"
                                value="{{ old('site_email', $settings['site_email'] ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الهاتف</label>
                            <input type="text" name="site_phone" class="form-control"
                                value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="site_address" class="form-control"
                                value="{{ old('site_address', $settings['site_address'] ?? '') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">الوصف المختصر</label>
                            <textarea name="site_description" class="form-control" rows="4">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الشعار</label>
                            <input type="file" name="site_logo" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">أيقونة الموقع</label>
                            <input type="file" name="site_favicon" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
