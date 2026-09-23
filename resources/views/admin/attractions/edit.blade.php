@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', 'Edit Attraction: ' . adminTrans($attraction->name))

@section('css')
    <style>
        .ck.ck-editor {
            color: #000 !important;
        }

        .ck.ck-editor__main>.ck-editor__editable {
            min-height: 250px;
            background: #ffffff !important;
            color: #000000 !important;
        }

        .tour-link-box {
            background: rgba(105, 108, 255, 0.08);
            border: 1px dashed rgba(105, 108, 255, 0.4);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-white fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Attraction: {{ adminTrans($attraction->name) }}</h4>
            <a href="{{ route('admin.attractions.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i> Back to Attractions
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.attractions.update', $attraction) }}" method="POST" enctype="multipart/form-data" id="attractionForm">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Left Main Content Column -->
                <div class="col-lg-8">
                    @include('admin.components.lang-tabs', ['model' => $attraction, 'activeLocales' => ['en', 'ar']])

                    @php
                        $rawName = $attraction->getRawOriginal('name') ?? $attraction->name;
                        $rawShortDesc = $attraction->getRawOriginal('short_description') ?? $attraction->short_description;
                        $rawDesc = $attraction->getRawOriginal('description') ?? $attraction->description;
                        $rawSeoTitle = $attraction->getRawOriginal('seo_title') ?? $attraction->seo_title;
                        $rawSeoDesc = $attraction->getRawOriginal('seo_description') ?? $attraction->seo_description;

                        if (is_string($rawName)) { $decoded = json_decode($rawName, true); if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rawName = $decoded; }
                        if (is_string($rawShortDesc)) { $decoded = json_decode($rawShortDesc, true); if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rawShortDesc = $decoded; }
                        if (is_string($rawDesc)) { $decoded = json_decode($rawDesc, true); if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rawDesc = $decoded; }
                        if (is_string($rawSeoTitle)) { $decoded = json_decode($rawSeoTitle, true); if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rawSeoTitle = $decoded; }
                        if (is_string($rawSeoDesc)) { $decoded = json_decode($rawSeoDesc, true); if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $rawSeoDesc = $decoded; }

                        $locales = [
                            'en' => ['name' => 'English', 'flag' => '🇺🇸', 'dir' => 'ltr'],
                            'ar' => ['name' => 'Arabic', 'flag' => '🇪🇬', 'dir' => 'rtl'],
                            'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'dir' => 'ltr'],
                            'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪', 'dir' => 'ltr'],
                        ];
                    @endphp

                    <div class="tab-content" id="langTabsContent">
                        @foreach ($locales as $code => $info)
                            @php
                                $valName = is_array($rawName) ? $rawName[$code] ?? '' : ($code === 'en' ? (string)$rawName : '');
                                $valShortDesc = is_array($rawShortDesc) ? $rawShortDesc[$code] ?? '' : ($code === 'en' ? (string)$rawShortDesc : '');
                                $valDesc = is_array($rawDesc) ? $rawDesc[$code] ?? '' : ($code === 'en' ? (string)$rawDesc : '');
                                $valSeoTitle = is_array($rawSeoTitle) ? $rawSeoTitle[$code] ?? '' : ($code === 'en' ? (string)$rawSeoTitle : '');
                                $valSeoDesc = is_array($rawSeoDesc) ? $rawSeoDesc[$code] ?? '' : ($code === 'en' ? (string)$rawSeoDesc : '');
                            @endphp

                            <div class="tab-pane fade {{ $code === 'en' ? 'show active' : '' }}"
                                id="tab-pane-{{ $code }}" role="tabpanel">
                                <div class="card bg-dark text-white border-secondary mb-4">
                                    <div class="card-header border-secondary">
                                        <h6 class="mb-0 text-white"><i class="fas fa-edit me-2"></i>Content ({{ $info['name'] }})</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label text-light">Attraction Name ({{ strtoupper($code) }}) @if ($code === 'en') <span class="text-danger">*</span> @endif</label>
                                            <input type="text" name="name[{{ $code }}]" class="form-control bg-dark text-white border-secondary"
                                                value="{{ old('name.' . $code, $valName) }}" dir="{{ $info['dir'] }}"
                                                @if ($code === 'en') required @endif
                                                placeholder="Attraction Name in {{ $info['name'] }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-light">Short Description ({{ strtoupper($code) }})</label>
                                            <textarea name="short_description[{{ $code }}]" class="form-control bg-dark text-white border-secondary" rows="3"
                                                dir="{{ $info['dir'] }}" placeholder="Brief summary of the attraction">{{ old('short_description.' . $code, $valShortDesc) }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label text-light mb-0">Full Description ({{ strtoupper($code) }})</label>
                                                <small class="text-info"><i class="fas fa-heading me-1"></i> Supports H1, H2, H3 & Hyperlinks</small>
                                            </div>

                                            <!-- Tour Package Link Helper Box -->
                                            <div class="tour-link-box d-flex flex-wrap align-items-center gap-2">
                                                <span class="text-white fw-bold fs-7"><i class="fas fa-link text-warning me-1"></i> Insert Tour Link:</span>
                                                <select class="form-select form-select-sm tour-select-{{ $code }} bg-dark text-white border-secondary" style="max-width: 280px;">
                                                    <option value="">-- Choose a Tour Package --</option>
                                                    @foreach ($packages as $pkg)
                                                        @php
                                                            $pkgTitle = adminTrans($pkg->title);
                                                            $pkgUrl = route('website.trips.show', $pkg->slug);
                                                        @endphp
                                                        <option value="{{ $pkgUrl }}" data-title="{{ $pkgTitle }}">{{ $pkgTitle }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="insertTourLink('{{ $code }}')">
                                                    <i class="fas fa-plus me-1"></i> Insert into Description
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-light" onclick="copyTourLink('{{ $code }}')">
                                                    <i class="fas fa-copy me-1"></i> Copy Link
                                                </button>
                                            </div>

                                            <textarea name="description[{{ $code }}]" class="form-control rich-editor" data-lang="{{ $code }}" rows="8"
                                                dir="{{ $info['dir'] }}" placeholder="Full detailed description...">{{ old('description.' . $code, $valDesc) }}</textarea>
                                        </div>

                                        <!-- SEO 2-Fields Section -->
                                        <div class="border-top border-secondary pt-3 mt-4">
                                            <h6 class="text-primary mb-3"><i class="fas fa-search me-2"></i>SEO ({{ $info['name'] }})</h6>
                                            <div class="mb-3">
                                                <label class="form-label text-light">SEO Title ({{ strtoupper($code) }})</label>
                                                <input type="text" name="seo_title[{{ $code }}]" class="form-control bg-dark text-white border-secondary"
                                                    value="{{ old('seo_title.' . $code, $valSeoTitle) }}" dir="{{ $info['dir'] }}"
                                                    placeholder="Meta title for search engines">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-light">SEO Description ({{ strtoupper($code) }})</label>
                                                <textarea name="seo_description[{{ $code }}]" class="form-control bg-dark text-white border-secondary" rows="2"
                                                    dir="{{ $info['dir'] }}" placeholder="Meta description for search engine snippets">{{ old('seo_description.' . $code, $valSeoDesc) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Right Sidebar Settings Column -->
                <div class="col-lg-4">
                    <div class="card bg-dark text-white border-secondary mb-4">
                        <div class="card-header border-secondary">
                            <h6 class="mb-0 text-white"><i class="fas fa-cog me-2"></i>Settings & Attributes</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-light">City / Destination</label>
                                <select name="city_id" class="form-select bg-dark text-white border-secondary">
                                    <option value="">Select City</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $attraction->city_id) == $city->id ? 'selected' : '' }}>
                                            {{ adminTrans($city->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light">URL Slug</label>
                                <input type="text" name="slug" class="form-control bg-dark text-white border-secondary"
                                    value="{{ old('slug', $attraction->slug) }}" placeholder="auto-generated-if-empty">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light">Featured Image</label>
                                @if ($attraction->image)
                                    <div class="mb-2">
                                        <small class="text-light opacity-75 d-block mb-1">Current Image:</small>
                                        <img src="{{ asset('storage/' . $attraction->image) }}" class="rounded border border-secondary"
                                            style="width: 100%; max-height: 160px; object-fit: cover;" alt="current image">
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control bg-dark text-white border-secondary" accept="image/*"
                                    onchange="previewImage(this, 'imagePreview')">
                                <div class="mt-2" id="imagePreview"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light">Opening Hours</label>
                                <input type="text" name="opening_hours" class="form-control bg-dark text-white border-secondary"
                                    value="{{ old('opening_hours', $attraction->opening_hours) }}" placeholder="e.g. Daily 8:00 AM - 5:00 PM">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light">Google Maps URL</label>
                                <input type="text" name="map_url" class="form-control bg-dark text-white border-secondary"
                                    value="{{ old('map_url', $attraction->map_url) }}" placeholder="https://maps.google.com/...">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label text-light">Latitude</label>
                                    <input type="text" name="latitude" class="form-control bg-dark text-white border-secondary"
                                        value="{{ old('latitude', $attraction->latitude) }}" placeholder="29.9792">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-light">Longitude</label>
                                    <input type="text" name="longitude" class="form-control bg-dark text-white border-secondary"
                                        value="{{ old('longitude', $attraction->longitude) }}" placeholder="31.1342">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control bg-dark text-white border-secondary"
                                    value="{{ old('sort_order', $attraction->sort_order ?? 0) }}">
                            </div>

                            <hr class="border-secondary">

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                                    {{ old('is_featured', $attraction->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label text-white fw-bold" for="is_featured">Featured Highlight</label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                    {{ old('is_active', $attraction->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label text-white fw-bold" for="is_active">Enabled / Published</label>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Update Attraction
                                </button>
                                <a href="{{ route('admin.attractions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        const editors = {};

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.rich-editor').forEach(textarea => {
                const lang = textarea.getAttribute('data-lang') || 'en';
                ClassicEditor.create(textarea, {
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'link', 'bulletedList', 'numberedList', '|',
                            'outdent', 'indent', '|',
                            'blockQuote', 'insertTable', 'undo', 'redo'
                        ]
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1 (H1)', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2 (H2)', class: 'ck-heading_heading2' },
                            { model: 'heading3', view: 'h3', title: 'Heading 3 (H3)', class: 'ck-heading_heading3' }
                        ]
                    },
                    language: lang === 'ar' ? 'ar' : 'en'
                }).then(editor => {
                    editors[lang] = editor;
                }).catch(error => {
                    console.error('CKEditor Init Error:', error);
                });
            });

            const form = document.getElementById('attractionForm');
            if (form) {
                form.addEventListener('submit', function() {
                    Object.keys(editors).forEach(lang => {
                        if (editors[lang]) {
                            editors[lang].updateSourceElement();
                        }
                    });
                });
            }
        });

        function insertTourLink(lang) {
            const select = document.querySelector('.tour-select-' + lang);
            if (!select || !select.value) {
                alert('Please select a tour package first.');
                return;
            }
            const url = select.value;
            const title = select.options[select.selectedIndex].getAttribute('data-title') || 'View Tour';
            const editor = editors[lang];
            if (editor) {
                const linkHtml = `<a href="${url}" title="${title}">${title}</a>`;
                const viewFragment = editor.data.processor.toView(linkHtml);
                const modelFragment = editor.data.toModel(viewFragment);
                editor.model.insertContent(modelFragment);
            } else {
                const textarea = document.querySelector(`textarea[name="description[${lang}]"]`);
                if (textarea) {
                    textarea.value += ` <a href="${url}">${title}</a>`;
                }
            }
        }

        function copyTourLink(lang) {
            const select = document.querySelector('.tour-select-' + lang);
            if (!select || !select.value) {
                alert('Please select a tour package first.');
                return;
            }
            navigator.clipboard.writeText(select.value).then(() => {
                alert('Tour URL copied to clipboard: ' + select.value);
            });
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="rounded border border-secondary" style="width: 100%; max-height: 180px; object-fit: cover;">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
