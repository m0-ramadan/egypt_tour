@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', 'Attractions / Landmarks Management')

@section('css')
    <style>
        .admin-card-dark,
        .card,
        .card.bg-dark {
            background-color: #2b3b4c !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
        }

        .table,
        .table th,
        .table td,
        .table tr {
            background-color: #2b3b4c !important;
            color: #ffffff !important;
        }

        .table th {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 700 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
        }

        .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
    </style>
@endsection

@section('content')
    @php
        $totalAttractions = $attractions->total() ?? $attractions->count();
        $activeAttractions = \App\Models\Attraction::where('is_active', true)->count();
        $inactiveAttractions = \App\Models\Attraction::where('is_active', false)->count();
        $featuredAttractions = \App\Models\Attraction::where('is_featured', true)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-white fw-bold"><i class="fas fa-landmark me-2 text-primary"></i>Attractions Management</h4>
            <a href="{{ route('admin.attractions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Attraction
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-dark text-white border-secondary p-3">
                    <div class="fs-4 fw-bold text-white">{{ number_format($totalAttractions) }}</div>
                    <div class="text-light opacity-75">Total Attractions</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-dark text-white border-secondary p-3">
                    <div class="fs-4 fw-bold text-success">{{ number_format($activeAttractions) }}</div>
                    <div class="text-light opacity-75">Enabled</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-dark text-white border-secondary p-3">
                    <div class="fs-4 fw-bold text-danger">{{ number_format($inactiveAttractions) }}</div>
                    <div class="text-light opacity-75">Disabled</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-dark text-white border-secondary p-3">
                    <div class="fs-4 fw-bold text-info">{{ number_format($featuredAttractions) }}</div>
                    <div class="text-light opacity-75">Featured</div>
                </div>
            </div>
        </div>

        <div class="card bg-dark text-white border-secondary mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.attractions.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-light">Search</label>
                            <input type="text" name="q" class="form-control bg-dark text-white border-secondary"
                                placeholder="Search by name, slug or description..." value="{{ request('q') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-light">City / Destination</label>
                            <select name="city_id" class="form-select bg-dark text-white border-secondary">
                                <option value="">All Cities</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}"
                                        {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ adminTrans($city->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-light">Status</label>
                            <select name="status" class="form-select bg-dark text-white border-secondary">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Enabled
                                </option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100" type="submit">Filter</button>
                            @if (request()->hasAny(['q', 'city_id', 'status']))
                                <a href="{{ route('admin.attractions.index') }}" class="btn btn-outline-light"><i
                                        class="fas fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card bg-dark text-white border-secondary">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">Attractions List</h5>
                <small class="text-light opacity-75">Showing {{ $attractions->firstItem() ?? 0 }} -
                    {{ $attractions->lastItem() ?? 0 }} of {{ $attractions->total() }}</small>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Image</th>
                            <th>Attraction Name</th>
                            <th>City</th>
                            <th>Slug</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attractions as $attraction)
                            <tr>
                                <td>
                                    @if ($attraction->image)
                                        <img src="{{ asset('storage/' . $attraction->image) }}" class="rounded"
                                            style="width: 50px; height: 50px; object-fit: cover;" alt="attraction">
                                    @else
                                        <div class="rounded bg-secondary d-flex align-items-center justify-content-center text-white-50"
                                            style="width: 50px; height: 50px;"><i class="fas fa-landmark"></i></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-white">{{ adminTrans($attraction->name) ?: 'Untitled' }}</div>
                                    @if ($attraction->is_featured)
                                        <span class="badge bg-info text-dark">Featured</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge bg-secondary">{{ adminTrans(optional($attraction->city)->name) ?: '-' }}</span>
                                </td>
                                <td><code class="text-info">{{ $attraction->slug }}</code></td>
                                <td>{{ $attraction->sort_order ?? 0 }}</td>
                                <td>
                                    <form action="{{ route('admin.attractions.toggle-status', $attraction) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm {{ $attraction->is_active ? 'btn-success' : 'btn-danger' }}"
                                            title="Click to toggle status">
                                            <i
                                                class="fas {{ $attraction->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                            {{ $attraction->is_active ? 'Enabled' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.attractions.show', $attraction) }}"
                                            class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.attractions.edit', $attraction) }}"
                                            class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.attractions.destroy', $attraction) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this attraction?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" title="Delete"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-landmark fs-1 opacity-25 mb-3 d-block"></i>
                                    <h5 class="text-light">No attractions found</h5>
                                    <a href="{{ route('admin.attractions.create') }}"
                                        class="btn btn-primary btn-sm mt-2">Add First Attraction</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attractions->hasPages())
                <div class="card-footer border-secondary">
                    {{ $attractions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
