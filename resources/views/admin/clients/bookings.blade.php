@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('حجوزات العميل'))

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

        .wrapper-card {
            background: var(--dark-card);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
        }

        .wrapper-header {
            background: var(--primary-gradient);
            padding: 25px 30px;
            color: #fff;
        }

        .booking-row {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    @php
        $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">العملاء</a></li>
                <li class="breadcrumb-item active">حجوزات العميل</li>
            </ol>
        </nav>

        <div class="wrapper-card">
            <div class="wrapper-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">حجوزات {{ $fullName ?: 'العميل' }}</h5>
                    <small class="opacity-75">عرض جميع الحجوزات المرتبطة بهذا العميل</small>
                </div>
                <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="p-4">
                @forelse ($bookings as $booking)
                    <div class="booking-row">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <strong>رقم الحجز:</strong>
                                <div>{{ $booking->booking_number ?? '#' . $booking->id }}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <strong>الحالة:</strong>
                                <div>{{ $booking->status ?? '-' }}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <strong>التاريخ:</strong>
                                <div>{{ optional($booking->booking_date)->translatedFormat('d M Y') ?? '-' }}</div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <strong>الإجمالي:</strong>
                                <div>{{ number_format($booking->total_amount ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد حجوزات لهذا العميل</div>
                @endforelse

                <div class="mt-3">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
