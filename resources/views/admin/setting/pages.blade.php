@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إعدادات الصفحات'))

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

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">إعدادات الصفحات</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">إعدادات الصفحات الثابتة</h5>
                <small class="opacity-75">اختيار وتخصيص الصفحات المرتبطة بإعدادات الموقع</small>
            </div>

            <div class="p-4">
                <form action="{{ route('admin.setting.updatepages') }}" method="POST">
                    @csrf

                    <div class="item-card">
                        <div class="mb-3">
                            <label class="form-label">صفحة من نحن</label>
                            <select name="about_page_id" class="form-select">
                                <option value="">اختر الصفحة</option>
                                @foreach ($pages ?? collect() as $page)
                                    <option value="{{ $page->id }}"
                                        {{ old('about_page_id', $settings['about_page_id'] ?? null) == $page->id ? 'selected' : '' }}>
                                        {{ adminTrans($page->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">صفحة الشروط والأحكام</label>
                            <select name="terms_page_id" class="form-select">
                                <option value="">اختر الصفحة</option>
                                @foreach ($pages ?? collect() as $page)
                                    <option value="{{ $page->id }}"
                                        {{ old('terms_page_id', $settings['terms_page_id'] ?? null) == $page->id ? 'selected' : '' }}>
                                        {{ adminTrans($page->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">صفحة سياسة الخصوصية</label>
                            <select name="privacy_page_id" class="form-select">
                                <option value="">اختر الصفحة</option>
                                @foreach ($pages ?? collect() as $page)
                                    <option value="{{ $page->id }}"
                                        {{ old('privacy_page_id', $settings['privacy_page_id'] ?? null) == $page->id ? 'selected' : '' }}>
                                        {{ adminTrans($page->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">صفحة اتصل بنا</label>
                            <select name="contact_page_id" class="form-select">
                                <option value="">اختر الصفحة</option>
                                @foreach ($pages ?? collect() as $page)
                                    <option value="{{ $page->id }}"
                                        {{ old('contact_page_id', $settings['contact_page_id'] ?? null) == $page->id ? 'selected' : '' }}>
                                        {{ adminTrans($page->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 px-4 pb-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
