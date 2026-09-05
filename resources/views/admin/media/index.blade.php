@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('مكتبة الصور والوسائط'))

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
            --dark-card-hover: #2b2b40;
            --dark-border: rgba(255, 255, 255, 0.08);
        }

        /* Force Entire Admin Page into Dark Mode */
        html, body {
            background-color: var(--dark-bg) !important;
            color: #e1e1e6 !important;
            font-family: "Cairo", sans-serif !important;
        }

        .layout-wrapper, .layout-container, .content-wrapper, .layout-page {
            background-color: var(--dark-bg) !important;
        }

        /* Navbar & Sidebar Dark Mode Overrides */
        .layout-navbar, .bg-navbar-theme {
            background-color: #1e1e2d !important;
            color: #fff !important;
            border-bottom: 1px solid var(--dark-border) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
        }

        .bg-menu-theme, #layout-menu {
            background-color: #1e1e2d !important;
            color: #a2a3b7 !important;
            border-right: 1px solid var(--dark-border) !important;
        }

        .bg-menu-theme .menu-link, .bg-menu-theme .menu-header {
            color: #a2a3b7 !important;
        }

        .bg-menu-theme .menu-item.active > .menu-link,
        .bg-menu-theme .menu-item.open > .menu-link {
            background-color: rgba(105, 108, 255, 0.16) !important;
            color: #696cff !important;
        }

        .footer, .footer-theme {
            background-color: #1e1e2d !important;
            color: #a2a3b7 !important;
            border-top: 1px solid var(--dark-border) !important;
        }

        /* Cards & Container Styling */
        .main-card {
            background: var(--dark-card);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .4);
            padding: 0;
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
            padding: 20px;
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
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
        }

        /* Filter Section */
        .filter-card {
            background: var(--dark-card);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 25px;
            border: 1px solid var(--dark-border);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .filter-card .form-label {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-right: 40px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.25) !important;
            border: 1px solid var(--dark-border) !important;
            color: #fff !important;
        }

        .search-box input:focus {
            background: rgba(0, 0, 0, 0.4) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, .25);
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

        .form-select-dark:focus {
            background-color: rgba(0, 0, 0, 0.4) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, .25);
        }

        .form-select-dark option {
            background-color: #1e1e2d !important;
            color: #fff !important;
        }

        /* Media Grid View Card */
        .media-grid-card {
            background: var(--dark-card);
            border-radius: 12px;
            border: 1px solid var(--dark-border);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .media-grid-card:hover {
            transform: translateY(-6px);
            border-color: rgba(105, 108, 255, 0.5);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .media-grid-img-wrapper {
            position: relative;
            width: 100%;
            padding-top: 75%; /* 4:3 Aspect Ratio */
            background-color: #11111b;
            overflow: hidden;
        }

        .media-grid-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .media-grid-card:hover .media-grid-img {
            transform: scale(1.08);
        }

        .media-grid-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(15, 15, 26, 0.9) 0%, rgba(15, 15, 26, 0.2) 60%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 15px;
            gap: 8px;
        }

        .media-grid-card:hover .media-grid-overlay {
            opacity: 1;
        }

        .media-grid-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .media-grid-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .media-grid-meta {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.55);
        }

        /* Table Styling */
        .media-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--dark-border);
            background-color: #11111b;
            transition: transform 0.2s ease;
        }

        .media-thumbnail:hover {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .table-dark-custom {
            color: #fff !important;
        }

        .table-dark-custom th {
            background: rgba(0, 0, 0, 0.2) !important;
            color: rgba(255, 255, 255, 0.85) !important;
            border-bottom: 2px solid var(--dark-border) !important;
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-dark-custom td {
            border-bottom: 1px solid var(--dark-border) !important;
            vertical-align: middle;
        }

        .table-dark-custom tbody tr:hover {
            background-color: rgba(105, 108, 255, 0.08) !important;
        }

        .badge-storage {
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 11px;
        }

        .badge-global {
            background: rgba(32, 201, 151, .15);
            color: var(--success-color);
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .badge-private {
            background: rgba(253, 126, 20, .15);
            color: var(--danger-color);
            border: 1px solid rgba(253, 126, 20, .3);
        }

        .badge-public {
            background: rgba(12, 99, 228, .15);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 64px;
            color: rgba(255, 255, 255, .12);
            margin-bottom: 18px;
        }

        /* Modal Dark Mode */
        .modal-content-dark {
            background-color: #1e1e2d !important;
            color: #fff !important;
            border: 1px solid var(--dark-border) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            border-radius: 16px;
        }

        .modal-preview-img {
            max-height: 420px;
            object-fit: contain;
            width: 100%;
            border-radius: 10px;
            background: #11111b;
            border: 1px solid var(--dark-border);
        }

        .modal-details-box {
            background: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid var(--dark-border) !important;
            border-radius: 12px;
        }

        .btn-fetch-media {
            background: #ffffff;
            color: #151521;
            font-weight: 700;
            border-radius: 8px;
            padding: 8px 18px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-fetch-media:hover {
            background: #e2e2e8;
            color: #151521;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }

        .view-btn {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--dark-border);
            color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            padding: 6px 12px;
            transition: all 0.2s ease;
        }

        .view-btn.active, .view-btn:hover {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }

        /* Pagination Styling */
        .pagination .page-link {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border-color: var(--dark-border) !important;
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 8px 16px;
            border-radius: 6px;
            margin: 0 2px;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-gradient) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
            font-weight: bold;
        }

        .pagination .page-link:hover {
            background-color: rgba(105, 108, 255, 0.25) !important;
            color: #fff !important;
        }

        .pagination .page-item.disabled .page-link {
            background-color: rgba(0, 0, 0, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.04) !important;
            color: rgba(255, 255, 255, 0.25) !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}" class="text-light opacity-75">{{ admin_t('Home') }}</a></li>
                <li class="breadcrumb-item active text-white fw-bold">{{ admin_t('Media Library') }}</li>
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

        {{-- Stat Cards --}}
        <div class="row mb-3">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['total']) }}</div>
                    <div class="stats-label">{{ admin_t('Total Media') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: rgba(32,201,151,.15); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['downloaded'] ?? 0) }}</div>
                    <div class="stats-label">{{ admin_t('Downloaded Locally') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: rgba(253,126,20,.15); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="stats-number">{{ number_format($stats['global']) }}</div>
                    <div class="stats-label">{{ admin_t('Global Media') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: rgba(12,99,228,.15); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="stats-number fs-6">
                        {{ $stats['last_sync'] ? \Carbon\Carbon::parse($stats['last_sync'])->diffForHumans() : admin_t('Never') }}
                    </div>
                    <div class="stats-label">{{ admin_t('Last Sync') }}</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.media.index') }}" id="filterForm">
                <input type="hidden" name="view" id="viewModeInput" value="{{ request('view', 'grid') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ admin_t('Search') }}</label>
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                placeholder="{{ admin_t('Search filename, title, alt text...') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Category') }}</label>
                        <select name="category" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Categories') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Country') }}</label>
                        <select name="country_slug" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Countries') }}</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country }}" {{ request('country_slug') == $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('City') }}</label>
                        <select name="city_slug" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Cities') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" {{ request('city_slug') == $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Sub Category') }}</label>
                        <select name="sub_category" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Sub Categories') }}</option>
                            @foreach ($subCategories as $subCat)
                                <option value="{{ $subCat }}" {{ request('sub_category') == $subCat ? 'selected' : '' }}>
                                    {{ $subCat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ admin_t('Storage Type') }}</label>
                        <select name="storage_type" class="form-select form-select-dark">
                            <option value="">{{ admin_t('All Storage') }}</option>
                            @foreach ($storageTypes as $st)
                                <option value="{{ $st }}" {{ request('storage_type') == $st ? 'selected' : '' }}>
                                    {{ ucfirst($st) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2 align-items-end ms-auto">
                        <button class="btn btn-primary w-100 font-weight-bold" type="submit">
                            <i class="fas fa-filter me-1"></i> {{ admin_t('Apply Filters') }}
                        </button>
                        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary w-100 text-white border-secondary">
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
                    <h5 class="mb-0 text-white fw-bold"><i class="fas fa-photo-video me-2"></i>{{ admin_t('Media Library') }}</h5>
                    <small class="opacity-75">{{ admin_t('Manage and synchronize SavvyHost media files') }}</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    {{-- Grid / Table View Switcher --}}
                    <div class="btn-group" role="group">
                        <button type="button" class="view-btn {{ request('view', 'grid') === 'grid' ? 'active' : '' }}" onclick="switchView('grid')" title="Grid View">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="view-btn {{ request('view') === 'table' ? 'active' : '' }}" onclick="switchView('table')" title="Table View">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.media.sync') }}" method="POST" id="syncForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-fetch-media" id="syncBtn">
                            <i class="fas fa-cloud-download-alt me-2" id="syncIcon"></i>
                            <span id="syncText">جلب الصور / Fetch Media</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-4">
                @if(request('view', 'grid') === 'grid')
                    {{-- Grid View --}}
                    <div class="row g-4">
                        @forelse($mediaItems as $item)
                            @php
                                $thumbSrc = $item->display_thumbnail_url;
                                $displayUrl = $item->display_url;
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <div class="media-grid-card">
                                    <div class="media-grid-img-wrapper">
                                        <img src="{{ $thumbSrc }}" alt="{{ $item->alt_text ?? $item->filename }}"
                                            class="media-grid-img" loading="lazy"
                                            onerror="this.onerror=null; this.src='https://via.placeholder.com/300x200?text=No+Image';">
                                        
                                        <div class="media-grid-overlay">
                                            <button type="button" class="btn btn-sm btn-info rounded-circle p-2"
                                                onclick="openPreviewModal({{ json_encode($item) }})" title="{{ admin_t('Preview') }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if ($displayUrl)
                                                <a href="{{ $displayUrl }}" target="_blank" class="btn btn-sm btn-light rounded-circle p-2"
                                                    title="{{ admin_t('Open Image') }}">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-warning rounded-circle p-2"
                                                    onclick="copyUrl('{{ $displayUrl }}')" title="{{ admin_t('Copy URL') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="position-absolute top-0 start-0 m-2 d-flex gap-1 flex-wrap">
                                            <span class="badge-storage {{ $item->is_global || $item->storage_type === 'global' ? 'badge-global' : 'badge-private' }}">
                                                {{ ucfirst($item->storage_type ?? ($item->is_global ? 'global' : 'private')) }}
                                            </span>
                                            @if ($item->is_downloaded || $item->local_path)
                                                <span class="badge bg-success text-white border border-success">
                                                    <i class="fas fa-hdd me-1"></i>محلي
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="media-grid-body">
                                        <div class="media-grid-title" title="{{ $item->title ?: $item->filename }}">
                                            {{ $item->title ?: $item->filename }}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 media-grid-meta">
                                            <span class="badge bg-dark border border-secondary text-light">#{{ $item->remote_id ?? $item->id }}</span>
                                            <span>{{ $item->size_human ?: ($item->size_bytes ? number_format($item->size_bytes / 1024, 1) . ' KB' : '-') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-photo-video"></i></div>
                                    <h5 class="empty-state-text text-white fw-bold">{{ admin_t('No media records found') }}</h5>
                                    <p class="text-muted">{{ admin_t('Click "Fetch Media" to synchronize images from SavvyHost API.') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Table View --}}
                    <div class="table-responsive p-0 rounded border border-secondary border-opacity-25">
                        <table class="table table-dark table-hover table-dark-custom mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">{{ admin_t('Thumbnail') }}</th>
                                    <th>{{ admin_t('ID') }}</th>
                                    <th>{{ admin_t('Filename / Title') }}</th>
                                    <th>{{ admin_t('Category') }}</th>
                                    <th>{{ admin_t('Location') }}</th>
                                    <th>{{ admin_t('Sub Category') }}</th>
                                    <th>{{ admin_t('Storage') }}</th>
                                    <th>{{ admin_t('Size') }}</th>
                                    <th>{{ admin_t('Created At') }}</th>
                                    <th class="text-end pe-4">{{ admin_t('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mediaItems as $item)
                                    @php
                                        $thumbSrc = $item->display_thumbnail_url;
                                        $displayUrl = $item->display_url;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <img src="{{ $thumbSrc }}" alt="{{ $item->alt_text ?? $item->filename }}"
                                                class="media-thumbnail" loading="lazy"
                                                onerror="this.onerror=null; this.src='https://via.placeholder.com/70?text=No+Image';">
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-light">#{{ $item->remote_id ?? $item->id }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-white">{{ $item->title ?: $item->filename }}</div>
                                            <small class="text-muted d-block">{{ $item->original_filename }}</small>
                                        </td>
                                        <td>
                                            @if ($item->category)
                                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">{{ $item->category }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->country_slug || $item->city_slug)
                                                <small class="d-block text-light">
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                    {{ implode(' / ', array_filter([$item->country_slug, $item->city_slug])) }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->sub_category)
                                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25">{{ $item->sub_category }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-storage {{ $item->is_global || $item->storage_type === 'global' ? 'badge-global' : 'badge-private' }}">
                                                {{ ucfirst($item->storage_type ?? ($item->is_global ? 'global' : 'private')) }}
                                            </span>
                                            @if ($item->is_downloaded || $item->local_path)
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 ms-1">
                                                    <i class="fas fa-hdd me-1"></i>محلي
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-light">{{ $item->size_human ?: ($item->size_bytes ? number_format($item->size_bytes / 1024, 1) . ' KB' : '-') }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $item->remote_created_at ? $item->remote_created_at->format('Y-m-d H:i') : ($item->created_at ? $item->created_at->format('Y-m-d H:i') : '-') }}
                                            </small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-info"
                                                    onclick="openPreviewModal({{ json_encode($item) }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if ($displayUrl)
                                                    <a href="{{ $displayUrl }}" target="_blank" class="btn btn-sm btn-outline-light"
                                                        title="{{ admin_t('Open Image') }}">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>

                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="copyUrl('{{ $displayUrl }}')" title="{{ admin_t('Copy URL') }}">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10">
                                            <div class="empty-state">
                                                <div class="empty-state-icon"><i class="fas fa-photo-video"></i></div>
                                                <h5 class="empty-state-text text-white fw-bold">{{ admin_t('No media records found') }}</h5>
                                                <p class="text-muted">{{ admin_t('Click "Fetch Media" to synchronize images from SavvyHost API.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @include('admin.layout.pagination', ['paginator' => $mediaItems])

        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark">
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title text-white fw-bold" id="modalTitle">{{ admin_t('Media Preview') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="modal-preview-img mb-3">
                    <div class="text-start modal-details-box p-3">
                        <div class="row g-2 text-white">
                            <div class="col-md-6"><strong>Filename:</strong> <span id="modalFilename" class="text-light ms-1">-</span></div>
                            <div class="col-md-6"><strong>UUID:</strong> <span id="modalUuid" class="text-light ms-1">-</span></div>
                            <div class="col-md-6"><strong>Category:</strong> <span id="modalCategory" class="text-light ms-1">-</span></div>
                            <div class="col-md-6"><strong>Sub Category:</strong> <span id="modalSubCategory" class="text-light ms-1">-</span></div>
                            <div class="col-md-6"><strong>Location:</strong> <span id="modalLocation" class="text-light ms-1">-</span></div>
                            <div class="col-md-6"><strong>Size:</strong> <span id="modalSize" class="text-light ms-1">-</span></div>
                            <div class="col-12"><strong>Alt Text:</strong> <span id="modalAltText" class="text-light ms-1">-</span></div>
                            <div class="col-12"><strong>Tags:</strong> <span id="modalTags" class="ms-1">-</span></div>
                            <div class="col-12 text-break mt-2">
                                <strong>Original URL:</strong>
                                <a id="modalUrlLink" href="" target="_blank" class="text-info ms-1"></a>
                            </div>
                            <div class="col-12 text-break" id="modalWebpWrapper">
                                <strong>WebP URL:</strong>
                                <a id="modalWebpLink" href="" target="_blank" class="text-success ms-1"></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ admin_t('Close') }}</button>
                    <button type="button" class="btn btn-warning" id="modalCopyBtn" onclick="copyUrlFromModal()">
                        <i class="fas fa-copy me-1"></i> {{ admin_t('Copy URL') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Progress Modal -->
    <div class="modal fade" id="syncProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light border border-secondary" style="border-radius: 16px;">
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="fas fa-cloud-download-alt text-primary"></i>
                        <span>جاري جلب وتنزيل الصور محلياً</span>
                    </h5>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 fw-bold">نسبة الإنجاز:</span>
                        <span class="fw-bold fs-4 text-primary" id="syncPercentText">0%</span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="progress mb-4" style="height: 22px; background-color: rgba(255,255,255,0.1); border-radius: 12px; overflow: hidden;">
                        <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold"
                             role="progressbar" style="width: 0%; transition: width 0.3s ease; font-size: 13px;">
                            0%
                        </div>
                    </div>

                    {{-- Detailed Stats Card --}}
                    <div class="p-3 rounded-3 border border-secondary border-opacity-50 text-start" style="background: rgba(0,0,0,0.35);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50"><i class="fas fa-download text-success me-2"></i>الصور التي تم تنزيلها محلياً:</span>
                            <span class="fw-bold text-success fs-6" id="syncDownloadedText">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50"><i class="fas fa-images text-info me-2"></i>إجمالي صور الـ API:</span>
                            <span class="fw-bold text-info fs-6" id="syncTotalText">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-white-50"><i class="fas fa-tasks text-warning me-2"></i>الصور التي تم معالجتها:</span>
                            <span class="fw-bold text-warning fs-6" id="syncProcessedText">0 / 0</span>
                        </div>
                    </div>

                    <div class="text-muted small mt-3 d-flex align-items-center justify-content-center gap-2" id="syncStatusWrapper">
                        <div class="spinner-border spinner-border-sm text-primary" role="status" id="syncSpinner"></div>
                        <span id="syncStatusMsg">جاري البدء والاتصال بالخادم...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentModalUrl = '';

        $(document).ready(function() {
            // Set dark-style on html tag for Tabler template consistency
            document.documentElement.classList.add('dark-style');
            document.documentElement.classList.remove('light-style');

            let pollInterval = null;

            $('#syncForm').on('submit', function(e) {
                e.preventDefault();

                const btn = $('#syncBtn');
                btn.prop('disabled', true);
                $('#syncIcon').removeClass('fa-cloud-download-alt').addClass('fa-spinner fa-spin');

                const progressModalEl = document.getElementById('syncProgressModal');
                const progressModal = new bootstrap.Modal(progressModalEl);
                progressModal.show();

                // Reset modal state
                $('#syncProgressBar').css('width', '0%').text('0%');
                $('#syncPercentText').text('0%');
                $('#syncDownloadedText').text('0');
                $('#syncTotalText').text('0');
                $('#syncProcessedText').text('0 / 0');
                $('#syncStatusMsg').text('جاري الاتصال بالسيرفر وجلب القائمة...');
                $('#syncSpinner').show();

                // Poll progress endpoint every 700ms
                pollInterval = setInterval(function() {
                    $.ajax({
                        url: "{{ route('admin.media.sync-progress') }}",
                        type: 'GET',
                        cache: false,
                        success: function(data) {
                            if (data) {
                                const percent = Math.min(100, Math.max(0, data.percentage || 0));
                                $('#syncProgressBar').css('width', percent + '%').text(percent + '%');
                                $('#syncPercentText').text(percent + '%');
                                $('#syncDownloadedText').text(data.downloaded || 0);
                                $('#syncTotalText').text(data.total || 0);
                                $('#syncProcessedText').text((data.processed || 0) + ' / ' + (data.total || 0));
                                if (data.message) {
                                    $('#syncStatusMsg').text(data.message);
                                }
                            }
                        }
                    });
                }, 700);

                // Send POST request
                $.ajax({
                    url: "{{ route('admin.media.sync') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        clearInterval(pollInterval);
                        $('#syncProgressBar').css('width', '100%').text('100%');
                        $('#syncPercentText').text('100%');
                        $('#syncSpinner').hide();
                        $('#syncStatusMsg').text('تم اكتمال التنزيل بنجاح!');

                        setTimeout(function() {
                            progressModal.hide();
                            btn.prop('disabled', false);
                            $('#syncIcon').removeClass('fa-spinner fa-spin').addClass('fa-cloud-download-alt');

                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم التنزيل بنجاح!',
                                    text: response.message || 'تم مزامنة وتنزيل جميع الصور بنجاح.',
                                    confirmButtonText: 'حسناً',
                                    background: '#1e1e2d',
                                    color: '#fff'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert(response.message || 'تم التنزيل بنجاح!');
                                window.location.reload();
                            }
                        }, 700);
                    },
                    error: function(xhr) {
                        clearInterval(pollInterval);
                        progressModal.hide();
                        btn.prop('disabled', false);
                        $('#syncIcon').removeClass('fa-spinner fa-spin').addClass('fa-cloud-download-alt');

                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ أثناء تنزيل الصور.';
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: msg,
                                confirmButtonText: 'حسناً',
                                background: '#1e1e2d',
                                color: '#fff'
                            });
                        } else {
                            alert('خطأ: ' + msg);
                        }
                    }
                });
            });
        });

        function switchView(viewName) {
            $('#viewModeInput').val(viewName);
            $('#filterForm').submit();
        }

        function openPreviewModal(item) {
            const displayUrl = item.local_url || item.display_url || item.webp_url || item.url || item.thumbnail_url;
            currentModalUrl = displayUrl || item.url || '';

            $('#modalImage').attr('src', displayUrl);
            $('#modalTitle').text(item.title || item.filename || 'Media Details');
            $('#modalFilename').text(item.filename || '-');
            $('#modalUuid').text(item.uuid || '-');
            $('#modalCategory').text(item.category || '-');
            $('#modalSubCategory').text(item.sub_category || '-');
            $('#modalLocation').text([item.country_slug, item.city_slug].filter(Boolean).join(' / ') || '-');
            $('#modalSize').text(item.size_human || (item.size_bytes ? (item.size_bytes / 1024).toFixed(1) + ' KB' : '-'));
            $('#modalAltText').text(item.alt_text || '-');

            if (item.tags && Array.isArray(item.tags) && item.tags.length > 0) {
                $('#modalTags').html(item.tags.map(t => `<span class="badge bg-secondary bg-opacity-50 text-white me-1">${t}</span>`).join(''));
            } else {
                $('#modalTags').text('-');
            }

            if (item.url) {
                $('#modalUrlLink').attr('href', item.url).text(item.url);
            } else {
                $('#modalUrlLink').removeAttr('href').text('-');
            }

            if (item.webp_url) {
                $('#modalWebpWrapper').show();
                $('#modalWebpLink').attr('href', item.webp_url).text(item.webp_url);
            } else {
                $('#modalWebpWrapper').hide();
            }

            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        }

        function copyUrl(url) {
            if (!url) return;
            navigator.clipboard.writeText(url).then(function() {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم النسخ!',
                        text: 'تم نسخ الرابط إلى الحافظة بنجاح.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#1e1e2d',
                        color: '#fff'
                    });
                } else {
                    alert('تم النسخ إلى الحافظة.');
                }
            }).catch(function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }

        function copyUrlFromModal() {
            if (currentModalUrl) {
                copyUrl(currentModalUrl);
            }
        }
    </script>
@endsection
