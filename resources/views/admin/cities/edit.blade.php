@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', 'Edit City')

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Public Sans", sans-serif !important;
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

        .image-preview,
        .current-image {
            margin-top: 12px;
        }

        .image-preview img,
        .current-image img {
            max-width: 220px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .current-image-title {
            font-size: 13px;
            color: rgba(255, 255, 255, .7);
            margin-bottom: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">Cities</a></li>
                <li class="breadcrumb-item active">Edit City</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Edit City</h5>
                    <small class="opacity-75">{{ adminTrans($city->name) ?? '' }}</small>
                </div>
                <a href="{{ route('admin.cities.index') }}" class="btn btn-light">Back</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.cities.update', $city) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $rawName = $city->getRawOriginal('name') ?? $city->name;
                        $rawShortDesc = $city->getRawOriginal('short_description') ?? $city->short_description;
                        $rawDesc = $city->getRawOriginal('description') ?? $city->description;
                        $rawSeoTitle = $city->getRawOriginal('seo_title') ?? $city->seo_title;
                        $rawSeoDesc = $city->getRawOriginal('seo_description') ?? $city->seo_description;
                    @endphp

                    @include('admin.components.lang-tabs', [
                        'model' => $city,
                        'activeLocales' => ['en', 'ar'],
                    ])

                    <div class="tab-content mb-4" id="langTabContent">
                        @foreach (['en' => ['name' => 'English', 'dir' => 'ltr'], 'ar' => ['name' => 'Arabic', 'dir' => 'rtl'], 'fr' => ['name' => 'French', 'dir' => 'ltr'], 'de' => ['name' => 'German', 'dir' => 'ltr']] as $code => $info)
                            @php
                                $valName = is_array($rawName)
                                    ? $rawName[$code] ?? ''
                                    : ($code === 'en'
                                        ? (string) $rawName
                                        : '');
                                $valShortDesc = is_array($rawShortDesc)
                                    ? $rawShortDesc[$code] ?? ''
                                    : ($code === 'en'
                                        ? (string) $rawShortDesc
                                        : '');
                                $valDesc = is_array($rawDesc)
                                    ? $rawDesc[$code] ?? ''
                                    : ($code === 'en'
                                        ? (string) $rawDesc
                                        : '');
                                $valSeoTitle = is_array($rawSeoTitle)
                                    ? $rawSeoTitle[$code] ?? ''
                                    : ($code === 'en'
                                        ? (string) $rawSeoTitle
                                        : '');
                                $valSeoDesc = is_array($rawSeoDesc)
                                    ? $rawSeoDesc[$code] ?? ''
                                    : ($code === 'en'
                                        ? (string) $rawSeoDesc
                                        : '');
                            @endphp
                            <div class="tab-pane fade {{ $code === 'en' ? 'show active' : '' }}"
                                id="tab-pane-{{ $code }}" role="tabpanel">
                                <div class="card bg-dark text-white border-secondary mb-3">
                                    <div class="card-header border-secondary">
                                        <h6 class="mb-0 text-white"><i class="fas fa-edit me-2"></i>Content
                                            ({{ $info['name'] }})</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">City Name ({{ strtoupper($code) }}) @if ($code === 'en')
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            <input type="text" name="name[{{ $code }}]" class="form-control"
                                                value="{{ old('name.' . $code, $valName) }}" dir="{{ $info['dir'] }}"
                                                @if ($code === 'en') required @endif
                                                placeholder="City Name in {{ $info['name'] }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Short Description ({{ strtoupper($code) }})</label>
                                            <textarea name="short_description[{{ $code }}]" class="form-control" rows="3" dir="{{ $info['dir'] }}"
                                                placeholder="Short description in {{ $info['name'] }}">{{ old('short_description.' . $code, $valShortDesc) }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Full Description ({{ strtoupper($code) }})</label>
                                            <textarea name="description[{{ $code }}]" class="form-control" rows="5" dir="{{ $info['dir'] }}"
                                                placeholder="Full description in {{ $info['name'] }}">{{ old('description.' . $code, $valDesc) }}</textarea>
                                        </div>

                                        <div class="border-top border-secondary pt-3 mt-3">
                                            <h6 class="text-primary mb-3"><i class="fas fa-search me-2"></i>SEO
                                                ({{ $info['name'] }})</h6>
                                            <div class="mb-3">
                                                <label class="form-label">SEO Title ({{ strtoupper($code) }})</label>
                                                <input type="text" name="seo_title[{{ $code }}]"
                                                    class="form-control"
                                                    value="{{ old('seo_title.' . $code, $valSeoTitle) }}"
                                                    dir="{{ $info['dir'] }}"
                                                    placeholder="SEO Meta Title in {{ $info['name'] }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">SEO Description ({{ strtoupper($code) }})</label>
                                                <textarea name="seo_description[{{ $code }}]" class="form-control" rows="2" dir="{{ $info['dir'] }}"
                                                    placeholder="SEO Meta Description in {{ $info['name'] }}">{{ old('seo_description.' . $code, $valSeoDesc) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="section-title">General Settings</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control"
                                value="{{ old('slug', $city->slug) }}" dir="ltr">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <select name="country_id" class="form-select">
                                <option value="">Select Country</option>
                                @foreach ($countries ?? collect() as $country)
                                    <option value="{{ $country->id }}"
                                        {{ old('country_id', $city->country_id) == $country->id ? 'selected' : '' }}>
                                        {{ adminTrans($country->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $city->sort_order ?? 0) }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Featured</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" value="1" name="is_featured"
                                    id="is_featured"
                                    {{ old('is_featured', $city->is_featured ?? false) ? 'checked' : '' }}>
                                <span>Yes</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hero Image</label>
                            <input type="file" name="hero_image" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'heroPreview')">
                            @if (!empty($city->hero_image))
                                <div class="current-image">
                                    <div class="current-image-title">Current Image</div>
                                    <img src="{{ asset('storage/' . $city->hero_image) }}" alt="hero image">
                                </div>
                            @endif
                            <div class="image-preview" id="heroPreview"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'featuredPreview')">
                            @if (!empty($city->featured_image))
                                <div class="current-image">
                                    <div class="current-image-title">Current Image</div>
                                    <img src="{{ asset('storage/' . $city->featured_image) }}" alt="featured image">
                                </div>
                            @endif
                            <div class="image-preview" id="featuredPreview"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control"
                                value="{{ old('latitude', $city->latitude) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control"
                                value="{{ old('longitude', $city->longitude) }}">
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active" {{ old('is_active', $city->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Update City</button>
                        <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">Cancel</a>
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
