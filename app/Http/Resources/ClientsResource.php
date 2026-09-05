<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'region_id' => $this->region_id,
            'region' => new RegionResource($this->whenLoaded('region')), // Optional nested region
            'country_id' => $this->country_id,
            'country' => new CountryResource($this->whenLoaded('country')), // Optional nested country
            'phone' => $this->phone,
            'phone2' => $this->phone2,
            'address' => $this->address,
            'status' => $this->status,
            'email' => $this->email,
            'sender_id' => $this->sender_id,
            //    'sender' => new UserResource($this->whenLoaded('user')), // Optional nested sender
            'locked' => $this->locked,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'birth_date' => $this->birth_date,
        ];
    }
}