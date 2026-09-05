<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    use HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $faqs = Faq::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere(function ($q) use ($search) {
                        $this->applyTranslatedSearch($q, ['question', 'answer'], $search);
                    });
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.faqs.index', ['faqs' => $faqs]);
    }

    public function create(): View
    {
        return $this->view('admin.faqs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'question' => ['nullable', 'string'],
            'answer' => ['nullable', 'string'],
            'context_type' => ['nullable', 'string'],
            'context_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data = $this->translateModelFields($data, ['question', 'answer']);

        Faq::create($data);

        return $this->success('admin.faqs.index', 'Faq created.');
    }

    public function show(Faq $faq): View
    {
        return $this->view('admin.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq): View
    {
        return $this->view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'question' => ['nullable', 'string'],
            'answer' => ['nullable', 'string'],
            'context_type' => ['nullable', 'string'],
            'context_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data = $this->translateModelFields($data, ['question', 'answer']);

        $faq->update($data);

        return $this->success('admin.faqs.index', 'Faq updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return $this->success('admin.faqs.index', 'Faq deleted.');
    }
}
