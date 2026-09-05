<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'nullable|string|min:6|confirmed',
            'google_id'   => 'nullable|string',
            'facebook_id' => 'nullable|string',
            'apple_id'    => 'nullable|string',
            'image'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'حقل الاسم مطلوب.',
            'name.string'       => 'الاسم يجب أن يكون نصًا.',
            'name.max'          => 'الاسم لا يجب أن يتجاوز 255 حرفًا.',

            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.string'      => 'البريد الإلكتروني يجب أن يكون نصًا.',
            'email.email'       => 'يرجى إدخال بريد إلكتروني صالح.',
            'email.max'         => 'البريد الإلكتروني لا يجب أن يتجاوز 255 حرفًا.',
            'email.unique'      => 'هذا البريد الإلكتروني مستخدم بالفعل.',

            'password.string'   => 'كلمة المرور يجب أن تكون نصًا.',
            'password.min'      => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
            'password.confirmed'=> 'تأكيد كلمة المرور غير متطابق.',

            'google_id.string'  => 'معرّف Google يجب أن يكون نصًا.',
            'facebook_id.string'=> 'معرّف Facebook يجب أن يكون نصًا.',
            'apple_id.string'   => 'معرّف Apple يجب أن يكون نصًا.',

            'image.string'      => 'رابط الصورة يجب أن يكون نصًا.',
        ];
    }
}
