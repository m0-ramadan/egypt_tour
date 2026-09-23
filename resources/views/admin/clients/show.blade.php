@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('View Client'))

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
    </style>
@endsection

@section('content')
    @php
        $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ admin_t('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">{{ admin_t('Clients') }}</a></li>
                <li class="breadcrumb-item active">{{ admin_t('View Client') }}</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $fullName ?: admin_t('No Name') }}</h4>
                    <small class="opacity-75">{{ $client->email ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-light">
                        {{ admin_t('Edit') }}
                    </a>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-light">
                        {{ admin_t('Back') }}
                    </a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Phone') }}</div>
                            <div class="info-value">{{ $client->phone ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Nationality') }}</div>
                            <div class="info-value">{{ $client->nationality ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Date of Birth') }}</div>
                            <div class="info-value">
                                {{ optional($client->date_of_birth)->translatedFormat('d M Y') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Passport Number') }}</div>
                            <div class="info-value">{{ $client->passport_number ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Passport Expiry') }}</div>
                            <div class="info-value">
                                {{ optional($client->passport_expiry)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Newsletter Subscribed') }}</div>
                            <div class="info-value">{{ $client->newsletter_subscribed ? admin_t('Yes') : admin_t('No') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Total Bookings') }}</div>
                            <div class="info-value">{{ $client->total_bookings ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Total Spent') }}</div>
                            <div class="info-value">{{ number_format($client->total_spent ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Last Activity') }}</div>
                            <div class="info-value">
                                {{ optional($client->last_activity)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">{{ admin_t('Notes') }}</div>
                            <div class="info-value">{{ $client->notes ?: admin_t('No Notes') }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.clients.bookings', $client) }}"
                        class="btn btn-primary">{{ admin_t('Bookings') }}</a>
                    <a href="{{ route('admin.clients.inquiries', $client) }}"
                        class="btn btn-secondary">{{ admin_t('Inquiries') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
