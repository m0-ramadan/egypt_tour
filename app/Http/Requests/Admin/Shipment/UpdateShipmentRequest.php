<?php

namespace App\Http\Requests\Admin\Shipment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "sender_name" =>'required',
             "sender_country" =>'required',
            "sender_address" =>'required',
            "sender_email" =>'required',
            "sender_phone" =>'required',
            "client_name" =>'required',
            "client_address" =>'required',
             "client_country" =>'required',
            "client_phone" =>'required',
            "client_phone2" =>'nullable',
            "size" =>'required',
            "box_number" =>'required',
            "description" =>'required',
            "price" =>'required',
            'image' => 'nullable|image'
        ];
    }
}
