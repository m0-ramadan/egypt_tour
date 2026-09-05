<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use App\Services\PageAiService;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class StaticPageController extends Controller
{
    use HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $pages = Page::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch(
                    $query,
                    ['title', 'body', 'seo_title', 'seo_description'],
                    $request->string('q')
                );
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.static-pages.index', compact('pages'));
    }

    public function create(): View
    {
        return $this->view('admin.static-pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'template' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_home' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, [
            'title',
            'body',
            'seo_title',
            'seo_description'
        ]);

        if (empty($data['slug']) && !empty($data['title'])) {
            $slugSource = is_array($data['title'])
                ? ($data['title']['en'] ?? $data['title']['ar'] ?? reset($data['title']))
                : $data['title'];

            $data['slug'] = Str::slug($slugSource ?: 'page-' . time());
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $data['is_home'] = $request->boolean('is_home');
        $data['is_active'] = $request->boolean('is_active', true);

        Page::create($data);

        return $this->success('admin.static-pages.index', 'Page created.');
    }

    public function show($pageId): View
    {
        $page = Page::findOrFail($pageId);

        return $this->view('admin.static-pages.show', compact('page'));
    }

    public function edit($pageId): View
    {
        $page = Page::findOrFail($pageId);

        return $this->view('admin.static-pages.edit', compact('page'));
    }


    public function update(Request $request, $page): RedirectResponse
    {
        $page = Page::findOrFail($page);

        $data = $request->validate([
            'slug' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'template' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_home' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        // ترجمة الحقول
        $data = $this->translateModelFields($data, [
            'title',
            'body',
            'seo_title',
            'seo_description'
        ]);

        // توليد slug
        if (empty($data['slug']) && !empty($data['title'])) {
            $slugSource = is_array($data['title'])
                ? ($data['title']['en'] ?? $data['title']['ar'] ?? reset($data['title']))
                : $data['title'];

            $data['slug'] = Str::slug($slugSource ?: 'page-' . $page->id);
        }

        // ✅ رفع الصورة
        if ($request->hasFile('featured_image')) {

            // حذف الصورة القديمة
            if (!empty($page->featured_image)) {
                Storage::disk('public')->delete($page->featured_image);
            }

            // رفع الجديدة
            $path = $request->file('featured_image')->store('pages', 'public');

            $data['featured_image'] = $path;
        }

        $data['is_home'] = $request->boolean('is_home');
        $data['is_active'] = $request->boolean('is_active', true);

        $page->update($data);

        return $this->success('admin.static-pages.index', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return $this->success('admin.static-pages.index', 'Page deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        Page::whereIn('id', (array) $request->input('ids', []))->delete();

        return back()->with('success', 'Bulk action applied.');
    }

    public function editWithAI(Page $page): View
    {
        return $this->view('admin.static-pages.edit-with-ai', compact('page'));
    }

    public function translateWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['nullable'],
            'body' => ['nullable'],
            'seo_title' => ['nullable'],
            'seo_description' => ['nullable'],
        ]);

        $translated = $pageAiService->translateFields($data, [
            'title',
            'body',
            'seo_title',
            'seo_description',
        ]);

        return response()->json($translated);
    }

    public function enhanceTitleWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
        ]);

        $title = $pageAiService->generateTitle($data['title']);

        return response()->json([
            'title' => $title ?: $data['title'],
        ]);
    }

    public function enhanceContentWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'instruction' => ['nullable', 'string'],
        ]);

        $content = $pageAiService->enhanceText(
            $data['content'],
            $data['instruction'] ?? 'حسن الأسلوب والتنظيم والوضوح'
        );

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function expandContentWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $content = $pageAiService->expandContent($data['content']);

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function simplifyContentWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $content = $pageAiService->simplifyContent($data['content']);

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function formatContentWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $content = $pageAiService->formatContent($data['content']);

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function checkGrammarWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $content = $pageAiService->checkGrammar($data['content']);

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function enhanceTextWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'instruction' => ['nullable', 'string'],
        ]);

        $content = $pageAiService->enhanceText(
            $data['content'],
            $data['instruction'] ?? null
        );

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function addSectionWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'section' => ['required', 'string'],
        ]);

        $content = $pageAiService->addSection(
            $data['content'],
            $data['section']
        );

        return response()->json([
            'content' => $content ?: $data['content'],
        ]);
    }

    public function loadTemplateWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'template' => ['required', 'string'],
        ]);

        $content = $pageAiService->loadTemplate($data['template']);

        return response()->json([
            'content' => $content ?: '',
        ]);
    }

    public function generateFromPromptWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'template' => ['nullable', 'string'],
        ]);

        $generated = $pageAiService->generatePage([
            'prompt' => $data['prompt'],
            'template' => $data['template'] ?? 'default',
        ]);

        return response()->json($generated ?: []);
    }

    public function generatePageWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'template' => ['nullable', 'string'],
        ]);

        $generated = $pageAiService->generatePage([
            'prompt' => $data['prompt'],
            'template' => $data['template'] ?? 'default',
        ]);

        if (!$generated) {
            return response()->json([], 422);
        }

        $translated = $pageAiService->translateFields($generated, [
            'title',
            'body',
            'seo_title',
            'seo_description',
        ]);

        return response()->json($translated);
    }

    public function generateTitleWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'topic' => ['required', 'string'],
        ]);

        $title = $pageAiService->generateTitle($data['topic']);

        return response()->json([
            'title' => $title ?: '',
        ]);
    }

    public function generateContentWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'template' => ['nullable', 'string'],
        ]);

        $content = $pageAiService->generateBody(
            $data['title'],
            $data['template'] ?? null
        );

        return response()->json([
            'content' => $content ?: '',
        ]);
    }

    public function generateMetaTitleWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $metaTitle = $pageAiService->generateMetaTitle(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'meta_title' => $metaTitle ?: '',
        ]);
    }

    public function generateMetaDescriptionWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $metaDescription = $pageAiService->generateMetaDescription(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'meta_description' => $metaDescription ?: '',
        ]);
    }

    public function generateKeywordsWithAI(
        Request $request,
        PageAiService $pageAiService
    ): JsonResponse {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $keywords = $pageAiService->generateKeywords(
            $data['title'],
            $data['content'] ?? null
        );

        return response()->json([
            'keywords' => $keywords,
        ]);
    }
}
