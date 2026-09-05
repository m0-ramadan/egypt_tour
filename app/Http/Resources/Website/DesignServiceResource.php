<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request)
{
    return [
        'id'          => $this->id,
        'name'        => $this->name,
        'price'       => (float) $this->price,
        'description' => $this->description,
        'is_free'     => $this->price == 0,
        'formatted'   => $this->price == 0 ? 'مجانًا' : number_format($this->price, 2) . ' ج.م',
    ];
}
}
