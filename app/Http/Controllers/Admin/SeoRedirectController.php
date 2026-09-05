<?php

namespace App\Http\Controllers\Admin;

use App\Models\SeoRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoRedirectController extends Controller
{
    public function index(Request $request): View
    {
        $seoRedirects = SeoRedirect::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . $request->string('q') . '%';
                $query->where('old_path', 'like', $search)
                    ->orWhere('new_path', 'like', $search);
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.seo-redirects.index', compact('seoRedirects'));
    }

    public function create(): View
    {
        return $this->view('admin.seo-redirects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'old_path' => ['required', 'string', 'max:255'],
            'new_path' => ['required', 'string', 'max:255'],
            'http_code' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        SeoRedirect::create($data);

        return redirect()->route('admin.seo-redirects.index')->with('success', 'SEO redirect created.');
    }

    public function show(SeoRedirect $seoRedirect): View
    {
        return $this->view('admin.seo-redirects.show', compact('seoRedirect'));
    }

    public function edit(SeoRedirect $seoRedirect): View
    {
        return $this->view('admin.seo-redirects.edit', compact('seoRedirect'));
    }

    public function update(Request $request, SeoRedirect $seoRedirect): RedirectResponse
    {
        $data = $request->validate([
            'old_path' => ['required', 'string', 'max:255'],
            'new_path' => ['required', 'string', 'max:255'],
            'http_code' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $seoRedirect->update($data);

        return redirect()->route('admin.seo-redirects.index')->with('success', 'SEO redirect updated.');
    }

    public function destroy(SeoRedirect $seoRedirect): RedirectResponse
    {
        $seoRedirect->delete();

        return redirect()->route('admin.seo-redirects.index')->with('success', 'SEO redirect deleted.');
    }

    public function toggleStatus(SeoRedirect $seoRedirect): RedirectResponse
    {
        $seoRedirect->update(['is_active' => ! (bool) $seoRedirect->is_active]);

        return back()->with('success', 'SEO redirect status updated.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $ids = (array) $request->input('ids', []);

        if ($action === 'delete') {
            SeoRedirect::whereIn('id', $ids)->delete();
        } elseif ($action === 'activate') {
            SeoRedirect::whereIn('id', $ids)->update(['is_active' => true]);
        } elseif ($action === 'deactivate') {
            SeoRedirect::whereIn('id', $ids)->update(['is_active' => false]);
        }

        return back()->with('success', 'Bulk action applied.');
    }
}
