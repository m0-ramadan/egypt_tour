<?php

namespace App\Http\Resources\Website;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'description' => $this->description,
        ];
    }
}