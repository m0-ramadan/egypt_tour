<?php

namespace App\Http\Controllers\Admin;

use App\Models\Country;
use App\Traits\HandlesTranslatedFields;
use App\Traits\UploadFileTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    use UploadFileTrait, HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $countries = Country::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch($query, ['name'], $request->string('q'));
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.countries.index', [
            'countries' => $countries,
        ]);
    }

    public function create(): View
    {
        return $this->view('admin.countries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'slug' => ['nullable', 'string'],
            'flag' => ['nullable', 'image'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $data = $this->translateModelFields($data, ['name']);

        if ($request->hasFile('flag')) {
            $data['flag'] = $this->uploadImage('countries', $request->file('flag'));
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        Country::create($data);

        return $this->success('admin.countries.index', 'Country created.');
    }

    public function show(Country $country): View
    {
        return $this->view('admin.countries.show', compact('country'));
    }

    public function edit(Country $country): View
    {
        return $this->view('admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'slug' => ['nullable', 'string'],
            'flag' => ['nullable', 'image'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $data = $this->translateModelFields($data, ['name']);

        if ($request->hasFile('flag')) {
            $data['flag'] = $this->uploadImage('countries', $request->file('flag'));
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        $country->update($data);

        return $this->success('admin.countries.index', 'Country updated.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return $this->success('admin.countries.index', 'Country deleted.');
    }
}
