<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $language = $request->header('lang', 'ar');
        return [
            'id' => $this->id,
            'name' => $this->name  ?? '',
            'phone1' => $this->phone1,
            'phone2' => $this->phone2,
            'address' => $this->address,
            'link_address' => $this->link_address,
            'region_id' => $this->region_id,
            'region' => new RegionResource($this->whenLoaded('region')), // تضمين بيانات المنطقة
            'vaults' => VaultResource::collection($this->vaults), // تضمين الخزائن
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}