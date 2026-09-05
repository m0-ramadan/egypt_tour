<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'url'       => get_user_image($this->url),
            'alt'       => $this->alt,
            'type'      => $this->type,
            'order'     => $this->order,
            'active'    => $this->is_active,
        ];
    }
}