@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Visitor Analytics & Insights'))

@section('css')
    <style>
        body {
            font-family: "Public Sans", sans-serif !important;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
        }

        .device-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
        }
        
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
        }

        .path-text {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ admin_t('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ admin_t('Visitor Analytics') }}</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="ti ti-chart-bar text-primary me-2"></i>{{ admin_t('Visitor Analytics & Traffic Insights') }}</h3>
                <p class="text-muted mb-0">{{ admin_t('Track website traffic, geographical locations, device & phone models, and browser details.') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.visitors.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-refresh me-1"></i> {{ admin_t('Refresh Data') }}
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row g-4 mb-4">
            {{-- Total Visits --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card card-border-shadow-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-primary fs-3">
                                    <i class="ti ti-eye"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($totalVisitors) }}</h4>
                                <small class="text-muted">{{ admin_t('Total Recorded Visits') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Today's Visits --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card card-border-shadow-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-success fs-3">
                                    <i class="ti ti-calendar-event"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($todayVisitors) }}</h4>
                                <small class="text-muted">{{ admin_t('Visits Today') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monthly Visits --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-info fs-3">
                                    <i class="ti ti-chart-dots"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($monthVisitors) }}</h4>
                                <small class="text-muted">{{ admin_t('Visits This Month') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Unique Visitors --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card stat-card card-border-shadow-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-warning fs-3">
                                    <i class="ti ti-user-check"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ number_format($uniqueVisitors) }}</h4>
                                <small class="text-muted">{{ admin_t('Unique Visitors (IPs)') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Breakdown Section --}}
        <div class="row g-4 mb-4">
            {{-- Device Types --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                        <h5 class="card-title mb-0"><i class="ti ti-device-mobile me-2 text-primary"></i>{{ admin_t('Device & Phone Types') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium"><i class="ti ti-device-mobile text-success me-1"></i> {{ admin_t('Mobile Phones') }}</span>
                                <span class="fw-bold">{{ $mobilePercent }}% ({{ $mobileCount }})</span>
                            </div>
                            <div class="progress progress-bar-custom bg-light">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $mobilePercent }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium"><i class="ti ti-device-laptop text-primary me-1"></i> {{ admin_t('Desktop Computers') }}</span>
                                <span class="fw-bold">{{ $desktopPercent }}% ({{ $desktopCount }})</span>
                            </div>
                            <div class="progress progress-bar-custom bg-light">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $desktopPercent }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium"><i class="ti ti-device-tablet text-info me-1"></i> {{ admin_t('Tablets') }}</span>
                                <span class="fw-bold">{{ $tabletPercent }}% ({{ $tabletCount }})</span>
                            </div>
                            <div class="progress progress-bar-custom bg-light">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $tabletPercent }}%"></div>
                            </div>
                        </div>

                        @if($botCount > 0)
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium"><i class="ti ti-robot text-danger me-1"></i> {{ admin_t('Search Bots / Crawlers') }}</span>
                                <span class="fw-bold">{{ $botCount }}</span>
                            </div>
                            <div class="progress progress-bar-custom bg-light">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ round(($botCount / max($totalVisitors, 1)) * 100, 1) }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Top Countries --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                        <h5 class="card-title mb-0"><i class="ti ti-world me-2 text-primary"></i>{{ admin_t('Top Visitor Countries') }}</h5>
                    </div>
                    <div class="card-body">
                        @forelse($topCountries as $c)
                            @php
                                $cPercent = round(($c->total / max($totalVisitors, 1)) * 100, 1);
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary fs-6"><i class="ti ti-map-pin"></i></span>
                                    </span>
                                    <span class="fw-medium">{{ $c->country ?: admin_t('Unknown / Local') }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-label-primary me-2">{{ $c->total }}</span>
                                    <small class="text-muted">{{ $cPercent }}%</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">{{ admin_t('No country data recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Operating Systems & Browsers --}}
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                        <h5 class="card-title mb-0"><i class="ti ti-brand-chrome me-2 text-primary"></i>{{ admin_t('Systems & Browsers') }}</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold text-muted mb-2"><i class="ti ti-cpu me-1"></i> {{ admin_t('Operating Systems') }}</h6>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @forelse($topPlatforms as $p)
                                <span class="badge bg-label-dark p-2">
                                    {{ $p->platform }}: <strong class="ms-1">{{ $p->total }}</strong>
                                </span>
                            @empty
                                <small class="text-muted">{{ admin_t('Unknown') }}</small>
                            @endforelse
                        </div>

                        <h6 class="fw-bold text-muted mb-2"><i class="ti ti-brand-chrome me-1"></i> {{ admin_t('Browsers') }}</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($topBrowsers as $b)
                                <span class="badge bg-label-info p-2">
                                    {{ $b->browser }}: <strong class="ms-1">{{ $b->total }}</strong>
                                </span>
                            @empty
                                <small class="text-muted">{{ admin_t('Unknown') }}</small>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Form & Visitors Table --}}
        <div class="card">
            <div class="card-header border-bottom">
                <form method="GET" action="{{ route('admin.visitors.index') }}" class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">{{ admin_t('Search Visitor') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ admin_t('Search by IP, Country, City, Device...') }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">{{ admin_t('Device Type') }}</label>
                        <select name="device_type" class="form-select">
                            <option value="">{{ admin_t('All Devices') }}</option>
                            <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>{{ admin_t('Mobile Phones') }}</option>
                            <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>{{ admin_t('Desktop Computers') }}</option>
                            <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>{{ admin_t('Tablets') }}</option>
                            <option value="bot" {{ request('device_type') == 'bot' ? 'selected' : '' }}>{{ admin_t('Bots & Crawlers') }}</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">{{ admin_t('Time Period') }}</label>
                        <select name="date_range" class="form-select">
                            <option value="">{{ admin_t('All Time') }}</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>{{ admin_t('Today') }}</option>
                            <option value="7days" {{ request('date_range') == '7days' ? 'selected' : '' }}>{{ admin_t('Last 7 Days') }}</option>
                            <option value="30days" {{ request('date_range') == '30days' ? 'selected' : '' }}>{{ admin_t('Last 30 Days') }}</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2 d-flex align-items-end gap-2 mt-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-filter me-1"></i> {{ admin_t('Filter') }}
                        </button>
                        @if(request()->anyFilled(['q', 'device_type', 'date_range']))
                            <a href="{{ route('admin.visitors.index') }}" class="btn btn-label-secondary" title="{{ admin_t('Reset Filter') }}">
                                <i class="ti ti-x"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ admin_t('IP Address & Host') }}</th>
                            <th>{{ admin_t('Country & City') }}</th>
                            <th>{{ admin_t('Device & OS') }}</th>
                            <th>{{ admin_t('Browser') }}</th>
                            <th>{{ admin_t('Visited Page') }}</th>
                            <th>{{ admin_t('Date & Time') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($visitors as $visitor)
                            <tr>
                                <td>{{ $visitor->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-label-dark me-2">{{ $visitor->ip }}</span>
                                        @if($visitor->is_bot)
                                            <span class="badge bg-danger device-badge"><i class="ti ti-robot me-1"></i> Bot</span>
                                        @endif
                                    </div>
                                    @if($visitor->host)
                                        <small class="text-muted d-block mt-1">{{ $visitor->host }}</small>
                                    @endif
                                </td>

                                <td>
                                    @if($visitor->country || $visitor->city)
                                        <div>
                                            <i class="ti ti-map-pin text-primary me-1"></i>
                                            <strong>{{ $visitor->country ?: admin_t('Unknown') }}</strong>
                                            @if($visitor->city)
                                                <small class="text-muted">({{ $visitor->city }})</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted"><i class="ti ti-map-pin-off me-1"></i> {{ admin_t('Local / Unknown') }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($visitor->is_mobile)
                                            <span class="badge bg-label-success device-badge me-2"><i class="ti ti-device-mobile me-1"></i> Phone / Mobile</span>
                                        @elseif($visitor->is_tablet)
                                            <span class="badge bg-label-info device-badge me-2"><i class="ti ti-device-tablet me-1"></i> Tablet</span>
                                        @else
                                            <span class="badge bg-label-primary device-badge me-2"><i class="ti ti-device-laptop me-1"></i> Desktop</span>
                                        @endif

                                        <div>
                                            <span class="fw-medium">{{ $visitor->platform ?: admin_t('Unknown OS') }}</span>
                                            @if($visitor->device && $visitor->device !== 'WebKit')
                                                <small class="text-muted d-block">{{ $visitor->device }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="fw-medium">{{ $visitor->browser ?: admin_t('Unknown') }}</span>
                                    @if($visitor->browser_version)
                                        <small class="text-muted">v{{ $visitor->browser_version }}</small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-label-secondary me-1">{{ $visitor->method ?: 'GET' }}</span>
                                    <span class="path-text" title="{{ $visitor->full_url ?: $visitor->path }}">
                                        {{ $visitor->path ?: '/' }}
                                    </span>
                                </td>

                                <td>
                                    <div><i class="ti ti-clock me-1 text-muted"></i> {{ $visitor->created_at ? $visitor->created_at->diffForHumans() : '-' }}</div>
                                    <small class="text-muted">{{ $visitor->created_at ? $visitor->created_at->format('Y-m-d H:i') : '' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ti ti-chart-bubble fs-1 d-block mb-2 text-secondary"></i>
                                    {{ admin_t('No visitor records found matching your filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    {{ admin_t('Showing') }} {{ $visitors->firstItem() ?? 0 }} - {{ $visitors->lastItem() ?? 0 }} {{ admin_t('of') }} {{ $visitors->total() }} {{ admin_t('visitors') }}
                </small>
                <div>
                    {{ $visitors->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
