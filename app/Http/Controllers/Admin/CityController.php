<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Country;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CityController extends Controller
{
    use HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $cities = City::query()
            ->with('country')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($q) use ($search) {
                    $this->applyTranslatedSearch(
                        $q,
                        ['name', 'short_description', 'description', 'seo_title', 'seo_description'],
                        $search
                    );

                    $q->orWhereHas('country', function ($countryQuery) use ($search) {
                        $this->applyTranslatedSearch($countryQuery, ['name'], $search);
                    });
                });
            })
            ->when($request->filled('country_id'), function ($query) use ($request) {
                $query->where('country_id', $request->country_id);
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

        $countries = Country::where('is_active', true)->get();

        return $this->view('admin.cities.index', [
            'cities' => $cities,
            'countries' => $countries,
        ]);
    }

    public function create(): View
    {
        $countries = Country::where('is_active', true)->get();

        return $this->view('admin.cities.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_id' => ['nullable', 'exists:countries,id'],
            'name' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
            'schema_json' => ['nullable', 'array'],
        ]);

        $data = $this->translateModelFields($data, [
            'name',
            'short_description',
            'description',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $request->file('hero_image')->store('cities', 'public');
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('cities', 'public');
        }

        if (empty($data['slug']) && !empty($data['name'])) {
            $slugSource = is_array($data['name'])
                ? ($data['name']['en'] ?? $data['name']['ar'] ?? reset($data['name']))
                : $data['name'];

            $data['slug'] = Str::slug($slugSource ?: 'city-' . time());
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        City::create($data);

        return $this->success('admin.cities.index', 'City created.');
    }

    public function show(City $city): View
    {
        $city->load(['country', 'attractions']);

        return $this->view('admin.cities.show', compact('city'));
    }

    public function edit(City $city): View
    {
        $countries = Country::where('is_active', true)->get();

        return $this->view('admin.cities.edit', compact('city', 'countries'));
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $data = $request->validate([
            'country_id' => ['nullable', 'exists:countries,id'],
            'name' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'seo_title' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
            'schema_json' => ['nullable', 'array'],
        ]);

        $data = $this->translateModelFields($data, [
            'name',
            'short_description',
            'description',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('hero_image')) {
            if ($city->hero_image && Storage::disk('public')->exists($city->hero_image)) {
                Storage::disk('public')->delete($city->hero_image);
            }

            $data['hero_image'] = $request->file('hero_image')->store('cities', 'public');
        }

        if ($request->hasFile('featured_image')) {
            if ($city->featured_image && Storage::disk('public')->exists($city->featured_image)) {
                Storage::disk('public')->delete($city->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')->store('cities', 'public');
        }

        if (empty($data['slug']) && !empty($data['name'])) {
            $slugSource = is_array($data['name'])
                ? ($data['name']['en'] ?? $data['name']['ar'] ?? reset($data['name']))
                : $data['name'];

            $data['slug'] = Str::slug($slugSource ?: 'city-' . $city->id);
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $city->update($data);

        return $this->success('admin.cities.index', 'City updated.');
    }

    public function destroy(City $city): RedirectResponse
    {
        if ($city->hero_image && Storage::disk('public')->exists($city->hero_image)) {
            Storage::disk('public')->delete($city->hero_image);
        }

        if ($city->featured_image && Storage::disk('public')->exists($city->featured_image)) {
            Storage::disk('public')->delete($city->featured_image);
        }

        $city->delete();

        return $this->success('admin.cities.index', 'City deleted.');
    }
}
