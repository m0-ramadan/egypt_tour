<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTypeResource extends JsonResource
{
    public function toArray($request)
    {
         $language = $request->header('lang', 'ar');
        return [
            'id' => $this->id,
            'name' => json_decode($this->name)->$language,
        ];
    }
}
