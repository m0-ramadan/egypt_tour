@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Admins'))

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
        $totalAdmins = $admins->total() ?? $admins->count();
        $activeAdmins = \App\Models\Admin::where('is_active', true)->count();
        $inactiveAdmins = \App\Models\Admin::where('is_active', false)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Admins</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalAdmins) }}</div>
                    <div class="stats-label">Total Admins</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">{{ number_format($activeAdmins) }}</div>
                    <div class="stats-label">Active</div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(253,126,20,.2); color:#fd7e14; border:1px solid rgba(253,126,20,.3);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stats-number">{{ number_format($inactiveAdmins) }}</div>
                    <div class="stats-label">Inactive</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.admins.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="Search By name Or Email Electronic">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">Filter</button>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Admins List</h5>
                    <small class="opacity-75">Manage dashboard admin accounts</small>
                </div>
                <a href="{{ route('admin.admins.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Add Admin
                </a>
            </div>

            <div class="p-4">
                @forelse($admins as $admin)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $admin->name ?? 'No Name' }}</h6>
                                <small class="text-light opacity-75">{{ $admin->email ?? '-' }}</small>
                            </div>

                            <span
                                class="badge-status {{ $admin->is_active ?? true ? 'status-active' : 'status-inactive' }}">
                                {{ $admin->is_active ?? true ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"><strong>Phone:</strong> {{ $admin->phone ?? '-' }}</div>
                            <div class="col-md-4"><strong>Role:</strong> {{ $admin->role->name ?? '-' }}</div>
                            <div class="col-md-4"><strong>Created At:</strong>
                                {{ optional($admin->created_at)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.admins.show', $admin) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-warning btn-sm">Edit</a>

                            @if (Route::has('admin.admins.toggle-status'))
                                <form action="{{ route('admin.admins.toggle-status', $admin) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-dark btn-sm" type="submit">Toggle Status</button>
                                </form>
                            @endif

                            @if (Route::has('admin.admins.reset-password'))
                                <form action="{{ route('admin.admins.reset-password', $admin) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-primary btn-sm" type="submit">Reset Password</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">No admins available</div>
                @endforelse

                @if (method_exists($admins, 'links'))
                    <div class="mt-4">{{ $admins->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
