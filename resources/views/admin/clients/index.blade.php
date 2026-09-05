@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إدارة العملاء'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --text-muted: #6c757d;
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

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-active {
            background: linear-gradient(135deg, rgba(21, 87, 36, 0.2) 0%, rgba(32, 201, 151, 0.2) 100%);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, 0.3);
        }

        .status-inactive {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(253, 126, 20, 0.2) 100%);
            color: #fd7e14;
            border: 1px solid rgba(253, 126, 20, 0.3);
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border-top: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
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

        .icon-revenue {
            background: rgba(12, 99, 228, 0.2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, 0.3);
        }

        .icon-pending {
            background: rgba(133, 100, 4, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .icon-delivered {
            background: linear-gradient(135deg, rgba(21, 87, 36, 0.2) 0%, rgba(32, 201, 151, 0.2) 100%);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, 0.3);
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .search-box input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
        }

        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
        }

        .order-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            border-right: 4px solid transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            background: rgba(105, 108, 255, 0.1);
            border-color: var(--primary-color);
        }

        .order-item.active-client {
            border-color: #20c997;
        }

        .order-item.inactive-client {
            border-color: #fd7e14;
        }

        .order-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-title {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }

        .order-date {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
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
            color: rgba(255, 255, 255, 0.8);
            min-width: 80px;
        }

        .detail-value {
            color: rgba(255, 255, 255, 0.9);
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state-icon {
            font-size: 60px;
            color: rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .empty-state-text {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
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
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .status-filter-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .status-filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: var(--primary-color);
        }

        .sort-dropdown {
            position: relative;
            display: inline-block;
        }

        .sort-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sort-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sort-dropdown-content {
            display: none;
            position: absolute;
            background: var(--dark-card);
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            z-index: 1;
            padding: 10px 0;
            margin-top: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sort-dropdown:hover .sort-dropdown-content {
            display: block;
        }

        .sort-item {
            padding: 10px 20px;
            cursor: pointer;
            transition: background 0.3s;
            color: rgba(255, 255, 255, 0.8);
        }

        .sort-item:hover {
            background: rgba(105, 108, 255, 0.1);
            color: #fff;
        }

        .sort-item.active {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4a9a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(105, 108, 255, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
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

            .order-actions {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $stats = [
            'total' => $clients->total() ?? $clients->count(),
            'active' => \App\Models\Client::where('newsletter_subscribed', true)->count(),
            'bookings' => \App\Models\Booking::count(),
            'revenue' => \App\Models\Booking::sum('total_amount'),
        ];
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y" bis_skin_checked="1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.index') }}">الرئيسية</a>
                </li>
                <li class="breadcrumb-item active">العملاء</li>
            </ol>
        </nav>

        <div class="row mb-4" bis_skin_checked="1">
            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-total" bis_skin_checked="1">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">{{ number_format($stats['total']) }}</div>
                    <div class="stats-label" bis_skin_checked="1">إجمالي العملاء</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-delivered" bis_skin_checked="1">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">{{ number_format($stats['active']) }}</div>
                    <div class="stats-label" bis_skin_checked="1">عملاء نشطون</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-pending" bis_skin_checked="1">
                        <i class="fas fa-suitcase-rolling"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">{{ number_format($stats['bookings']) }}</div>
                    <div class="stats-label" bis_skin_checked="1">إجمالي الحجوزات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-revenue" bis_skin_checked="1">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">{{ number_format($stats['revenue'], 2) }}</div>
                    <div class="stats-label" bis_skin_checked="1">قيمة الحجوزات</div>
                </div>
            </div>
        </div>

        <div class="status-filter" bis_skin_checked="1">
            <button class="status-filter-btn {{ !request('status') ? 'active' : '' }}" onclick="filterByStatus('all')">
                جميع العملاء
            </button>
            <button class="status-filter-btn {{ request('status') == 'active' ? 'active' : '' }}"
                onclick="filterByStatus('active')">
                <i class="fas fa-user-check me-2"></i>نشط
            </button>
            <button class="status-filter-btn {{ request('status') == 'inactive' ? 'active' : '' }}"
                onclick="filterByStatus('inactive')">
                <i class="fas fa-user-times me-2"></i>غير نشط
            </button>
        </div>

        <div class="filter-card" bis_skin_checked="1">
            <h6 class="mb-3"><i class="fas fa-filter me-2"></i>فلترة متقدمة</h6>

            <div class="filter-row" bis_skin_checked="1">
                <div class="search-box" bis_skin_checked="1">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" placeholder="بحث بالاسم، البريد، الهاتف..." id="searchInput"
                        value="{{ request('search') }}">
                </div>

                <div class="sort-dropdown" bis_skin_checked="1">
                    <button class="sort-btn">
                        <i class="fas fa-sort-amount-down"></i>
                        الترتيب حسب
                    </button>
                    <div class="sort-dropdown-content" bis_skin_checked="1">
                        <div class="sort-item {{ request('sort_by') == 'created_at' && request('sort_direction') == 'desc' ? 'active' : '' }}"
                            onclick="sortBy('created_at', 'desc')">
                            الأحدث أولاً
                        </div>
                        <div class="sort-item {{ request('sort_by') == 'created_at' && request('sort_direction') == 'asc' ? 'active' : '' }}"
                            onclick="sortBy('created_at', 'asc')">
                            الأقدم أولاً
                        </div>
                        <div class="sort-item {{ request('sort_by') == 'total_spent' && request('sort_direction') == 'desc' ? 'active' : '' }}"
                            onclick="sortBy('total_spent', 'desc')">
                            الأعلى إنفاقاً
                        </div>
                        <div class="sort-item {{ request('sort_by') == 'total_spent' && request('sort_direction') == 'asc' ? 'active' : '' }}"
                            onclick="sortBy('total_spent', 'asc')">
                            الأقل إنفاقاً
                        </div>
                    </div>
                </div>

                <div class="input-group" bis_skin_checked="1">
                    <input type="date" class="form-control" id="dateFrom" value="{{ request('date_from') }}">
                    <span class="input-group-text">إلى</span>
                    <input type="date" class="form-control" id="dateTo" value="{{ request('date_to') }}">
                </div>
            </div>

            <div class="filter-row" bis_skin_checked="1">
                <input type="number" class="form-control" id="bookingCount" placeholder="أقل عدد حجوزات"
                    value="{{ request('bookings_min') }}">

                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter me-2"></i>تطبيق الفلاتر
                </button>
                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                </button>
            </div>
        </div>

        <div class="row" bis_skin_checked="1">
            <div class="col-12" bis_skin_checked="1">
                <div class="order-card" bis_skin_checked="1">
                    <div class="order-header" bis_skin_checked="1">
                        <div class="d-flex justify-content-between align-items-center" bis_skin_checked="1">
                            <div bis_skin_checked="1">
                                <h5 class="mb-0">قائمة العملاء</h5>
                                <small class="opacity-75">إدارة جميع عملاء منصة السفر</small>
                            </div>
                            <div class="d-flex gap-3" bis_skin_checked="1">
                                <a href="{{ route('admin.clients.export') }}" class="btn btn-light">
                                    <i class="fas fa-file-export me-2"></i>تصدير
                                </a>
                                <a href="{{ route('admin.clients.create') }}" class="btn btn-light">
                                    <i class="fas fa-plus me-2"></i>إضافة عميل جديد
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" bis_skin_checked="1">
                        @if ($clients->isEmpty())
                            <div class="empty-state" bis_skin_checked="1">
                                <div class="empty-state-icon" bis_skin_checked="1">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="empty-state-text">لا يوجد عملاء</h5>
                                <p class="text-muted">لم يتم إنشاء أي عميل حتى الآن</p>
                                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>إضافة عميل جديد
                                </a>
                            </div>
                        @else
                            @foreach ($clients as $client)
                                @php
                                    $isActive = $client->newsletter_subscribed ?? false ? 'active' : 'inactive';
                                    $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                                @endphp
                                <div class="order-item {{ $isActive == 'active' ? 'active-client' : 'inactive-client' }}"
                                    bis_skin_checked="1">
                                    <div class="order-header-info" bis_skin_checked="1">
                                        <div class="order-title" bis_skin_checked="1">
                                            <div class="d-flex align-items-center gap-3" bis_skin_checked="1">
                                                <span>{{ $fullName ?: 'بدون اسم' }}</span>
                                                <span class="badge-status status-{{ $isActive }}">
                                                    {{ $isActive == 'active' ? 'نشط' : 'غير نشط' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="order-date" bis_skin_checked="1">
                                            <i class="far fa-clock me-1"></i>
                                            {{ optional($client->created_at)->translatedFormat('d M Y - h:i A') }}
                                        </div>
                                    </div>

                                    <div class="order-details" bis_skin_checked="1">
                                        <div class="detail-item" bis_skin_checked="1">
                                            <span class="detail-label">البريد:</span>
                                            <span class="detail-value">{{ $client->email ?? '-' }}</span>
                                        </div>

                                        <div class="detail-item" bis_skin_checked="1">
                                            <span class="detail-label">الهاتف:</span>
                                            <span class="detail-value">{{ $client->phone ?? '-' }}</span>
                                        </div>

                                        <div class="detail-item" bis_skin_checked="1">
                                            <span class="detail-label">الجنسية:</span>
                                            <span class="detail-value">{{ $client->nationality ?? '-' }}</span>
                                        </div>

                                        <div class="detail-item" bis_skin_checked="1">
                                            <span class="detail-label">إجمالي الحجوزات:</span>
                                            <span class="detail-value">{{ $client->total_bookings ?? 0 }}</span>
                                        </div>

                                        <div class="detail-item" bis_skin_checked="1">
                                            <span class="detail-label">إجمالي الإنفاق:</span>
                                            <span
                                                class="detail-value">{{ number_format($client->total_spent ?? 0, 2) }}</span>
                                        </div>

                                        <div class="detail-item" bis_skin_checked="1">
                                            <span class="detail-label">آخر نشاط:</span>
                                            <span class="detail-value">
                                                {{ optional($client->last_activity)->translatedFormat('d M Y') ?? '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="order-actions" bis_skin_checked="1">
                                        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye me-1"></i>عرض التفاصيل
                                        </a>
                                        <a href="{{ route('admin.clients.edit', $client) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i>تعديل
                                        </a>
                                        <a href="{{ route('admin.clients.bookings', $client) }}"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fas fa-suitcase me-1"></i>الحجوزات
                                        </a>
                                        <a href="{{ route('admin.clients.inquiries', $client) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-comments me-1"></i>الاستفسارات
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-id="{{ $client->id }}" data-name="{{ $fullName ?: 'العميل' }}">
                                            <i class="fas fa-trash me-1"></i>حذف
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if ($clients->hasPages())
                                <div class="m-3">
                                    {{ $clients->links() }}
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
                const clientId = $(this).data('id');
                const clientName = $(this).data('name');

                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: `سيتم حذف العميل "${clientName}" نهائياً`,
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
                            url: "{{ route('admin.clients.destroy', '') }}/" + clientId,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف',
                                    text: 'تم حذف العميل بنجاح',
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
                    status: status
                });
            }
        }

        function sortBy(sortBy, sortDirection) {
            updateUrl({
                sort_by: sortBy,
                sort_direction: sortDirection
            });
        }

        function applyFilters() {
            updateUrl({
                search: $('#searchInput').val(),
                date_from: $('#dateFrom').val(),
                date_to: $('#dateTo').val(),
                bookings_min: $('#bookingCount').val()
            });
        }

        function resetFilters() {
            window.location.href = "{{ route('admin.clients.index') }}";
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
