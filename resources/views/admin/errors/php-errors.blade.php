@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('أخطاء PHP'))

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

        .error-block {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-right: 4px solid #dc3545;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .error-title {
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }

        .error-meta {
            color: rgba(255, 255, 255, .7);
            font-size: 13px;
            margin-bottom: 12px;
        }

        .error-content {
            background: rgba(0, 0, 0, .25);
            border-radius: 10px;
            padding: 15px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.9;
            color: #e9e9e9;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.errors.index') }}">سجل الأخطاء</a></li>
                <li class="breadcrumb-item active">أخطاء PHP</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">أخطاء PHP</h5>
                    <small class="opacity-75">عرض وتحليل سجلات PHP Errors</small>
                </div>
                <a href="{{ route('admin.errors.index') }}" class="btn btn-light btn-sm">رجوع</a>
            </div>

            <div class="p-4">
                @forelse($phpErrors ?? [] as $error)
                    <div class="error-block">
                        <div class="error-title">
                            {{ $error['title'] ?? ($error['type'] ?? 'PHP Error') }}
                        </div>

                        <div class="error-meta">
                            <strong>الملف:</strong> {{ $error['file'] ?? '-' }}
                            @if (!empty($error['line']))
                                | <strong>السطر:</strong> {{ $error['line'] }}
                            @endif
                            @if (!empty($error['date']))
                                | <strong>التاريخ:</strong> {{ $error['date'] }}
                            @endif
                        </div>

                        <div class="error-content">{{ $error['message'] ?? ($error['content'] ?? '-') }}</div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد أخطاء PHP حالياً</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
