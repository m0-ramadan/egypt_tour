<?php

namespace App\Http\Controllers\Api\Website;

use Illuminate\Http\Request;
use App\Models\ArticleCategory;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ArticleResource;
use App\Http\Resources\Website\ArticleCategoryResource;

class ArticleCategoryController extends Controller
{
    use ApiResponseTrait;

    // عرض جميع الأقسام الرئيسية
    public function index()
    {
        $categories = ArticleCategory::with(['children', 'activeArticles'])
            ->active()
            ->mainCategories()
            ->orderBy('order')
            ->get();

        return $this->success(ArticleCategoryResource::collection($categories), 'تم جلب الأقسام بنجاح');
    }

    // عرض قسم معين مع مقالاته
    public function show($slug, Request $request)
    {
        $category = ArticleCategory::with(['children', 'parent'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // جلب مقالات القسم مع pagination
        $articles = $category->activeArticles()
            ->with(['author'])
            ->published()
            ->latest()
            ->paginate($request->input('per_page', 12));

        $response = [
            'category' => new ArticleCategoryResource($category),
            'articles' => ArticleResource::collection($articles)
        ];

        return $this->success($response, 'تم جلب القسم والمقالات بنجاح');
    }

    // عرض الأقسام مع عدد المقالات
    public function withCounts()
    {
        $categories = ArticleCategory::with(['children' => function ($query) {
            $query->active()->withCount(['activeArticles' => function ($q) {
                $q->published();
            }]);
        }])
            ->active()
            ->mainCategories()
            ->withCount(['activeArticles' => function ($query) {
                $query->published();
            }])
            ->orderBy('order')
            ->get();

        return $this->success(ArticleCategoryResource::collection($categories), 'تم جلب الأقسام مع الإحصائيات بنجاح');
    }
}
