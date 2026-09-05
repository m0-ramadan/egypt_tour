<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;


class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // because user must be authenticated already
    }

    public function rules(): array
    {
        return [
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'يجب إدخال كلمة المرور الحالية',
            'new_password.required' => 'يجب إدخال كلمة المرور الجديدة',
            'new_password.min'      => 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل',
            'new_password.confirmed' => 'تأكيد كلمة المرور لا يتطابق',
        ];
    }
}