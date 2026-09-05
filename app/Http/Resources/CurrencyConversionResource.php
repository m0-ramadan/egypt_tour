<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyConversionResource extends JsonResource
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
            'from_currency' =>(int) $this->from_currency,
            'to_currency' => (int) $this->to_currency,
            'conversion_value' => (float) $this->conversion_rate,

        ];
    }
}