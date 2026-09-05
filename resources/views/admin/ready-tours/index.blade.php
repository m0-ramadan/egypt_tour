@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Ready Tours') . ' - AI Tour Templates')

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
            --warning-color: #ffc107;
            --dark-bg: #151521;
            --dark-card: #1e1e2d;
            --dark-border: rgba(255, 255, 255, 0.08);
        }

        html, body {
            background-color: var(--dark-bg) !important;
            color: #e1e1e6 !important;
            font-family: "Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }

        .layout-wrapper, .layout-container, .content-wrapper, .layout-page {
            background-color: var(--dark-bg) !important;
        }

        .layout-navbar, .bg-navbar-theme {
            background-color: #1e1e2d !important;
            color: #fff !important;
            border-bottom: 1px solid var(--dark-border) !important;
        }

        .bg-menu-theme, #layout-menu {
            background-color: #1e1e2d !important;
            color: #a2a3b7 !important;
            border-right: 1px solid var(--dark-border) !important;
        }

        .bg-menu-theme .menu-item.active > .menu-link,
        .bg-menu-theme .menu-item.open > .menu-link {
            background-color: rgba(105, 108, 255, 0.16) !important;
            color: #696cff !important;
        }

        .footer, .footer-theme {
            background-color: #1e1e2d !important;
            color: #a2a3b7 !important;
        }

        .main-card {
            background: var(--dark-card);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .4);
            border: 1px solid var(--dark-border);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: white;
            padding: 22px 28px;
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .35);
            border-top: 4px solid var(--primary-color);
            transition: transform .3s ease, border-color .3s ease;
            margin-bottom: 20px;
            border-left: 1px solid var(--dark-border);
            border-right: 1px solid var(--dark-border);
            border-bottom: 1px solid var(--dark-border);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            border-top-color: #764ba2;
        }

        .stats-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .stats-number {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 2px;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
        }

        .filter-card {
            background: var(--dark-card);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid var(--dark-border);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .filter-card .form-label {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            font-size: 0.82rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-left: 40px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.25) !important;
            border: 1px solid var(--dark-border) !important;
            color: #fff !important;
        }

        .search-box input:focus {
            background: rgba(0, 0, 0, 0.4) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, .4);
        }

        .form-select-dark {
            background-color: rgba(0, 0, 0, 0.25) !important;
            border: 1px solid var(--dark-border) !important;
            color: #fff !important;
            border-radius: 8px;
        }

        .form-select-dark option {
            background-color: #1e1e2d !important;
            color: #fff !important;
        }

        .tour-grid-card {
            background: var(--dark-card);
            border-radius: 14px;
            border: 1px solid var(--dark-border);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .tour-grid-card:hover {
            transform: translateY(-6px);
            border-color: rgba(105, 108, 255, 0.5);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
        }

        .tour-grid-img-wrapper {
            position: relative;
            width: 100%;
            padding-top: 60%;
            background-color: #11111b;
            overflow: hidden;
        }

        .tour-grid-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .tour-grid-card:hover .tour-grid-img {
            transform: scale(1.08);
        }

        .tour-grid-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .tour-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .tour-type-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-excursion {
            background: rgba(12, 99, 228, 0.2);
            color: #6ea8fe;
            border: 1px solid rgba(12, 99, 228, 0.4);
        }

        .badge-package {
            background: rgba(105, 108, 255, 0.2);
            color: #9b9eff;
            border: 1px solid rgba(105, 108, 255, 0.4);
        }

        .badge-cruise {
            background: rgba(32, 201, 151, 0.2);
            color: #75e6da;
            border: 1px solid rgba(32, 201, 151, 0.4);
        }

        .modal-content-dark {
            background-color: #1e1e2d !important;
            color: #fff !important;
            border: 1px solid var(--dark-border) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            border-radius: 16px;
        }

        .btn-action-primary {
            background: var(--primary-gradient);
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }

        .btn-action-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }

        .progress-dark {
            background-color: rgba(255, 255, 255, 0.1);
            height: 16px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-gradient {
            background: var(--primary-gradient);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}" class="text-light opacity-75">{{ admin_t('Home') }}</a></li>
                <li class="breadcrumb-item active text-white fw-bold">{{ admin_t('Ready Tours') }}</li>
            </ol>
        </nav>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show bg-success text-white border-0 mb-4 shadow" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show bg-danger text-white border-0 mb-4 shadow" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Statistics Cards --}}
        <div class="row mb-3">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['total']) }}</div>
                    <div class="stats-label">{{ admin_t('Total Tours') }}</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stats-card" style="border-top-color: #20c997;">
                    <div class="stats-icon" style="background: rgba(32,201,151,.15); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['imported']) }}</div>
                    <div class="stats-label">{{ admin_t('Imported to Site') }}</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stats-card" style="border-top-color: #fd7e14;">
                    <div class="stats-icon" style="background: rgba(253,126,20,.15); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['not_imported']) }}</div>
                    <div class="stats-label">{{ admin_t('Not Imported') }}</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stats-card" style="border-top-color: #0c63e4;">
                    <div class="stats-icon" style="background: rgba(12,99,228,.15); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['excursions']) }}</div>
                    <div class="stats-label">{{ admin_t('Excursions') }}</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stats-card" style="border-top-color: #696cff;">
                    <div class="stats-icon" style="background: rgba(105,108,255,.15); color:#696cff; border:1px solid rgba(105,108,255,.3);">
                        <i class="fas fa-suitcase-rolling"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['packages']) }}</div>
                    <div class="stats-label">{{ admin_t('Packages') }}</div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="stats-card" style="border-top-color: #ffc107;">
                    <div class="stats-icon" style="background: rgba(255,193,7,.15); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-ship"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['nile_cruises']) }}</div>
                    <div class="stats-label">{{ admin_t('Nile Cruises') }}</div>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.ready-tours.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ admin_t('Search Tour') }}</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ admin_t('Search by title, code or region...') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Tour Type') }}</label>
                        <select name="tour_type" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Types') }}</option>
                            <option value="excursion" {{ request('tour_type') == 'excursion' ? 'selected' : '' }}>{{ admin_t('Excursion (Day Tour)') }}</option>
                            <option value="package" {{ request('tour_type') == 'package' ? 'selected' : '' }}>{{ admin_t('Package (Multi-day)') }}</option>
                            <option value="nile_cruise" {{ request('tour_type') == 'nile_cruise' ? 'selected' : '' }}>{{ admin_t('Nile Cruise') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Category') }}</label>
                        <select name="category" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Categories') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                                    {{ $cat->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('City') }}</label>
                        <select name="city" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Cities') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->slug }}" {{ request('city') == $city->slug ? 'selected' : '' }}>
                                    {{ $city->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Import Status') }}</label>
                        <select name="import_status" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Statuses') }}</option>
                            <option value="not_imported" {{ request('import_status') == 'not_imported' ? 'selected' : '' }}>{{ admin_t('Not Imported') }}</option>
                            <option value="imported" {{ request('import_status') == 'imported' ? 'selected' : '' }}>{{ admin_t('Imported to Site') }}</option>
                            <option value="failed" {{ request('import_status') == 'failed' ? 'selected' : '' }}>{{ admin_t('Import Failed') }}</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">{{ admin_t('Sort') }}</label>
                        <select name="sort" class="form-select form-select-dark">
                            <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>{{ admin_t('Newest') }}</option>
                            <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>{{ admin_t('Most Popular') }}</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>{{ admin_t('Price: Low to High') }}</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>{{ admin_t('Price: High to Low') }}</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                        <button class="btn btn-primary px-4 fw-bold" type="submit">
                            <i class="fas fa-filter me-1"></i> {{ admin_t('Apply Filters') }}
                        </button>
                        <a href="{{ route('admin.ready-tours.index') }}" class="btn btn-outline-secondary text-white">
                            <i class="fas fa-redo me-1"></i> {{ admin_t('Reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Main Container Card --}}
        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0 text-white fw-bold"><i class="fas fa-sparkles me-2"></i>{{ admin_t('Ready Tours Catalog') }} (AI Tour Templates)</h5>
                    <small class="opacity-75">{{ admin_t('Browse and import AI tour templates from SavvyHost directly into your site packages.') }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light fw-bold text-dark" onclick="startSync()">
                        <i class="fas fa-cloud-download-alt me-1 text-primary"></i> {{ admin_t('Fetch Ready Tours') }}
                    </button>
                    <button type="button" class="btn btn-outline-light fw-bold" onclick="importSelected()">
                        <i class="fas fa-plus-circle me-1"></i> {{ admin_t('Import Selected') }}
                    </button>
                    <button type="button" class="btn btn-success fw-bold text-white" onclick="importAll()">
                        <i class="fas fa-file-import me-1"></i> {{ admin_t('Import All to Site') }}
                    </button>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-4">
                    @forelse($templates as $template)
                        @php
                            $isImported = $template->import_status === 'imported' || $template->import_status === 'imported_with_warnings' || ($template->imported_package_id && $template->importedPackage);
                            $previewMedia = $template->previewMedia;
                            $thumbSrc = $previewMedia?->display_thumbnail_url;
                        @endphp
                        <div class="col-xl-4 col-lg-6 col-md-6">
                            <div class="tour-grid-card">
                                <div class="tour-grid-img-wrapper">
                                    @if ($thumbSrc)
                                        <img src="{{ $thumbSrc }}" alt="{{ $template->display_name }}" class="tour-grid-img" loading="lazy">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-dark text-muted">
                                            <i class="fas fa-route fa-3x opacity-25"></i>
                                        </div>
                                    @endif

                                    <div class="position-absolute top-0 start-0 m-3 d-flex gap-2 flex-wrap">
                                        <div class="form-check me-2">
                                            <input class="form-check-input template-checkbox" type="checkbox" value="{{ $template->id }}" style="transform: scale(1.2);">
                                        </div>

                                        @if ($template->remote_tour_type === 'excursion')
                                            <span class="tour-type-badge badge-excursion"><i class="fas fa-sun me-1"></i>Excursion</span>
                                        @elseif($template->remote_tour_type === 'nile_cruise')
                                            <span class="tour-type-badge badge-cruise"><i class="fas fa-ship me-1"></i>Nile Cruise</span>
                                        @else
                                            <span class="tour-type-badge badge-package"><i class="fas fa-suitcase-rolling me-1"></i>Package</span>
                                        @endif
                                    </div>

                                    <div class="position-absolute bottom-0 start-0 m-3">
                                        @if ($isImported)
                                            <span class="badge bg-success text-white px-3 py-2 border border-success"><i class="fas fa-check-circle me-1"></i> {{ admin_t('Imported to Site') }}</span>
                                        @elseif($template->import_status === 'failed')
                                            <span class="badge bg-danger text-white px-3 py-2 border border-danger"><i class="fas fa-exclamation-triangle me-1"></i> {{ admin_t('Import Failed') }}</span>
                                        @else
                                            <span class="badge bg-secondary text-light px-3 py-2 border border-secondary"><i class="fas fa-clock me-1"></i> {{ admin_t('Not Imported') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="tour-grid-body">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="tour-title">{{ $template->display_name }}</h6>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-dark border border-secondary text-light"><i class="fas fa-clock me-1 text-warning"></i>{{ $template->duration_formatted }}</span>
                                            @if ($template->region)
                                                <span class="badge bg-dark border border-secondary text-info"><i class="fas fa-map-marker-alt me-1"></i>{{ $template->region }}</span>
                                            @endif
                                            @if (!empty($template->cities))
                                                <span class="badge bg-dark border border-secondary text-light"><i class="fas fa-city me-1"></i>{{ implode(', ', (array) $template->cities) }}</span>
                                            @endif
                                        </div>

                                        @if ($template->suggested_min_price)
                                            <div class="mb-3">
                                                <small class="text-muted d-block">{{ admin_t('Suggested Price:') }}</small>
                                                <span class="fs-5 fw-bold text-success">
                                                    ${{ number_format($template->suggested_min_price, 2) }} {{ $template->price_currency ?: 'USD' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-3 border-top border-secondary border-opacity-25 d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-info flex-grow-1" onclick="openPreviewModal({{ json_encode($template) }})">
                                            <i class="fas fa-eye me-1"></i> {{ admin_t('Preview') }}
                                        </button>

                                        @if ($isImported && $template->imported_package_id)
                                            <a href="{{ route('admin.packages.edit', $template->imported_package_id) }}" class="btn btn-sm btn-success flex-grow-1" target="_blank">
                                                <i class="fas fa-edit me-1"></i> {{ admin_t('Edit Package') }}
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-action-primary flex-grow-1" onclick="importSingle({{ $template->id }})">
                                                <i class="fas fa-plus-circle me-1"></i> {{ admin_t('Add to My Tours') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-route fa-4x opacity-20 text-white mb-3"></i>
                            <h5 class="text-white fw-bold">{{ admin_t('No ready tours found') }}</h5>
                            <p class="text-muted">{{ admin_t('Click "Fetch Ready Tours" to synchronize the latest templates from SavvyHost.') }}</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    @include('admin.layout.pagination', ['paginator' => $templates])
                </div>
            </div>
        </div>
    </div>

    {{-- Sync Modal --}}
    <div class="modal fade" id="syncModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark">
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title text-white fw-bold"><i class="fas fa-sync fa-spin me-2 text-primary"></i>{{ admin_t('Synchronizing Ready Tours...') }}</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="progress progress-dark mb-3">
                        <div id="syncProgressBar" class="progress-bar progress-bar-gradient" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="syncProgressPercentage" class="fs-4 fw-bold text-white mb-2">0%</div>
                    <div id="syncProgressMessage" class="text-muted">{{ admin_t('Connecting to server...') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-dark">
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title text-white fw-bold"><i class="fas fa-spinner fa-spin me-2 text-success"></i>{{ admin_t('Importing and Preparing Tour...') }}</h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="progress progress-dark mb-3">
                        <div id="importProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="importProgressPercentage" class="fs-4 fw-bold text-white mb-2">0%</div>
                    <div id="importProgressMessage" class="text-muted">{{ admin_t('Validating and creating data...') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-dark">
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title text-white fw-bold" id="previewModalTitle">{{ admin_t('Ready Tour Preview') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="previewModalBody">
                    {{-- Dynamically populated --}}
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25" id="previewModalFooter">
                    <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">{{ admin_t('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let syncInterval = null;
        let importInterval = null;

        function startSync() {
            const syncModal = new bootstrap.Modal(document.getElementById('syncModal'));
            syncModal.show();

            document.getElementById('syncProgressBar').style.width = '0%';
            document.getElementById('syncProgressPercentage').innerText = '0%';
            document.getElementById('syncProgressMessage').innerText = "{{ admin_t('Starting synchronization...') }}";

            $.ajax({
                url: "{{ route('admin.ready-tours.sync') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    const processUuid = response.process_uuid;
                    trackSyncProgress(processUuid, syncModal);
                },
                error: function(xhr) {
                    syncModal.hide();
                    alert("{{ admin_t('Sync error:') }} " + (xhr.responseJSON?.message || "{{ admin_t('Connection failed') }}"));
                }
            });
        }

        function trackSyncProgress(processUuid, syncModal) {
            syncInterval = setInterval(function() {
                $.ajax({
                    url: "/admin/ready-tours/sync-progress/" + processUuid,
                    type: "GET",
                    success: function(data) {
                        if (data) {
                            const pct = Math.round(data.percentage || 0);
                            document.getElementById('syncProgressBar').style.width = pct + '%';
                            document.getElementById('syncProgressPercentage').innerText = pct + '%';
                            document.getElementById('syncProgressMessage').innerText = data.message || '';

                            if (data.status === 'completed' || pct >= 100) {
                                clearInterval(syncInterval);
                                setTimeout(function() {
                                    syncModal.hide();
                                    window.location.reload();
                                }, 800);
                            } else if (data.status === 'failed') {
                                clearInterval(syncInterval);
                                syncModal.hide();
                                alert("{{ admin_t('Sync failed:') }} " + (data.message || "{{ admin_t('Unknown error') }}"));
                            }
                        }
                    }
                });
            }, 1000);
        }

        function importSingle(templateId) {
            const importModal = new bootstrap.Modal(document.getElementById('importModal'));
            importModal.show();

            document.getElementById('importProgressBar').style.width = '10%';
            document.getElementById('importProgressPercentage').innerText = '10%';
            document.getElementById('importProgressMessage').innerText = "{{ admin_t('Starting import...') }}";

            $.ajax({
                url: "/admin/ready-tours/" + templateId + "/import",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    document.getElementById('importProgressBar').style.width = '100%';
                    document.getElementById('importProgressPercentage').innerText = '100%';
                    document.getElementById('importProgressMessage').innerText = "{{ admin_t('Imported successfully!') }}";

                    setTimeout(function() {
                        importModal.hide();
                        if (response.redirect_url) {
                            if (confirm("{{ admin_t('Tour imported successfully! Would you like to edit the package now?') }}")) {
                                window.location.href = response.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            window.location.reload();
                        }
                    }, 500);
                },
                error: function(xhr) {
                    importModal.hide();
                    alert("{{ admin_t('Failed to import tour:') }} " + (xhr.responseJSON?.message || "{{ admin_t('Unknown error') }}"));
                }
            });
        }

        function importSelected() {
            const selected = [];
            $('.template-checkbox:checked').each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                alert("{{ admin_t('Please select at least one tour template to import.') }}");
                return;
            }

            if (!confirm(`{{ admin_t('Are you sure you want to import') }} ${selected.length} {{ admin_t('selected tour templates to your site?') }}`)) {
                return;
            }

            const importModal = new bootstrap.Modal(document.getElementById('importModal'));
            importModal.show();

            $.ajax({
                url: "{{ route('admin.ready-tours.import-selected') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    template_ids: selected
                },
                success: function(response) {
                    document.getElementById('importProgressBar').style.width = '100%';
                    document.getElementById('importProgressPercentage').innerText = '100%';
                    document.getElementById('importProgressMessage').innerText = response.message;

                    setTimeout(function() {
                        importModal.hide();
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    importModal.hide();
                    alert("{{ admin_t('Bulk import failed:') }} " + (xhr.responseJSON?.message || "{{ admin_t('Unknown error') }}"));
                }
            });
        }

        function importAll() {
            if (!confirm("{{ admin_t('Are you sure you want to import all non-imported templates to your site?') }}")) {
                return;
            }

            const importModal = new bootstrap.Modal(document.getElementById('importModal'));
            importModal.show();

            $.ajax({
                url: "{{ route('admin.ready-tours.import-all') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    document.getElementById('importProgressBar').style.width = '100%';
                    document.getElementById('importProgressPercentage').innerText = '100%';
                    document.getElementById('importProgressMessage').innerText = response.message;

                    setTimeout(function() {
                        importModal.hide();
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    importModal.hide();
                    alert("{{ admin_t('Bulk import failed:') }} " + (xhr.responseJSON?.message || "{{ admin_t('Unknown error') }}"));
                }
            });
        }

        function openPreviewModal(template) {
            const name = template.name?.en || template.name?.ar || 'Tour #' + template.remote_id;
            document.getElementById('previewModalTitle').innerText = name;

            let html = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-white fw-bold mb-2">{{ admin_t('Tour Details') }}</h6>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent text-light border-secondary"><strong class="text-white">{{ admin_t('Tour Type:') }}</strong> ${template.remote_tour_type || '-'}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary"><strong class="text-white">{{ admin_t('Category:') }}</strong> ${template.remote_category || '-'}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary"><strong class="text-white">{{ admin_t('Region:') }}</strong> ${template.region || '-'}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary"><strong class="text-white">{{ admin_t('Cities:') }}</strong> ${Array.isArray(template.cities) ? template.cities.join(', ') : '-'}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary"><strong class="text-white">{{ admin_t('Duration:') }}</strong> ${template.duration_value || '-'} ${template.duration_unit || ''}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary"><strong class="text-white">{{ admin_t('Suggested Price:') }}</strong> $${template.suggested_min_price || 0} ${template.price_currency || 'USD'}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-white fw-bold mb-2">{{ admin_t('Highlights') }}</h6>
                        <ul class="text-light">
                            ${Array.isArray(template.highlights) && template.highlights.length ? template.highlights.map(h => `<li>${typeof h === 'object' ? (h.text || h.title) : h}</li>`).join('') : '<li>{{ admin_t("No highlights recorded") }}</li>'}
                        </ul>
                    </div>
                </div>
            `;

            document.getElementById('previewModalBody').innerHTML = html;

            let footerHtml = `<button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">{{ admin_t('Close') }}</button>`;
            if (template.import_status !== 'imported' && template.import_status !== 'imported_with_warnings') {
                footerHtml += `<button type="button" class="btn btn-success fw-bold text-white ms-2" onclick="importSingle(${template.id})">{{ admin_t('Add to My Tours') }}</button>`;
            }
            document.getElementById('previewModalFooter').innerHTML = footerHtml;

            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        }
    </script>
@endsection
