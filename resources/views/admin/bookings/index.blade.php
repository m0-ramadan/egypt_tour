@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('الحجوزات'))

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

        .status-pending {
            background: rgba(255, 193, 7, .2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, .3);
        }

        .status-confirmed {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-cancelled {
            background: rgba(220, 53, 69, .2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, .3);
        }

        .status-completed {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $totalBookings = $bookings->total() ?? $bookings->count();
        $pendingBookings = \App\Models\Booking::where('status', 'pending')->count();
        $confirmedBookings = \App\Models\Booking::where('status', 'confirmed')->count();
        $completedBookings = \App\Models\Booking::where('status', 'completed')->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الحجوزات</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalBookings) }}</div>
                    <div class="stats-label">إجمالي الحجوزات</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stats-number">{{ number_format($pendingBookings) }}</div>
                    <div class="stats-label">قيد الانتظار</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div class="stats-number">{{ number_format($confirmedBookings) }}</div>
                    <div class="stats-label">مؤكدة</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="stats-number">{{ number_format($completedBookings) }}</div>
                    <div class="stats-label">مكتملة</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.bookings.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            placeholder="اسم العميل أو المرجع">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الباقة</label>
                        <select name="package_id" class="form-select">
                            <option value="">كل الباقات</option>
                            @foreach ($packages ?? collect() as $package)
                                <option value="{{ $package->id }}"
                                    {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>confirmed
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>completed
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>cancelled
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">قائمة الحجوزات</h5>
                    <small class="opacity-75">إدارة جميع الحجوزات السياحية</small>
                </div>
                <a href="{{ route('admin.bookings.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>إضافة حجز
                </a>
            </div>

            <div class="p-4">
                @forelse($bookings as $booking)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $booking->booking_reference ?? 'بدون مرجع' }}</h6>
                                <small class="text-light opacity-75">
                                    {{ $booking->client->name ?? ($booking->client_name ?? '-') }}
                                </small>
                            </div>

                            <span class="badge-status status-{{ $booking->status }}">
                                {{ $booking->status }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3"><strong>الباقة:</strong> {{ $booking->package->name ?? '-' }}</div>
                            <div class="col-md-3"><strong>عدد الأفراد:</strong> {{ $booking->travellers_count ?? '-' }}
                            </div>
                            <div class="col-md-3"><strong>إجمالي السعر:</strong>
                                {{ number_format($booking->total_amount ?? 0, 2) }}</div>
                            <div class="col-md-3"><strong>العملة:</strong> {{ $booking->currency_code ?? '-' }}</div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3"><strong>تاريخ السفر:</strong>
                                {{ optional($booking->travel_date)->translatedFormat('d M Y') ?? '-' }}</div>
                            <div class="col-md-3"><strong>تاريخ الحجز:</strong>
                                {{ optional($booking->created_at)->translatedFormat('d M Y') ?? '-' }}</div>
                            <div class="col-md-3">
                                <strong>الهاتف:</strong>
                                @if(!empty($booking->phone))
                                    @php($cleanBPhone = preg_replace('/[^0-9]/', '', $booking->phone))
                                    <span class="dir-ltr d-inline-block font-monospace mx-1">{{ $booking->phone }}</span>
                                    <a href="https://wa.me/{{ $cleanBPhone }}" target="_blank" class="btn btn-sm btn-success rounded-circle px-2 py-1 me-1" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp fs-6"></i>
                                    </a>
                                    <a href="tel:{{ $booking->phone }}" class="btn btn-sm btn-info rounded-circle px-2 py-1" title="اتصال هاتفي">
                                        <i class="fas fa-phone-alt fs-6"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                            <div class="col-md-3">
                                <strong>البريد:</strong>
                                @if(!empty($booking->email))
                                    <a href="mailto:{{ $booking->email }}" class="text-info text-decoration-none" title="مراسلة عبر البريد الإلكتروني">
                                        <i class="fas fa-envelope text-primary me-1"></i>{{ $booking->email }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-warning btn-sm">تعديل</a>
                            <a href="{{ route('admin.bookings.print', $booking) }}"
                                class="btn btn-secondary btn-sm">طباعة</a>

                            @if (Route::has('admin.bookings.update-status'))
                                <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST"
                                    class="d-flex gap-2">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="pending">pending</option>
                                        <option value="confirmed">confirmed</option>
                                        <option value="completed">completed</option>
                                        <option value="cancelled">cancelled</option>
                                    </select>
                                    <button class="btn btn-dark btn-sm" type="submit">تحديث</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد حجوزات حالياً</div>
                @endforelse

                @if (method_exists($bookings, 'links'))
                    <div class="mt-4">{{ $bookings->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
