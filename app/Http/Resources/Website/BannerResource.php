<?php

namespace App\Http\Resources\Website;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'type'          => new BannerTypeResource($this->whenLoaded('bannerType')),
            'order'         => $this->section_order,
            'is_active'     => $this->is_active,

            // Settings
            'slider_settings' => new SliderSettingsResource($this->whenLoaded('sliderSettings')),
            'grid_layout'     => new GridLayoutResource($this->whenLoaded('gridLayout')),

            // Items
            'items' => BannerItemResource::collection($this->whenLoaded('items')),

            'dates' => [
                'start' => $this->start_date,
                'end'   => $this->end_date,
            ]
        ];
    }
}