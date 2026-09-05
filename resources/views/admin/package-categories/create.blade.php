@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة تصنيف'))

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

        .form-select option {
            color: #212529;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.package-categories.index') }}">تصنيفات الباقات</a></li>
                <li class="breadcrumb-item active">إضافة تصنيف</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">إضافة تصنيف جديد</h5>
                    <small class="opacity-75">إدخال بيانات التصنيف</small>
                </div>
                <a href="{{ route('admin.package-categories.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>يرجى مراجعة البيانات:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.package-categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="section-title">البيانات الأساسية</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug') }}" dir="ltr" required>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع التصنيف <span class="text-danger">*</span></label>
                            <select name="category_type" class="form-select @error('category_type') is-invalid @enderror" required>
                                @foreach (\App\Models\PackageCategory::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category_type', 'travel_package') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">التصنيف الأب</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">تصنيف رئيسي</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected((string) old('parent_id') === (string) $parent->id)>
                                        {{ adminTrans($parent->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الدولة</label>
                            <select name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                <option value="">كل الدول</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected((string) old('country_id') === (string) $country->id)>
                                        {{ adminTrans($country->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" min="0" name="sort_order"
                                class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
                            @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الصورة</label>
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                                class="form-control @error('image') is-invalid @enderror">
                            <div class="form-text text-light opacity-75">JPG، PNG أو WEBP بحد أقصى 2MB.</div>
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الأيقونة</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                value="{{ old('icon') }}" placeholder="fas fa-map-marked-alt" dir="ltr">
                            @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الحد الأدنى للأيام</label>
                            <input type="number" min="0" name="min_days" class="form-control @error('min_days') is-invalid @enderror"
                                value="{{ old('min_days') }}">
                            @error('min_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الحد الأقصى للأيام</label>
                            <input type="number" min="0" name="max_days" class="form-control @error('max_days') is-invalid @enderror"
                                value="{{ old('max_days') }}">
                            @error('max_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">السعر يبدأ من</label>
                            <input type="number" min="0" step="0.01" name="price_from"
                                class="form-control @error('price_from') is-invalid @enderror" value="{{ old('price_from') }}">
                            @error('price_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active" @checked((bool) old('is_active', true))>
                                <label class="form-check-label" for="is_active">مفعل</label>
                            </div>

                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_featured"
                                    id="is_featured" @checked((bool) old('is_featured', false))>
                                <label class="form-check-label" for="is_featured">مميز</label>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-4">SEO</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="seo_title" class="form-control @error('seo_title') is-invalid @enderror"
                                value="{{ old('seo_title') }}">
                            @error('seo_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="seo_description" class="form-control @error('seo_description') is-invalid @enderror" rows="3">{{ old('seo_description') }}</textarea>
                            @error('seo_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.package-categories.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
