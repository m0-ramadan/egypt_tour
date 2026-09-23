@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('View Booking'))

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
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                <li class="breadcrumb-item active">View Booking</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $booking->booking_reference ?? 'Without Reference' }}</h4>
                    <small class="opacity-75">{{ $booking->client->name ?? ($booking->client_name ?? '-') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-light">Edit</a>
                    <a href="{{ route('admin.bookings.print', $booking) }}" class="btn btn-light">Print</a>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-light">Back</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Client</div>
                            <div class="info-value">{{ $booking->client->name ?? ($booking->client_name ?? '-') }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Package</div>
                            <div class="info-value">{{ $booking->package->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{ $booking->status ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Number of Guests</div>
                            <div class="info-value">
                                {{ $booking->travellers_count ?? '-' }}
                                <div class="small opacity-75 mt-1" style="font-size: 13px;">
                                    {{ $booking->adults ?? 0 }} Adults · {{ $booking->children ?? 0 }} Children ·
                                    {{ $booking->infants ?? 0 }} Infants
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Travel Date</div>
                            <div class="info-value">{{ optional($booking->travel_date)->translatedFormat('d M Y') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Total Price</div>
                            <div class="info-value">{{ number_format($booking->total_amount ?? 0, 2) }}
                                {{ $booking->currency_code ?? '' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">
                                @if (!empty($booking->phone))
                                    @php($cleanBKPhone = preg_replace('/[^0-9]/', '', $booking->phone))
                                    <span class="dir-ltr d-inline-block font-monospace me-2">{{ $booking->phone }}</span>
                                    <a href="https://wa.me/{{ $cleanBKPhone }}" target="_blank"
                                        class="btn btn-sm btn-success rounded-circle px-2 py-1 me-1"
                                        title="Message via WhatsApp">
                                        <i class="fab fa-whatsapp fs-6"></i>
                                    </a>
                                    <a href="tel:{{ $booking->phone }}"
                                        class="btn btn-sm btn-info rounded-circle px-2 py-1" title="Phone Call">
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
                            <div class="info-label">Email Address</div>
                            <div class="info-value">
                                @if (!empty($booking->email))
                                    <a href="mailto:{{ $booking->email }}" class="text-white text-decoration-none me-2">
                                        {{ $booking->email }}
                                    </a>
                                    <a href="mailto:{{ $booking->email }}"
                                        class="btn btn-sm btn-primary rounded-circle px-2 py-1" title="Message via Email">
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
                            <div class="info-label">Notes</div>
                            <div class="info-value">{{ $booking->notes ?: '-' }}</div>
                        </div>
                    </div>

                    @if ($booking->items->isNotEmpty())
                        @foreach ($booking->items as $item)
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Accommodation / Cabin</div>
                                    <div class="info-value">{{ $item->option_label }}</div>
                                    @if ($item->occupancy_type)
                                        <small class="opacity-75">{{ $item->occupancy_type }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <div class="info-label">Number of Rooms / Cabins</div>
                                    <div class="info-value">{{ $item->room_count }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <div class="info-label">Selected Option Price</div>
                                    <div class="info-value">{{ number_format((float) $item->total_amount, 2) }}
                                        {{ $booking->currency_code }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if ($booking->travelers->isNotEmpty())
                        <div class="col-12">
                            <div class="info-box">
                                <div class="info-label mb-3">Passenger Details</div>
                                <div class="table-responsive">
                                    <table class="table table-dark table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Category</th>
                                                <th>Title</th>
                                                <th>Passport Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($booking->travelers as $traveler)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if ($traveler->traveler_type === 'infant')
                                                            <span class="badge bg-info text-dark">Infant</span>
                                                        @elseif($traveler->traveler_type === 'child')
                                                            <span class="badge bg-warning text-dark">Child</span>
                                                        @else
                                                            <span class="badge bg-primary">Adult</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $traveler->title }}</td>
                                                    <td>{{ $traveler->first_name }} {{ $traveler->last_name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
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
