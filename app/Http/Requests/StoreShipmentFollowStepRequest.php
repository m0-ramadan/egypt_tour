<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentFollowStepRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all users to make this request
    }

    public function rules()
    {
        return [
            'type' => 'required|in:1,2', 
            'shipment_id' => 'required|integer|exists:shipments,id',
            'event_id' => 'nullable|integer',
            'event_name' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}