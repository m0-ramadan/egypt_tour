@include('admin.i18n.locale')
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.index') }}" class="app-brand-link">
            <span style="width: 75px !important ; height:75px !important" class="app-brand-logo demo"></span>
            <span class="app-brand-text demo menu-text fw-bold" style="font-size: 0.90rem">{{ env('APP_NAME') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- Admin overview --}}
        <li class="menu-item">
            <div class="menu-link d-flex align-items-center">
                @php
                    $admin = auth('admin')->user();
                @endphp
                <div class="me-3">
                    <img src="{{ get_user_image($admin->image) }}" class="rounded-circle"
                        style="width: 40px; height: 40px; object-fit: cover;" alt="{{ admin_t('Admin') }}">
                </div>
                <div>
                    <a href="{{ route('admin.index') }}" class="text-body fw-semibold">
                        {{ $admin->name ?? admin_t('Admin') }}
                    </a>
                </div>
            </div>
        </li>

        {{-- Dashboard --}}
        <li class="menu-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <a href="{{ route('admin.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-home"></i>
                <div>{{ admin_t('Dashboard') }}</div>
            </a>
        </li>

        {{-- Visitors Analytics --}}
        <li class="menu-item {{ request()->routeIs('admin.visitors.*') ? 'active' : '' }}">
            <a href="{{ route('admin.visitors.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-chart-bar"></i>
                <div>{{ admin_t('Visitor Analytics') }}</div>
            </a>
        </li>

        {{-- Travel content --}}
        @php
            $travelContentOpen = request()->routeIs(
                'admin.countries.*',
                'admin.cities.*',
                'admin.regions.*',
                'admin.destinations.*',
                'admin.package-categories.*',
                'admin.packages.*',
                'admin.ready-tours.*',
                'admin.package-prices.*',
                'admin.banners.*',
                'admin.testimonials.*',
            );
        @endphp
        <li class="menu-item {{ $travelContentOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-world"></i>
                <div>{{ admin_t('Travel Content') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.countries.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.countries.index') }}" class="menu-link">
                        <div>{{ admin_t('Countries') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.cities.index') }}" class="menu-link">
                        <div>{{ admin_t('Cities') }}</div>
                    </a>
                </li>

                {{-- <li class="menu-item {{ request()->routeIs('admin.regions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.regions.index') }}" class="menu-link">
                        <div>المناطق</div>
                    </a>
                </li> --}}
                {{-- 
                <li class="menu-item {{ request()->routeIs('admin.destinations.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.destinations.index') }}" class="menu-link">
                        <div>الوجهات</div>
                    </a>
                </li> --}}
                <li class="menu-item {{ request()->routeIs('admin.attractions.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.attractions.index') }}" class="menu-link">
                        <div>{{ admin_t('Attractions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.package-categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.package-categories.index') }}" class="menu-link">
                        <div>{{ admin_t('Package Categories') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.packages.index') }}" class="menu-link">
                        <div>{{ admin_t('Packages') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.ready-tours.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.ready-tours.index') }}" class="menu-link">
                        <div>{{ admin_t('Ready Tours') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.package-prices.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.package-prices.index') }}" class="menu-link">
                        <div>{{ admin_t('Package Prices') }}</div>
                    </a>
                </li>

                {{-- <li class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.banners.index') }}" class="menu-link">
                        <div>البانرات</div>
                    </a>
                </li> --}}

                <li class="menu-item {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.testimonials.index') }}" class="menu-link">
                        <div>{{ admin_t('Testimonials') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Booking management --}}
        @php
            $bookingOpen = request()->routeIs(
                'admin.bookings.*',
                'admin.inquiries.*',
                'admin.clients.*',
                'admin.communications.*',
                'admin.payments.*',
                'admin.payment-methods.*',
            );
        @endphp
        <li class="menu-item {{ $bookingOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-briefcase"></i>
                <div>{{ admin_t('Booking Management') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.bookings.index') }}" class="menu-link">
                        <div>{{ admin_t('Bookings') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.inquiries.index') }}" class="menu-link">
                        <div>{{ admin_t('Inquiries') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.clients.index') }}" class="menu-link">
                        <div>{{ admin_t('Clients') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.communications.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.communications.index') }}" class="menu-link">
                        <div>{{ admin_t('Communication Logs') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.payments.index') }}" class="menu-link">
                        <div>{{ admin_t('Payments') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.payment-methods.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.payment-methods.index') }}" class="menu-link">
                        <div>{{ admin_t('Payment Methods') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Content --}}
        @php
            $contentOpen = request()->routeIs(
                'admin.media.*',
                'admin.articles.*',
                'admin.articles.statistics',
                'admin.static-pages.*',
                'admin.faqs.*',
                'admin.menus.*',
            );
        @endphp
        <li class="menu-item {{ $contentOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-file-text"></i>
                <div>{{ admin_t('Content') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.media.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-photo"></i>
                        <div>{{ admin_t('Media') }}</div>
                    </a>
                </li>

                <li
                    class="menu-item {{ request()->routeIs('admin.articles.index', 'admin.articles.show', 'admin.articles.edit', 'admin.articles.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.articles.index') }}" class="menu-link">
                        <div>{{ admin_t('Articles') }}</div>
                    </a>
                </li>


                <li class="menu-item {{ request()->routeIs('admin.articles.create-with-ai') ? 'active' : '' }}">
                    <a href="{{ route('admin.articles.create-with-ai') }}" class="menu-link">
                        <div>{{ admin_t('Create article with AI') }}</div>
                    </a>
                </li>

                {{-- <li class="menu-item {{ request()->routeIs('admin.articles.statistics') ? 'active' : '' }}">
                    <a href="{{ route('admin.articles.statistics') }}" class="menu-link">
                        <div>إحصائيات المقالات</div>
                    </a>
                </li> --}}

                <li class="menu-item {{ request()->routeIs('admin.static-pages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.static-pages.index') }}" class="menu-link">
                        <div>{{ admin_t('Static Pages') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.faqs.index') }}" class="menu-link">
                        <div>{{ admin_t('FAQs') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.menus.index') }}" class="menu-link">
                        <div>{{ admin_t('Menus') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Communication --}}
        @php
            $communicationOpen = request()->routeIs('admin.contact-us.*', 'admin.subscribe.*', 'admin.social-media.*');
        @endphp
        <li class="menu-item {{ $communicationOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-messages"></i>
                <div>{{ admin_t('Communication') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.contact-us.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.contact-us.index') }}" class="menu-link">
                        <div>{{ admin_t('Contact Messages') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.subscribe.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscribe.index') }}" class="menu-link">
                        <div>{{ admin_t('Subscribers') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.social-media.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.social-media.index') }}" class="menu-link">
                        <div>{{ admin_t('Social Media') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Localization --}}
        @php
            $localizationOpen = request()->routeIs('admin.languages.*', 'admin.translations.*');
        @endphp
        <li class="menu-item {{ $localizationOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-language"></i>
                <div>{{ admin_t('Localization') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.languages.index') }}" class="menu-link">
                        <div>{{ admin_t('Languages') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.translations.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.translations.index') }}" class="menu-link">
                        <div>{{ admin_t('Translations') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- SEO --}}
        @php
            $seoOpen = request()->routeIs('admin.seo-meta.*', 'admin.seo-redirects.*');
        @endphp
        <li class="menu-item {{ $seoOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-search"></i>
                <div>SEO</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.seo-meta.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.seo-meta.index') }}" class="menu-link">
                        <div>{{ admin_t('SEO Data') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.seo-redirects.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.seo-redirects.index') }}" class="menu-link">
                        <div>{{ admin_t('Redirects') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Administration --}}
        @php
            $administrationOpen = request()->routeIs(
                'admin.admins.*',
                'admin.users.*',
                'admin.user.*',
                'admin.roles.*',
                'admin.permissions.*',
            );
        @endphp
        <li class="menu-item {{ $administrationOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user-shield"></i>
                <div>{{ admin_t('Administration') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.admins.index') }}" class="menu-link">
                        <div>{{ admin_t('Admins') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.users.*', 'admin.user.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="menu-link">
                        <div>{{ admin_t('Users') }}</div>
                    </a>
                </li>
                <li
                    class="menu-item {{ request()->routeIs('admin.roles.index', 'admin.roles.create', 'admin.roles.edit', 'admin.roles.show', 'admin.roles.permissions', 'admin.roles.assign.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <div>{{ admin_t('Roles') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                        <div>{{ admin_t('Permissions') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- System --}}
        @php
            $systemOpen = request()->routeIs('admin.settings.*', 'admin.setting.*', 'admin.errors.*');
        @endphp
        <li class="menu-item {{ $systemOpen ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div>{{ admin_t('System') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}" class="menu-link">
                        <div>{{ admin_t('System Settings') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.setting.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.setting.pages') }}" class="menu-link">
                        <div>{{ admin_t('Page Settings') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.errors.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.errors.index') }}" class="menu-link">
                        <div>{{ admin_t('Error Logs') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Log out --}}
        <li class="menu-item mt-3">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="menu-link btn btn-link text-start w-100"
                    style="border: none; background: transparent;">
                    <i class="menu-icon tf-icons ti ti-logout"></i>
                    <div>{{ admin_t('Log out') }}</div>
                </button>
            </form>
        </li>
    </ul>
</aside>
