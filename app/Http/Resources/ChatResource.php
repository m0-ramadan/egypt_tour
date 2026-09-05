<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $attributes = [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'admin_id' => $this->admin_id ?? null,
            'representative_id' => $this->representative_id ?? null,
            'message' => $this->message,
            'is_admin' => $this->is_admin,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d'),
        ];

        // Remove any null values from the array
        return array_filter($attributes, function ($value) {
            return $value !== null;
        });
    }
}
