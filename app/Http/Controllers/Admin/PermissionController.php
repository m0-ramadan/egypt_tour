<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $permissions = Permission::latest()->paginate($this->perPage($request));
        return $this->view('admin.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return $this->view('admin.permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'unique:permissions,name']]);

        Permission::create([
            'name' => $data['name'],
            'guard_name' => 'admin',
        ]);

        return $this->success('admin.permissions.index', 'Permission created.');
    }

    public function edit(Permission $permission): View
    {
        return $this->view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'unique:permissions,name,' . $permission->id]]);
        $permission->update($data);

        return $this->success('admin.permissions.index', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();
        return $this->success('admin.permissions.index', 'Permission deleted.');
    }

    public function generateForModule(Request $request): RedirectResponse
    {
        $data = $request->validate(['module' => ['required', 'string']]);

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            Permission::findOrCreate($action . ' ' . $data['module'], 'admin');
        }

        return back()->with('success', 'Module permissions generated.');
    }
}
