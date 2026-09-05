<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'items_count'=> $this->items->sum('quantity'),
            'subtotal'   => $this->subtotal,
            'total'      => $this->total,
            'items'      => CartItemResource::collection($this->items),
        ];
    }
}