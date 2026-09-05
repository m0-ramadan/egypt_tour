@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل معلم'))

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

        .order-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .order-header {
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

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
        }

        .page-loader {
            position: fixed;
            inset: 0;
            background: rgba(30, 30, 45, 0.92);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .page-loader.active {
            display: flex;
        }

        .loader-box {
            width: 100%;
            max-width: 420px;
            background: #2b3b4c;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .loader-spinner {
            width: 65px;
            height: 65px;
            border: 5px solid rgba(255, 255, 255, .15);
            border-top: 5px solid #696cff;
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        .loader-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }

        .loader-text {
            font-size: 14px;
            color: rgba(255, 255, 255, .75);
            margin-bottom: 20px;
        }

        .progress-wrapper {
            width: 100%;
            height: 14px;
            background: rgba(255, 255, 255, .08);
            border-radius: 30px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-bar-custom {
            width: 0%;
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 30px;
            transition: width .3s ease;
        }

        .progress-percent {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .image-preview,
        .current-image {
            margin-top: 12px;
        }

        .image-preview img,
        .current-image img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .current-image-title {
            font-size: 13px;
            color: rgba(255, 255, 255, .75);
            margin-bottom: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-loader" id="pageLoader">
        <div class="loader-box">
            <div class="loader-spinner"></div>
            <div class="loader-title">جاري تحديث المعلم...</div>
            <div class="loader-text">برجاء الانتظار أثناء حفظ التعديلات</div>

            <div class="progress-wrapper">
                <div class="progress-bar-custom" id="progressBar"></div>
            </div>

            <div class="progress-percent" id="progressPercent">0%</div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attractions.index') }}">المعالم السياحية</a></li>
                <li class="breadcrumb-item active">تعديل معلم</li>
            </ol>
        </nav>

        <div class="order-card">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">تعديل معلم</h5>
                        <small class="opacity-75">تعديل بيانات المعلم السياحي</small>
                    </div>
                    <a href="{{ route('admin.attractions.index') }}" class="btn btn-light">رجوع</a>
                </div>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.attractions.update', $attraction) }}" method="POST"
                    enctype="multipart/form-data" id="attractionForm">
                    @csrf
                    @method('PUT')

                    <div class="section-title">البيانات الأساسية</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">المدينة</label>
                            <select name="city_id" class="form-select">
                                <option value="">اختر المدينة</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}"
                                        {{ old('city_id', $attraction->city_id) == $city->id ? 'selected' : '' }}>
                                        {{ adminTrans($city->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control"
                                value="{{ old('slug', $attraction->slug) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', adminTrans($attraction->name)) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الصورة</label>

                            @if ($attraction->image)
                                <div class="current-image">
                                    <div class="current-image-title">الصورة الحالية</div>
                                    <img src="{{ asset('storage/' . $attraction->image) }}" alt="current image">
                                </div>
                            @endif

                            <input type="file" name="image" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'imagePreview')">
                            <div class="image-preview" id="imagePreview"></div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">ساعات العمل</label>
                            <input type="text" name="opening_hours" class="form-control"
                                value="{{ old('opening_hours', $attraction->opening_hours) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">رابط الخريطة</label>
                            <input type="text" name="map_url" class="form-control"
                                value="{{ old('map_url', $attraction->map_url) }}">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">خط العرض</label>
                            <input type="text" name="latitude" class="form-control"
                                value="{{ old('latitude', $attraction->latitude) }}">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">خط الطول</label>
                            <input type="text" name="longitude" class="form-control"
                                value="{{ old('longitude', $attraction->longitude) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $attraction->sort_order ?? 0) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">مميز</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="is_featured" value="1"
                                    {{ old('is_featured', $attraction->is_featured) ? 'checked' : '' }}>
                                <span>نعم</span>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">وصف مختصر</label>
                            <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', adminTrans($attraction->short_description)) }}</textarea>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control" rows="6">{{ old('description', adminTrans($attraction->description)) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" name="seo_title" class="form-control"
                                value="{{ old('seo_title', adminTrans($attraction->seo_title)) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Description</label>
                            <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', adminTrans($attraction->seo_description)) }}</textarea>
                        </div>

                        <div class="col-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $attraction->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label">مفعل</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">تحديث</button>
                        <a href="{{ route('admin.attractions.index') }}" class="btn btn-secondary">إلغاء</a>
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

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('attractionForm');
            const submitBtn = document.getElementById('submitBtn');
            const pageLoader = document.getElementById('pageLoader');
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');

            let progress = 0;
            let interval = null;
            let submitted = false;

            form.addEventListener('submit', function(event) {
                if (submitted) {
                    event.preventDefault();
                    return false;
                }

                submitted = true;
                submitBtn.disabled = true;
                pageLoader.classList.add('active');

                interval = setInterval(() => {
                    if (progress < 90) {
                        progress += Math.floor(Math.random() * 10) + 3;
                        if (progress > 90) progress = 90;

                        progressBar.style.width = progress + '%';
                        progressPercent.textContent = progress + '%';
                    }
                }, 200);
            });

            window.addEventListener('pageshow', function() {
                clearInterval(interval);
                progress = 0;

                if (progressBar) progressBar.style.width = '0%';
                if (progressPercent) progressPercent.textContent = '0%';
                if (pageLoader) pageLoader.classList.remove('active');
                if (submitBtn) submitBtn.disabled = false;

                submitted = false;
            });
        });
    </script>
@endsection
