<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * تجهيز البيانات قبل الفاليديشن
     */


    public function rules(): array
    {
        return [
            'customer_name'     => 'nullable|string|max:100',

            'customer_phone'    => ['nullable','string',],

            'customer_email'    => 'nullable|email',

  'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',


            // وسائل الدفع المتاحة
            'payment_method'    => 'required|exists:payment_methods,id',

            'notes'             => 'nullable|string|max:1000',

            'coupon_code'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in'    => 'طريقة الدفع غير صحيحة',
        ];
    }
}
