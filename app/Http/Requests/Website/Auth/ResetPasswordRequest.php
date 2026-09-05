<?php

namespace App\Http\Requests\Website\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'exists:users,email'],
            'otp'      => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'البريد الإلكتروني مطلوب',
            'otp.required'      => 'رمز OTP مطلوب',
            'otp.digits'        => 'رمز OTP يجب أن يكون 6 أرقام',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed'=> 'تأكيد كلمة المرور غير متطابق',
        ];
    }
}
