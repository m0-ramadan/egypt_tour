@include('admin.i18n.locale')
<div class="page-main-header">
    <div class="main-header-right row m-0">
        <div class="main-header-left">
            <div class="logo-wrapper"><a href="{{ route('admin.index') }}">{{ env('APP_NAME') }}</a></div>
            <div class="dark-logo-wrapper"><a href="index.html"><img class="img-fluid"
                        src="../assets/images/logo/dark-logo.png" alt=""></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle" data-feather="align-center"
                    id="sidebar-toggle"></i></div>
        </div>
        <div class="left-menu-header col">
            <ul>
                <li>

                </li>
            </ul>
        </div>
        <div class="nav-right col pull-right right-menu p-0">
            <ul class="nav-menus">
                <li><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i
                            data-feather="maximize"></i></a></li>


                <li>
                    <div class="mode"><i class="fa fa-moon-o"></i></div>
                </li>

                <li class="onhover-dropdown">
                    <div class="notification-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell">
                            <path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0">
                            </path>
                        </svg><span class="dot-animated" style="right: -15px"><span style="padding: 4px 4px;"
                                class="pull-right badge badge-primary badge-pill"
                                id='notification-badge-number'>0</span></span>
                    </div>
                    <ul class="notification-dropdown onhover-show-div" id='notification_container'>


                    </ul>
                </li>



                <li class="onhover-dropdown p-0">
                    <button class="btn btn-primary-light" type="button"><a href="{{ route('admin.logout') }}"
                            onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i
                                data-feather="log-out"></i>Log out</a></button>

                    <form id="logout-form"action="{{ route('admin.logout') }}" method="post">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
        <div class="d-lg-none mobile-toggle pull-right w-auto"><i data-feather="more-horizontal"></i></div>
    </div>
</div>
