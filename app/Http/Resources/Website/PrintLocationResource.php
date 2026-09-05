<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request)
{
    return [
        'id'               => $this->id,
        'name'             => $this->name,
        'additional_price' => (float) $this->additional_price,
        'formatted_price'  => $this->additional_price > 0 
            ? '+' . number_format($this->additional_price, 2) . ' ج.م' 
            : 'مجانًا',
    ];
}
}
