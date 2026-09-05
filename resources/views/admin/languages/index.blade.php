@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إدارة اللغات'))

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

        .order-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .order-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
            border-radius: 15px 15px 0 0;
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

        .icon-total {
            background: var(--primary-gradient);
            color: white;
        }

        .icon-active {
            background: rgba(32, 201, 151, .2);
            color: var(--success-color);
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .icon-default {
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .icon-inactive {
            background: rgba(255, 193, 7, .2);
            color: var(--warning-color);
            border: 1px solid rgba(255, 193, 7, .3);
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

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
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

        .status-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .status-filter-btn {
            padding: 8px 20px;
            border-radius: 25px;
            background: rgba(255, 255, 255, .05);
            color: rgba(255, 255, 255, .7);
            border: 1px solid rgba(255, 255, 255, .1);
            cursor: pointer;
            transition: all .3s ease;
            font-size: 14px;
        }

        .status-filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: var(--primary-color);
        }

        .order-item {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all .3s ease;
            border-right: 4px solid transparent;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .order-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, .3);
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .order-item.active-language {
            border-color: var(--success-color);
        }

        .order-item.inactive-language {
            border-color: var(--danger-color);
        }

        .order-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .order-title {
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            font-size: 16px;
        }

        .order-date {
            font-size: 12px;
            color: rgba(255, 255, 255, .7);
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: rgba(255, 255, 255, .8);
            min-width: 80px;
        }

        .detail-value {
            color: rgba(255, 255, 255, .9);
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-active {
            background: linear-gradient(135deg, rgba(21, 87, 36, .2) 0%, rgba(32, 201, 151, .2) 100%);
            color: var(--success-color);
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-inactive {
            background: linear-gradient(135deg, rgba(220, 53, 69, .2) 0%, rgba(253, 126, 20, .2) 100%);
            color: var(--danger-color);
            border: 1px solid rgba(253, 126, 20, .3);
        }

        .status-default {
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .sort-btn {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .8);
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sort-dropdown {
            position: relative;
            display: inline-block;
        }

        .sort-dropdown-content {
            display: none;
            position: absolute;
            background: var(--dark-card);
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, .3);
            border-radius: 10px;
            z-index: 1;
            padding: 10px 0;
            margin-top: 5px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .sort-dropdown:hover .sort-dropdown-content {
            display: block;
        }

        .sort-item {
            padding: 10px 20px;
            cursor: pointer;
            transition: background .3s;
            color: rgba(255, 255, 255, .8);
        }

        .sort-item:hover {
            background: rgba(105, 108, 255, .1);
            color: #fff;
        }

        .sort-item.active {
            background: var(--primary-gradient);
            color: white;
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

        .empty-state-text {
            color: rgba(255, 255, 255, .7);
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .order-header-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $stats = [
            'total' => $languages->total() ?? $languages->count(),
            'active' => \App\Models\Language::where('is_active', true)->count(),
            'default' => \App\Models\Language::where('is_default', true)->count(),
            'inactive' => \App\Models\Language::where('is_active', false)->count(),
        ];
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">اللغات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-total"><i class="fas fa-language"></i></div>
                    <div class="stats-number">{{ number_format($stats['total']) }}</div>
                    <div class="stats-label">إجمالي اللغات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-active"><i class="fas fa-check-circle"></i></div>
                    <div class="stats-number">{{ number_format($stats['active']) }}</div>
                    <div class="stats-label">لغات مفعلة</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-default"><i class="fas fa-star"></i></div>
                    <div class="stats-number">{{ number_format($stats['default']) }}</div>
                    <div class="stats-label">اللغة الافتراضية</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-inactive"><i class="fas fa-ban"></i></div>
                    <div class="stats-number">{{ number_format($stats['inactive']) }}</div>
                    <div class="stats-label">لغات غير مفعلة</div>
                </div>
            </div>
        </div>

        <div class="status-filter">
            <button class="status-filter-btn {{ !request('status') ? 'active' : '' }}" onclick="filterByStatus('all')">
                جميع اللغات
            </button>
            <button class="status-filter-btn {{ request('status') == 'active' ? 'active' : '' }}"
                onclick="filterByStatus('active')">
                <i class="fas fa-check-circle me-2"></i>مفعلة
            </button>
            <button class="status-filter-btn {{ request('status') == 'inactive' ? 'active' : '' }}"
                onclick="filterByStatus('inactive')">
                <i class="fas fa-ban me-2"></i>غير مفعلة
            </button>
            <button class="status-filter-btn {{ request('default') == '1' ? 'active' : '' }}"
                onclick="filterByDefault('1')">
                <i class="fas fa-star me-2"></i>افتراضية
            </button>
        </div>

        <div class="filter-card">
            <h6 class="mb-3"><i class="fas fa-filter me-2"></i>فلترة متقدمة</h6>

            <div class="filter-row">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" placeholder="بحث بالاسم، الكود، الاسم المحلي..."
                        id="searchInput" value="{{ request('search') }}">
                </div>

                <div class="sort-dropdown">
                    <button class="sort-btn">
                        <i class="fas fa-sort-amount-down"></i>
                        الترتيب حسب
                    </button>
                    <div class="sort-dropdown-content">
                        <div class="sort-item {{ request('sort_by') == 'sort_order' && request('sort_direction') == 'asc' ? 'active' : '' }}"
                            onclick="sortBy('sort_order', 'asc')">الترتيب التصاعدي</div>
                        <div class="sort-item {{ request('sort_by') == 'name' && request('sort_direction') == 'asc' ? 'active' : '' }}"
                            onclick="sortBy('name', 'asc')">الاسم أ - ي</div>
                        <div class="sort-item {{ request('sort_by') == 'name' && request('sort_direction') == 'desc' ? 'active' : '' }}"
                            onclick="sortBy('name', 'desc')">الاسم ي - أ</div>
                        <div class="sort-item {{ request('sort_by') == 'created_at' && request('sort_direction') == 'desc' ? 'active' : '' }}"
                            onclick="sortBy('created_at', 'desc')">الأحدث أولاً</div>
                    </div>
                </div>
            </div>

            <div class="filter-row">
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-2"></i>تطبيق الفلاتر
                </button>
                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                </button>
                <form action="{{ route('admin.languages.toggle-all') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="status" value="1">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-power-off me-2"></i>تفعيل الكل
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="order-card p-0">
                    <div class="order-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">قائمة اللغات</h5>
                                <small class="opacity-75">إدارة جميع لغات النظام</small>
                            </div>
                            <div class="d-flex gap-3">
                                <a href="{{ route('admin.languages.create') }}" class="btn btn-light">
                                    <i class="fas fa-plus me-2"></i>إضافة لغة
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if ($languages->isEmpty())
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-language"></i></div>
                                <h5 class="empty-state-text">لا توجد لغات</h5>
                                <p class="text-muted">لم يتم إنشاء أي لغة حتى الآن</p>
                                <a href="{{ route('admin.languages.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>إضافة لغة جديدة
                                </a>
                            </div>
                        @else
                            @foreach ($languages as $language)
                                @php
                                    $isActive = $language->is_active ?? true ? 'active' : 'inactive';
                                    $isDefault = $language->is_default ?? false;
                                @endphp
                                <div
                                    class="order-item {{ $isActive == 'active' ? 'active-language' : 'inactive-language' }}">
                                    <div class="order-header-info">
                                        <div class="order-title">
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                <span>{{ $language->name ?? 'بدون اسم' }}</span>
                                                <span class="badge-status status-{{ $isActive }}">
                                                    {{ $isActive == 'active' ? 'مفعلة' : 'غير مفعلة' }}
                                                </span>
                                                @if ($isDefault)
                                                    <span class="badge-status status-default">افتراضية</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="order-date">
                                            <i class="far fa-clock me-1"></i>
                                            {{ optional($language->created_at)->translatedFormat('d M Y - h:i A') }}
                                        </div>
                                    </div>

                                    <div class="order-details">
                                        <div class="detail-item">
                                            <span class="detail-label">الكود:</span>
                                            <span class="detail-value">{{ $language->code ?? '-' }}</span>
                                        </div>

                                        <div class="detail-item">
                                            <span class="detail-label">الاسم المحلي:</span>
                                            <span class="detail-value">{{ $language->native_name ?? '-' }}</span>
                                        </div>

                                        <div class="detail-item">
                                            <span class="detail-label">الترتيب:</span>
                                            <span class="detail-value">{{ $language->sort_order ?? 0 }}</span>
                                        </div>

                                        <div class="detail-item">
                                            <span class="detail-label">آخر تحديث:</span>
                                            <span
                                                class="detail-value">{{ optional($language->updated_at)->translatedFormat('d M Y') ?? '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="order-actions">
                                        <a href="{{ route('admin.languages.edit', $language) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i>تعديل
                                        </a>

                                        <form action="{{ route('admin.languages.toggle', $language) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-power-off me-1"></i>تبديل الحالة
                                            </button>
                                        </form>

                                        @if (!($language->is_default ?? false))
                                            <form action="{{ route('admin.languages.set-default', $language) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">
                                                    <i class="fas fa-star me-1"></i>اجعلها افتراضية
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $language->id }}" data-name="{{ $language->name ?? 'اللغة' }}">
                                            <i class="fas fa-trash me-1"></i>حذف
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if ($languages->hasPages())
                                <div class="m-3">
                                    {{ $languages->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let searchTimeout;
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 500);
            });

            $('.delete-btn').on('click', function() {
                const languageId = $(this).data('id');
                const languageName = $(this).data('name');

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: `سيتم حذف اللغة "${languageName}" نهائياً`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذف',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.languages.destroy', '') }}/" +
                                languageId,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف',
                                    text: 'تم حذف اللغة بنجاح',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطأ',
                                    text: 'حدث خطأ أثناء الحذف',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
            });
        });

        function filterByStatus(status) {
            if (status === 'all') {
                updateUrl({
                    status: null
                });
            } else {
                updateUrl({
                    status: status,
                    default: null
                });
            }
        }

        function filterByDefault(value) {
            updateUrl({
                default: value,
                status: null
            });
        }

        function sortBy(sortBy, sortDirection) {
            updateUrl({
                sort_by: sortBy,
                sort_direction: sortDirection
            });
        }

        function applyFilters() {
            updateUrl({
                search: $('#searchInput').val()
            });
        }

        function resetFilters() {
            window.location.href = "{{ route('admin.languages.index') }}";
        }

        function updateUrl(params) {
            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);

            Object.keys(params).forEach(key => {
                if (params[key] === null || params[key] === '') {
                    searchParams.delete(key);
                } else {
                    searchParams.set(key, params[key]);
                }
            });

            searchParams.set('page', '1');
            url.search = searchParams.toString();
            window.location.href = url.toString();
        }
    </script>
@endsection
