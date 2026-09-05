<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryNative extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $language = $request->header('lang', 'ar');
        return [
            'id' => $this->id,
            'country' => $language == 'en' ? $this->country : $this->country_ar,
          //  'country_ar' => $this->country_ar,

        ];
    }
}