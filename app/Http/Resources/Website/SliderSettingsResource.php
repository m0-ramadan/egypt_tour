<?php

namespace App\Http\Resources\Website;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'autoplay'         => $this->autoplay,
            'autoplay_delay'   => $this->autoplay_delay,
            'loop'             => $this->loop,
            'show_navigation'  => $this->show_navigation,
            'show_pagination'  => $this->show_pagination,
            'slides_per_view'  => $this->slides_per_view,
            'space_between'    => $this->space_between,
            'breakpoints'      => $this->breakpoints
                ? json_decode($this->breakpoints, true)
                : null,
        ];
    }
}