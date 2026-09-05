@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة رابط تواصل'))

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.social-media.index') }}">وسائل التواصل</a>
                </li>
                <li class="breadcrumb-item active">إضافة جديد</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">إضافة رابط تواصل جديد</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.social-media.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم المنصة</label>
                            <input type="text" name="platform" class="form-control" value="{{ old('platform') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الرابط</label>
                            <input type="text" name="url" class="form-control" value="{{ old('url') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الأيقونة</label>
                            <input type="text" name="icon" class="form-control" placeholder="fa fa-facebook">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label d-block">الحالة</label>
                            <input type="checkbox" name="is_active" value="1" checked>
                            مفعل
                        </div>

                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary">حفظ</button>
                        <a href="{{ route('admin.social-media.index') }}" class="btn btn-secondary">رجوع</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection
