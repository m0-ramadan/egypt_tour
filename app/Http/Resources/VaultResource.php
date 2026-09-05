<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaultResource extends JsonResource
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
            'name' => $this->name,
            'balance' => number_format($this->balance, 2, '.', ''),
            'currency' => $this->currency
        ];
    }
}