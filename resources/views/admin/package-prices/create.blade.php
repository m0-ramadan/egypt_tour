@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('إضافة سعر باقة'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .form-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: 10px;
        }

        .form-control,
        .form-select,
        textarea {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.package-prices.index') }}">أسعار الباقات</a></li>
                <li class="breadcrumb-item active">إضافة سعر</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">إضافة سعر جديد</h5>
                    <small class="opacity-75">إدخال بيانات سعر الباقة</small>
                </div>
                <a href="{{ route('admin.package-prices.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="form-body">
                <form action="{{ route('admin.package-prices.store') }}" method="POST">
                    @csrf

                    <div class="section-title">بيانات السعر</div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">الباقة</label>
                            <select name="package_id" class="form-select">
                                <option value="">اختر الباقة</option>
                                @foreach ($packages ?? collect() as $package)
                                    <option value="{{ $package->id }}"
                                        {{ old('package_id', request('package_id')) == $package->id ? 'selected' : '' }}>
                                        {{ adminTrans($package->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">العملة</label>
                            <select name="currency_id" class="form-select">
                                <option value="">اختر العملة</option>
                                @foreach ($currencies ?? collect() as $currency)
                                    <option value="{{ $currency->id }}"
                                        {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم العرض (Label)</label>
                            <input type="text" name="label" class="form-control" value="{{ old('label') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم الموسم</label>
                            <input type="text" name="season_name" class="form-control" value="{{ old('season_name') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">نوع السعر</label>
                            <input type="text" name="price_type" class="form-control" value="{{ old('price_type') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">نوع الغرفة</label>
                            <input type="text" name="room_type" class="form-control" value="{{ old('room_type') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">السعر</label>
                            <input type="number" step="0.01" name="amount" class="form-control"
                                value="{{ old('amount') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">أقل عدد أفراد</label>
                            <input type="number" name="pax_min" class="form-control" value="{{ old('pax_min') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">أقصى عدد أفراد</label>
                            <input type="number" name="pax_max" class="form-control" value="{{ old('pax_max') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">أقل عدد للمجموعة</label>
                            <input type="number" name="group_size_min" class="form-control"
                                value="{{ old('group_size_min') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">أقصى عدد للمجموعة</label>
                            <input type="number" name="group_size_max" class="form-control"
                                value="{{ old('group_size_max') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ البداية</label>
                            <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ النهاية</label>
                            <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">حفظ</button>
                        <a href="{{ route('admin.package-prices.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
