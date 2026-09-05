<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'value' => $this->discount_value,
            'type'  => $this->discount_type, // fixed | percentage
        ];
    }
}