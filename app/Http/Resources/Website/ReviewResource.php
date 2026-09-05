<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_verified' => (bool) $this->is_verified, // إذا كان لديك هذا الحقل
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
            'human_created_at' => $this->created_at->diffForHumans(),

            // العلاقات
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'image' => $this->product->image,
                    'price' => $this->product->price,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'avatar' => $this->user->avatar,
                    'is_verified' => (bool) $this->user->is_verified,
                ];
            }),

            // إضافات للواجهة
            'stars' => $this->getStarsArray(),
        ];
    }

    /**
     * الحصول على مصفوفة النجوم للعرض
     */
    private function getStarsArray(): array
    {
        $stars = [];
        $rating = $this->rating;

        for ($i = 1; $i <= 5; $i++) {
            $stars[] = [
                'value' => $i,
                'filled' => $i <= $rating,
                'half' => $i == ceil($rating) && $rating != floor($rating),
            ];
        }

        return $stars;
    }
}
