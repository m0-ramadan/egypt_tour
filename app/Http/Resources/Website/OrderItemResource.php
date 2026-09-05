<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'quantity'          => $this->quantity,
            'price_per_unit'    => $this->price_per_unit,
            'total_price'       => $this->total_price,
            'formatted_price'   => number_format($this->total_price, 2) . ' ج.م',
            'is_sample'         => $this->is_sample,
            'note'              => $this->note,

            // تفاصيل المنتج
            'product' =>new ProductResource($this->product) ,
          
        ];
    }
}