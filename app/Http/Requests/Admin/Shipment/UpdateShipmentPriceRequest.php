<?php

namespace App\Http\Requests\Admin\Shipment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentPriceRequest extends FormRequest
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
            'id' => 'required|exists:shipment_prices,id',
            'price'=> 'required',
            'currency' => 'required',
            'weight'  => 'required',
            'tax' => 'required',
            'increase' => 'required'
        ];
    }
}
