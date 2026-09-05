<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'nullable|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'يرجى إدخال بريد إلكتروني صالح.',

            'password.min'   => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
        ];
    }
}
