@include('admin.i18n.locale')
@extends('admin.auth.layouts.master')

@section('title', admin_t('إعادة تعيين كلمة المرور'))

@section('content')
    <form class="theme-form login-form" action="{{ route('admin.password.update') }}" method="post">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <h4>إعادة تعيين كلمة المرور</h4>
        <h6>قم بإدخال كلمة المرور الجديدة.</h6>

        <div class="form-group">
            <label>كلمة المرور الجديدة</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-lock"></i></span>
                <input class="form-control" type="password" name="password" required placeholder="*********">
            </div>
        </div>

        <div class="form-group">
            <label>تأكيد كلمة المرور</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-lock"></i></span>
                <input class="form-control" type="password" name="password_confirmation" required placeholder="*********">
            </div>
        </div>

        <div class="form-group">
            <button class="btn btn-primary btn-block" type="submit">إعادة التعيين</button>
        </div>

        <div class="form-group">
            <a href="{{ route('admin.login') }}" class="text-sm text-primary">العودة لتسجيل الدخول</a>
        </div>
    </form>
@endsection
