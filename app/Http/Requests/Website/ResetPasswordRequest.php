<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'    => 'رمز إعادة التعيين مطلوب',
            'email.required'    => 'البريد الإلكتروني مطلوب',
            'email.exists'      => 'لا يوجد حساب بهذا البريد الإلكتروني',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed'=> 'تأكيد كلمة المرور غير متطابق',
        ];
    }
}