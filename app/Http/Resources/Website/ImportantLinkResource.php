<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportantLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'key'         => $this->key,
            'name'        => $this->name,
            'description' => $this->description,
            'url'         => $this->url,
        ];
    }
}