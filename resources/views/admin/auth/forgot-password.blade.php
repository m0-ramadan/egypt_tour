@include('admin.i18n.locale')
@extends('admin.auth.layouts.master')

@section('title', admin_t('Forgot Password?'))

@section('styles')
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('dashboard/assets/css/bootstrap-rtl.min.css') }}">
    @endif
@endsection

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg"
        dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="authentication-inner row">

            <!-- Left side illustration -->
            <div class="d-none d-lg-flex col-lg-7 align-items-center p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center w-100 h-100">
                    <img src="{{ asset('dashboard/assets/img/illustrations/forgot-password-illustration-light.png') }}"
                        alt="Cover Restore Word Password" class="img-fluid auth-illustration">

                    <img src="{{ asset('dashboard/assets/img/illustrations/bg-shape-image-light.png') }}" alt="Background System"
                        class="platform-bg">
                </div>
            </div>
            <!-- /Left side -->

            <!-- Reset Password Form -->
            <div class="d-flex col-12 col-lg-5 align-items-center p-4 p-sm-5">
                <div class="w-px-400 mx-auto">

                    <!-- Logo -->
                    <div class="app-brand mb-4 text-center">
                        <a href="{{ url('/') }}" class="app-brand-link justify-content-center">
                            <img src="{{ asset('website/logo/egypt-tour-pro.png') }}"
                                style="max-height: 85px; width: auto; max-width: 280px; object-fit: contain;"
                                alt="Logo {{ env('APP_NAME') }}">
                        </a>
                    </div>

                    <h3 class="mb-2 text-center">Forgot Password? 🔒</h3>
                    <p class="mb-4 text-center text-muted">
                        Enter Your Email Electronic And Will be Send Link Reset Set Word Password To You.
                    </p>

                    <!-- Flash messages -->
                    @if (session('status'))
                        <div class="alert alert-success text-center" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="formForgotPassword" class="mb-3" method="POST" action="{{ route('admin.password.email') }}"
                        novalidate>
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control text-start @error('email') is-invalid @enderror"
                                dir="ltr" id="email" name="email" value="{{ old('email') }}"
                                placeholder="name@example.com" autofocus required>

                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Send Link Button -->
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">
                            Send Link Reset Set
                        </button>

                        <!-- Return to Sign In link -->
                        <div class="text-center">
                            <a href="{{ route('admin.login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="ti ti-chevron-left scaleX-n1-rtl me-1"></i>
                                Return To Sign In
                            </a>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="text-center mt-5">
                        <small class="text-muted">
                            Developed by
                            <a href="https://nofalseo.com" target="_blank" class="text-primary fw-medium">
                                {{ env('APP_NAME') }}
                            </a>
                        </small>
                    </div>

                </div>
            </div>
            <!-- /Reset Password Form -->

        </div>
    </div>
@endsection

@section('scripts')
    <!-- No custom JS needed -->
    <script>
        // Add custom form validation here
    </script>
@endsection
