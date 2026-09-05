 @php
     $travelPackagesUrl = route('website.trips', ['type' => 'travel_package']);
     $dayToursUrl = route('website.tours.all', ['type' => 'day_tour']);
     $shoreExcursionsUrl = route('website.tours.all', ['type' => 'shore_excursion']);
     $tailorMadeUrl = route('website.tailor_made.index');
     $contactEmail = 'info@etrotours.com';
     $navigationDestinations = collect($navigationDestinations ?? []);
     $isHomePage = request()->routeIs('website.home');
     $isDestinationsPage = request()->routeIs('website.destinations.*');
     $isMultiCountryPage = request()->routeIs('website.multi_country');
     $isTravelPackagesPage = request()->routeIs('website.trips') && request('type') === 'travel_package';
     $isDayToursPage = request()->routeIs('website.tours.all') && request('type') === 'day_tour';
     $isShoreExcursionsPage = request('type') === 'shore_excursion';
     $isOffersPage = request()->routeIs('website.offers');
     $isContactPage = request()->routeIs('website.contact.*');
     $isTailorMadePage = request()->routeIs('website.tailor_made.*');
     $isNileCruisesPage = request()->routeIs('website.nile_cruises.*');
 @endphp

 <!-- Enhanced Navigation -->
 <nav class="navbar navbar-expand-lg">
     <div class="container">
         <a class="navbar-brand" href="{{ route('website.home') }}">
             <img class="d-none d-lg-block" src="{{ asset('website/logo/logo-lat.png') }}" alt="{{ __('Etro Tours') }}"
                 width="200" height="64">
             <img class="d-lg-none" src="{{ asset('website/logo/logo-lat.png') }}" alt="{{ __('Etro Tours') }}"
                 width="148" height="48">
         </a>

         <!-- Mobile Actions -->
         <div class="d-lg-none mobile-actions">
             <button type="button" aria-label="{{ __('Dark Mode') }}" title="{{ __('Dark Mode') }}"
                 class="mobile-action-btn theme-toggle-btn" data-theme-toggle data-dark-label="{{ __('Dark Mode') }}"
                 data-light-label="{{ __('Light Mode') }}">
                 <i class="la la-moon"></i>
             </button>
             <a href="{{ route('website.search.index') }}" aria-label="{{ __('Search') }}" class="mobile-action-btn">
                 <i class="la la-search"></i>
             </a>
             <a href="mailto:{{ $contactEmail }}" aria-label="{{ __('Email Us') }}" class="mobile-action-btn">
                 <i class="la la-envelope"></i>
             </a>
            <a href="tel:+201553383000" aria-label="{{ __('Call Us') }}" class="mobile-action-btn call-btn">
                <i class="la la-phone"></i>
            </a>
             <a href="viber://chat?number=201553383000" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Viber') }}"
                 class="mobile-action-btn viber communication-btn">
                 <i class="lab la-viber"></i>
             </a>
             <a href="https://wa.me/201553383000" target="_blank" rel="noopener noreferrer" aria-label="{{ __('WhatsApp') }}"
                 class="mobile-action-btn whatsapp communication-btn">
                 <i class="lab la-whatsapp"></i>
             </a>
         </div>

         <!-- Mobile Toggle Button -->
         <button type="button" class="d-lg-none mobile-toggle" data-mobile-menu-toggle
             aria-label="{{ __('Open Menu') }}" aria-controls="modernMobileMenu" aria-expanded="false">
             <div class="hamburger" id="hamburger">
                 <span></span>
                 <span></span>
                 <span></span>
             </div>
         </button>

         <!-- Desktop Navigation -->
         <div class="d-none d-lg-flex w-100 justify-content-between align-items-center">
             <ul class="navbar-nav mx-auto">
                 <li class="nav-item">
                     <a class="nav-link{{ $isHomePage ? ' is-active' : '' }}" href="{{ route('website.home') }}">
                         <i class="la la-home"></i>
                         {{ __('Home') }}
                     </a>
                 </li>
                 <li class="nav-item dropdown destinations-dropdown">
                     <button type="button" class="nav-link dropdown-toggle{{ $isDestinationsPage ? ' is-active' : '' }}"
                         data-bs-toggle="dropdown" data-navbar-dropdown-toggle aria-expanded="false"
                         aria-controls="desktopDestinationsMenu">
                         <i class="la la-globe"></i>
                         {{-- <i class="la la-map-marker"></i> --}}
                         {{ __('Destinations') }}
                     </button>
                     <ul class="dropdown-menu" id="desktopDestinationsMenu">
                         @forelse ($navigationDestinations as $destination)
                             <li><a class="dropdown-item" href="{{ $destination['url'] }}">
                                     <i class="la la-map-marker"></i> {{ $destination['title'] }}
                                 </a></li>
                         @empty
                             <li><a class="dropdown-item" href="{{ route('website.destinations.index') }}">
                                     <i class="la la-map"></i> {{ __('View Destinations') }}
                                 </a></li>
                         @endforelse
                     </ul>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link{{ $isNileCruisesPage ? ' is-active' : '' }}"
                         href="{{ route('website.nile_cruises.index') }}">
                         <i class="la la-ship"></i>
                         {{ __('Egypt Nile Cruise') }}
                     </a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link{{ $isShoreExcursionsPage ? ' is-active' : '' }}"
                         href="{{ $shoreExcursionsUrl }}">
                         <i class="la la-ship"></i>
                         {{ __('Shore Excursions') }}
                     </a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link special-offer{{ $isOffersPage ? ' is-active' : '' }}"
                         href="{{ route('website.offers') }}" style="width: max-content;">
                         <i class="la la-fire"></i>
                         {{ __('Travel Deals') }}
                     </a>
                 </li>
             </ul>

             <!-- Desktop Actions -->
             <div class="navbar-actions">
                 <button type="button" class="action-btn theme-toggle-btn" aria-label="{{ __('Dark Mode') }}"
                     title="{{ __('Dark Mode') }}" data-theme-toggle data-dark-label="{{ __('Dark Mode') }}"
                     data-light-label="{{ __('Light Mode') }}">
                     <i class="la la-moon"></i>
                 </button>
                 <a href="{{ route('website.search.index') }}" class="action-btn" aria-label="{{ __('Search') }}">
                     <i class="la la-search"></i>
                 </a>
                 <a href="{{ $tailorMadeUrl }}" class="btn-tailor{{ $isTailorMadePage ? ' is-active' : '' }}">
                     <i class="la la-magic"></i>
                     {{ __('Tailor-made') }}
                 </a>
                 <div class="dropdown language-dropdown">
                     @php
                         $localeNormalizer = app(\App\Support\LocaleNormalizer::class);
                         $currentLocale = $localeNormalizer->normalize(app()->getLocale());
                         $activeLanguages = \Illuminate\Support\Facades\Cache::remember(
                             'active_languages',
                             3600,
                             function () {
                                 return \App\Models\Language::where('is_active', 1)->get();
                             },
                         );
                     @endphp
                     <button type="button" class="language-toggle" data-bs-toggle="dropdown"
                         data-navbar-dropdown-toggle aria-expanded="false" aria-controls="desktopLanguageMenu">
                         <i class="la la-language"></i>
                         <span>{{ strtoupper($currentLocale) }}</span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-end language-menu" id="desktopLanguageMenu">
                         @foreach ($activeLanguages as $lang)
                             <li>
                                 <a class="dropdown-item {{ $currentLocale === $lang->normalized_code ? 'active' : '' }}"
                                     href="{{ route('website.lang.switch', $lang->normalized_code) }}">
                                     <img src="{{ asset('website/flags/4x3/' . $lang->display_flag_code . '.webp') }}"
                                         alt="" width="22" height="17" loading="lazy" decoding="async" aria-hidden="true">
                                     {{ $lang->display_name }}
                                 </a>
                             </li>
                         @endforeach
                     </ul>
                 </div>
             </div>
         </div>
     </div>
 </nav>
 <!-- Modern Mobile Menu -->
 <div class="modern-mobile-menu" id="modernMobileMenu" aria-label="{{ __('Mobile Navigation') }}">
     <div class="mobile-menu-header">
         <div class="mobile-menu-brand">
             <img src="{{ asset('website/logo/logo-lat.png') }}" alt="{{ __('Etro Tours') }}" width="132"
                 height="42">
         </div>
         <button type="button" class="mobile-close-btn" data-mobile-menu-toggle aria-label="{{ __('Close Menu') }}">
             <i class="la la-times"></i>
         </button>
     </div>

     <div class="mobile-menu-content">
         <div class="mobile-nav-item">
             <a href="{{ route('website.home') }}" class="mobile-nav-link{{ $isHomePage ? ' is-active' : '' }}">
                 <i class="la la-home"></i> {{ __('Home') }}
             </a>
         </div>

         <!-- Destinations Submenu -->
         <div class="mobile-nav-item">
             <button type="button" class="mobile-destinations-toggle{{ $isDestinationsPage ? ' is-active' : '' }}"
                 data-mobile-destinations-toggle aria-expanded="false" aria-controls="mobileDestinationsSubmenu">
                 <div style="display: flex; align-items: center;">
                     <i class="la la-globe" style="margin-right: 15px;"></i>
                     {{ __('Destinations') }}
                 </div>
                 <i class="la la-angle-down chevron"></i>
             </button>
             <div class="mobile-destinations-submenu" id="mobileDestinationsSubmenu">
                 @forelse ($navigationDestinations as $destination)
                     <div class="mobile-submenu-item">
                         <a href="{{ $destination['url'] }}" class="mobile-submenu-link">
                             <i class="la la-map-marker"></i> {{ $destination['title'] }}
                         </a>
                     </div>
                 @empty
                     <div class="mobile-submenu-item">
                         <a href="{{ route('website.destinations.index') }}" class="mobile-submenu-link">
                             <i class="la la-map"></i> {{ __('View Destinations') }}
                         </a>
                     </div>
                 @endforelse
             </div>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ route('website.nile_cruises.index') }}"
                 class="mobile-nav-link{{ $isNileCruisesPage ? ' is-active' : '' }}">
                 <i class="la la-ship"></i> {{ __('Egypt Nile Cruise') }}
             </a>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ $travelPackagesUrl }}"
                 class="mobile-nav-link{{ $isTravelPackagesPage ? ' is-active' : '' }}">
                 <i class="la la-suitcase"></i> {{ __('Tours Packages') }}
             </a>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ $dayToursUrl }}" class="mobile-nav-link{{ $isDayToursPage ? ' is-active' : '' }}">
                 <i class="la la-sun"></i> {{ __('Day Tours') }}
             </a>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ $shoreExcursionsUrl }}"
                 class="mobile-nav-link{{ $isShoreExcursionsPage ? ' is-active' : '' }}">
                 <i class="la la-ship"></i> {{ __('Shore Excursions') }}
             </a>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ route('website.offers') }}"
                 class="mobile-nav-link special-deals{{ $isOffersPage ? ' is-active' : '' }}">
                 <i class="la la-fire"></i> {{ __('Travel Deals') }}
             </a>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ route('website.contact.index') }}"
                 class="mobile-nav-link{{ $isContactPage ? ' is-active' : '' }}">
                 <i class="la la-envelope"></i> {{ __('Contact Us') }}
             </a>
         </div>

         <div class="mobile-nav-item">
             <a href="{{ $tailorMadeUrl }}" class="mobile-nav-link{{ $isTailorMadePage ? ' is-active' : '' }}">
                 <i class="la la-magic"></i> {{ __('Tailor-made Trips') }}
             </a>
         </div>

         <!-- Language Submenu -->
         <div class="mobile-nav-item">
             <button type="button" class="mobile-language-toggle" data-mobile-language-toggle
                 aria-expanded="false" aria-controls="mobileLanguageSubmenu">
                 <div style="display: flex; align-items: center;">
                     <i class="la la-language" style="margin-right: 12px;"></i>
                     {{ __('Language') }}
                 </div>
                 <i class="la la-angle-down chevron"></i>
             </button>
             <div class="mobile-language-submenu" id="mobileLanguageSubmenu">
                 @foreach ($activeLanguages as $lang)
                     <div class="mobile-language-item">
                         <a href="{{ route('website.lang.switch', $lang->normalized_code) }}"
                             class="mobile-language-link {{ $currentLocale === $lang->normalized_code ? 'active' : '' }}">
                             <img src="{{ asset('website/flags/4x3/' . $lang->display_flag_code . '.webp') }}"
                                 alt="" width="22" height="17" loading="lazy" decoding="async" aria-hidden="true">
                             {{ $lang->display_name }}
                         </a>
                     </div>
                 @endforeach
             </div>
         </div>

         <div class="mobile-actions-grid">
             <button type="button" class="mobile-action-card theme-toggle-btn" data-theme-toggle
                 aria-label="{{ __('Dark Mode') }}" title="{{ __('Dark Mode') }}"
                 data-dark-label="{{ __('Dark Mode') }}" data-light-label="{{ __('Light Mode') }}">
                 <i class="la la-moon"></i>
                 {{ __('Toggle Theme') }}
             </button>

             <a href="tel:+201553383000" class="mobile-action-card">
                 <i class="la la-phone"></i>
                 {{ __('Call Us') }}
             </a>

             <a href="{{ route('website.search.index') }}" class="mobile-action-card">
                 <i class="la la-search"></i>
                 {{ __('Search') }}
             </a>
         </div>

         <a href="{{ $tailorMadeUrl }}" class="mobile-enquiry-btn2">
             <i class="la la-paper-plane"></i> {{ __('Plan Your Journey') }}
         </a>
     </div>
 </div>

 <!-- JavaScript -->
 <script>
     // Enhanced Navigation Functions
     function toggleMobileMenu() {
         const mobileMenu = document.getElementById('modernMobileMenu');
         const hamburger = document.getElementById('hamburger');

         if (!mobileMenu || !hamburger) {
             return;
         }

         mobileMenu.classList.toggle('active');
         hamburger.classList.toggle('active');
         const isOpen = mobileMenu.classList.contains('active');

         document.querySelectorAll('[data-mobile-menu-toggle]').forEach(function(button) {
             button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
         });

         // Prevent body scroll when menu is open
         if (isOpen) {
             document.body.style.overflow = 'hidden';
         } else {
             document.body.style.overflow = '';
             closeAllSubmenus();
         }
     }

     function toggleMobileDestinations() {
         const submenu = document.getElementById('mobileDestinationsSubmenu');
         const icon = document.querySelector('.mobile-destinations-toggle i.chevron');
         const toggle = document.querySelector('[data-mobile-destinations-toggle]');

         if (!submenu || !toggle) {
             return;
         }

         submenu.classList.toggle('active');
         icon?.classList.toggle('rotated');
         toggle.setAttribute('aria-expanded', submenu.classList.contains('active') ? 'true' : 'false');

         // Close other submenu
         const languageSubmenu = document.getElementById('mobileLanguageSubmenu');
         const languageIcon = document.querySelector('.mobile-language-toggle i.chevron');
         if (languageSubmenu?.classList.contains('active')) {
             languageSubmenu.classList.remove('active');
             languageIcon?.classList.remove('rotated');
             document.querySelector('[data-mobile-language-toggle]')?.setAttribute('aria-expanded', 'false');
         }
     }

     function toggleMobileLanguage() {
         const submenu = document.getElementById('mobileLanguageSubmenu');
         const icon = document.querySelector('.mobile-language-toggle i.chevron');
         const toggle = document.querySelector('[data-mobile-language-toggle]');

         if (!submenu || !toggle) {
             return;
         }

         submenu.classList.toggle('active');
         icon?.classList.toggle('rotated');
         toggle.setAttribute('aria-expanded', submenu.classList.contains('active') ? 'true' : 'false');

         // Close other submenu
         const destinationsSubmenu = document.getElementById('mobileDestinationsSubmenu');
         const destinationsIcon = document.querySelector('.mobile-destinations-toggle i.chevron');
         if (destinationsSubmenu?.classList.contains('active')) {
             destinationsSubmenu.classList.remove('active');
             destinationsIcon?.classList.remove('rotated');
             document.querySelector('[data-mobile-destinations-toggle]')?.setAttribute('aria-expanded', 'false');
         }
     }

     function closeAllSubmenus() {
         const submenus = document.querySelectorAll('.mobile-destinations-submenu, .mobile-language-submenu');
         const icons = document.querySelectorAll(
             '.mobile-destinations-toggle i.chevron, .mobile-language-toggle i.chevron');

         submenus.forEach(submenu => submenu.classList.remove('active'));
         icons.forEach(icon => icon.classList.remove('rotated'));
         document.querySelectorAll('[data-mobile-destinations-toggle], [data-mobile-language-toggle]')
             .forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
     }

     // Navbar scroll effect
     const navbar = document.querySelector('.navbar');
     let navbarScrollFrame = null;

     window.addEventListener('scroll', function() {
         if (!navbar || navbarScrollFrame !== null) {
             return;
         }

         navbarScrollFrame = window.requestAnimationFrame(function() {
             navbar.classList.toggle('scrolled', window.scrollY > 50);
             navbarScrollFrame = null;
         });
     }, { passive: true });

     // Close menu on ESC key
     document.addEventListener('keydown', function(e) {
         if (e.key === 'Escape') {
             const mobileMenu = document.getElementById('modernMobileMenu');
             if (mobileMenu?.classList.contains('active')) {
                 toggleMobileMenu();
             }
         }
     });


     // Ripple effect for buttons
     function createRipple(e) {
         const button = e.currentTarget;
         const circle = document.createElement('span');
         const diameter = Math.max(button.clientWidth, button.clientHeight);
         const radius = diameter / 2;

         circle.style.width = circle.style.height = `${diameter}px`;
         circle.style.left = `${e.clientX - button.getBoundingClientRect().left - radius}px`;
         circle.style.top = `${e.clientY - button.getBoundingClientRect().top - radius}px`;
         circle.classList.add('ripple');

         const ripple = button.querySelector('.ripple');
         if (ripple) {
             ripple.remove();
         }

         button.appendChild(circle);
     }

     // Add ripple effect to interactive elements
     document.querySelectorAll('.mobile-action-btn, .btn-tailor, .mobile-enquiry-btn2, .action-btn').forEach(button => {
         button.addEventListener('click', createRipple);
     });

     // Add loading effect to navigation links
     document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
         link.addEventListener('click', function(e) {

             // Ignore dropdown links.
             if (this.classList.contains('dropdown-toggle')) return;
             if (this.closest('.dropdown')) return;

             const href = this.getAttribute('href');
             if (!href || href === '#' || href === 'javascript:void(0)') return;

             const spinner = document.createElement('span');
             spinner.className = 'nav-loading-spinner';
             this.prepend(spinner);

             setTimeout(() => {
                 spinner.remove();
             }, 1000);
         });
     });

     document.querySelectorAll('a[href^="#"]').forEach(anchor => {
         anchor.addEventListener('click', function(e) {
             const href = this.getAttribute('href');

             // Ignore empty hash links.
             if (href === '#') return;

             const target = document.querySelector(href);
             if (!target) return;

             e.preventDefault();

             const offsetTop = target.offsetTop - 80;
             window.scrollTo({
                 top: offsetTop,
                 behavior: 'smooth'
             });
         });
      });

     document.querySelectorAll('.navbar .destinations-dropdown').forEach(function(dropdown) {
         const toggle = dropdown.querySelector('[data-navbar-dropdown-toggle]');
         const menu = dropdown.querySelector('.dropdown-menu');

         if (!toggle || !menu) {
             return;
         }

         dropdown.addEventListener('mouseenter', function() {
             dropdown.classList.add('show');
             menu.classList.add('show');
             toggle.setAttribute('aria-expanded', 'true');
         });

         dropdown.addEventListener('mouseleave', function() {
             dropdown.classList.remove('show');
             menu.classList.remove('show');
             toggle.setAttribute('aria-expanded', 'false');
         });
     });
  </script>

  <style>
      .dropdown-submenu {
          position: relative;
      }
      .dropdown-submenu > .dropdown-submenu-menu {
          top: 0;
          left: 100%;
          margin-top: -6px;
          display: none;
          position: absolute;
          box-shadow: 0 10px 30px rgba(0,0,0,0.15);
          border-radius: 8px;
          border: none;
          min-width: 220px;
          background: #ffffff;
      }
      [dir="rtl"] .dropdown-submenu > .dropdown-submenu-menu {
          left: auto;
          right: 100%;
      }
      .dropdown-submenu:hover > .dropdown-submenu-menu {
          display: block;
      }
  </style>
