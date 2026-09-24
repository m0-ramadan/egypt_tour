@include('admin.i18n.locale')
<!-- Favicon -->
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}" />
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />



<!-- Icons -->
{{-- <link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/fonts/fontawesome.css') }}"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    crossorigin="anonymous">


<!-- Fonts -->
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/fonts/tabler-icons.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/fonts/flag-icons.css') }}" />

<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/css/rtl/core.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/css/rtl/theme-default.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/css/demo.css') }}" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/node-waves/node-waves.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/typeahead-js/typeahead.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/swiper/swiper.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet"
    href="{{ asset('dashboard/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet"
    href="{{ asset('dashboard/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
<link rel="stylesheet"
    href="{{ asset('dashboard/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/css/pages/cards-advance.css') }}" />

<!-- Select Inputs -->
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />

<!-- SweetAlert -->
<link rel="stylesheet" href="{{ asset('dashboard/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />

<!-- Custom CSS -->
<link rel="stylesheet" href="{{ asset('dashboard/assets/css/custome.css') }}" />

<!-- Jodit WYSIWYG Editor CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.9/jodit.min.css" />
<style>
    /* Jodit Editor Styling Overrides */
    .jodit-container {
        background-color: #ffffff !important;
        color: #111111 !important;
        border-radius: 8px !important;
    }

    .jodit-container .jodit-wysiwyg,
    .jodit-container .jodit-workplace,
    .jodit-workplace iframe {
        background-color: #ffffff !important;
        color: #111111 !important;
    }

    .jodit-container .jodit-wysiwyg p,
    .jodit-container .jodit-wysiwyg div,
    .jodit-container .jodit-wysiwyg span,
    .jodit-container .jodit-wysiwyg td,
    .jodit-container .jodit-wysiwyg th,
    .jodit-container .jodit-wysiwyg li,
    .jodit-container .jodit-wysiwyg h1,
    .jodit-container .jodit-wysiwyg h2,
    .jodit-container .jodit-wysiwyg h3,
    .jodit-container .jodit-wysiwyg h4,
    .jodit-container .jodit-wysiwyg h5,
    .jodit-container .jodit-wysiwyg h6,
    .jodit-container .jodit-wysiwyg strong,
    .jodit-container .jodit-wysiwyg b,
    .jodit-container .jodit-wysiwyg em,
    .jodit-container .jodit-wysiwyg i,
    .jodit-container .jodit-wysiwyg u,
    .jodit-container .jodit-wysiwyg a {
        color: #111111 !important;
    }

    .jodit-placeholder {
        color: #888888 !important;
    }

    .jodit-toolbar__box {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .jodit-toolbar-button__button {
        color: #333333 !important;
    }

    .jodit-status-bar {
        background-color: #f8f9fa !important;
        color: #333333 !important;
        border-top: 1px solid #dee2e6 !important;
    }

    .jodit-status-bar * {
        color: #333333 !important;
    }
</style>



<!-- Helpers -->
<script src="{{ asset('dashboard/assets/vendor/js/helpers.js') }}"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

<script src="{{ asset('dashboard/assets/vendor/js/template-customizer.js') }}"></script>


<script src="{{ asset('dashboard/assets/js/config.js') }}"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Noto+Kufi+Arabic:wght@100..900&display=swap"
    rel="stylesheet">
