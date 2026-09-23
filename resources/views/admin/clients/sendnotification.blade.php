@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Send Notification to Customer'))

@section('css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/toastr.min.css') }}">
    <style>
        label {
            font-family: 'Cairo', sans-serif;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            font-family: 'Cairo', sans-serif;
            color: #dc3545;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header pb-0">
            <h5>Send Notification To {{ $client->name }}</h5>
        </div>
        <div class="card-body">
            <form class="form theme-form" action="{{ route('admin.client.sendnotification.single', $client->id) }}"
                method="post">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="mr-sm-2" for="client_id"
                                    style="font-family: 'Cairo', sans-serif;">{{ $client->type == 1 ? 'type' : 'Merchant' }}</label>
                                <input type="text" class="form-control"
                                    value="{{ $client->name }} ({{ $client->phone }})" readonly>
                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="mr-sm-2" for="title" style="font-family: 'Cairo', sans-serif;">SEO Title
                                    Notification</label>
                                <input class="form-control @error('title') is-invalid @enderror" name="title"
                                    id="title" type="text" placeholder="Notification Title" value="{{ old('title') }}">
                                @error('title')
                                    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2"
                                        role="alert">
                                        <p>{{ $message }}</p>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="mr-sm-2" for="content" style="font-family: 'Cairo', sans-serif;">Notification Content</label>
                                <textarea class="form-control @error('content') is-invalid @enderror" name="content" id="content" rows="4"
                                    placeholder="Notification Content">{{ old('content') }}</textarea>
                                @error('content')
                                    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2"
                                        role="alert">
                                        <p>{{ $message }}</p>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">Send Notification</button>
                    <a class="btn btn-light" href="{{ route('admin.client.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('admin/assets/js/tooltip-init.js') }}"></script>
    <script src="{{ asset('admin/assets/js/toastr.min.js') }}"></script>
    <script>
        @if (session('success'))
            toastr.success("{{ session('success') }}", "Success");
        @endif
        @if (session('error'))
            toastr.error("{{ session('error') }}", "Error");
        @endif
        @if (session('warning'))
            toastr.warning("{{ session('warning') }}", "Warning");
        @endif
    </script>
@endsection
