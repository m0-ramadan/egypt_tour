@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة مدينة'))

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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .form-body {
            padding: 30px;
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

        .image-preview {
            margin-top: 12px;
        }

        .image-preview img {
            max-width: 220px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .12);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">المدن</a></li>
                <li class="breadcrumb-item active">إضافة مدينة</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">إضافة مدينة جديدة</h5>
                    <small class="opacity-75">إدخال بيانات المدينة</small>
                </div>
                <a href="{{ route('admin.cities.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.cities.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="section-title">البيانات الأساسية</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم المدينة</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الدولة</label>
                            <select name="country_id" class="form-select">
                                <option value="">اختر الدولة</option>
                                @foreach ($countries ?? collect() as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                        {{ adminTrans($country->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">مميزة</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" value="1" name="is_featured"
                                    id="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                <span>نعم</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الصورة الرئيسية</label>
                            <input type="file" name="hero_image" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'heroPreview')">
                            <div class="image-preview" id="heroPreview"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الصورة البارزة</label>
                            <input type="file" name="featured_image" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'featuredPreview')">
                            <div class="image-preview" id="featuredPreview"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">خط العرض</label>
                            <input type="text" name="latitude" class="form-control" value="{{ old('latitude') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">خط الطول</label>
                            <input type="text" name="longitude" class="form-control" value="{{ old('longitude') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">وصف مختصر</label>
                            <textarea name="short_description" class="form-control" rows="3">{{ old('short_description') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Description</label>
                            <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">مفعلة</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
