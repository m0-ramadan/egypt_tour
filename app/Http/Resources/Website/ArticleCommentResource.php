<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,

            // معلومات المستخدم
            'user' => $this->when($this->user_id, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'avatar' => $this->user->avatar ? asset('storage/' . $this->user->avatar) : null,
                    'role' => $this->user->role?->name
                ];
            }, function () {
                return [
                    'name' => $this->name,
                    'email' => $this->email,
                    'avatar' => null,
                    'is_guest' => true
                ];
            }),

            // محتوى التعليق
            'content' => $this->content,
            'content_short' => $this->when(
                $request->routeIs('articles.comments.index'),
                str()->limit($this->content, 100)
            ),

            // معلومات التعليق
            'parent_id' => $this->parent_id,
            'is_approved' => $this->is_approved,
            'created_at' => [
                'human' => $this->created_at->diffForHumans(),
                'datetime' => $this->created_at->format('Y-m-d H:i:s'),
                'date' => $this->created_at->format('Y-m-d'),
                'time' => $this->created_at->format('H:i')
            ],
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // الردود (إذا كانت متاحة)
            'replies' => $this->replies?->map(fn($reply) => new ArticleCommentResource($reply)),

            'replies_count' => $this->replies?->count(),
        ];
    }
}
