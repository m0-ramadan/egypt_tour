@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Add Booking'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .form-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: 10px;
        }

        .form-control,
        .form-select,
        textarea {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                <li class="breadcrumb-item active">Add Booking</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Add New Booking</h5>
                    <small class="opacity-75">Enter Details Booking</small>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light">Back</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.bookings.store') }}" method="POST">
                    @csrf

                    <div class="section-title">Client and Package Details</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client</label>
                            <select name="client_id" class="form-select">
                                <option value="">Select Client</option>
                                @foreach ($clients ?? collect() as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package</label>
                            <select name="package_id" class="form-select">
                                <option value="">Select Package</option>
                                @foreach ($packages ?? collect() as $package)
                                    <option value="{{ $package->id }}"
                                        {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Booking Reference</label>
                            <input type="text" name="booking_reference" class="form-control"
                                value="{{ old('booking_reference') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Adults</label>
                            <input type="number" min="1" name="adults" class="form-control"
                                value="{{ old('adults', 1) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Children</label>
                            <input type="number" min="0" name="children" class="form-control"
                                value="{{ old('children', 0) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Infants (Infants)</label>
                            <input type="number" min="0" name="infants" class="form-control"
                                value="{{ old('infants', 0) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Travel Date</label>
                            <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>pending</option>
                                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>confirmed
                                </option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>completed
                                </option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>cancelled
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Price</label>
                            <input type="number" step="0.01" name="total_amount" class="form-control"
                                value="{{ old('total_amount') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency_code" class="form-control"
                                value="{{ old('currency_code', 'USD') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="5">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
