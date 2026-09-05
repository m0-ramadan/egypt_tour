<?php

namespace App\Http\Controllers\Admin;

use App\Models\Region;
use App\Models\Branchs;
use App\Models\Transfer;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    //     public function __construct()
    // {
    //     // Ensure auth:admin middleware is applied
    //     $this->middleware('auth:admin');
    //     // Apply Spatie permission middleware
    //     $this->middleware('permission:عرض موظفي التحويلات', ['only' => ['index']]);
    //     $this->middleware('permission:إضافة موظف تحويلات', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:تعديل موظفي التحويلات', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission:حذف موظفي التحويلات', ['only' => ['destroy']]);
    // }

    /**
     * Display a listing of transfers.
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['parent', 'branch', 'region']);
        $query->where('type', 2);
        $transfers = $query->get();
        return view('Admin.employee.index', compact('transfers'));
    }


    /**
     * Show the form for creating a new transfer.
     */
    public function create()
    {
        $countries = Country::get();
        $branches = Branchs::all();
        $regions = Region::all();
        $parents = Transfer::where('type', 1)->get(); // Admins or employees as parents

        return view('Admin.employee.create', compact('branches', 'countries', 'parents'));
    }

    /**
     * Store a newly created transfer in storage.
     */
    public function store(Request $request)
    {

        //  dd("dd");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:transfers,email',
            'phone' => 'required|string|max:255|unique:transfers,phone',
            'password' => 'required|string|min:8|confirmed',
            'code' => 'nullable|string|max:255',
            'type' => 'required|integer|in:1,2,3',
            'parent_id' => 'nullable|integer|exists:transfers,id',
            'branch_id' => 'nullable|integer|exists:branchss,id',
        ]);

        $parent_id = Transfer::where('parent_id', $request->parent_id)->first();

        Transfer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'code' => $validated['code'],
            'type' => $validated['type'],
            'parent_id' => $parent_id->parent_id,
            'branch_id' => $validated['branch_id'],
            'city_id' => $request->regions_id,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', __('messages.created_successfully'));
    }

    /**
     * Show the form for editing the specified transfer.
     */
    public function edit($id)
    {
        $transfer = Transfer::findOrFail($id);
        $branches = Branchs::all();
        $regions = Region::all();
        $countries = Country::all();
        $parents = Transfer::where('type', 1)->where('id', '!=', $id)->get(); // Exclude self

        return view('Admin.employee.edit', compact('transfer', 'branches', 'regions', 'parents', 'countries'));
    }

    /**
     * Update the specified transfer in storage.
     */
    public function update(Request $request, $id)
    {
        $transfer = Transfer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:transfers,email,' . $id,
            'phone' => 'required|string|max:255|unique:transfers,phone,' . $id,
            'password' => 'nullable|string|min: Hungary|confirmed',
            'code' => 'nullable|string|max:255',
            'type' => 'required|integer|in:1,2,3',
            'parent_id' => 'nullable|integer|exists:transfers,id',
            'branch_id' => 'nullable|integer|exists:branchss,id',
            'city_id' => 'nullable|integer|exists:regions,id',
        ]);

        $branch_id = Transfer::first($request->parent_id);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'],
            'branch_id' => $branch_id->branch_id,
            'city_id' => $validated['city_id'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $transfer->update($data);

        return redirect()->route('admin.employees.index')
            ->with('success', __('messages.updated_successfully'));
    }

    /**
     * Remove the specified transfer from storage.
     */
    public function destroy($id)
    {
        $transfer = Transfer::findOrFail($id);

        try {

            $transfer->delete();
            return redirect()->route('admin.employees.index')
                ->with('success', 'تم حذف الموظف بنجاح');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.employees.index')
                ->with('error', __('messages.delete_error_employee'));
        }
    }


    public function getRegions($country_id)
    {
        $regions = Region::where('country_id', $country_id)->get();
        return response()->json($regions);
    }


}