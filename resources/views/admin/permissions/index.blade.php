@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إدارة الصلاحيات'))

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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border-top: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
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

        .icon-modules {
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .icon-system {
            background: rgba(32, 201, 151, .2);
            color: var(--success-color);
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .icon-custom {
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

        .permission-row {
            border-color: var(--info-color);
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
        $total = $permissions->total() ?? $permissions->count();
        $names = $permissions->pluck('name');
        $modules = $names
            ->map(function ($name) {
                $parts = explode(' ', trim($name), 2);
                return $parts[1] ?? $name;
            })
            ->unique()
            ->count();
        $system = $names
            ->filter(
                fn($name) => str_contains($name, 'admin') ||
                    str_contains($name, 'role') ||
                    str_contains($name, 'permission'),
            )
            ->count();
        $custom = max($total - $system, 0);
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الصلاحيات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-total"><i class="fas fa-key"></i></div>
                    <div class="stats-number">{{ number_format($total) }}</div>
                    <div class="stats-label">إجمالي الصلاحيات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-modules"><i class="fas fa-cubes"></i></div>
                    <div class="stats-number">{{ number_format($modules) }}</div>
                    <div class="stats-label">عدد الموديولات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-system"><i class="fas fa-shield-halved"></i></div>
                    <div class="stats-number">{{ number_format($system) }}</div>
                    <div class="stats-label">صلاحيات النظام</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon icon-custom"><i class="fas fa-sliders"></i></div>
                    <div class="stats-number">{{ number_format($custom) }}</div>
                    <div class="stats-label">صلاحيات مخصصة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <h6 class="mb-3"><i class="fas fa-filter me-2"></i>فلترة متقدمة</h6>

            <div class="filter-row">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" placeholder="بحث باسم الصلاحية أو الموديول..."
                        id="searchInput" value="{{ request('search') }}">
                </div>

                <div class="sort-dropdown">
                    <button class="sort-btn">
                        <i class="fas fa-sort-amount-down"></i>
                        الترتيب حسب
                    </button>
                    <div class="sort-dropdown-content">
                        <div class="sort-item {{ request('sort_by') == 'name' && request('sort_direction') == 'asc' ? 'active' : '' }}"
                            onclick="sortBy('name', 'asc')">الاسم أ - ي</div>
                        <div class="sort-item {{ request('sort_by') == 'name' && request('sort_direction') == 'desc' ? 'active' : '' }}"
                            onclick="sortBy('name', 'desc')">الاسم ي - أ</div>
                        <div class="sort-item {{ request('sort_by') == 'created_at' && request('sort_direction') == 'desc' ? 'active' : '' }}"
                            onclick="sortBy('created_at', 'desc')">الأحدث أولاً</div>
                    </div>
                </div>

                <input type="text" class="form-control" id="moduleFilter" placeholder="اسم الموديول"
                    value="{{ request('module') }}">
            </div>

            <div class="filter-row">
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-2"></i>تطبيق الفلاتر
                </button>
                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                </button>
                <form action="{{ route('admin.permissions.generate') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="module" class="form-control" placeholder="اسم الموديول لإنشاء صلاحياته">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-magic me-2"></i>توليد صلاحيات
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
                                <h5 class="mb-0">قائمة الصلاحيات</h5>
                                <small class="opacity-75">إدارة جميع صلاحيات النظام</small>
                            </div>
                            <div class="d-flex gap-3">
                                <a href="{{ route('admin.permissions.create') }}" class="btn btn-light">
                                    <i class="fas fa-plus me-2"></i>إضافة صلاحية
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if ($permissions->isEmpty())
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-key"></i></div>
                                <h5 class="empty-state-text">لا توجد صلاحيات</h5>
                                <p class="text-muted">لم يتم إنشاء أي صلاحية حتى الآن</p>
                                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>إضافة صلاحية جديدة
                                </a>
                            </div>
                        @else
                            @foreach ($permissions as $permission)
                                @php
                                    $parts = explode(' ', trim($permission->name), 2);
                                    $action = $parts[0] ?? '-';
                                    $module = $parts[1] ?? $permission->name;
                                @endphp
                                <div class="order-item permission-row">
                                    <div class="order-header-info">
                                        <div class="order-title">
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                <span>{{ $permission->name }}</span>
                                                <span class="badge-status">{{ $action }}</span>
                                            </div>
                                        </div>
                                        <div class="order-date">
                                            <i class="far fa-clock me-1"></i>
                                            {{ optional($permission->created_at)->translatedFormat('d M Y - h:i A') }}
                                        </div>
                                    </div>

                                    <div class="order-details">
                                        <div class="detail-item">
                                            <span class="detail-label">الموديول:</span>
                                            <span class="detail-value">{{ $module }}</span>
                                        </div>

                                        <div class="detail-item">
                                            <span class="detail-label">النوع:</span>
                                            <span class="detail-value">{{ $action }}</span>
                                        </div>

                                        <div class="detail-item">
                                            <span class="detail-label">المعرف:</span>
                                            <span class="detail-value">#{{ $permission->id }}</span>
                                        </div>

                                        <div class="detail-item">
                                            <span class="detail-label">الحارس:</span>
                                            <span class="detail-value">{{ $permission->guard_name ?? 'admin' }}</span>
                                        </div>
                                    </div>

                                    <div class="order-actions">
                                        <a href="{{ route('admin.permissions.edit', $permission) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i>تعديل
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $permission->id }}" data-name="{{ $permission->name }}">
                                            <i class="fas fa-trash me-1"></i>حذف
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            @include('admin.layout.pagination', ['paginator' => $permissions])


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
            $('#searchInput, #moduleFilter').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 500);
            });

            $('.delete-btn').on('click', function() {
                const permissionId = $(this).data('id');
                const permissionName = $(this).data('name');

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: `سيتم حذف الصلاحية "${permissionName}" نهائياً`,
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
                            url: "{{ route('admin.permissions.destroy', '') }}/" +
                                permissionId,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف',
                                    text: 'تم حذف الصلاحية بنجاح',
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

        function sortBy(sortBy, sortDirection) {
            updateUrl({
                sort_by: sortBy,
                sort_direction: sortDirection
            });
        }

        function applyFilters() {
            updateUrl({
                search: $('#searchInput').val(),
                module: $('#moduleFilter').val()
            });
        }

        function resetFilters() {
            window.location.href = "{{ route('admin.permissions.index') }}";
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
