@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('سجل الأخطاء'))

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

        .main-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
            border-top: 4px solid var(--primary-color);
            transition: transform .3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .filter-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            transition: all .3s ease;
        }

        .item-card:hover {
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
            transform: translateX(-5px);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-error {
            background: rgba(220, 53, 69, .2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, .3);
        }

        .status-warning {
            background: rgba(255, 193, 7, .2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, .3);
        }

        .status-info {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .log-preview {
            background: rgba(0, 0, 0, .2);
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            line-height: 1.8;
            color: #d7d7d7;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
@endsection

@section('content')
    @php
        $totalErrors = is_countable($errors ?? null) ? count($errors) : 0;
        $phpErrors = is_countable($phpErrors ?? null) ? count($phpErrors) : 0;
        $todayErrors = collect($errors ?? [])
            ->filter(function ($item) {
                return !empty($item['date']) &&
                    \Illuminate\Support\Str::contains($item['date'], now()->format('Y-m-d'));
            })
            ->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">سجل الأخطاء</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalErrors) }}</div>
                    <div class="stats-label">إجمالي السجلات</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(220,53,69,.2); color:#dc3545; border:1px solid rgba(220,53,69,.3);">
                        <i class="fas fa-bug"></i>
                    </div>
                    <div class="stats-number">{{ number_format($phpErrors) }}</div>
                    <div class="stats-label">أخطاء PHP</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stats-number">{{ number_format($todayErrors) }}</div>
                    <div class="stats-label">أخطاء اليوم</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.errors.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">بحث داخل السجلات</label>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                            placeholder="ابحث في ملف الخطأ أو الرسالة">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">النوع</label>
                        <select name="type" class="form-select">
                            <option value="">الكل</option>
                            <option value="php" {{ request('type') == 'php' ? 'selected' : '' }}>PHP</option>
                            <option value="laravel" {{ request('type') == 'laravel' ? 'selected' : '' }}>Laravel</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.errors.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">سجل الأخطاء</h5>
                    <small class="opacity-75">عرض ملفات الأخطاء وسجلات الاستثناءات</small>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.errors.php-errors') }}" class="btn btn-light btn-sm">أخطاء PHP</a>
                    <a href="{{ route('admin.errors.search') }}" class="btn btn-light btn-sm">بحث متقدم</a>

                    @if (Route::has('admin.errors.clear-all'))
                        <form action="{{ route('admin.errors.clear-all') }}" method="POST">
                            @csrf
                            <button class="btn btn-danger btn-sm" type="submit">حذف الكل</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="p-4">
                @forelse($errors ?? [] as $error)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $error['file'] ?? ($error['name'] ?? 'log file') }}</h6>
                                <small class="text-light opacity-75">{{ $error['date'] ?? '-' }}</small>
                            </div>

                            <span
                                class="badge-status {{ ($error['level'] ?? 'error') === 'warning' ? 'status-warning' : 'status-error' }}">
                                {{ strtoupper($error['level'] ?? 'error') }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <strong>الحجم:</strong> {{ $error['size'] ?? '-' }}
                        </div>

                        @if (!empty($error['preview']))
                            <div class="log-preview mb-3">{{ $error['preview'] }}</div>
                        @endif

                        <div class="d-flex gap-2 flex-wrap">
                            @if (!empty($error['filename']) && Route::has('admin.errors.download'))
                                <a href="{{ route('admin.errors.download', $error['filename']) }}"
                                    class="btn btn-info btn-sm">
                                    تحميل
                                </a>
                            @endif

                            @if (Route::has('admin.errors.destroy'))
                                <form action="{{ route('admin.errors.destroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="filename" value="{{ $error['filename'] ?? '' }}">
                                    <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد ملفات أخطاء حالياً</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
