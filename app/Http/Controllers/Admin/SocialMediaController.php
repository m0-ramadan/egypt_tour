<?php

namespace App\Http\Controllers\Admin;

use App\Models\SocialMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialMediaController extends Controller
{
    public function index(Request $request): View
    {
        $social_media = SocialMedia::query()
            ->when($request->filled('q'), fn($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.social-media.index', ['socialMedia' => $social_media]);
    }

    public function create(): View
    {
        return $this->view('admin.social-media.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'platform' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'icon' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        SocialMedia::create($data);

        return $this->success('admin.social-media.index', 'SocialMedia created.');
    }

    public function show(SocialMedia $socialMedia): View
    {
        return $this->view('admin.social-media.show', compact('socialMedia'));
    }

    public function edit(SocialMedia $socialMedia): View
    {
        return $this->view('admin.social-media.edit', compact('socialMedia'));
    }

    public function update(Request $request, SocialMedia $socialMedia): RedirectResponse
    {
        $data = $request->validate([
            'platform' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
            'icon' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $socialMedia->update($data);

        return $this->success('admin.social-media.index', 'SocialMedia updated.');
    }

    public function destroy(SocialMedia $socialMedia): RedirectResponse
    {
        $socialMedia->delete();

        return $this->success('admin.social-media.index', 'SocialMedia deleted.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        foreach ((array) $request->input('items', []) as $item) {
            if (! empty($item['id'])) {
                SocialMedia::where('id', $item['id'])->update($item);
            }
        }

        return back()->with('success', 'Social links updated.');
    }
}
