<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BlogController extends Controller
{

    public function index(Request $request)
    {
        $languages = [
            'en',
            'ar',
            'fr',
            'es',
            'ru',
            'It',
            'ch',
        ];

        $articles = Article::query()
            ->with(['category', 'author'])
            ->active()
            ->published()
            ->when($request->filled('keyword'), function ($query) use ($request, $languages) {
                $keyword = trim($request->keyword);

                $query->where(function ($q) use ($keyword, $languages) {
                    foreach ($languages as $locale) {
                        $q->orWhere("title->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("content->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("excerpt->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("seo_title->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("seo_description->{$locale}", 'like', "%{$keyword}%")
                            ->orWhere("seo_keywords->{$locale}", 'like', "%{$keyword}%");
                    }
                });
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $popularArticles = Article::query()
            ->with(['category'])
            ->active()
            ->published()
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $categories = ArticleCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('website.pages.blogs.index', compact(
            'articles',
            'popularArticles',
            'categories'
        ));
    }

    public function show(string $categorySlugOrSlug, ?string $slug = null)
    {
        $articleSlug = $slug ?? $categorySlugOrSlug;
        $requestedCategorySlug = $slug ? $categorySlugOrSlug : null;

        $article = Article::query()
            ->with(['category', 'author', 'tags'])
            ->active()
            ->published()
            ->where('slug', $articleSlug)
            ->firstOrFail();

        if (
            $requestedCategorySlug !== null &&
            $article->category?->slug &&
            $requestedCategorySlug !== $article->category->slug
        ) {
            return redirect()->route('website.blogs.show.legacy', [
                'categorySlug' => $article->category->slug,
                'slug' => $article->slug,
            ], 301);
        }

        $article->increment('views_count');

        $relatedArticles = Article::query()
            ->with(['category'])
            ->active()
            ->published()
            ->where('id', '!=', $article->id)
            ->when($article->category_id, function ($query) use ($article) {
                $query->where('category_id', $article->category_id);
            })
            ->latest('published_at')
            ->limit(6)
            ->get();

        $popularArticles = Article::query()
            ->with(['category'])
            ->active()
            ->published()
            ->where('id', '!=', $article->id)
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $categories = ArticleCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('website.pages.blogs.show', compact(
            'article',
            'relatedArticles',
            'popularArticles',
            'categories'
        ));
    }

    public function category(string $slug)
    {
        $category = ArticleCategory::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $articles = Article::query()
            ->with(['category', 'author'])
            ->active()
            ->published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12);

        return view('website.pages.blogs.index', compact('articles', 'category'));
    }
}
