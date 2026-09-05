@include('admin.i18n.locale')
 @php
     $user = auth()->guard('admin')->user();
     $isRtl = app()->getLocale() === 'ar';
 @endphp
 <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
     id="layout-navbar">
     <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
         <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
             <i class="ti ti-menu-2 ti-sm"></i>
         </a>
     </div>

     <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
         <!-- Search -->
         <div class="navbar-nav align-items-center">
             <div class="nav-item navbar-search-wrapper mb-0">
                 <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                     <i class="ti ti-search ti-md me-2"></i>
                     <span class="d-none d-md-inline-block text-muted">{{ admin_t('بحث') }} (Ctrl+/)</span>
                 </a>
             </div>
         </div>
         <!-- /Search -->

         <ul class="navbar-nav flex-row align-items-center ms-auto">
             <!-- Quick links  -->

             <!-- Quick links -->

             <!-- Style Switcher -->
             <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                 <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                     <i class="ti ti-md"></i>
                 </a>
                 <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                     <li>
                         <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                             <span class="align-middle"><i class="ti ti-sun me-2"></i>{{ admin_t('فاتح') }}</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                             <span class="align-middle"><i class="ti ti-moon me-2"></i>{{ admin_t('داكن') }}</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                             <span
                                 class="align-middle"><i class="ti ti-device-desktop me-2"></i>{{ admin_t('النظام') }}</span>
                         </a>
                     </li>
                 </ul>
             </li>
             <!-- / Style Switcher-->

             <!-- Notification -->
             <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                 <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                     data-bs-auto-close="outside" aria-expanded="false">
                     <i class="ti ti-bell ti-md" id="btn_notify"></i>
                     <span class=" ">
                         <span style="width: 14px;top: 8px;padding:0;height: 14px;"
                             class="badge bg-danger rounded-pill badge-notifications"></span>

                     </span>
                 </a>

                 <ul class="dropdown-menu dropdown-menu-end py-0">
                     <li class="dropdown-menu-header border-bottom">
                         <div class="dropdown-header d-flex align-items-center py-3">
                             <h5 class="text-body mb-0 me-auto">{{ admin_t('الإشعارات') }}</h5>
                             <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                                 data-bs-toggle="tooltip" data-bs-placement="top"
                                 title="{{ admin_t('تحديد الكل كمقروء') }}">
                                 <i class="ti ti-mail-opened fs-4"></i>
                             </a>
                         </div>
                     </li>


                     <li class="dropdown-notifications-list scrollable-container" class="notification-body">
                         <ul class="list-group list-group-flush" id="list_notifications">


                             <li class="list-group item list-group-item-action dropdown-notifications-item">
                                 <div class="d-flex">
                                     <div class="flex-shrink-0 me-3">

                                         </div>
                                         <div class="flex-grow-1">

                                         <h6 class="mb-1">{{ admin_t('انضمام مستخدم جديد') }}</h6>
                                         <p class="mb-0">{{ admin_t('انضم مستخدم جديد إلى المنصة') }}</p>

                                         <small class="text-muted text-nowrap">2026-08-11
                                             13:53:34</small>
                                     </div>

                                 </div>
                             </li>


                         </ul>
                     </li>

                     <li class="dropdown-menu-footer border-top">
                         <a href="#"
                             class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center"
                             id="view-all-notifications">
                             {{ admin_t('عرض المزيد من الإشعارات') }}
                         </a>
                     </li>
                 </ul>
             </li>
             <!--/ Notification -->

             <!-- User -->
             <li class="nav-item navbar-dropdown dropdown-user dropdown">
                 <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                     <div class="avatar avatar-online">
                         <img src="{{ get_user_image($user->image) }}" alt class="h-auto rounded-circle" />
                     </div>
                 </a>

                 <ul class="dropdown-menu dropdown-menu-end">
                     <li>
                         <a class="dropdown-item" href="pages-account-settings-account.html">
                             <div class="d-flex">
                                 <div class="flex-shrink-0 me-3">
                                     <div class="avatar avatar-online">
                                         <img src="{{ get_user_image($user->avatar) }}" alt
                                             class="h-auto rounded-circle" />
                                     </div>
                                 </div>

                                 <div class="flex-grow-1">
                                     <span class="fw-medium d-block"> {{ $user->name }} </span>
                                     <small class="text-muted">{{ $user->email }}</small>
                                 </div>
                             </div>
                         </a>
                     </li>

                     <li>
                         <div class="dropdown-divider"></div>
                     </li>

                     <li>

                     </li>

                     <li>
                         <div class="dropdown-divider"></div>
                     </li>

                     <li>
                         <a class="dropdown-item" href="#"
                             onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                             <i class="ti ti-logout me-2 ti-sm"></i>
                             <span class="align-middle">{{ admin_t('تسجيل الخروج') }}</span>
                         </a>
                     </li>
                 </ul>
             </li>
             <!--/ User -->
         </ul>
     </div>

     <!-- Search Small Screens -->
     <div class="navbar-search-wrapper search-input-wrapper d-none">
         <input type="text" class="form-control search-input container-xxl border-0"
             placeholder="{{ admin_t('ابحث...') }}" aria-label="{{ admin_t('ابحث...') }}" />
         <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
     </div>
     <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
         @csrf
     </form>

 </nav>
