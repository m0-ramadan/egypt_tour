@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Regions'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .main-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
            border-top: 4px solid var(--primary-color);
            transition: transform .3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .filter-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            transition: all .3s ease;
        }

        .item-card:hover {
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
            transform: translateX(-5px);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-active {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-inactive {
            background: rgba(253, 126, 20, .2);
            color: #fd7e14;
            border: 1px solid rgba(253, 126, 20, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $totalRegions = $regions->total() ?? $regions->count();
        $activeRegions = \App\Models\Destination::where('is_active', true)->count();
        $inactiveRegions = \App\Models\Destination::where('is_active', false)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Regions</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-map"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalRegions) }}</div>
                    <div class="stats-label">Total Regions</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activeRegions) }}</div>
                    <div class="stats-label">Enabled</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stats-number">{{ number_format($inactiveRegions) }}</div>
                    <div class="stats-label">Inactive</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.regions.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                            placeholder="Search Named Region">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <select name="country_id" class="form-select">
                            <option value="">All Countries</option>
                            @foreach ($countries ?? collect() as $country)
                                <option value="{{ $country->id }}"
                                    {{ request('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Enabled</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Disabled
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">Filter</button>
                        <a href="{{ route('admin.regions.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Regions List</h5>
                    <small class="opacity-75">Manage Regions And Connect it With Countries</small>
                </div>
                <a href="{{ route('admin.regions.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Add Region
                </a>
            </div>

            <div class="p-4">
                @forelse($regions as $region)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $region->name ?? 'No Name' }}</h6>
                                <small class="text-light opacity-75">{{ $region->slug ?? '-' }}</small>
                            </div>

                            <span
                                class="badge-status {{ $region->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                {{ $region->is_active ?? true ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"><strong>Country:</strong> {{ $region->country->name ?? '-' }}</div>
                            <div class="col-md-4"><strong>Order:</strong> {{ $region->sort_order ?? 0 }}</div>
                            <div class="col-md-4"><strong>Created At:</strong>
                                {{ optional($region->created_at)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <strong>Description:</strong> {{ \Illuminate\Support\Str::limit($region->description ?? '-', 180) }}
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.regions.show', $region) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('admin.regions.edit', $region) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.regions.destroy', $region) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">No Regions available</div>
                @endforelse

                @if (method_exists($regions, 'links'))
                    <div class="mt-4">{{ $regions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
