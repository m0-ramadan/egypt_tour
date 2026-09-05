<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = $request->header('lang', 'en');  

        return [
            'id' => $this->id,
            'image' => 'https://oya-ly.com/'.$this->image,
            'title' =>  ($this->title),  // Fetch title based on the requested language
            'desc' =>  ($this->desc),    // Fetch description based on the requested language
        ];
    }
}

