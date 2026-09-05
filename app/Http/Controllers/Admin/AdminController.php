<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $admins = Admin::query()
            ->when($request->filled('q'), fn($q) => $q->where('name', 'like', '%' . $request->string('q') . '%')
                ->orWhere('email', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        $roles = Role::all();
        return $this->view('admin.admins.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $admin = Admin::create($data);

        if (!empty($data['role_id'])) {
            $admin->assignRole($data['role_id']);
        }
        return $this->success('admin.admins.index', 'Admin created.');
    }

    public function show(Admin $admin): View
    {
        return $this->view('admin.admins.show', compact('admin'));
    }

    public function edit(Admin $admin): View
    {
        return $this->view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email,' . $admin->id],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return $this->success('admin.admins.index', 'Admin updated.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $admin->delete();

        return $this->success('admin.admins.index', 'Admin deleted.');
    }

    public function checkEmail(Request $request): JsonResponse
    {
        return response()->json([
            'exists' => Admin::where('email', $request->input('email'))->exists(),
        ]);
    }

    public function toggleStatus(Admin $admin): RedirectResponse
    {
        $admin->update(['is_active' => ! (bool) $admin->is_active]);

        return back()->with('success', 'Status updated.');
    }

    public function resetPassword(Admin $admin): RedirectResponse
    {
        $password = Str::random(10);
        $admin->update(['password' => Hash::make($password)]);

        return back()->with('success', 'Temporary password: ' . $password);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        Admin::whereIn('id', (array) $request->input('ids', []))->delete();

        return back()->with('success', 'Selected admins deleted.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        Admin::whereIn('id', (array) $request->input('ids', []))
            ->update(['is_active' => (bool) $request->boolean('status')]);

        return back()->with('success', 'Selected admins updated.');
    }

    public function export()
    {
        return response()->json(Admin::select('id', 'name', 'email', 'phone', 'created_at')->get());
    }
}
