@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', 'Edit Package Category')

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
            color: #fff;
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

        .current-image {
            width: 130px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, .15);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.package-categories.index') }}">Package Categories</a>
                </li>
                <li class="breadcrumb-item active">Edit Category</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Edit Category</h5>
                    <small class="opacity-75">{{ adminTrans($category->name) }}</small>
                </div>
                <a href="{{ route('admin.package-categories.index') }}" class="btn btn-light">Back</a>
            </div>

            <div class="form-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please check the form inputs:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.package-categories.update', $category) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $rawName = $category->getRawOriginal('name') ?? $category->name;
                        $rawDesc = $category->getRawOriginal('description') ?? $category->description;
                        $rawSeoTitle = $category->getRawOriginal('seo_title') ?? $category->seo_title;
                        $rawSeoDesc = $category->getRawOriginal('seo_description') ?? $category->seo_description;
                    @endphp

                    @include('admin.components.lang-tabs', [
                        'model' => $category,
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
                                            <label class="form-label">Category Name ({{ strtoupper($code) }}) @if ($code === 'en')
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            <input type="text" name="name[{{ $code }}]" class="form-control"
                                                value="{{ old('name.' . $code, $valName) }}" dir="{{ $info['dir'] }}"
                                                @if ($code === 'en') required @endif
                                                placeholder="Category Name in {{ $info['name'] }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Description ({{ strtoupper($code) }})</label>
                                            <textarea name="description[{{ $code }}]" class="form-control" rows="4" dir="{{ $info['dir'] }}"
                                                placeholder="Category description in {{ $info['name'] }}">{{ old('description.' . $code, $valDesc) }}</textarea>
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
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug', $category->slug) }}" dir="ltr" required>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category Type <span class="text-danger">*</span></label>
                            <select name="category_type" class="form-select @error('category_type') is-invalid @enderror"
                                required>
                                @foreach (\App\Models\PackageCategory::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category_type', $category->category_type) === $value)>
                                        {{ $value }} ({{ $label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('category_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">Top Level Category</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>
                                        {{ adminTrans($parent->name) }}{{ $parent->is_active ? '' : ' (Inactive)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <select name="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                <option value="">All Countries</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected((string) old('country_id', $category->country_id) === (string) $country->id)>
                                        {{ adminTrans($country->name) }}{{ $country->is_active ? '' : ' (Inactive)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" min="0" name="sort_order"
                                class="form-control @error('sort_order') is-invalid @enderror"
                                value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image</label>
                            @if ($category->image)
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <img src="{{ asset('storage/' . $category->image) }}"
                                        alt="{{ adminTrans($category->name) }}" class="current-image">
                                    <div class="form-check">
                                        <input type="hidden" name="remove_image" value="0">
                                        <input class="form-check-input" type="checkbox" name="remove_image"
                                            value="1" id="remove_image" @checked((bool) old('remove_image', false))>
                                        <label class="form-check-label" for="remove_image">Remove Current Image</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                                class="form-control @error('image') is-invalid @enderror">
                            <div class="form-text text-light opacity-75">Leave blank to keep existing image.</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon Class</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                                value="{{ old('icon', $category->icon) }}" placeholder="fas fa-map-marked-alt"
                                dir="ltr">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min Days</label>
                            <input type="number" min="0" name="min_days"
                                class="form-control @error('min_days') is-invalid @enderror"
                                value="{{ old('min_days', $category->min_days) }}">
                            @error('min_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Days</label>
                            <input type="number" min="0" name="max_days"
                                class="form-control @error('max_days') is-invalid @enderror"
                                value="{{ old('max_days', $category->max_days) }}">
                            @error('max_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price Starts From</label>
                            <input type="number" min="0" step="0.01" name="price_from"
                                class="form-control @error('price_from') is-invalid @enderror"
                                value="{{ old('price_from', $category->price_from) }}">
                            @error('price_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                    id="is_active" @checked((bool) old('is_active', $category->is_active))>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>

                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_featured"
                                    id="is_featured" @checked((bool) old('is_featured', $category->is_featured))>
                                <label class="form-check-label" for="is_featured">Featured</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Update Category</button>
                        <a href="{{ route('admin.package-categories.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
