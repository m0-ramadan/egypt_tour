<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $roles = Role::latest()->paginate($this->perPage($request));
        return $this->view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        return $this->view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'admin']);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->success('admin.roles.index', 'Role created.');
    }

    public function show(Role $role): View
    {
        return $this->view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get();
        return $this->view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->success('admin.roles.index', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        return $this->success('admin.roles.index', 'Role deleted.');
    }

    public function permissions(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get();
        return $this->view('admin.roles.permissions', compact('role', 'permissions'));
    }

    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        $role->syncPermissions($request->input('permissions', []));
        return back()->with('success', 'Permissions synced.');
    }

    public function assignIndex(): View
    {
        $roles = Role::orderBy('name')->get();
        $admins = Admin::orderBy('name')->get();

        return $this->view('admin.roles.assign', compact('roles', 'admins'));
    }

    public function assignRoles(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'admin_id' => ['required', 'integer', 'exists:admins,id'],
            'roles' => ['nullable', 'array'],
        ]);

        $admin = Admin::findOrFail($data['admin_id']);
        $admin->syncRoles($data['roles'] ?? []);

        return back()->with('success', 'Roles assigned.');
    }
}
