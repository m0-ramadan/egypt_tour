<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAddressRequest extends FormRequest
{
    /**
     * السماح لجميع المستخدمين بإرسال الطلب
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من البيانات
     */
    public function rules(): array
    {
        return [
            'first_name'       => 'sometimes|string|max:255',
            'last_name'        => 'sometimes|string|max:255',
            'building'         => 'nullable|string|max:255',
            'floor'            => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',
            'address_details'  => 'nullable|string',
            'label'            => 'nullable|string|max:255',
            'phone'            => 'sometimes|string|max:20',
            'city'             => 'sometimes|string|max:255',
            'area'             => 'sometimes|string|max:255',
            'type'             => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
        ];
    }

    /**
     * رسائل التحقق باللغة العربية
     */
    public function messages(): array
    {
        return [
            'first_name.string'       => 'الاسم الأول يجب أن يكون نصًا.',
            'first_name.max'          => 'الاسم الأول لا يمكن أن يزيد عن 255 حرفًا.',
            'last_name.string'        => 'اسم العائلة يجب أن يكون نصًا.',
            'last_name.max'           => 'اسم العائلة لا يمكن أن يزيد عن 255 حرفًا.',
            'building.string'         => 'المبنى يجب أن يكون نصًا.',
            'building.max'            => 'المبنى لا يمكن أن يزيد عن 255 حرفًا.',
            'floor.string'            => 'الطابق يجب أن يكون نصًا.',
            'floor.max'               => 'الطابق لا يمكن أن يزيد عن 255 حرفًا.',
            'apartment_number.string' => 'رقم الشقة يجب أن يكون نصًا.',
            'apartment_number.max'    => 'رقم الشقة لا يمكن أن يزيد عن 255 حرفًا.',
            'address_details.string'  => 'تفاصيل العنوان يجب أن تكون نصًا.',
            'label.string'            => 'العلامة يجب أن تكون نصًا.',
            'label.max'               => 'العلامة لا يمكن أن تزيد عن 255 حرفًا.',
            'phone.string'            => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max'               => 'رقم الهاتف لا يمكن أن يزيد عن 20 حرفًا.',
            'city.string'             => 'المدينة يجب أن تكون نصًا.',
            'city.max'                => 'المدينة لا يمكن أن تزيد عن 255 حرفًا.',
            'area.string'             => 'المنطقة يجب أن تكون نصًا.',
            'area.max'                => 'المنطقة لا يمكن أن تزيد عن 255 حرفًا.',
            'type.string'             => 'نوع العنوان يجب أن يكون نصًا.',
            'type.max'                => 'نوع العنوان لا يمكن أن يزيد عن 255 حرفًا.',
            'latitude.numeric'        => 'خط العرض يجب أن يكون رقمًا.',
            'longitude.numeric'       => 'خط الطول يجب أن يكون رقمًا.',
        ];
    }
}
