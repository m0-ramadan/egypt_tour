<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactUsReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'contact_us_id' => $this->contact_us_id,
            'message'     => $this->message,
            'sender_type' => $this->sender_type,
            'user'        => $this->whenLoaded('user', function () {
                return [
                    'id'   => $this->user->id,
                    'name' => $this->user->name ?? null,
                ];
            }),
            'created_at'  => optional($this->created_at)->format('Y-m-d H:i'),
        ];
    }
}
