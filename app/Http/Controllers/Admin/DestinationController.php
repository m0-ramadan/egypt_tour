<?php

namespace App\Http\Controllers\Admin;

use App\Models\Country;
use App\Models\Destination;
use App\Traits\HandlesTranslatedFields;
use App\Traits\UploadFileTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationController extends Controller
{
    use UploadFileTrait, HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $destinations = Destination::with(['country', 'city', 'parent'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch($query, ['name', 'short_description', 'description'], $request->string('q'));
            })
            ->latest()
            ->paginate($this->perPage($request));

        $countries = Country::with('cities')
            ->where('is_active', true)
            ->get();

        return $this->view('admin.destinations.index', [
            'destinations' => $destinations,
            'countries' => $countries,
        ]);
    }

    public function create(): View
    {
        $countries = Country::with(['cities' => function ($q) {
            $q->select('id', 'country_id', 'name')->orderBy('id');
        }])
            ->select('id', 'name')
            ->orderBy('id')
            ->get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'name' => adminTrans($country->name),
                    'cities' => $country->cities->map(function ($city) {
                        return [
                            'id' => $city->id,
                            'name' => adminTrans($city->name),
                        ];
                    })->values()->toArray(),
                ];
            })
            ->values();

        $parents = Destination::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        return view('admin.destinations.create', compact('countries', 'parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $data = $request->validate([
                'parent_id' => ['nullable', 'integer', 'exists:destinations,id'],
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'type' => ['required', 'in:country,region,city,attraction,poi'],
                'slug' => ['required', 'string', 'max:190', 'unique:destinations,slug'],
                'name' => ['required', 'string', 'max:190'],
                'short_description' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'latitude' => ['nullable', 'numeric'],
                'longitude' => ['nullable', 'numeric'],
                'is_featured' => ['nullable', 'boolean'],
                'is_active' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer'],
                'seo_title' => ['nullable', 'string', 'max:255'],
                'seo_description' => ['nullable', 'string'],
                'schema_json' => ['nullable', 'string'],
            ]);

            $data = $this->translateModelFields($data, [
                'name',
                'short_description',
                'description',
                'seo_title',
                'seo_description',
            ]);

            if ($request->hasFile('hero_image')) {
                $data['hero_image'] = $this->uploadImage('destinations', $request->file('hero_image'));
            }

            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $this->uploadImage('destinations', $request->file('featured_image'));
            }

            $data['is_active'] = $request->boolean('is_active');
            $data['is_featured'] = $request->boolean('is_featured');
            $data['sort_order'] = $data['sort_order'] ?? 0;

            Destination::create($data);

            return $this->success('admin.destinations.index', 'Destination created.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function show(Destination $destination): View
    {
        $destination->load(['country', 'city', 'parent']);

        return $this->view('admin.destinations.show', compact('destination'));
    }

    public function edit(Destination $destination): View
    {
        $countries = Country::with('cities')
            ->where('is_active', true)
            ->get();

        $parents = Destination::where('id', '!=', $destination->id)
            ->where('is_active', true)
            ->get();

        return $this->view('admin.destinations.edit', compact('destination', 'countries', 'parents'));
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'type' => ['required', 'in:country,region,city,attraction,poi'],
            'slug' => ['required', 'string', 'max:190', 'unique:destinations,slug,' . $destination->id],
            'name' => ['required', 'string', 'max:190'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'schema_json' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, [
            'name',
            'short_description',
            'description',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $this->uploadImage('destinations', $request->file('hero_image'));
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploadImage('destinations', $request->file('featured_image'));
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $destination->update($data);

        return $this->success('admin.destinations.index', 'Destination updated.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $destination->delete();

        return $this->success('admin.destinations.index', 'Destination deleted.');
    }

    public function statistics()
    {
        return response()->json([
            'total' => Destination::count(),
            'active' => Destination::where('is_active', true)->count(),
            'featured' => Destination::where('is_featured', true)->count(),
        ]);
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $action = (string) $request->input('action');

        if ($action === 'delete') {
            Destination::whereIn('id', $ids)->delete();
        } elseif ($action === 'activate') {
            Destination::whereIn('id', $ids)->update(['is_active' => true]);
        } elseif ($action === 'deactivate') {
            Destination::whereIn('id', $ids)->update(['is_active' => false]);
        }

        return back()->with('success', 'Bulk action applied.');
    }

    public function toggleStatus(Destination $destination): RedirectResponse
    {
        $destination->update([
            'is_active' => !(bool) $destination->is_active,
        ]);

        return back()->with('success', 'Destination status updated.');
    }

    public function toggleFeatured(Destination $destination): RedirectResponse
    {
        $destination->update([
            'is_featured' => !(bool) $destination->is_featured,
        ]);

        return back()->with('success', 'Destination featured updated.');
    }

    public function duplicate(Destination $destination): RedirectResponse
    {
        $copy = $destination->replicate();
        $copy->slug = $destination->slug . '-' . now()->timestamp;
        $copy->name = [
            'en' => $destination->display_name . ' (Copy)',
            'ar' => $destination->display_name . ' (Copy)',
        ];
        $copy->save();

        return redirect()->route('admin.destinations.edit', $copy)->with('success', 'Destination duplicated.');
    }
}
