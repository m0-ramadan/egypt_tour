@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Edit Page'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
            --warning-color: #ffc107;
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .panel-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .panel-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .panel-body {
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

        .btn-outline-light {
            border-color: rgba(255, 255, 255, .3);
            color: #fff;
        }

        .btn-ai {
            background: rgba(12, 99, 228, .18);
            border: 1px solid rgba(12, 99, 228, .35);
            color: #9ec5fe;
        }

        .btn-ai:hover {
            background: rgba(12, 99, 228, .28);
            color: #fff;
        }

        .ai-box {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .ai-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-box {
            background: #1f2937;
            border: 1px solid rgba(255, 255, 255, .1);
            padding: 22px 28px;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            min-width: 260px;
        }

        .spinner {
            width: 42px;
            height: 42px;
            margin: 0 auto 12px;
            border: 4px solid rgba(255, 255, 255, .15);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .ck.ck-editor {
            width: 100%;
        }

        .ck.ck-editor__main>.ck-editor__editable {
            min-height: 380px;
            background: #fff !important;
            color: #000 !important;
            border-radius: 0 0 10px 10px !important;
        }

        .ck.ck-toolbar {
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="loading-overlay" id="ai-loading">
        <div class="loading-box">
            <div class="spinner"></div>
            <div id="loading-text">In progress Processing Request...</div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.static-pages.index') }}">Static Pages</a></li>
                <li class="breadcrumb-item active">Edit Page</li>
            </ol>
        </nav>

        <div class="panel-card mb-4">
            <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Edit Page</h5>
                    <small class="opacity-75">Edit And Edit Content Page With Tools AI Artificial</small>
                </div>
                <a href="{{ route('admin.static-pages.index') }}" class="btn btn-light">Back</a>
            </div>

            <div class="panel-body">
                <div class="ai-box">
                    <div class="section-title">AI Tools</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Prompt Edit Page</label>
                            <textarea id="ai_prompt" class="form-control" rows="4"
                                placeholder="Example: Improve page layout, clear copy, and SEO focus"></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Template</label>
                            <select id="template" name="template" class="form-select">
                                <option value="default"
                                    {{ old('template', $page->template ?? 'default') == 'default' ? 'selected' : '' }}>
                                    Default</option>
                                <option value="landing"
                                    {{ old('template', $page->template ?? '') == 'landing' ? 'selected' : '' }}>Landing Page
                                </option>
                                <option value="about"
                                    {{ old('template', $page->template ?? '') == 'about' ? 'selected' : '' }}>About Us
                                </option>
                                <option value="services"
                                    {{ old('template', $page->template ?? '') == 'services' ? 'selected' : '' }}>Services
                                </option>
                                <option value="contact"
                                    {{ old('template', $page->template ?? '') == 'contact' ? 'selected' : '' }}>Contact Us
                                </option>
                                <option value="faq"
                                    {{ old('template', $page->template ?? '') == 'faq' ? 'selected' : '' }}>FAQs
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="ai-actions">
                        <button type="button" class="btn btn-ai" id="btn-generate-page">Reset Generate Page Full</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-title">Generate Title</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-body">Generate Content</button>
                        <button type="button" class="btn btn-ai" id="btn-enhance-body">Improve Content</button>
                        <button type="button" class="btn btn-ai" id="btn-expand-body">Expand Content</button>
                        <button type="button" class="btn btn-ai" id="btn-simplify-body">Simplify Content</button>
                        <button type="button" class="btn btn-ai" id="btn-format-body">Format Content</button>
                        <button type="button" class="btn btn-ai" id="btn-grammar-body">Proofread</button>
                        <button type="button" class="btn btn-ai" id="btn-translate-all">Translate All Fields</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-seo">Generate SEO</button>
                    </div>
                </div>

                <form id="page-form" action="{{ route('admin.static-pages.update', $page) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="section-title">Page Details</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="{{ old('title', adminTrans($page->title)) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                value="{{ old('slug', $page->slug) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Type</label>
                            <select name="template" class="form-select">
                                <option value="default"
                                    {{ old('template', $page->template ?? 'default') == 'default' ? 'selected' : '' }}>
                                    Default</option>
                                <option value="landing"
                                    {{ old('template', $page->template ?? '') == 'landing' ? 'selected' : '' }}>Landing
                                    Page</option>
                                <option value="about"
                                    {{ old('template', $page->template ?? '') == 'about' ? 'selected' : '' }}>About Us
                                </option>
                                <option value="services"
                                    {{ old('template', $page->template ?? '') == 'services' ? 'selected' : '' }}>Services
                                </option>
                                <option value="contact"
                                    {{ old('template', $page->template ?? '') == 'contact' ? 'selected' : '' }}>Contact Us
                                </option>
                                <option value="faq"
                                    {{ old('template', $page->template ?? '') == 'faq' ? 'selected' : '' }}>FAQs
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Home Page</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="is_home" value="1"
                                    {{ old('is_home', $page->is_home ?? false) ? 'checked' : '' }}>
                                <span>Set as Home Page</span>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                                <span>Enabled</span>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="body" id="body-editor" class="form-control" rows="14">{{ old('body', adminTrans($page->body ?? ($page->content ?? ''))) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meta Title</label>
                            <input type="text" id="seo_title" name="seo_title" class="form-control"
                                value="{{ old('seo_title', adminTrans($page->seo_title ?? ($page->meta_title ?? ''))) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea id="seo_description" name="seo_description" class="form-control" rows="3">{{ old('seo_description', adminTrans($page->seo_description ?? ($page->meta_description ?? ''))) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control"
                                value="{{ old('featured_image', $page->featured_image ?? '') }}"
                                placeholder="Link Image Or Path">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publish Date</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                value="{{ old('published_at', !empty($page->published_at) ? \Carbon\Carbon::parse($page->published_at)->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit" id="submit-btn">Save Changes</button>
                        <a href="{{ route('admin.static-pages.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.9/jodit.min.js"></script>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        const joditInstance = Jodit.make('#body-editor', {
            height: 550,
            language: '{{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}',
            direction: '{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}',
            toolbarButtonSize: 'middle',
            theme: 'default',
            iframeStyle: 'html, body { color: #111111 !important; background: #ffffff !important; }',
            style: {
                color: '#111111',
                background: '#ffffff'
            },
            buttons: [
                'source', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'font', 'fontsize', 'brush', '|',
                'paragraph', 'align', '|',
                'ul', 'ol', 'outdent', 'indent', '|',
                'direction', '|',
                'image', 'video', 'link', 'table', '|',
                'hr', 'eraser', 'copyformat', '|',
                'symbol', 'fullsize', '|',
                'undo', 'redo', 'find'
            ],
            uploader: {
                insertImageAsBase64URI: true
            },
            showXPathInStatusbar: false
        });

        const bodyEditor = {
            getData: () => joditInstance ? joditInstance.value : (document.getElementById('body-editor')?.value || ''),
            setData: (val) => {
                if (joditInstance) {
                    joditInstance.value = val || '';
                }
                if (document.getElementById('body-editor')) {
                    document.getElementById('body-editor').value = val || '';
                }
            }
        };

        function showLoading(text = 'In progress Processing Request...') {
            document.getElementById('loading-text').textContent = text;
            document.getElementById('ai-loading').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('ai-loading').style.display = 'none';
        }

        function currentBody() {
            return bodyEditor ? bodyEditor.getData() : '';
        }

        function setBody(value) {
            if (bodyEditor) {
                bodyEditor.setData(value || '');
            }
        }

        function currentTitle() {
            return document.getElementById('title').value || '';
        }

        function currentTemplate() {
            return document.getElementById('template').value || 'default';
        }

        async function postAi(url, payload, loadingText = 'Processing the AI request...') {
            showLoading(loadingText);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                return await response.json();
            } catch (error) {
                console.error(error);
                alert('An error occurred while connecting to AI service.');
                return null;
            } finally {
                hideLoading();
            }
        }

        document.getElementById('btn-generate-page').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.generate-page') }}', {
                prompt: document.getElementById('ai_prompt').value || currentTitle(),
                template: currentTemplate()
            }, 'In progress Reset Generate Page Full...');

            if (!data) return;

            if (data.title) {
                document.getElementById('title').value = data.title.ar || data.title.en || '';
            }

            if (data.body) {
                setBody(data.body.ar || data.body.en || '');
            }

            if (data.seo_title) {
                document.getElementById('seo_title').value = data.seo_title.ar || data.seo_title.en || '';
            }

            if (data.seo_description) {
                document.getElementById('seo_description').value = data.seo_description.ar || data
                    .seo_description.en || '';
            }
        });

        document.getElementById('btn-generate-title').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.generate-title') }}', {
                topic: document.getElementById('ai_prompt').value || currentTitle()
            }, 'In progress Generate Address...');

            if (data && data.title) {
                document.getElementById('title').value = data.title;
            }
        });

        document.getElementById('btn-generate-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.generate-content') }}', {
                title: currentTitle() || document.getElementById('ai_prompt').value,
                template: currentTemplate()
            }, 'In progress Generate Content...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-enhance-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.enhance-content') }}', {
                content: currentBody(),
                instruction: 'Improve Style And organization And clarity With Preserve On Meaning'
            }, 'In progress Improve Content...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-expand-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.expand-content') }}', {
                content: currentBody()
            }, 'In progress Expand Content...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-simplify-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.simplify-content') }}', {
                content: currentBody()
            }, 'In progress Simplify Content...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-format-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.format-content') }}', {
                content: currentBody()
            }, 'In progress Format Content...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-grammar-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.check-grammar') }}', {
                content: currentBody()
            }, 'In progress Proofread Language...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-translate-all').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.translate') }}', {
                title: document.getElementById('title').value,
                body: currentBody(),
                seo_title: document.getElementById('seo_title').value,
                seo_description: document.getElementById('seo_description').value
            }, 'In progress Translate Fields...');

            if (!data) return;

            if (data.title) {
                document.getElementById('title').value = data.title.ar || data.title.en || '';
            }

            if (data.body) {
                setBody(data.body.ar || data.body.en || '');
            }

            if (data.seo_title) {
                document.getElementById('seo_title').value = data.seo_title.ar || data.seo_title.en || '';
            }

            if (data.seo_description) {
                document.getElementById('seo_description').value = data.seo_description.ar || data
                    .seo_description.en || '';
            }
        });

        document.getElementById('btn-generate-seo').addEventListener('click', async function() {
            const title = currentTitle();
            const content = currentBody();

            const metaTitle = await postAi('{{ route('admin.static-pages.ai.generate-meta-title') }}', {
                title: title,
                content: content
            }, 'In progress Generate Title SEO...');

            if (metaTitle && metaTitle.meta_title) {
                document.getElementById('seo_title').value = metaTitle.meta_title;
            }

            const metaDescription = await postAi(
                '{{ route('admin.static-pages.ai.generate-meta-description') }}', {
                    title: title,
                    content: content
                }, 'In progress Generate Description SEO...');

            if (metaDescription && metaDescription.meta_description) {
                document.getElementById('seo_description').value = metaDescription.meta_description;
            }
        });

        document.getElementById('page-form').addEventListener('submit', function() {
            showLoading('In progress Save Changes...');
            document.getElementById('submit-btn').disabled = true;
        });
    </script>
@endsection
