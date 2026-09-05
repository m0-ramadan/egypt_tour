<?php
// app/Http/Resources/ProductReviewResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'review' => $this->review,
            'rating' => $this->rating,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
        ];
    }
}
