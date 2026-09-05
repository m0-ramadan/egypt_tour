<?php
// app/Http/Requests/OrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_per_unit' => 'required|numeric|min:0',

            // Order details
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,wallet',
            'status' => 'required|in:pending,processing,delivered,cancelled',
            'notes' => 'nullable|string',

            // Optional fields
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',

            // Like4App specific
            'provider' => 'nullable|in:internal,like4app',
        ];
    }

    public function messages()
    {
        return [
            'customer_name.required' => 'اسم العميل مطلوب',
            'customer_email.required' => 'البريد الإلكتروني مطلوب',
            'customer_email.email' => 'البريد الإلكتروني غير صحيح',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
            'items.min' => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'معرف المنتج مطلوب',
            'items.*.product_id.exists' => 'المنتج غير موجود',
            'items.*.quantity.required' => 'الكمية مطلوبة',
            'items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل',
            'items.*.price_per_unit.required' => 'السعر للوحدة مطلوب',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'status.required' => 'حالة الطلب مطلوبة',
        ];
    }
}
