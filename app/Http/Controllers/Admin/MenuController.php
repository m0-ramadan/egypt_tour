<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Traits\HandlesTranslatedFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    use HandlesTranslatedFields;

    public function index(Request $request): View
    {
        $menus = Menu::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $this->applyTranslatedSearch($query, ['name'], $request->string('q'));
            })
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        $languages = Language::all();
        return $this->view('admin.menus.create', compact('languages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_key' => ['nullable', 'string', 'max:100'],
        ]);

        $data = $this->translateModelFields($data, ['name']);

        Menu::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created.');
    }

    public function show(Menu $menu): View
    {
        return $this->view('admin.menus.show', compact('menu'));
    }

    public function edit(Menu $menu): View
    {
        return $this->view('admin.menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location_key' => ['nullable', 'string', 'max:100'],
        ]);

        $data = $this->translateModelFields($data, ['name']);

        $menu->update($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function items(Menu $menu): View
    {
        $items = $menu->allItems()->get();

        return $this->view('admin.menus.items', compact('menu', 'items'));
    }

    public function storeItem(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'max:50'],
            'linked_type' => ['nullable', 'string', 'max:100'],
            'linked_id' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = $this->translateModelFields($data, ['label']);
        $data['menu_id'] = $menu->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        MenuItem::create($data);

        return back()->with('success', 'Menu item created.');
    }

    public function updateItem(Request $request, MenuItem $item): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'max:50'],
            'linked_type' => ['nullable', 'string', 'max:100'],
            'linked_id' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = $this->translateModelFields($data, ['label']);

        $item->update($data);

        return back()->with('success', 'Menu item updated.');
    }

    public function destroyItem(MenuItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('success', 'Menu item deleted.');
    }

    public function reorderItems(Request $request): RedirectResponse
    {
        foreach ((array) $request->input('items', []) as $entry) {
            if (!empty($entry['id'])) {
                MenuItem::where('id', $entry['id'])->update([
                    'sort_order' => (int) ($entry['sort_order'] ?? 0),
                    'parent_id' => $entry['parent_id'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Menu items reordered.');
    }
}
