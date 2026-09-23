@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('View Country'))

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
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
        }

        .profile-header {
            background: var(--primary-gradient);
            padding: 30px;
            color: #fff;
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

        .country-flag {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }

        .country-flag:hover {
            transform: scale(1.05);
        }

        .no-image {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            margin: auto;
            font-weight: 600;
        }

        .mini-flag {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.countries.index') }}">Countries</a></li>
                <li class="breadcrumb-item active">View Country</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if ($country->flag)
                        <img src="{{ asset($country->flag) }}" alt="{{ adminTrans($country->name) }}" class="mini-flag">
                    @endif

                    <div>
                        <h4 class="mb-1">{{ $country->display_name ?? 'No Name' }}</h4>
                        <small class="opacity-75">{{ $country->code ?? '-' }}</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-light">Edit</a>
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-light">Back</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        @if ($country->flag)
                            <img src="{{ asset($country->flag) }}" alt="{{ adminTrans($country->name) }}"
                                class="country-flag">
                        @else
                            <div class="no-image">No Image</div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Code</div>
                            <div class="info-value">{{ $country->code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{ $country->is_active ?? true ? 'Enabled' : 'Inactive' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Featured</div>
                            <div class="info-value">{{ $country->is_featured ?? false ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Sort Order</div>
                            <div class="info-value">{{ $country->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Number of Cities</div>
                            <div class="info-value">{{ $country->cities_count ?? ($country->cities->count() ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Description</div>
                            <div class="info-value">{{ adminTrans($country->description) ?: 'No description' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
