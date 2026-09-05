<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitiesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = $request->header('lang', 'ar');
        return [
            'id' => $this->id,
            'region' => $this->region_ar,
            'item_order' => $this->item_order,
        ];
    }
}