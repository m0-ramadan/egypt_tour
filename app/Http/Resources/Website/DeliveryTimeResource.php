<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryTimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'from' => $this->from_days,
            'to'   => $this->to_days,
            'text' => "من {$this->from_days} إلى {$this->to_days} أيام",
        ];
    }
}