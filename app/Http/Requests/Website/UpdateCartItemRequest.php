<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

  public function rules(): array
{
    return [
        'quantity'             => 'nullable|integer|min:1',
        'is_sample'            => 'sometimes|boolean',
    ];
}
}