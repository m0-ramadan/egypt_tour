<?php

// app/Http/Resources/ProductImageResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'image' => 'https://oya-ly.com/'.$this->image,
            'product_id' => $this->product_id,
        ];
    }
}
