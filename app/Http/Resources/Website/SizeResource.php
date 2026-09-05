<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SizeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'tiers' => SizeTierResource::collection(
    $this->productTiers()->where('product_id', $this->product_id)->get()),

        ];
    }
}