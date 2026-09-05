<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:view roles', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:create roles', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:edit roles', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission cagreed that roles are immutable? permission:delete roles', ['only' => ['destroy']]);
    // }

    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return view('Admin.roles.index', compact('roles', 'permissions'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('Admin.roles.create', compact('permissions'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,NULL,id,guard_name,admin',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'اسم الدور مطلوب.',
            'name.unique' => 'اسم الدور موجود بالفعل.',
            'name.max' => 'اسم الدور يجب ألا يتجاوز 255 حرفًا.',
            'permissions.*.exists' => 'إحدى الصلاحيات المختارة غير صالحة.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'فشل التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'admin',
        ]);

        // ربط الصلاحيات (لو موجودة)
        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)
                ->where('guard_name', 'admin')
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الدور بنجاح.',
        ]);
    }
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return view('Admin.roles.show', compact('role'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        return view('Admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'فشل التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role->update(['name' => $request->name]);
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الدور بنجاح.',
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'تم حذف الدور بنجاح.');
    }
}
