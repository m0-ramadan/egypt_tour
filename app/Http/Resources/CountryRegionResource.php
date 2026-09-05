<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryRegionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $language = $request->header('lang', 'ar');

        return [
            'id' => $this->id,
            'country' => $language == 'en' ? $this->country : $this->country_ar,
            'country_ar' => $this->country_ar,
            'status' => $this->status,
            'item_order' => $this->item_order,
            'regions' => RegionResource::collection($this->region),
        ];
    }
}