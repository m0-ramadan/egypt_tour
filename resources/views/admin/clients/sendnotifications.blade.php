@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Send Notification to Agents'))

@section('css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/toastr.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header pb-0">
            <h5>
                Send a notification to all
                {{ request('t') == 1 ? 't\' : 'Merchants' }}
            </h5>

        </div>
        <div class="card-body">
            <form class="form theme-form" action="{{ route('admin.client.sendnotification', request('t')) }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="row g-3 mb-3">
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
