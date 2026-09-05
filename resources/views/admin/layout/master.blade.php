@include('admin.i18n.locale')
<!doctype html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<html lang="{{ $locale }}" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    data-theme="theme-default" data-assets-path="{{ asset('dashboard/assets') }}/"
    data-template="vertical-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- تنسيق خاص لمعالجة الأخطاء -->
    <style>
        /* تنسيق للصور الفاشلة */
        img.img-error {
            opacity: 0.7;
            border: 2px dashed #ccc !important;
            background-color: #f8f9fa !important;
            padding: 10px !important;
        }

        /* تحسين الأداء للـ animations */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
    <title>
        @yield('title')
    </title>

    <meta name="description" content="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: {{ $isRtl ? '"Cairo", "Noto Kufi Arabic", sans-serif' : '"Public Sans", sans-serif' }} !important;
            background: #1e1e2d;
        }
    </style>
    @include('admin.layout.css')


</head>
@yield('css')

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('admin.layout.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('admin.layout.nav')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    @yield('content')

                    <!-- / Content -->

                    <!-- Footer -->
                    @include('admin.layout.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        {{-- <div class="layout-overlay layout-menu-toggle"></div> --}}

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    <form id="form_action_delete" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
    </form>
    <form id="form_action_post" method="POST" class="d-none">
        @csrf
    </form>
    <!-- Core JS -->
    @include('admin.layout.js')
    @include('admin.i18n.runtime')
    @yield('js')

    <script>
        // ✅ حل إسعافي: لو أي Swal اتعملت بدون نصوص للأزرار، نركّب نصوص افتراضية
        if (window.Swal && typeof Swal.fire === 'function') {
            const __fire = Swal.fire.bind(Swal);
            Swal.fire = function(opts, ...rest) {
                if (opts && typeof opts === 'object') {
                    if (!opts.confirmButtonText) opts.confirmButtonText = @json(admin_t('موافق'));
                    if (opts.showCancelButton && !opts.cancelButtonText) opts.cancelButtonText = @json(admin_t('إلغاء'));
                    if (opts.showDenyButton && !opts.denyButtonText) opts.denyButtonText = @json(admin_t('لا'));
                }
                return __fire(opts, ...rest);
            };
        }
    </script>
</body>

</html>
