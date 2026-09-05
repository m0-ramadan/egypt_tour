<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeTierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'quantity'       => $this->quantity,
            'price_per_unit' => $this->price_per_unit,
        ];
    }
}
