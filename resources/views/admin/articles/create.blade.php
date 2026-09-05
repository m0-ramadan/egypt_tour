@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة مقال'))

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

        .btn-ai {
            background: rgba(12, 99, 228, .18);
            border: 1px solid rgba(12, 99, 228, .35);
            color: #9ec5fe;
        }

        .btn-ai:hover {
            background: rgba(12, 99, 228, .28);
            color: #fff;
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

        .ck.ck-editor {
            width: 100%;
        }

        .ck.ck-editor__main>.ck-editor__editable {
            min-height: 360px;
            background: #fff !important;
            color: #000 !important;
            border-radius: 0 0 10px 10px !important;
        }

        .ck.ck-toolbar {
            border-radius: 10px 10px 0 0 !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="loading-overlay" id="ai-loading">
        <div class="loading-box">
            <div class="spinner"></div>
            <div>جاري تنفيذ طلب الذكاء الاصطناعي...</div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.articles.index') }}">المقالات</a></li>
                <li class="breadcrumb-item active">إضافة مقال</li>
            </ol>
        </nav>

        <div class="order-card">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">إضافة مقال جديد</h5>
                        <small class="opacity-75">إدخال بيانات المقال الأساسية مع أدوات الذكاء الاصطناعي</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.articles.create-with-ai') }}" class="btn btn-light">
                            إنشاء بالذكاء الاصطناعي
                        </a>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-light">
                            رجوع
                        </a>
                    </div>
                </div>
            </div>

            <div class="form-body">
                <div class="section-title">أدوات الذكاء الاصطناعي</div>
                <div class="ai-actions mb-4">
                    <button type="button" class="btn btn-ai" id="btn-ai-title">توليد العنوان</button>
                    <button type="button" class="btn btn-ai" id="btn-ai-content">توليد المحتوى</button>
                    <button type="button" class="btn btn-ai" id="btn-ai-excerpt">توليد الملخص</button>
                    <button type="button" class="btn btn-ai" id="btn-ai-enhance">تحسين المحتوى</button>
                    <button type="button" class="btn btn-ai" id="btn-ai-translate">ترجمة الكل</button>
                    <button type="button" class="btn btn-ai" id="btn-ai-seo">توليد SEO</button>
                </div>

                <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="section-title">البيانات الأساسية</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">عنوان المقال</label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="{{ old('title') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                value="{{ old('slug') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">النوع</label>
                            <input type="text" name="article_type" class="form-control"
                                value="{{ old('article_type') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                value="{{ old('published_at') }}">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">الوصف المختصر</label>
                            <textarea id="excerpt" name="excerpt" class="form-control" rows="3">{{ old('excerpt') }}</textarea>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">المحتوى</label>
                            <textarea id="content-editor" name="content" class="form-control" rows="10">{{ old('content') }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">صورة المقال</label>
                            <input type="file" name="featured_image" class="form-control">
                        </div>
                    </div>

                    <div class="section-title mt-4">SEO</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" id="seo_title" name="seo_title" class="form-control"
                                value="{{ old('seo_title') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">SEO Description</label>
                            <textarea id="seo_description" name="seo_description" class="form-control" rows="3">{{ old('seo_description') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" id="is_active"
                                    name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">منشور</label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" id="is_featured"
                                    name="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">مميز</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">حفظ</button>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        let contentEditor;
        const csrfToken = '{{ csrf_token() }}';

        ClassicEditor
            .create(document.querySelector('#content-editor'), {
                language: 'ar'
            })
            .then(editor => {
                contentEditor = editor;
            })
            .catch(error => console.error(error));

        function showAiLoading() {
            document.getElementById('ai-loading').style.display = 'flex';
        }

        function hideAiLoading() {
            document.getElementById('ai-loading').style.display = 'none';
        }

        async function postAi(url, payload) {
            showAiLoading();

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
                hideAiLoading();
            }
        }

        function getContent() {
            return contentEditor ? contentEditor.getData() : '';
        }

        function setContent(value) {
            if (contentEditor) {
                contentEditor.setData(value || '');
            }
        }

        document.getElementById('btn-ai-title').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate-title') }}', {
                topic: document.getElementById('title').value || document.getElementById('excerpt')
                    .value || 'مقال جديد'
            });

            if (data?.title) {
                document.getElementById('title').value = data.title;
            }
        });

        document.getElementById('btn-ai-content').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate-content') }}', {
                title: document.getElementById('title').value || 'مقال جديد',
                excerpt: document.getElementById('excerpt').value || ''
            });

            if (data?.content) {
                setContent(data.content);
            }
        });

        document.getElementById('btn-ai-excerpt').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate-excerpt') }}', {
                title: document.getElementById('title').value || 'مقال جديد',
                content: getContent()
            });

            if (data?.excerpt) {
                document.getElementById('excerpt').value = data.excerpt;
            }
        });

        document.getElementById('btn-ai-enhance').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-enhance-content') }}', {
                content: getContent(),
                instruction: 'حسن الأسلوب والتنظيم والوضوح مع الحفاظ على المعنى'
            });

            if (data?.content) {
                setContent(data.content);
            }
        });

        document.getElementById('btn-ai-translate').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-translate-all') }}', {
                title: document.getElementById('title').value,
                excerpt: document.getElementById('excerpt').value,
                content: getContent(),
                seo_title: document.getElementById('seo_title').value,
                seo_description: document.getElementById('seo_description').value
            });

            if (!data) return;

            if (data.title) document.getElementById('title').value = data.title.ar || data.title.en || '';
            if (data.excerpt) document.getElementById('excerpt').value = data.excerpt.ar || data.excerpt.en ||
                '';
            if (data.content) setContent(data.content.ar || data.content.en || '');
            if (data.seo_title) document.getElementById('seo_title').value = data.seo_title.ar || data.seo_title
                .en || '';
            if (data.seo_description) document.getElementById('seo_description').value = data.seo_description
                .ar || data.seo_description.en || '';
        });

        document.getElementById('btn-ai-seo').addEventListener('click', async function() {
            const metaTitle = await postAi('{{ route('admin.articles.ai-generate-meta-title') }}', {
                title: document.getElementById('title').value,
                content: getContent()
            });

            const metaDescription = await postAi(
                '{{ route('admin.articles.ai-generate-meta-description') }}', {
                    title: document.getElementById('title').value,
                    content: getContent()
                });

            if (metaTitle?.meta_title) document.getElementById('seo_title').value = metaTitle.meta_title;
            if (metaDescription?.meta_description) document.getElementById('seo_description').value =
                metaDescription.meta_description;
        });
    </script>
@endsection
