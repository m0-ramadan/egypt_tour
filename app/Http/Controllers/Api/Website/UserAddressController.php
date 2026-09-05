<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\UserAddress;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Website\UserAddressResource;
use App\Http\Requests\Website\StoreUserAddressRequest;
use App\Http\Requests\Website\UpdateUserAddressRequest;


class UserAddressController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::id())->get();
        return $this->success(UserAddressResource::collection($addresses), 'تم جلب العناوين بنجاح');
    }

    public function show($id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);
        return $address
            ? $this->success(new UserAddressResource($address), 'تم جلب العنوان بنجاح')
            : $this->error('العنوان غير موجود', 404);
    }

    public function store(StoreUserAddressRequest $request)
    {
        $address = UserAddress::create($request->validated() + ['user_id' => Auth::id()]);
        return $this->success(new UserAddressResource($address), 'تم إضافة العنوان بنجاح', 201);
    }

    public function update(UpdateUserAddressRequest $request, $id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);

        if (!$address) return $this->error('العنوان غير موجود', 404);

        $address->update($request->validated());
        return $this->success(new UserAddressResource($address), 'تم تحديث العنوان بنجاح');
    }

    public function destroy($id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);
        if (!$address) return $this->error('العنوان غير موجود', 404);

        $address->delete();
        return $this->success(null, 'تم حذف العنوان بنجاح');
    }
}