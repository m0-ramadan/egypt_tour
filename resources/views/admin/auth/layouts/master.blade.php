@include('admin.i18n.locale')
<!doctype html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<html lang="{{ $locale }}" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    data-theme="theme-default" data-assets-path="#" data-template="vertical-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title')
    </title>

    <meta name="description" content="" />
    @include('admin.layout.css')
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/css/pages/page-auth.css') }}" />
    @yield('styles')
</head>

<body>

    <div id="admin-auth-language-switcher" style="position:fixed;top:18px;inset-inline-end:18px;z-index:9999;display:flex;gap:6px">
        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['lang' => 'ar'])) }}" class="btn btn-sm {{ app()->getLocale() === 'ar' ? 'btn-primary' : 'btn-outline-primary' }}">العربية</a>
        <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['lang' => 'en'])) }}" class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-outline-primary' }}">English</a>
    </div>

    <!-- Content -->
    @yield('content')
    <!-- / Content -->

    <!-- Core JS -->
    @include('admin.layout.js')
    @include('admin.i18n.runtime')
</body>

</html>
