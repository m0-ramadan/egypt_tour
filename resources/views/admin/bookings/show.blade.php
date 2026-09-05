@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض الحجز'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .profile-card {
            background: var(--dark-card);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .profile-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 30px;
        }

        .profile-body {
            padding: 30px;
        }

        .info-box {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">الحجوزات</a></li>
                <li class="breadcrumb-item active">عرض الحجز</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $booking->booking_reference ?? 'بدون مرجع' }}</h4>
                    <small class="opacity-75">{{ $booking->client->name ?? ($booking->client_name ?? '-') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.bookings.print', $booking) }}" class="btn btn-light">طباعة</a>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">العميل</div>
                            <div class="info-value">{{ $booking->client->name ?? ($booking->client_name ?? '-') }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الباقة</div>
                            <div class="info-value">{{ $booking->package->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $booking->status ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">عدد الأفراد</div>
                            <div class="info-value">{{ $booking->travellers_count ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">تاريخ السفر</div>
                            <div class="info-value">{{ optional($booking->travel_date)->translatedFormat('d M Y') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">إجمالي السعر</div>
                            <div class="info-value">{{ number_format($booking->total_amount ?? 0, 2) }}
                                {{ $booking->currency_code ?? '' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الهاتف</div>
                            <div class="info-value">
                                @if(!empty($booking->phone))
                                    @php($cleanBKPhone = preg_replace('/[^0-9]/', '', $booking->phone))
                                    <span class="dir-ltr d-inline-block font-monospace me-2">{{ $booking->phone }}</span>
                                    <a href="https://wa.me/{{ $cleanBKPhone }}" target="_blank" class="btn btn-sm btn-success rounded-circle px-2 py-1 me-1" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp fs-6"></i>
                                    </a>
                                    <a href="tel:{{ $booking->phone }}" class="btn btn-sm btn-info rounded-circle px-2 py-1" title="اتصال هاتفي">
                                        <i class="fas fa-phone-alt fs-6"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">البريد الإلكتروني</div>
                            <div class="info-value">
                                @if(!empty($booking->email))
                                    <a href="mailto:{{ $booking->email }}" class="text-white text-decoration-none me-2">
                                        {{ $booking->email }}
                                    </a>
                                    <a href="mailto:{{ $booking->email }}" class="btn btn-sm btn-primary rounded-circle px-2 py-1" title="مراسلة عبر البريد">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">ملاحظات</div>
                            <div class="info-value">{{ $booking->notes ?: '-' }}</div>
                        </div>
                    </div>

                    @if($booking->items->isNotEmpty())
                        @foreach($booking->items as $item)
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">الإقامة / الكابينة</div>
                                    <div class="info-value">{{ $item->option_label }}</div>
                                    @if($item->occupancy_type)<small class="opacity-75">{{ $item->occupancy_type }}</small>@endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box"><div class="info-label">عدد الغرف / الكابينات</div><div class="info-value">{{ $item->room_count }}</div></div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box"><div class="info-label">سعر الاختيار</div><div class="info-value">{{ number_format((float)$item->total_amount, 2) }} {{ $booking->currency_code }}</div></div>
                            </div>
                        @endforeach
                    @endif

                    @if($booking->travelers->isNotEmpty())
                        <div class="col-12">
                            <div class="info-box">
                                <div class="info-label mb-3">بيانات المسافرين</div>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped mb-0">
                                        <thead><tr><th>#</th><th>الفئة</th><th>اللقب</th><th>الاسم كما في جواز السفر</th></tr></thead>
                                        <tbody>@foreach($booking->travelers as $traveler)<tr><td>{{ $loop->iteration }}</td><td>{{ $traveler->traveler_type }}</td><td>{{ $traveler->title }}</td><td>{{ $traveler->first_name }} {{ $traveler->last_name }}</td></tr>@endforeach</tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
