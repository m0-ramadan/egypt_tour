@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('System Settings'))

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

        .panel-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .panel-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
        }

        .panel-body {
            padding: 30px;
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
            margin-bottom: 5px;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
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

        .form-label {
            color: rgba(255, 255, 255, .85);
            font-weight: 600;
            margin-bottom: 8px;
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

        .setting-item {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, .3);
            color: #fff;
        }

        .file-row {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .nav-pill {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 25px;
            background: rgba(255, 255, 255, .05);
            color: rgba(255, 255, 255, .8);
            border: 1px solid rgba(255, 255, 255, .1);
            text-decoration: none;
            margin: 0 8px 8px 0;
        }

        .nav-pill.active,
        .nav-pill:hover {
            background: var(--primary-gradient);
            color: #fff;
        }
    </style>

@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">System Settings</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;"><i
                            class="fas fa-sliders-h"></i></div>
                    <div class="stats-number">4</div>
                    <div class="stats-label">Sections Main</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);"><i
                            class="fas fa-envelope"></i></div>
                    <div class="stats-number">SMTP</div>
                    <div class="stats-label">Email And Send</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);"><i
                            class="fas fa-cog"></i></div>
                    <div class="stats-number">General</div>
                    <div class="stats-label">General Settings</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);"><i
                            class="fas fa-folder"></i></div>
                    <div class="stats-number">Files</div>
                    <div class="stats-label">Manage Files</div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0">Dashboard Settings System</h5>
                <small class="opacity-75">Access Quick Per Settings System</small>
            </div>
            <div class="panel-body">
                <div class="mb-4">
                    <a class="nav-pill" href="{{ route('admin.settings.general') }}">General Settings</a>
                    <a class="nav-pill" href="{{ route('admin.settings.smtp') }}">SMTP Settings</a>
                    <a class="nav-pill" href="{{ route('admin.settings.communication') }}">Communication Settings</a>
                    <a class="nav-pill" href="{{ route('admin.settings.files') }}">Files</a>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Storage</div>
                            <div class="info-value">Manage Files Temporary And Storage Used</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">Control In Cash And Mode Maintenance And Status System</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                        @csrf
                        <button class="btn btn-primary" type="submit">Clear Cash</button>
                    </form>
                    <form action="{{ route('admin.settings.toggle-maintenance') }}" method="POST">
                        @csrf
                        <button class="btn btn-secondary" type="submit">Toggle Mode Maintenance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
