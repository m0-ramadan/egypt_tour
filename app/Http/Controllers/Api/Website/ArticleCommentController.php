<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\ArticleComment;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ArticleCommentResource;
use App\Http\Requests\Website\StoreArticleCommentRequest;


class ArticleCommentController extends Controller
{
    use ApiResponseTrait;

    // عرض تعليقات مقالة معينة
    public function index($articleId)
    {
        $comments = ArticleComment::with(['user', 'replies.user'])
            ->where('article_id', $articleId)
            ->whereNull('parent_id')
            ->approved()
            ->latest()
            ->get();

        return $this->success(ArticleCommentResource::collection($comments), 'تم جلب التعليقات بنجاح');
    }

    // إضافة تعليق جديد
    public function store(StoreArticleCommentRequest $request)
    {
        $article = Article::findOrFail($request->article_id);
        $user = auth()->user();
        $comment = ArticleComment::create([
            'article_id' => $article->id,
            'user_id' => $user?->id,
            'parent_id' => $request->parent_id,
            'name' => $request->name ??  ($user ? $user->name : null),
            'email' => $request->email ?? ($user ? $user->email : null),
            'content' => $request->content,
            'is_approved' => auth()->guard('web')->check() // إذا كان مسجل دخول يوافق تلقائياً
        ]);

        return $this->success(new ArticleCommentResource($comment), 'تم إضافة التعليق بنجاح وسيظهر بعد المراجعة');
    }
}
