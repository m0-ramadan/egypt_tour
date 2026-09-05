<?php

namespace App\Http\Controllers\Admin;

use App\Models\SeoMeta;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoMetaController extends Controller
{
    use HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $seoMeta = SeoMeta::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($q) use ($search) {
                    $this->applyTranslatedSearch($q, ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description'], $search);
                    $q->orWhere('model_type', 'like', '%' . $search . '%')
                        ->orWhere('locale', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.seo-meta.index', compact('seoMeta'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'max:255'],
            'model_id' => ['required', 'integer'],
            'locale' => ['nullable', 'string', 'max:10'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'schema_json' => ['nullable', 'array'],
        ]);

        $data = $this->translateModelFields($data, [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_title',
            'og_description',
        ]);

        SeoMeta::create($data);

        return back()->with('success', 'SEO meta created.');
    }

    public function update(Request $request, SeoMeta $seoMetum): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['nullable', 'string', 'max:10'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'schema_json' => ['nullable', 'array'],
        ]);

        $data = $this->translateModelFields($data, [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_title',
            'og_description',
        ]);

        $seoMetum->update($data);

        return back()->with('success', 'SEO meta updated.');
    }

    public function destroy(SeoMeta $seoMetum): RedirectResponse
    {
        $seoMetum->delete();

        return back()->with('success', 'SEO meta deleted.');
    }

    public function byModel(string $type, int $id): View
    {
        $seoMeta = SeoMeta::query()
            ->where('model_type', $type)
            ->where('model_id', $id)
            ->latest()
            ->paginate(50);

        return $this->view('admin.seo-meta.by-model', compact('seoMeta', 'type', 'id'));
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        foreach ((array) $request->input('items', []) as $row) {
            if (!empty($row['id'])) {
                $payload = [
                    'locale' => $row['locale'] ?? null,
                    'meta_title' => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_description'] ?? null,
                    'meta_keywords' => $row['meta_keywords'] ?? null,
                    'canonical_url' => $row['canonical_url'] ?? null,
                ];

                $payload = $this->translateModelFields($payload, [
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                ]);

                SeoMeta::where('id', $row['id'])->update($payload);
            }
        }

        return back()->with('success', 'SEO meta updated.');
    }
}
