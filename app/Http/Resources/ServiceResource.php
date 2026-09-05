<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Retrieve language from the request header (defaults to 'en' if not provided)
         
        return [
            'id' => $this->id,
            'name' => $this->name, // Fallback to 'en' if language not available
            'image' => asset($this->image),
             'description' => $this->description,
       
        ];
    }
}
