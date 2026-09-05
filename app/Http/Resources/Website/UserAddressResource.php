<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->first_name.' '.$this->last_name,
            'phone' => $this->phone,
            'label' => $this->label,
            'building' => $this->building,
            'floor' => $this->floor,
            'apartment_number' => $this->apartment_number,
            'details' => $this->address_details,
            'city' => $this->city,
            'area' => $this->area,
            'type' => $this->type,
        ];
    }
}
