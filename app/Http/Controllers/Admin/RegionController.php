<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(Request $request): View
    {
        $regions = Destination::query()
            ->when($request->filled('q'), fn($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.regions.index', ['regions' => $regions]);
    }

    public function create(): View
    {
        return $this->view('admin.regions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string'],
            'slug' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Destination::create($data);

        return $this->success('admin.regions.index', 'Region created.');
    }

    public function show(Destination $region): View
    {
        return $this->view('admin.regions.show', compact('region'));
    }

    public function edit(Destination $region): View
    {
        return $this->view('admin.regions.edit', compact('region'));
    }

    public function update(Request $request, Destination $region): RedirectResponse
    {
        $data = $request->validate([
            'country_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string'],
            'slug' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $region->update($data);

        return $this->success('admin.regions.index', 'Region updated.');
    }

    public function destroy(Destination $region): RedirectResponse
    {
        $region->delete();

        return $this->success('admin.regions.index', 'Region deleted.');
    }
}
