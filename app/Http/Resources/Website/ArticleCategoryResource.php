<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'articles_count' => $this->when(isset($this->articles_count), $this->articles_count),
            // 'parent' => $this->whenLoaded('parent', fn() => new self($this->parent)),
            // 'children' => $this->whenLoaded('children', fn() => self::collection($this->children)),
            'articles' => ArticleResource::collection($this->activeArticles->take(5))
        ];
    }
}
