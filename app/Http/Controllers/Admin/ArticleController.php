<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use App\Services\ArticleAiService;
use App\Traits\HandlesTranslatedFields;
use App\Traits\UploadFileTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;


class ArticleController extends Controller
{
    use HandlesTranslatedFields, UploadFileTrait;

    public function index(Request $request): View
    {
        $articles = Article::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch(
                    $query,
                    ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
                    $request->string('q')
                );
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.articles.index', ['articles' => $articles]);
    }

    public function create(): View
    {
        return $this->view('admin.articles.create');
    }


    public function show(Article $article): View
    {
        return $this->view('admin.articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        return $this->view('admin.articles.edit', compact('article'));
    }


    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'slug' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'article_type' => ['nullable', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, [
            'title',
            'excerpt',
            'content',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploadImage('articles', $request->file('featured_image'));
        }

        if (empty($data['slug']) && !empty($data['title'])) {
            $slugSource = is_array($data['title'])
                ? ($data['title']['en'] ?? $data['title']['ar'] ?? reset($data['title']))
                : $data['title'];

            $data['slug'] = Str::slug($slugSource ?: 'article-' . time());
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        Article::create($data);

        return $this->success('admin.articles.index', 'Article created.');
    }

    public function update(Request $request, $article)
    {
        $article = Article::findOrFail($article);
        $data = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'slug' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'article_type' => ['nullable', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, [
            'title',
            'excerpt',
            'content',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploadImage('articles', $request->file('featured_image'));
        }

        if (empty($data['slug']) && !empty($data['title'])) {
            $slugSource = is_array($data['title'])
                ? ($data['title']['en'] ?? $data['title']['ar'] ?? reset($data['title']))
                : $data['title'];

            $data['slug'] = Str::slug($slugSource ?: 'article-' . $article->id);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        $article->update($data);

        return $this->success('admin.articles.index', 'Article updated.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return $this->success('admin.articles.index', 'Article deleted.');
    }

    public function statistics(): JsonResponse
    {
        return response()->json([
            'total' => Article::count(),
            'published' => Article::where('is_active', true)->count(),
            'featured' => Article::where('is_featured', true)->count(),
        ]);
    }

    public function bulkActions(Request $request): RedirectResponse
    {
        if ($request->input('action') === 'delete') {
            Article::whereIn('id', (array) $request->input('ids', []))->delete();
        }

        return back()->with('success', 'Bulk action applied.');
    }

    public function toggleStatus(Article $article): RedirectResponse
    {
        $article->update(['is_active' => ! (bool) $article->is_active]);

        return back()->with('success', 'Article status updated.');
    }

    public function toggleFeatured(Article $article): RedirectResponse
    {
        $article->update(['is_featured' => ! (bool) $article->is_featured]);

        return back()->with('success', 'Article featured updated.');
    }

    public function createWithAI(): View
    {
        return $this->view('admin.articles.create-with-ai');
    }

    public function storeWithAI(
        Request $request,
        ArticleAiService $articleAiService
    ): RedirectResponse {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'category_id' => ['nullable', 'integer'],
            'post_type' => ['nullable', 'string'],
            'author_id' => ['nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $generated = $articleAiService->generateArticle([
            'prompt' => $data['prompt'],
            'category' => '',
            'tone' => 'professional',
            'language_hint' => 'Arabic and English',
        ]);

        if (!$generated) {
            return back()->withInput()->with('error', 'فشل توليد المقال بالذكاء الاصطناعي');
        }

        $translated = $articleAiService->translateFields($generated, [
            'title',
            'excerpt',
            'content',
            'seo_title',
            'seo_description',
        ]);

        $finalData = [
            'category_id' => $data['category_id'] ?? null,
            'post_type' => $data['post_type'] ?? null,
            'author_id' => $data['author_id'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'title' => $translated['title'] ?? [],
            'excerpt' => $translated['excerpt'] ?? [],
            'content' => $translated['content'] ?? [],
            'seo_title' => $translated['seo_title'] ?? [],
            'seo_description' => $translated['seo_description'] ?? [],
        ];

        $finalData['slug'] = $articleAiService->makeSlugFromTitle($finalData['title'] ?? 'article-' . time());

        Article::create($finalData);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'تم إنشاء المقال بالذكاء الاصطناعي');
    }

    public function enhanceWithAI(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'instruction' => ['nullable', 'string'],
        ]);

        $content = $articleAiService->enhanceContent(
            $data['content'],
            $data['instruction'] ?? null
        );

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function generateWithAI(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'category' => ['nullable', 'string'],
            'tone' => ['nullable', 'string'],
        ]);

        $generated = $articleAiService->generateArticle([
            'prompt' => $data['prompt'],
            'category' => $data['category'] ?? '',
            'tone' => $data['tone'] ?? 'professional',
            'language_hint' => 'Arabic and English',
        ]);

        return response()->json($generated ?: []);
    }

    public function generateFullArticle(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'topic' => ['required', 'string'],
            'category' => ['nullable', 'string'],
            'tone' => ['nullable', 'string'],
        ]);

        $generated = $articleAiService->generateArticle([
            'topic' => $data['topic'],
            'category' => $data['category'] ?? '',
            'tone' => $data['tone'] ?? 'professional',
            'language_hint' => 'Arabic and English',
        ]);

        return response()->json($generated ?: []);
    }

    public function generateTitle(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'topic' => ['required', 'string'],
            'tone' => ['nullable', 'string'],
        ]);

        $title = $articleAiService->generateTitle(
            $data['topic'],
            $data['tone'] ?? null
        );

        return response()->json([
            'title' => $title ?: '',
        ]);
    }

    public function generateContent(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'tone' => ['nullable', 'string'],
        ]);

        $content = $articleAiService->generateContent(
            $data['title'],
            $data['excerpt'] ?? null,
            $data['tone'] ?? null
        );

        return response()->json([
            'content' => $content ?: '',
        ]);
    }

    public function enhanceContent(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'instruction' => ['nullable', 'string'],
        ]);

        $content = $articleAiService->enhanceContent(
            $data['content'],
            $data['instruction'] ?? null
        );

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function generateExcerpt(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $excerpt = $articleAiService->generateExcerpt(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'excerpt' => $excerpt ?: '',
        ]);
    }

    public function translateAll(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['nullable'],
            'excerpt' => ['nullable'],
            'content' => ['nullable'],
            'seo_title' => ['nullable'],
            'seo_description' => ['nullable'],
        ]);

        $translated = $articleAiService->translateFields($data, [
            'title',
            'excerpt',
            'content',
            'seo_title',
            'seo_description',
        ]);

        return response()->json($translated);
    }

    public function improveAll(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $improved = $articleAiService->improveAll($data);

        return response()->json($improved);
    }

    public function generateMetaTitle(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $metaTitle = $articleAiService->generateMetaTitle(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'meta_title' => $metaTitle ?: '',
        ]);
    }

    public function generateMetaDescription(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $metaDescription = $articleAiService->generateMetaDescription(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'meta_description' => $metaDescription ?: '',
        ]);
    }

    public function generateKeywords(
        Request $request,
        ArticleAiService $articleAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $keywords = $articleAiService->generateKeywords(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'keywords' => $keywords,
        ]);
    }
}
