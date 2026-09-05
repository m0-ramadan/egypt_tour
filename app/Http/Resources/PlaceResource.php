<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'place_type_id' => $this->place_type_id,
            'phone' => $this->phone,
            'address' => $this->address,
            'images' => $this->images , 
        ];
    }
}
