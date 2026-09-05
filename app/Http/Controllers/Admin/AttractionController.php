<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attraction;
use App\Models\City;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Traits\UploadFileTrait;

class AttractionController extends Controller
{
    use HandlesTranslatedFields, UploadFileTrait;

    public function index(Request $request): View
    {
        $attractions = Attraction::query()
            ->with('city')
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch(
                    $query,
                    ['name', 'short_description', 'description', 'seo_title', 'seo_description'],
                    $request->string('q')
                );
            })
            ->when($request->filled('city_id'), function ($query) use ($request) {
                $query->where('city_id', $request->city_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        $cities = City::where('is_active', true)->get();

        return $this->view('admin.attractions.index', compact('attractions', 'cities'));
    }

    public function create(): View
    {
        $cities = City::where('is_active', true)->get();

        return $this->view('admin.attractions.create', compact('cities'));
    }

    public function show(Attraction $attraction): View
    {
        $attraction->load('city');

        return $this->view('admin.attractions.show', compact('attraction'));
    }

    public function edit(Attraction $attraction): View
    {
        $cities = City::where('is_active', true)->get();

        return $this->view('admin.attractions.edit', compact('attraction', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'city_id' => ['nullable', 'exists:cities,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'opening_hours' => ['nullable', 'string'],
            'map_url' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, [
            'name',
            'short_description',
            'description',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage('attractions', $request->file('image'));
        }

        if (empty($data['slug']) && !empty($data['name'])) {
            $slugSource = is_array($data['name'])
                ? ($data['name']['en'] ?? $data['name']['ar'] ?? reset($data['name']))
                : $data['name'];

            $data['slug'] = Str::slug($slugSource ?: 'attraction-' . time());
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Attraction::create($data);

        return $this->success('admin.attractions.index', 'Attraction created.');
    }

    public function update(Request $request, Attraction $attraction): RedirectResponse
    {
        $data = $request->validate([
            'city_id' => ['nullable', 'exists:cities,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'opening_hours' => ['nullable', 'string'],
            'map_url' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($data, [
            'name',
            'short_description',
            'description',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('image')) {
            $this->deletePublicFile($attraction->image);
            $data['image'] = $this->uploadImage('attractions', $request->file('image'));
        }

        if (empty($data['slug']) && !empty($data['name'])) {
            $slugSource = is_array($data['name'])
                ? ($data['name']['en'] ?? $data['name']['ar'] ?? reset($data['name']))
                : $data['name'];

            $data['slug'] = Str::slug($slugSource ?: 'attraction-' . $attraction->id);
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $attraction->update($data);

        return $this->success('admin.attractions.index', 'Attraction updated.');
    }

    public function destroy(Attraction $attraction): RedirectResponse
    {
        if ($attraction->image && Storage::disk('public')->exists($attraction->image)) {
            Storage::disk('public')->delete($attraction->image);
        }

        $attraction->delete();

        return $this->success('admin.attractions.index', 'Attraction deleted.');
    }

    public function statistics(): JsonResponse
    {
        return response()->json([
            'total' => Attraction::count(),
            'active' => Attraction::where('is_active', true)->count(),
            'inactive' => Attraction::where('is_active', false)->count(),
            'featured' => Attraction::where('is_featured', true)->count(),
        ]);
    }

    public function bulkActions(Request $request): RedirectResponse
    {
        $ids = (array) $request->input('ids', []);
        $action = (string) $request->input('action');

        if ($action === 'delete') {
            $items = Attraction::whereIn('id', $ids)->get();

            foreach ($items as $item) {
                if ($item->image && Storage::disk('public')->exists($item->image)) {
                    Storage::disk('public')->delete($item->image);
                }

                $item->delete();
            }
        } elseif ($action === 'activate') {
            Attraction::whereIn('id', $ids)->update(['is_active' => true]);
        } elseif ($action === 'deactivate') {
            Attraction::whereIn('id', $ids)->update(['is_active' => false]);
        } elseif ($action === 'feature') {
            Attraction::whereIn('id', $ids)->update(['is_featured' => true]);
        } elseif ($action === 'unfeature') {
            Attraction::whereIn('id', $ids)->update(['is_featured' => false]);
        }

        return back()->with('success', 'Bulk action applied.');
    }

    public function toggleStatus(Attraction $attraction): RedirectResponse
    {
        $attraction->update([
            'is_active' => !(bool) $attraction->is_active,
        ]);

        return back()->with('success', 'Attraction status updated.');
    }

    public function toggleFeatured(Attraction $attraction): RedirectResponse
    {
        $attraction->update([
            'is_featured' => !(bool) $attraction->is_featured,
        ]);

        return back()->with('success', 'Attraction featured updated.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'description' => ['nullable', 'string'],
        ]);

        $data = $this->translateModelFields($validated, [
            'name',
            'description',
        ]);

        $slugSource = is_array($data['name'])
            ? ($data['name']['en'] ?? $data['name']['ar'] ?? reset($data['name']))
            : $data['name'];

        $data['slug'] = Str::slug($slugSource ?: 'attraction-' . time()) . '-' . uniqid();
        $data['is_active'] = true;
        $data['is_featured'] = false;

        $attraction = Attraction::create($data);
        $attraction->load('city');

        $nameDisplay = $attraction->display_name ?: $request->input('name');
        $cityName = $attraction->city ? ($attraction->city->display_name ?: 'City') : 'No city';

        return response()->json([
            'status' => 'success',
            'message' => __('Facility / Place added successfully!'),
            'attraction' => [
                'id' => $attraction->id,
                'name' => $nameDisplay,
                'city_name' => $cityName,
                'search_text' => mb_strtolower($nameDisplay . ' ' . $cityName),
            ],
        ]);
    }
}
