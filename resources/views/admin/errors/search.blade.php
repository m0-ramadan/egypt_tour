@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('البحث في الأخطاء'))

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

        .result-card {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }

        .result-content {
            background: rgba(0, 0, 0, .2);
            border-radius: 10px;
            padding: 12px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.8;
            font-size: 13px;
            color: #ddd;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.errors.index') }}">سجل الأخطاء</a></li>
                <li class="breadcrumb-item active">بحث</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">البحث داخل ملفات الأخطاء</h5>
                <small class="opacity-75">ابحث باسم الملف أو نص الخطأ أو محتوى السجل</small>
            </div>

            <div class="form-body">
                <form method="GET" action="{{ route('admin.errors.search') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">كلمة البحث</label>
                            <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                                placeholder="مثال: Class not found أو SQLSTATE">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">النوع</label>
                            <select name="type" class="form-select">
                                <option value="">الكل</option>
                                <option value="php" {{ request('type') == 'php' ? 'selected' : '' }}>PHP</option>
                                <option value="laravel" {{ request('type') == 'laravel' ? 'selected' : '' }}>Laravel
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">بحث</button>
                        </div>
                    </div>
                </form>

                @if (request()->filled('q'))
                    <div class="mb-4">
                        <h6>نتائج البحث عن: <span class="text-info">{{ request('q') }}</span></h6>
                    </div>

                    @forelse($results ?? [] as $result)
                        <div class="result-card">
                            <div class="mb-2">
                                <strong>الملف:</strong> {{ $result['file'] ?? '-' }}
                            </div>

                            @if (!empty($result['line']))
                                <div class="mb-2">
                                    <strong>السطر:</strong> {{ $result['line'] }}
                                </div>
                            @endif

                            @if (!empty($result['date']))
                                <div class="mb-2">
                                    <strong>التاريخ:</strong> {{ $result['date'] }}
                                </div>
                            @endif

                            <div class="result-content">{{ $result['content'] ?? ($result['message'] ?? '-') }}</div>
                        </div>
                    @empty
                        <div class="text-center py-4">لا توجد نتائج مطابقة</div>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
@endsection
