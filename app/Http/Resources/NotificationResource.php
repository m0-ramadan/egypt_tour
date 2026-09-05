<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'read_at' => $this->read_at,
            'is_active' => $this->is_active,
             'created_at' => $this->created_at->format('Y-m-d H:m'),
        ];
    }
}
