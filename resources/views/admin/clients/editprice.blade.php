@include('admin.i18n.locale')
@extends('admin.layout.master')
@section('title', admin_t('اسعار شحن الفروع'))
@section('css')

@endsection

@section('content')

    <div class="card">
        <div class="card-header pb-0">
            <h5>اسعار الفروع</h5>
        </div>
        <div class="card-body">

            <form class="form theme-form" action="{{ route('admin.branch.updateprice') }}" method="post"
                enctype="multipart/form-data">
                @csrf

                @method ('PUT')
                <div class="row">

                    <input type="hidden" name="id" value="{{ $pricing->id }}">



                    <div class="col-md-3">
                        <label class="mr-sm-2" for="validationCustom02" style="font-family: 'Cairo', sans-serif;">من
                            فرع</label>
                        <div class="input-group mb-3 ">

                            <select class="form-select" aria-label="Default select example" id="country-select"
                                name="branchOne">
                                <option selected>حدد من فرع</option>
                                @foreach ($branchs as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', $branch->branchss_id_1 ?? '') == $pricing->branch_id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="mr-sm-2" for="validationCustom02" style="font-family: 'Cairo', sans-serif;">الى
                            مدينة</label>
                        <div class="input-group mb-3 ">
                            <select class="form-select" aria-label="Default select example" id="country-select"
                                name="city_id">
                                <option selected>حدد الى المدينة</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}"
                                        {{ old('city_id', $region->id ?? '') == $pricing->city_id ? 'selected' : '' }}>
                                        {{ $region->region_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="mr-sm-2" for="validationCustom02"
                            style="font-family: 'Cairo',sans-serif;">السعر</label>
                        <input class="form-control @error('image') is-invalid fparsley-error parsley-error @enderror"
                            name="price" id="exampleInputPassword2" type="text" placeholder="السعر"
                            value="{{ old('price', $pricing->price) }}">
                        @error('region_ar')
                            <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
                                <p>{{ $message }}</p>
                            </span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="currency" style="font-family: 'Cairo', sans-serif;">العملة</label>
                        <select class="form-select" name="currency" id="currency" aria-label="اختر العملة">
                            <option disabled selected>من فضلك حدد عملة الخزينة</option>
                            <option value="LYD" {{ old('currency', $pricing->currency) == 'LYD' ? 'selected' : '' }}>LYD
                            </option>
                            <option value="EGP" {{ old('currency', $pricing->currency) == 'EGP' ? 'selected' : '' }}>EGP
                            </option>
                            <option value="USD" {{ old('currency', $pricing->currency) == 'USD' ? 'selected' : '' }}>$
                                (USD)</option>
                        </select>
                    </div>



                </div>



                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">حفظ الاعدادت </button>
                    <a class="btn btn-light" href="{{ URL::previous() }}">إلغاء </a>
                </div>
        </div>

        </form>
    </div>
    </div>
@endsection
