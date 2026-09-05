<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    use HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $testimonials = Testimonial::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere(function ($q) use ($search) {
                        $this->applyTranslatedSearch($q, ['customer_country', 'content', 'response_from_admin'], $search);
                    });
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.testimonials.index', ['testimonials' => $testimonials]);
    }

    public function create(): View
    {
        return $this->view('admin.testimonials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string'],
            'customer_country' => ['nullable', 'string'],
            'initials' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'source_url' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric'],
            'content' => ['nullable', 'string'],
            'is_verified' => ['nullable', 'boolean'],
            'verified_purchase' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'response_from_admin' => ['nullable', 'string'],
            'responded_at' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data = $this->translateModelFields($data, [
            'customer_country',
            'content',
            'response_from_admin',
        ]);

        Testimonial::create($data);

        return $this->success('admin.testimonials.index', 'Testimonial created.');
    }

    public function show(Testimonial $testimonial): View
    {
        return $this->view('admin.testimonials.show', compact('testimonial'));
    }

    public function edit(Testimonial $testimonial): View
    {
        return $this->view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string'],
            'customer_country' => ['nullable', 'string'],
            'initials' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'source_url' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric'],
            'content' => ['nullable', 'string'],
            'is_verified' => ['nullable', 'boolean'],
            'verified_purchase' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'response_from_admin' => ['nullable', 'string'],
            'responded_at' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data = $this->translateModelFields($data, [
            'customer_country',
            'content',
            'response_from_admin',
        ]);

        $testimonial->update($data);

        return $this->success('admin.testimonials.index', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return $this->success('admin.testimonials.index', 'Testimonial deleted.');
    }

    public function toggleStatus(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_active' => !(bool) ($testimonial->is_active ?? true)]);

        return back()->with('success', 'Testimonial status updated.');
    }

    public function toggleFeatured(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_featured' => !(bool) $testimonial->is_featured]);

        return back()->with('success', 'Testimonial featured updated.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        Testimonial::whereIn('id', (array) $request->input('ids', []))->delete();

        return back()->with('success', 'Selected testimonials removed.');
    }
}
