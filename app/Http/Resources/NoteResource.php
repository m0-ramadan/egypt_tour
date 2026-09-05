<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
public function toArray($request)
{
    $locale = $request->header('lang', 'ar');
    return [
        'id' => $this->id,
        'name' => $this->text,
    ];
}

}