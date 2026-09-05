<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        
         $regionsWithBranches = $this->regions->filter(fn($region) => $region->branches()->exists());
        
        $language = $request->header('lang', 'ar');
        return [
            'id' => $this->id,
            'country' => $language == 'en' ? $this->country : $this->country_ar,
            'status' => $this->status,
            'item_order' => $this->item_order,
            'branch' => BranchResource::collection($this->branchs), // Include regions if loaded
            'region' => RegionResource::collection($this->region),
            'cities' => CitiesResource::collection($regionsWithBranches),

        ];
    }
}