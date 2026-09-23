@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('View User'))

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
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">View User</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $user->name ?? 'No Name' }}</h4>
                    <small class="opacity-75">{{ $user->email ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light">
                        Edit
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light">
                        Back
                    </a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">{{ $user->phone ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{ $user->is_active ?? true ? 'Active' : 'Inactive' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Verify Email</div>
                            <div class="info-value">{{ !empty($user->email_verified_at) ? 'Verified' : 'Not Verified' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Created At</div>
                            <div class="info-value">
                                {{ optional($user->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">
                                {{ optional($user->updated_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.users.activities', $user) }}" class="btn btn-primary">Activities</a>
                </div>
            </div>
        </div>
    </div>
@endsection
