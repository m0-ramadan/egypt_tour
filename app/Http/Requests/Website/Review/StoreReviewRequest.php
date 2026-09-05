<?php

namespace App\Http\Requests\Website\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'يرجى اختيار المنتج',
            'product_id.exists' => 'المنتج المحدد غير موجود',
            'rating.required' => 'يرجى إعطاء تقييم',
            'rating.integer' => 'التقييم يجب أن يكون رقم',
            'rating.min' => 'التقييم يجب أن يكون بين 1 و 5',
            'rating.max' => 'التقييم يجب أن يكون بين 1 و 5',
            'comment.max' => 'التعليق يجب ألا يتجاوز 1000 حرف',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'المنتج',
            'rating' => 'التقييم',
            'comment' => 'التعليق',
        ];
    }
}
