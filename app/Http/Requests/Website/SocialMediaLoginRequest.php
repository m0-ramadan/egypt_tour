<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class SocialMediaLoginRequest extends FormRequest
{
    /**
     * السماح لكل المستخدمين بإرسال الطلب
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
            'provider'    => 'required|in:google,facebook,apple',
            'provider_id' => 'required|string',
            'email'       => 'nullable|email',
            'name'        => 'nullable|string|max:255',
            'image'       => 'nullable|string',
        ];
    }


    public function messages(): array
    {
        return [
            'provider.required'    => 'مزود الدخول مطلوب.',
            'provider.in'          => 'مزود الدخول يجب أن يكون google أو facebook أو apple.',
            'provider_id.required' => 'معرف المزود مطلوب.',
            'provider_id.string'   => 'معرف المزود يجب أن يكون نصًا.',
            'email.email'          => 'البريد الإلكتروني غير صالح.',
            'name.string'          => 'الاسم يجب أن يكون نصًا.',
            'name.max'             => 'الاسم لا يمكن أن يزيد عن 255 حرفًا.',
            'image.string'         => 'رابط الصورة يجب أن يكون نصًا.',
        ];
    }
}
