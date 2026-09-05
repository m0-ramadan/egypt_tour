<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'icon'      => $this->icon,
            'is_active' => $this->is_active,
            'is_wallet' => $this->is_wallet,
        ];
    }
}