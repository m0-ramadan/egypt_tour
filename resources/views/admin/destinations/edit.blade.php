@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل وجهة'))

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
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
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

        .current-image,
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .1);
            margin-top: 10px;
        }

        .image-preview {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.destinations.index') }}">الوجهات</a></li>
                <li class="breadcrumb-item active">تعديل وجهة</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">تعديل بيانات الوجهة</h5>
                    <small class="opacity-75">{{ adminTrans($destination->name ?? '') }}</small>
                </div>
                <a href="{{ route('admin.destinations.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.destinations.update', $destination) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="section-title">البيانات الأساسية</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', adminTrans($destination->name)) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control"
                                value="{{ old('slug', $destination->slug) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">النوع</label>
                            <select name="type" class="form-select">
                                <option value="city" {{ old('type', $destination->type) == 'city' ? 'selected' : '' }}>
                                    city</option>
                                <option value="country"
                                    {{ old('type', $destination->type) == 'country' ? 'selected' : '' }}>country</option>
                                <option value="region" {{ old('type', $destination->type) == 'region' ? 'selected' : '' }}>
                                    region</option>
                                <option value="attraction"
                                    {{ old('type', $destination->type) == 'attraction' ? 'selected' : '' }}>attraction
                                </option>
                                <option value="poi" {{ old('type', $destination->type) == 'poi' ? 'selected' : '' }}>poi
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الدولة</label>
                            <select name="country_id" id="country_id" class="form-select">
                                <option value="">اختر الدولة</option>
                                @foreach ($countries ?? collect() as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('country_id', $destination->country_id) == $country->id ? 'selected' : '' }}>
                                        {{ adminTrans($country->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">المدينة</label>
                            <select name="city_id" id="city_id" class="form-select">
                                <option value="">اختر المدينة</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الوجهة الأم</label>
                            <select name="parent_id" class="form-select">
                                <option value="">بدون</option>
                                @foreach ($parents ?? collect() as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_id', $destination->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ adminTrans($parent->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $destination->sort_order ?? 0) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control"
                                value="{{ old('latitude', $destination->latitude) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control"
                                value="{{ old('longitude', $destination->longitude) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hero Image</label>
                            <input type="file" name="hero_image" class="form-control" id="hero_image">

                            @if (!empty($destination->hero_image))
                                <img src="{{ asset($destination->hero_image) }}"
                                    alt="{{ adminTrans($destination->name) }}" class="current-image">
                            @endif

                            <img id="hero_preview" class="image-preview" alt="hero preview">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control" id="featured_image">

                            @if (!empty($destination->featured_image))
                                <img src="{{ asset($destination->featured_image) }}"
                                    alt="{{ adminTrans($destination->name) }}" class="current-image">
                            @endif

                            <img id="featured_preview" class="image-preview" alt="featured preview">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', adminTrans($destination->short_description)) }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', adminTrans($destination->description)) }}</textarea>
                        </div>
                    </div>

                    <div class="section-title mt-4">SEO</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" name="seo_title" class="form-control"
                                value="{{ old('seo_title', adminTrans($destination->seo_title)) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Description</label>
                            <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', adminTrans($destination->seo_description)) }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Schema JSON</label>
                            <textarea name="schema_json" class="form-control" rows="6">{{ old('schema_json', $destination->schema_json) }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active"
                                    {{ old('is_active', $destination->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">مفعلة</label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_featured"
                                    id="is_featured"
                                    {{ old('is_featured', $destination->is_featured ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">مميزة</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.destinations.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const countries = @json($countries);
        const countrySelect = document.getElementById('country_id');
        const citySelect = document.getElementById('city_id');
        const selectedCityId = "{{ old('city_id', $destination->city_id) }}";

        function loadCities(countryId, cityId = null) {
            citySelect.innerHTML = '<option value="">اختر المدينة</option>';

            if (!countryId) return;

            const selectedCountry = countries.find(country => String(country.id) === String(countryId));

            if (selectedCountry && selectedCountry.cities && selectedCountry.cities.length > 0) {
                selectedCountry.cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;

                    if (cityId && String(city.id) === String(cityId)) {
                        option.selected = true;
                    }

                    citySelect.appendChild(option);
                });
            }
        }

        countrySelect.addEventListener('change', function() {
            loadCities(this.value);
        });

        window.addEventListener('load', function() {
            if (countrySelect.value) {
                loadCities(countrySelect.value, selectedCityId);
            }
        });

        function previewImage(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) {
                    preview.style.display = 'none';
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            });
        }

        previewImage('hero_image', 'hero_preview');
        previewImage('featured_image', 'featured_preview');
    </script>
@endsection
