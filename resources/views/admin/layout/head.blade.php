@include('admin.i18n.locale')
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/vendor/fonts/fontawesome.css') }}">
<!-- Font Awesome-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/fontawesome.css') }}">
<!-- ico-font-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/icofont.css') }}">
<!-- Themify icon-->
<link rel="styles'
    heet" type="text/css" href="{{ asset('dashboard/assets/css/themify.css') }}">
<!-- Flag icon-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/flag-icon.css') }}">
<!-- Feather icon-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/feather-icon.css') }}">
<!-- Plugins css start-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/animate.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/chartist.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/date-picker.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/prism.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/vector-map.css') }}">
<!-- Plugins css Ends-->
<!-- Bootstrap css-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/bootstrap.css') }}">
<!-- App css-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/style.css') }}">
<link id="color" rel="stylesheet" href="{{ asset('dashboard/assets/css/color-1.css') }}" media="screen">
<!-- Responsive css-->
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/css/responsive.css') }}">

<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/app/css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('dashboard/assets/app/css/buttons.dataTables.min.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">


@yield('css')
