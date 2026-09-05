@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إنشاء مقال بالذكاء الاصطناعي'))

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

        .preview-box {
            background: rgba(255, 255, 255, .05);
            border: 1px dashed rgba(255, 255, 255, .15);
            border-radius: 12px;
            padding: 20px;
            min-height: 180px;
            white-space: pre-wrap;
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

        .page-loader .loader-box {
            width: 100%;
            max-width: 420px;
            background: #2b3b4c;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .page-loader .loader-spinner {
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

    <div class="page-loader" id="pageLoader">
        <div class="loader-box">
            <div class="loader-spinner"></div>
            <div class="loader-title">جاري حفظ المقال...</div>
            <div class="loader-text">برجاء الانتظار أثناء حفظ البيانات</div>

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
                <li class="breadcrumb-item"><a href="{{ route('admin.articles.index') }}">المقالات</a></li>
                <li class="breadcrumb-item active">إنشاء مقال بالذكاء الاصطناعي</li>
            </ol>
        </nav>

        <div class="panel-card">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">إنشاء مقال بالذكاء الاصطناعي</h5>
                    <small class="opacity-75">اكتب الفكرة ودع الذكاء الاصطناعي ينشئ المقال</small>
                </div>
                <a href="{{ route('admin.articles.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="panel-body">
                <div class="ai-box">
                    <div class="section-title">أدوات الذكاء الاصطناعي</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">الفكرة / البرومبت</label>
                            <textarea id="prompt" class="form-control" rows="5"
                                placeholder="مثال: اكتب مقالًا احترافيًا عن أفضل أماكن السياحة في الأقصر للمسافرين العرب مع نصائح عملية وخاتمة تسويقية">{{ old('prompt') }}</textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">النبرة</label>
                            <input type="text" id="tone" class="form-control"
                                value="{{ old('tone', 'professional') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">نوع المقال</label>
                            <input type="text" id="post_type_preview" class="form-control"
                                value="{{ old('post_type', 'blog') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">التصنيف</label>
                            <input type="text" id="category_preview" class="form-control" placeholder="اختياري">
                        </div>
                    </div>

                    <div class="ai-actions">
                        <button type="button" class="btn btn-ai" id="btn-generate-full">توليد المقال كاملًا</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-title">توليد العنوان</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-content">توليد المحتوى</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-excerpt">توليد الملخص</button>
                        <button type="button" class="btn btn-ai" id="btn-generate-seo">توليد SEO</button>
                    </div>
                </div>

                <form action="{{ route('admin.articles.store-with-ai') }}" method="POST" id="articleAiForm">
                    @csrf

                    <div class="section-title">بيانات المقال</div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">البرومبت</label>
                            <textarea name="prompt" id="prompt_submit" class="form-control" rows="5">{{ old('prompt') }}</textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">نوع المقال</label>
                            <input type="text" name="post_type" class="form-control"
                                value="{{ old('post_type', 'blog') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">المعرف الخاص بالكاتب</label>
                            <input type="number" name="author_id" class="form-control" value="{{ old('author_id') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                value="{{ old('published_at') }}">
                        </div>

                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="w-100">
                                <div class="form-control d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span>منشور</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="w-100">
                                <div class="form-control d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="is_featured"
                                        value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <span>مميز</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-4">معاينة أولية</div>
                    <div class="preview-box" id="preview-box">سيتم عرض النتيجة الأولية هنا بعد التوليد.</div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">حفظ المقال المولد</button>
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-secondary">إنشاء يدوي</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const csrfToken = '{{ csrf_token() }}';

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

        function previewText(data) {
            let text = '';

            if (data.title) text += 'العنوان: ' + (data.title.ar || data.title.en || data.title) + '\n\n';
            if (data.excerpt) text += 'الملخص: ' + (data.excerpt.ar || data.excerpt.en || data.excerpt) + '\n\n';
            if (data.content) text += 'المحتوى: ' + (data.content.ar || data.content.en || data.content) + '\n\n';
            if (data.seo_title) text += 'SEO Title: ' + (data.seo_title.ar || data.seo_title.en || data.seo_title) + '\n\n';
            if (data.seo_description) text += 'SEO Description: ' + (data.seo_description.ar || data.seo_description.en ||
                data.seo_description);

            document.getElementById('preview-box').textContent = text || 'لم يتم إرجاع نتيجة.';
        }

        document.getElementById('btn-generate-full').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate') }}', {
                prompt: document.getElementById('prompt').value,
                category: document.getElementById('category_preview').value,
                tone: document.getElementById('tone').value
            });

            if (!data) return;

            previewText(data);
            document.getElementById('prompt_submit').value = document.getElementById('prompt').value;
        });

        document.getElementById('btn-generate-title').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate-title') }}', {
                topic: document.getElementById('prompt').value,
                tone: document.getElementById('tone').value
            });

            if (data && data.title) {
                document.getElementById('preview-box').textContent = 'العنوان المقترح:\n' + data.title;
            }
        });

        document.getElementById('btn-generate-content').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate-content') }}', {
                title: document.getElementById('prompt').value,
                tone: document.getElementById('tone').value
            });

            if (data && data.content) {
                document.getElementById('preview-box').textContent = data.content;
            }
        });

        document.getElementById('btn-generate-excerpt').addEventListener('click', async function() {
            const data = await postAi('{{ route('admin.articles.ai-generate-excerpt') }}', {
                title: document.getElementById('prompt').value
            });

            if (data && data.excerpt) {
                document.getElementById('preview-box').textContent = 'الملخص:\n' + data.excerpt;
            }
        });

        document.getElementById('btn-generate-seo').addEventListener('click', async function() {
            const metaTitle = await postAi('{{ route('admin.articles.ai-generate-meta-title') }}', {
                title: document.getElementById('prompt').value
            });

            const metaDescription = await postAi(
                '{{ route('admin.articles.ai-generate-meta-description') }}', {
                    title: document.getElementById('prompt').value
                });

            let text = '';
            if (metaTitle?.meta_title) text += 'SEO Title:\n' + metaTitle.meta_title + '\n\n';
            if (metaDescription?.meta_description) text += 'SEO Description:\n' + metaDescription
                .meta_description;

            document.getElementById('preview-box').textContent = text || 'لم يتم إرجاع نتائج SEO.';
        });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('articleAiForm');
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
