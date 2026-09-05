<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleCommentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'article_id' => 'required|exists:articles,id',
            'content'    => 'required|string|min:3|max:1000',
            'parent_id'  => 'nullable|exists:article_comments,id',
        ];

        if (!auth()->check()) {
            $rules['name']  = 'required|string|max:255';
            $rules['email'] = 'required|email|max:255';
        }

        return $rules;
    }

    /**
     * ✅ رسائل التحقق العربية
     */
    public function messages()
    {
        return [
            'article_id.required' => 'المقال مطلوب',
            'article_id.exists'   => 'المقال غير موجود',

            'content.required' => 'التعليق مطلوب',
            'content.string'   => 'التعليق يجب أن يكون نصًا',
            'content.min'      => 'التعليق يجب ألا يقل عن 3 أحرف',
            'content.max'      => 'التعليق يجب ألا يزيد عن 1000 حرف',

            'parent_id.exists' => 'التعليق الأب غير موجود',

            'name.required' => 'الاسم مطلوب',
            'name.string'   => 'الاسم يجب أن يكون نصًا',
            'name.max'      => 'الاسم يجب ألا يزيد عن 255 حرفًا',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.max'      => 'البريد الإلكتروني يجب ألا يزيد عن 255 حرفًا',
        ];
    }

    /**
     * (اختياري) تعريب أسماء الحقول
     */
    public function attributes()
    {
        return [
            'article_id' => 'المقال',
            'content'    => 'التعليق',
            'parent_id'  => 'التعليق الأب',
            'name'       => 'الاسم',
            'email'      => 'البريد الإلكتروني',
        ];
    }
}
