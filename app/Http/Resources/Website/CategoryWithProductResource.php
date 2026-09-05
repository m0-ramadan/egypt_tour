<?php


namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWithProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'parent'      => $this->whenLoaded('parent', fn() => new self($this->parent)),
            'children'    => self::collection(CategoryResource::collection($this->children)),
            'order'       => $this->order,
            'image'       => $this->image ? get_user_image($this->image) : null,
            'sub_image' => $this->sub_image,
            'is_parent'   => $this->isParent(),
            'products'    => ProductResource::collection($this->products),
            'category_banners' => $this->categoryBanners ? BannerItemResource::collection($this->categoryBanners) : null,
        ];
    }
}
