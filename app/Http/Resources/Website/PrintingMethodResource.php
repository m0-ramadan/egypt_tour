<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintingMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request)
{
    return [
        'id'           => $this->id,
        'name'         => $this->name,
        'description'  => $this->description,
        'base_price'   => (float) $this->base_price,
        'formatted_price' => number_format($this->base_price, 2) . ' ج.م',
    ];
}
}
