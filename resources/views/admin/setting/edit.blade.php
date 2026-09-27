@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Edit Settings'))

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

        .img-preview {
            max-height: 80px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
            margin-top: 8px;
        }

        .section-divider {
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            margin: 25px 0 20px;
            padding-bottom: 8px;
            font-weight: 700;
            color: #f4c36a;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.setting.edit') }}">Settings</a></li>
                <li class="breadcrumb-item active">Edit Settings & Hero Images</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">Website & Hero Images Settings</h5>
                <small class="opacity-75">Configure main site information, notification emails, and hero banner background
                    images</small>
            </div>

            <div class="form-body">
                @if (session('success'))
                    <div class="alert alert-success bg-success text-white border-0 mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.setting.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="section-divider">General Info & Email Notifications</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control"
                                value="{{ old('site_name', $settings['site_name'] ?? 'Egypt Tour Pro') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Notification Email (Receives Bookings & Inquiries)</label>
                            <input type="email" name="site_email" class="form-control"
                                value="{{ old('site_email', $settings['site_email'] ?? ($settings['admin_email'] ?? '')) }}"
                                placeholder="e.g. info@egypttourpro.com">
                            <small class="text-white-50">Notifications for book form, bookings, and inquiries will be sent
                                here.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="site_phone" class="form-control"
                                value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title / Address</label>
                            <input type="text" name="site_address" class="form-control"
                                value="{{ old('site_address', $settings['site_address'] ?? '') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="site_description" class="form-control" rows="3">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="section-divider">Main Website Hero Images (صور أجزاء الموقع الرئيسية)</div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Home Hero Image (صورة الهيرو بالصفحة الرئيسية)</label>
                            <input type="file" name="hero_image_home" class="form-control" accept="image/*">
                            @if (!empty($settings['hero_image_home']))
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <img src="{{ asset($settings['hero_image_home']) }}" class="img-preview"
                                        alt="Home Hero">
                                    <small class="text-success">Current Home Hero</small>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label">Packages Section Hero Image (صورة باقات السفر)</label>
                            <input type="file" name="hero_image_packages" class="form-control" accept="image/*">
                            @if (!empty($settings['hero_image_packages']))
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <img src="{{ asset($settings['hero_image_packages']) }}" class="img-preview"
                                        alt="Packages Hero">
                                    <small class="text-success">Current Packages Hero</small>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label">Nile Cruises Hero Image (صورة كروزات النيل)</label>
                            <input type="file" name="hero_image_cruises" class="form-control" accept="image/*">
                            @if (!empty($settings['hero_image_cruises']))
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <img src="{{ asset($settings['hero_image_cruises']) }}" class="img-preview"
                                        alt="Cruises Hero">
                                    <small class="text-success">Current Cruises Hero</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="section-divider">Branding Images</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="site_logo" class="form-control" accept="image/*">
                            @if (!empty($settings['site_logo']))
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <img src="{{ asset($settings['site_logo']) }}" class="img-preview bg-dark p-2"
                                        alt="Logo">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="site_favicon" class="form-control" accept="image/*">
                            @if (!empty($settings['site_favicon']))
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <img src="{{ asset($settings['site_favicon']) }}" class="img-preview"
                                        style="max-height:32px;" alt="Favicon">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Save Settings</button>
                        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
