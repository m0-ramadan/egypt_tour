@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Add Social Link'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.social-media.index') }}">Social Media</a>
                </li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New Link Communication</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.social-media.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Platform Name</label>
                            <input type="text" name="platform" class="form-control" value="{{ old('platform') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link</label>
                            <input type="text" name="url" class="form-control" value="{{ old('url') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon</label>
                            <input type="text" name="icon" class="form-control" placeholder="fa fa-facebook">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">Status</label>
                            <input type="checkbox" name="is_active" value="1" checked>
                            Enabled
                        </div>

                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary">Save</button>
                        <a href="{{ route('admin.social-media.index') }}" class="btn btn-secondary">Back</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection
