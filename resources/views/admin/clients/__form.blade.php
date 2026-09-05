@include('admin.i18n.locale')
@php
use App\Models\Transfer;
use App\Models\Branchs;
use App\Models\Region;
@endphp

@if (session('success'))
<div class="col-md-8 m-2">
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
</div>
@endif

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="name" style="font-family: 'Cairo', sans-serif;">Name</label>
    <input class="form-control @error('name') is-invalid fparsley-error parsley-error @enderror" name="name" id="name"
        type="text" placeholder="Name" value="{{ old('name', isset($transfer) ? $transfer->name : '') }}">
    @error('name')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="email" style="font-family: 'Cairo', sans-serif;">Email</label>
    <input class="form-control @error('email') is-invalid fparsley-error parsley-error @enderror" name="email"
        id="email" type="email" placeholder="Email"
        value="{{ old('email', isset($transfer) ? $transfer->email : '') }}">
    @error('email')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="phone" style="font-family: 'Cairo', sans-serif;">Phone</label>
    <input class="form-control @error('phone') is-invalid fparsley-error parsley-error @enderror" name="phone"
        id="phone" type="text" placeholder="Phone" value="{{ old('phone', isset($transfer) ? $transfer->phone : '') }}">
    @error('phone')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="password" style="font-family: 'Cairo', sans-serif;">Password{{ isset($transfer) ? '
        (Leave blank to keep unchanged)' : '' }}</label>
    <input class="form-control @error('password') is-invalid fparsley-error parsley-error @enderror" name="password"
        id="password" type="password" placeholder="Password" value="">
    @error('password')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="password_confirmation" style="font-family: 'Cairo', sans-serif;">Password
        Confirmation</label>
    <input class="form-control @error('password_confirmation') is-invalid fparsley-error parsley-error @enderror"
        name="password_confirmation" id="password_confirmation" type="password" placeholder="Password Confirmation"
        value="">
    @error('password_confirmation')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="code" style="font-family: 'Cairo', sans-serif;">Code</label>
    <input class="form-control @error('code') is-invalid fparsley-error parsley-error @enderror" name="code" id="code"
        type="text" placeholder="Code (Optional)" value="{{ old('code', isset($transfer) ? $transfer->code : '') }}">
    @error('code')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<input type="hidden" name="type" value="2">

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="parent_id" style="font-family: 'Cairo', sans-serif;">Parent</label>
    <select class="form-control @error('parent_id') is-invalid fparsley-error parsley-error @enderror" name="parent_id"
        id="parent_id">
        <option value="">-- Select Parent --</option>
        @foreach ($parents as $parent)
        <option value="{{ $parent->id }}" @if (old('parent_id', isset($transfer) ? $transfer->parent_id : '') ==
            $parent->id) selected @endif>
            {{ adminTrans($parent->name) }} ({{ $parent->type == 1 ? 'Admin' : 'Employee' }})
        </option>
        @endforeach
    </select>
    @error('parent_id')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="branch_id" style="font-family: 'Cairo', sans-serif;">Branch</label>
    <select class="form-control @error('branch_id') is-invalid fparsley-error parsley-error @enderror" name="branch_id"
        id="branch_id">
        <option value="">-- Select Branch --</option>
        @foreach ($branches as $branch)
        <option value="{{ $branch->id }}" @if (old('branch_id', isset($transfer) ? $transfer->branch_id : '') ==
            $branch->id) selected @endif>
            {{ $branch->name }}
        </option>
        @endforeach
    </select>
    @error('branch_id')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>

<div class="col-md-8 m-2">
    <label class="mr-sm-2" for="city_id" style="font-family: 'Cairo', sans-serif;">City</label>
    <select class="form-control @error('city_id') is-invalid fparsley-error parsley-error @enderror" name="city_id"
        id="city_id">
        <option value="">-- Select City --</option>
        @foreach ($regions as $region)
        <option value="{{ $region->id }}" @if (old('city_id', isset($transfer) ? $transfer->city_id : '') ==
            $region->id) selected @endif>
            {{ $region->region_ar }}
        </option>
        @endforeach
    </select>
    @error('city_id')
    <span class="invalid-feedback text-black font-weight-bold text-capitalize mt-2" role="alert">
        <p>{{ $message }}</p>
    </span>
    @enderror
</div>