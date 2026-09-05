@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تعديل صفحة'))

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
            <div id="loading-text">جاري تنفيذ الطلب...</div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.static-pages.index') }}">الصفحات الثابتة</a></li>
                <li class="breadcrumb-item active">تعديل صفحة</li>
            </ol>
        </nav>

        <div class="panel-card mb-4">
            <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">تعديل الصفحة</h5>
                    <small class="opacity-75">تعديل وتحرير محتوى الصفحة مع أدوات الذكاء الاصطناعي</small>
                </div>
                <a href="{{ route('admin.static-pages.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="panel-body">
                <div class="ai-box">
                    <div class="section-title">أدوات الذكاء الاصطناعي</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">برومبت تعديل الصفحة</label>
                            <textarea id="ai_prompt" class="form-control" rows="4"
                                placeholder="مثال: حسّن الصفحة لتكون أكثر احترافية ووضوحًا وركز على السيو وتجربة المستخدم"></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">القالب</label>
                            <select id="template" name="template" class="form-select">
                                <option value="default"
                                    {{ old('template', $page->template ?? 'default') == 'default' ? 'selected' : '' }}>
                                    افتراضي</option>
                                <option value="landing"
                                    {{ old('template', $page->template ?? '') == 'landing' ? 'selected' : '' }}>Landing Page
                                </option>
                                <option value="about"
                                    {{ old('template', $page->template ?? '') == 'about' ? 'selected' : '' }}>من نحن
                                </option>
                                <option value="services"
                                    {{ old('template', $page->template ?? '') == 'services' ? 'selected' : '' }}>خدمات
                                </option>
                                <option value="contact"
                                    {{ old('template', $page->template ?? '') == 'contact' ? 'selected' : '' }}>تواصل معنا
                                </option>
                                <option value="faq"
                                    {{ old('template', $page->template ?? '') == 'faq' ? 'selected' : '' }}>الأسئلة الشائعة
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="ai-actions">
                        <button type="button" class="btn btn-ai" id="btn-generate-page">إعادة توليد الصفحة كاملة</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-title">توليد العنوان</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-body">توليد المحتوى</button>
                        <button type="button" class="btn btn-ai" id="btn-enhance-body">تحسين المحتوى</button>
                        <button type="button" class="btn btn-ai" id="btn-expand-body">توسيع المحتوى</button>
                        <button type="button" class="btn btn-ai" id="btn-simplify-body">تبسيط المحتوى</button>
                        <button type="button" class="btn btn-ai" id="btn-format-body">تنسيق المحتوى</button>
                        <button type="button" class="btn btn-ai" id="btn-grammar-body">تدقيق لغوي</button>
                        <button type="button" class="btn btn-ai" id="btn-translate-all">ترجمة كل الحقول</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-seo">توليد SEO</button>
                    </div>
                </div>

                <form id="page-form" action="{{ route('admin.static-pages.update', $page) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="section-title">بيانات الصفحة</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">العنوان</label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="{{ old('title', adminTrans($page->title)) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الرابط المختصر (Slug)</label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                value="{{ old('slug', $page->slug) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع القالب</label>
                            <select name="template" class="form-select">
                                <option value="default"
                                    {{ old('template', $page->template ?? 'default') == 'default' ? 'selected' : '' }}>
                                    افتراضي</option>
                                <option value="landing"
                                    {{ old('template', $page->template ?? '') == 'landing' ? 'selected' : '' }}>Landing
                                    Page</option>
                                <option value="about"
                                    {{ old('template', $page->template ?? '') == 'about' ? 'selected' : '' }}>من نحن
                                </option>
                                <option value="services"
                                    {{ old('template', $page->template ?? '') == 'services' ? 'selected' : '' }}>خدمات
                                </option>
                                <option value="contact"
                                    {{ old('template', $page->template ?? '') == 'contact' ? 'selected' : '' }}>تواصل معنا
                                </option>
                                <option value="faq"
                                    {{ old('template', $page->template ?? '') == 'faq' ? 'selected' : '' }}>الأسئلة الشائعة
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">الصفحة الرئيسية</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="is_home" value="1"
                                    {{ old('is_home', $page->is_home ?? false) ? 'checked' : '' }}>
                                <span>تعيين كصفحة رئيسية</span>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">الحالة</label>
                            <div class="form-control d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                                <span>مفعلة</span>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">المحتوى</label>
                            <textarea name="body" id="body-editor" class="form-control" rows="14">{{ old('body', adminTrans($page->body ?? ($page->content ?? ''))) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">عنوان الميتا</label>
                            <input type="text" id="seo_title" name="seo_title" class="form-control"
                                value="{{ old('seo_title', adminTrans($page->seo_title ?? ($page->meta_title ?? ''))) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">وصف الميتا</label>
                            <textarea id="seo_description" name="seo_description" class="form-control" rows="3">{{ old('seo_description', adminTrans($page->seo_description ?? ($page->meta_description ?? ''))) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الصورة البارزة</label>
                            <input type="file" name="featured_image" class="form-control"
                                value="{{ old('featured_image', $page->featured_image ?? '') }}"
                                placeholder="رابط الصورة أو المسار">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                value="{{ old('published_at', !empty($page->published_at) ? \Carbon\Carbon::parse($page->published_at)->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit" id="submit-btn">حفظ التعديلات</button>
                        <a href="{{ route('admin.static-pages.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        let bodyEditor;
        const csrfToken = '{{ csrf_token() }}';

        ClassicEditor
            .create(document.querySelector('#body-editor'), {
                licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE4MDY0NTExOTksImp0aSI6ImQ4ZTJkMmViLTU3MzgtNDBlNy05NDdjLTQyMDZmODRjZjAwYSIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiXSwiZmVhdHVyZXMiOlsiRFJVUCIsIkUyUCIsIkUyVyJdLCJyZW1vdmVGZWF0dXJlcyI6WyJQQiIsIlJGIiwiU0NIIiwiVENQIiwiVEwiLCJUQ1IiLCJJUiIsIlNVQSIsIkI2NEEiLCJMUCIsIkhFIiwiUkVEIiwiUEZPIiwiV0MiLCJGQVIiLCJCS00iLCJGUEgiLCJNUkUiXSwidmMiOiJkYTIyOTI0NSJ9.2qP138AkjE60FSHcexHum5kGto4HUDWbtEv8YA5s_wQuhj4j-MbQPjMGDAGzXiTifLzrUPSCAR5djloNMk8YxA',
                language: 'ar',
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            })
            .then(editor => {
                bodyEditor = editor;
            })
            .catch(error => {
                console.error(error);
            });

        function showLoading(text = 'جاري تنفيذ الطلب...') {
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

        async function postAi(url, payload, loadingText = 'جاري تنفيذ طلب الذكاء الاصطناعي...') {
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
                alert('حدث خطأ أثناء الاتصال بخدمة الذكاء الاصطناعي');
                return null;
            } finally {
                hideLoading();
            }
        }

        document.getElementById('btn-generate-page').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.generate-page') }}', {
                prompt: document.getElementById('ai_prompt').value || currentTitle(),
                template: currentTemplate()
            }, 'جاري إعادة توليد الصفحة كاملة...');

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
            }, 'جاري توليد العنوان...');

            if (data && data.title) {
                document.getElementById('title').value = data.title;
            }
        });

        document.getElementById('btn-generate-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.generate-content') }}', {
                title: currentTitle() || document.getElementById('ai_prompt').value,
                template: currentTemplate()
            }, 'جاري توليد المحتوى...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-enhance-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.enhance-content') }}', {
                content: currentBody(),
                instruction: 'حسن الأسلوب والتنظيم والوضوح مع الحفاظ على المعنى'
            }, 'جاري تحسين المحتوى...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-expand-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.expand-content') }}', {
                content: currentBody()
            }, 'جاري توسيع المحتوى...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-simplify-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.simplify-content') }}', {
                content: currentBody()
            }, 'جاري تبسيط المحتوى...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-format-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.format-content') }}', {
                content: currentBody()
            }, 'جاري تنسيق المحتوى...');

            if (data && data.content) {
                setBody(data.content);
            }
        });

        document.getElementById('btn-grammar-body').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.static-pages.ai.check-grammar') }}', {
                content: currentBody()
            }, 'جاري التدقيق اللغوي...');

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
            }, 'جاري ترجمة الحقول...');

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
            }, 'جاري توليد عنوان SEO...');

            if (metaTitle && metaTitle.meta_title) {
                document.getElementById('seo_title').value = metaTitle.meta_title;
            }

            const metaDescription = await postAi(
                '{{ route('admin.static-pages.ai.generate-meta-description') }}', {
                    title: title,
                    content: content
                }, 'جاري توليد وصف SEO...');

            if (metaDescription && metaDescription.meta_description) {
                document.getElementById('seo_description').value = metaDescription.meta_description;
            }
        });

        document.getElementById('page-form').addEventListener('submit', function() {
            showLoading('جاري حفظ التعديلات...');
            document.getElementById('submit-btn').disabled = true;
        });
    </script>
@endsection
