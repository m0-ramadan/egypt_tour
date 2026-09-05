<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ArticleResource;
use App\Http\Resources\Website\ArticleCategoryResource;

class ArticleController extends Controller
{
    use ApiResponseTrait;

    // عرض قائمة المقالات
    public function index(Request $request)
    {
        $query = Article::with(['category', 'author'])
            ->active()
            ->published()
            ->latest();

        // البحث
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // التصفية حسب الفئة
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // التصفية حسب الوسم
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // المقالات المميزة
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // الباقة مع pagination
        $perPage = $request->input('per_page', 12);
        $articles = $query->paginate($perPage);

        return $this->success(ArticleResource::collection($articles), 'تم جلب المقالات بنجاح');
    }

    // عرض مقالة واحدة
    public function show($slug)
    {
        $article = Article::with(['category', 'author', 'tags', 'comments.replies'])
            ->where('slug', $slug)
            ->active()
            ->published()
            ->firstOrFail();

        // زيادة عدد المشاهدات
        $article->incrementViews();

        return $this->success(new ArticleResource($article), 'تم جلب المقالة بنجاح');
    }

    // عرض المقالات المميزة
    public function featured()
    {
        $articles = Article::with(['category', 'author'])
            ->active()
            ->published()
            ->featured()
            ->latest()
            ->take(6)
            ->get();

        return $this->success(ArticleResource::collection($articles), 'تم جلب المقالات المميزة بنجاح');
    }

    // المقالات الأكثر مشاهدة
    public function popular()
    {
        $articles = Article::with(['category', 'author'])
            ->active()
            ->published()
            ->popular()
            ->take(6)
            ->get();

        return $this->success(ArticleResource::collection($articles), 'تم جلب المقالات الأكثر مشاهدة بنجاح');
    }

    // عرض المقالات ذات الصلة
    public function related($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        $relatedArticles = Article::with(['category', 'author'])
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->active()
            ->published()
            ->latest()
            ->take(4)
            ->get();

        return $this->success(ArticleResource::collection($relatedArticles), 'تم جلب المقالات ذات الصلة بنجاح');
    }
}
