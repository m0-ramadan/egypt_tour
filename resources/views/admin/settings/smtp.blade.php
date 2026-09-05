@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إعدادات SMTP'))

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
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">الإعدادات</a></li>
                <li class="breadcrumb-item active">SMTP</li>
            </ol>
        </nav>

        <div class="panel-card">
            <div class="panel-header">
                <h5 class="mb-0">إعدادات SMTP</h5>
                <small class="opacity-75">تهيئة خادم البريد والإرسال</small>
            </div>
            <div class="panel-body">
                <form action="{{ route('admin.settings.smtp.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mail Host</label>
                            <input type="text" name="mail_host" class="form-control"
                                value="{{ $settings['mail_host'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mail Port</label>
                            <input type="text" name="mail_port" class="form-control"
                                value="{{ $settings['mail_port'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="mail_username" class="form-control"
                                value="{{ $settings['mail_username'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="mail_password" class="form-control"
                                value="{{ $settings['mail_password'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Encryption</label>
                            <input type="text" name="mail_encryption" class="form-control"
                                value="{{ $settings['mail_encryption'] ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Address</label>
                            <input type="email" name="mail_from_address" class="form-control"
                                value="{{ $settings['mail_from_address'] ?? '' }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                </form>
                <form action="{{ route('admin.settings.smtp.test') }}" method="POST">
                    @csrf
                    <button class="btn btn-secondary" type="submit">اختبار SMTP</button>
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
