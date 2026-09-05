@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('SEO Redirects'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
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
        }

        .item-card:hover {
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-active {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-inactive {
            background: rgba(253, 126, 20, .2);
            color: #fd7e14;
            border: 1px solid rgba(253, 126, 20, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $total = $seoRedirects->total() ?? $seoRedirects->count();
        $active = \App\Models\SeoRedirect::where('is_active', true)->count();
        $inactive = \App\Models\SeoRedirect::where('is_active', false)->count();
        $permanent = \App\Models\SeoRedirect::where('http_code', 301)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">SEO Redirects</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;"><i
                            class="fas fa-route"></i></div>
                    <div class="stats-number">{{ number_format($total) }}</div>
                    <div class="stats-label">إجمالي التحويلات</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);"><i
                            class="fas fa-check-circle"></i></div>
                    <div class="stats-number">{{ number_format($active) }}</div>
                    <div class="stats-label">فعالة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);"><i
                            class="fas fa-ban"></i></div>
                    <div class="stats-number">{{ number_format($inactive) }}</div>
                    <div class="stats-label">غير فعالة</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);"><i
                            class="fas fa-link"></i></div>
                    <div class="stats-number">{{ number_format($permanent) }}</div>
                    <div class="stats-label">301 Redirects</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.seo-redirects.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" class="form-control" name="q" value="{{ request('q') }}"
                            placeholder="بحث في المسار القديم أو الجديد">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">HTTP Code</label>
                        <select name="http_code" class="form-select">
                            <option value="">الكل</option>
                            <option value="301" {{ request('http_code') == '301' ? 'selected' : '' }}>301</option>
                            <option value="302" {{ request('http_code') == '302' ? 'selected' : '' }}>302</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعالة</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير فعالة
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.seo-redirects.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة التحويلات</h5>
                    <small class="opacity-75">إدارة تحويلات الروابط</small>
                </div>
                <a href="{{ route('admin.seo-redirects.create') }}" class="btn btn-light">إضافة Redirect</a>
            </div>

            <div class="p-4">
                @forelse($seoRedirects as $redirect)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <div class="mb-2"><strong>Old:</strong> {{ $redirect->old_path }}</div>
                                <div><strong>New:</strong> {{ $redirect->new_path }}</div>
                            </div>

                            <div class="d-flex gap-2 mt-2 mt-md-0 flex-wrap">
                                <span
                                    class="badge-status {{ $redirect->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                    {{ $redirect->is_active ?? true ? 'فعالة' : 'غير فعالة' }}
                                </span>
                                <span class="badge-status"
                                    style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                                    {{ $redirect->http_code }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.seo-redirects.show', $redirect) }}"
                                class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.seo-redirects.edit', $redirect) }}"
                                class="btn btn-warning btn-sm">تعديل</a>

                            @if (Route::has('admin.seo-redirects.toggle-status'))
                                <form action="{{ route('admin.seo-redirects.toggle-status', $redirect) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-secondary btn-sm" type="submit">تبديل الحالة</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.seo-redirects.destroy', $redirect) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد تحويلات حالياً</div>
                @endforelse

                @if (method_exists($seoRedirects, 'links'))
                    <div class="mt-4">{{ $seoRedirects->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
