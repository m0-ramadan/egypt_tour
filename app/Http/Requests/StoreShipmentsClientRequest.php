<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentsClientRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name_received' => 'nullable|string|max:255',
            'phone_received' => 'nullable|string|max:20',
            'phone_received_2' => 'nullable|string|max:20',
            'country_received_id' => 'nullable|exists:countries,id',
            'country_region_id' => 'nullable|exists:regions,id',
            'address_received' => 'nullable|string',
            'type_shipments_id' => 'nullable|exists:services,id',
            'describe_shipments' => 'nullable|string',
            'price' => 'nullable|numeric',
            'assembly_commission' => 'nullable|numeric',
            'all_paper_coin' => 'nullable|numeric',
            'cost_shipment_by' => 'nullable|numeric',
            'additional_shipping_cost' => 'nullable|numeric',
            'collection_commission' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'person_id' => 'nullable|numeric', // ID of the related model
            'person_type' => 'nullable|string', // Class name of the related model
        ];
    }
}