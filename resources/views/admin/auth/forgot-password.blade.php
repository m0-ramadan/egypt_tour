@include('admin.i18n.locale')
@extends('admin.auth.layouts.master')

@section('title', admin_t('نسيت كلمة المرور؟'))

@section('styles')
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="{{ asset('dashboard/assets/css/bootstrap-rtl.min.css') }}">
    @endif
@endsection

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg"
        dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="authentication-inner row">

            <!-- الجانب الأيسر (الصورة التوضيحية) - يظهر فقط على الشاشات الكبيرة -->
            <div class="d-none d-lg-flex col-lg-7 align-items-center p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center w-100 h-100">
                    <img src="{{ asset('dashboard/assets/img/illustrations/forgot-password-illustration-light.png') }}"
                        alt="غلاف استعادة كلمة المرور" class="img-fluid auth-illustration">

                    <img src="{{ asset('dashboard/assets/img/illustrations/bg-shape-image-light.png') }}" alt="خلفية النظام"
                        class="platform-bg">
                </div>
            </div>
            <!-- /الجانب الأيسر -->

            <!-- نموذج استعادة كلمة المرور -->
            <div class="d-flex col-12 col-lg-5 align-items-center p-4 p-sm-5">
                <div class="w-px-400 mx-auto">

                    <!-- الشعار -->
                    <div class="app-brand mb-5 text-center">
                        <a href="{{ url('/') }}" class="app-brand-link">
                            <img height="90" width="270" src="{{ asset('dashboard/assets/img/logo_.png') }}"
                                alt="شعار {{ env('APP_NAME') }}">
                        </a>
                    </div>

                    <h3 class="mb-2 text-center">نسيت كلمة المرور؟ 🔒</h3>
                    <p class="mb-4 text-center text-muted">
                        أدخل بريدك الإلكتروني وسيتم إرسال رابط إعادة تعيين كلمة المرور إليك.
                    </p>

                    <!-- رسائل النجاح أو الخطأ العامة -->
                    @if (session('status'))
                        <div class="alert alert-success text-center" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="formForgotPassword" class="mb-3" method="POST" action="{{ route('admin.password.email') }}"
                        novalidate>
                        @csrf

                        <!-- البريد الإلكتروني -->
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control text-start @error('email') is-invalid @enderror"
                                dir="ltr" id="email" name="email" value="{{ old('email') }}"
                                placeholder="name@example.com" autofocus required>

                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- زر إرسال الرابط -->
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">
                            إرسال رابط إعادة التعيين
                        </button>

                        <!-- رابط العودة لتسجيل الدخول -->
                        <div class="text-center">
                            <a href="{{ route('admin.login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="ti ti-chevron-left scaleX-n1-rtl me-1"></i>
                                العودة إلى تسجيل الدخول
                            </a>
                        </div>
                    </form>

                    <!-- التذييل -->
                    <div class="text-center mt-5">
                        <small class="text-muted">
                            تم التطوير بواسطة
                            <a href="https://nofalseo.com" target="_blank" class="text-primary fw-medium">
                                {{ env('APP_NAME') }}
                            </a>
                        </small>
                    </div>

                </div>
            </div>
            <!-- /نموذج استعادة كلمة المرور -->

        </div>
    </div>
@endsection

@section('scripts')
    <!-- لا نحتاج JS خاص هنا، لكن نتركه للتوافق مع القالب -->
    <script>
        // يمكن إضافة أي تحقق إضافي للـ form إذا أردت
    </script>
@endsection
