<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate($this->perPage($request));

        return $this->view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return $this->view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create($data);

        return $this->success('admin.users.index', 'User created.');
    }

    public function show(User $user): View
    {
        return $this->view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return $this->view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->update($data);

        return $this->success('admin.users.index', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return $this->success('admin.users.index', 'User deleted.');
    }

    public function export()
    {
        return response()->json(User::latest()->get());
    }

    public function getStats()
    {
        return response()->json([
            'total' => User::count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! (bool) ($user->is_active ?? true)]);
        return back()->with('success', 'User status updated.');
    }

    public function activities(User $user): View
    {
        $activities = method_exists($user, 'activities') ? $user->activities()->latest()->paginate(20) : collect();
        return $this->view('admin.users.activities', compact('user', 'activities'));
    }

}
