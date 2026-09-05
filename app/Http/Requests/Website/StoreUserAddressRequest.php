<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true; // لأن المستخدم Auth
    }

    public function rules()
    {
        return [
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'nullable|string|max:255',
            'building'         => 'nullable|string|max:255',
            'floor'            => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',
            'address_details'  => 'nullable|string',
            'label'            => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'city'             => 'nullable|string|max:255',
            'area'             => 'nullable|string|max:255',
            'type'             => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'first_name.string'   => 'الاسم الأول يجب أن يكون نصًا.',
            'first_name.max'      => 'الاسم الأول لا يجب أن يتجاوز 255 حرفًا.',

            'last_name.string'    => 'الاسم الأخير يجب أن يكون نصًا.',
            'last_name.max'       => 'الاسم الأخير لا يجب أن يتجاوز 255 حرفًا.',

            'building.string'     => 'اسم المبنى يجب أن يكون نصًا.',
            'building.max'        => 'اسم المبنى لا يجب أن يتجاوز 255 حرفًا.',

            'floor.string'        => 'رقم الطابق يجب أن يكون نصًا.',
            'floor.max'           => 'رقم الطابق لا يجب أن يتجاوز 255 حرفًا.',

            'apartment_number.string' => 'رقم الشقة يجب أن يكون نصًا.',
            'apartment_number.max'    => 'رقم الشقة لا يجب أن يتجاوز 255 حرفًا.',

            'address_details.string'  => 'تفاصيل العنوان يجب أن تكون نصًا.',

            'label.string'        => 'التسمية يجب أن تكون نصًا.',
            'label.max'           => 'التسمية لا يجب أن تتجاوز 255 حرفًا.',

            'phone.required'      => 'رقم الهاتف مطلوب.',
            'phone.string'        => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max'           => 'رقم الهاتف لا يجب أن يتجاوز 20 رقمًا.',

            'city.required'       => 'المدينة مطلوبة.',
            'city.string'         => 'المدينة يجب أن تكون نصًا.',
            'city.max'            => 'اسم المدينة لا يجب أن يتجاوز 255 حرفًا.',

            'area.required'       => 'المنطقة مطلوبة.',
            'area.string'         => 'اسم المنطقة يجب أن يكون نصًا.',
            'area.max'            => 'اسم المنطقة لا يجب أن يتجاوز 255 حرفًا.',

            'type.string'         => 'النوع يجب أن يكون نصًا.',
            'type.max'            => 'النوع لا يجب أن يتجاوز 255 حرفًا.',

            'latitude.numeric'    => 'خط العرض يجب أن يكون رقمًا.',
            'longitude.numeric'   => 'خط الطول يجب أن يكون رقمًا.',
        ];
    }
}
