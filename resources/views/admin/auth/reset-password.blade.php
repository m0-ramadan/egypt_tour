@include('admin.i18n.locale')
@extends('admin.auth.layouts.master')

@section('title', admin_t('Reset Password'))

@section('content')
    <form class="theme-form login-form" action="{{ route('admin.password.update') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <h4>Reset Password</h4>
        <h6>Do With Enter Word Password New.</h6>

        <div class="form-group">
            <label>Word Password New</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-lock"></i></span>
                <input class="form-control" type="password" name="password" required placeholder="*********">
            </div>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-lock"></i></span>
                <input class="form-control" type="password" name="password_confirmation" required placeholder="*********">
            </div>
        </div>

        <div class="form-group">
            <button class="btn btn-primary btn-block" type="submit">Reset Set</button>
        </div>

        <div class="form-group">
            <a href="{{ route('admin.login') }}" class="text-sm text-primary">Return For Sign In</a>
        </div>
    </form>
@endsection
