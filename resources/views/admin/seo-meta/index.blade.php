@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('SEO Meta'))

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
            color: white;
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
            margin-bottom: 5px;
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

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-right: 40px;
            border-radius: 25px;
            background: rgba(255, 255, 255, .05);
            border-color: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .search-box input:focus {
            background: rgba(255, 255, 255, .1);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, .5);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all .3s ease;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card:hover {
            transform: translateX(-5px);
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: rgba(255, 255, 255, .8);
            margin-left: 5px;
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state-icon {
            font-size: 60px;
            color: rgba(255, 255, 255, .1);
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    @php
        $totalSeo = $seoMeta->total() ?? $seoMeta->count();
        $withCanonical = \App\Models\SeoMeta::whereNotNull('canonical_url')->count();
        $withOg = \App\Models\SeoMeta::whereNotNull('og_title')->orWhereNotNull('og_description')->count();
        $localized = \App\Models\SeoMeta::whereNotNull('locale')->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">SEO Meta</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-magnifying-glass-chart"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalSeo) }}</div>
                    <div class="stats-label">إجمالي السجلات</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="stats-number">{{ number_format($withCanonical) }}</div>
                    <div class="stats-label">Canonical URL</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-share-nodes"></i>
                    </div>
                    <div class="stats-number">{{ number_format($withOg) }}</div>
                    <div class="stats-label">Open Graph</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-language"></i>
                    </div>
                    <div class="stats-number">{{ number_format($localized) }}</div>
                    <div class="stats-label">سجلات مترجمة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.seo-meta.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="q" value="{{ request('q') }}"
                                placeholder="بحث بعنوان SEO أو النوع أو اللغة">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">اللغة</label>
                        <input type="text" class="form-control" name="locale" value="{{ request('locale') }}"
                            placeholder="ar / en">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Model ID</label>
                        <input type="number" class="form-control" name="model_id" value="{{ request('model_id') }}">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.seo-meta.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">قائمة SEO Meta</h5>
                <small class="opacity-75">إدارة بيانات الـ SEO الخاصة بالموديلات المختلفة</small>
            </div>

            <div class="p-4">
                @forelse($seoMeta as $item)
                    <div class="item-card">
                        <div class="item-header">
                            <div>
                                <h6 class="mb-1">{{ $item->meta_title ?: 'بدون Meta Title' }}</h6>
                                <small class="text-light opacity-75">{{ $item->model_type ?? '-' }}</small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                @if ($item->locale)
                                    <span class="badge-status">{{ $item->locale }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="detail-row">
                            <div>
                                <span class="detail-label">Model ID:</span>
                                <span>{{ $item->model_id ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="detail-label">Canonical:</span>
                                <span>{{ $item->canonical_url ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="detail-label">OG Title:</span>
                                <span>{{ $item->og_title ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="detail-label">التاريخ:</span>
                                <span>{{ optional($item->created_at)->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="detail-label">الوصف:</span>
                            <span>{{ \Illuminate\Support\Str::limit($item->meta_description ?? '-', 180) }}</span>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            @if (Route::has('admin.seo-meta.by-model'))
                                <a href="{{ route('admin.seo-meta.by-model', ['type' => $item->model_type, 'id' => $item->model_id]) }}"
                                    class="btn btn-info btn-sm">
                                    عرض حسب العنصر
                                </a>
                            @endif

                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editSeoModal{{ $item->id }}">
                                تعديل
                            </button>

                            <form action="{{ route('admin.seo-meta.destroy', $item) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="editSeoModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content bg-dark text-white">
                                <form action="{{ route('admin.seo-meta.update', $item) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                        <h5 class="modal-title">تعديل SEO Meta</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Locale</label>
                                                <input type="text" name="locale" class="form-control"
                                                    value="{{ $item->locale }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                    value="{{ $item->meta_title }}">
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea name="meta_description" class="form-control" rows="3">{{ $item->meta_description }}</textarea>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Meta Keywords</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                    value="{{ $item->meta_keywords }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Canonical URL</label>
                                                <input type="text" name="canonical_url" class="form-control"
                                                    value="{{ $item->canonical_url }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">OG Title</label>
                                                <input type="text" name="og_title" class="form-control"
                                                    value="{{ $item->og_title }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">OG Image</label>
                                                <input type="text" name="og_image" class="form-control"
                                                    value="{{ $item->og_image }}">
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">OG Description</label>
                                                <textarea name="og_description" class="form-control" rows="3">{{ $item->og_description }}</textarea>
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Schema JSON</label>
                                                <textarea name="schema_json" class="form-control" rows="5">{{ is_array($item->schema_json) ? json_encode($item->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $item->schema_json }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-primary" type="submit">حفظ</button>
                                        <button class="btn btn-secondary" type="button"
                                            data-bs-dismiss="modal">إلغاء</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-magnifying-glass-chart"></i>
                        </div>
                        <h5 class="empty-state-text">لا توجد سجلات SEO حالياً</h5>
                    </div>
                @endforelse

                @if (method_exists($seoMeta, 'links'))
                    <div class="mt-4">
                        {{ $seoMeta->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
