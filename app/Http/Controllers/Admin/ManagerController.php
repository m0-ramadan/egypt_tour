<?php

namespace App\Http\Controllers\Admin;

use App\Models\Region;
use App\Models\Branchs;
use App\Models\Transfer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Flasher\Toastr\Prime\ToastrInterface;
use Flasher\Toastr\Laravel\Facade\Toastr;
use Illuminate\Support\Str;

class ManagerController extends Controller
{
    /**
     * Display a listing of transfers.
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['parent', 'branch', 'region']);
        $query->where('type', 1);
        $transfers = $query->get();
        return view('Admin.manager.index', compact('transfers'));
    }

    /**
     * Show the form for creating a new transfer.
     */
    public function create()
    {
        $branches = Branchs::all();
        $regions = Region::all();
        $parents = Transfer::whereIn('type', [1, 2])->get(); // Admins or managers as parents

        return view('Admin.manager.create', compact('branches', 'regions', 'parents'));
    }

    /**
     * Store a newly created transfer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:transfers,email',
            'phone' => 'required|string|max:255|unique:transfers,phone',
            'password' => 'required|string|min:6',
             'branch_id' => 'nullable|integer|exists:branchss,id',
        ]);
        $branch = Branchs::findOrFail($request->branch_id);
     
        Transfer::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'phone'       => $validated['phone'],
            'password'    => Hash::make($validated['password']),
            'code'        =>  mt_rand(100000, 999999),
            'branch_id'   => $validated['branch_id'],
            'city_id'     => $branch->region_id,
            'permissions' => $request->role,
         ]);
         
      

         Toastr::addSuccess('success','تمت الاضافة بنجاح');
         return redirect()->route('admin.managers.index');
 
    }

    /**
     * Show the form for editing the specified transfer.
     */
    public function edit($id)
    {
        $transfer = Transfer::findOrFail($id);
        $branches = Branchs::all();
        $regions = Region::all();
        $parents = Transfer::whereIn('type', [1, 2])->where('id', '!=', $id)->get(); // Exclude self

        return view('Admin.manager.edit', compact('transfer', 'branches', 'regions', 'parents'));
    }

    /**
     * Update the specified transfer in storage.
     */
    
     public function update(Request $request)
     {
         $transfer = Transfer::findOrFail($request->id);
     
         $validated = $request->validate([
             'name'      => 'required|string|max:255',
             'email'     => 'required|string|email|max:255|unique:transfers,email,' . $transfer->id,
             'phone'     => 'required|string|max:255|unique:transfers,phone,' . $transfer->id,
             'password'  => 'nullable|string|min:6',
             'branch_id' => 'nullable|integer|exists:branchss,id',
         ]);
     
         $branch = null;
         if ($request->filled('branch_id')) {
             $branch = Branchs::findOrFail($request->branch_id);
         }
     if($request->account_type==2){$type=1;$premission=0;}
     if($request->account_type==1){$type=1;$premission=1;}
     if($request->account_type==3){$type=2;$premission=0;}
         $data = [
             'name'        => $validated['name'],
             'email'       => $validated['email'],
             'phone'       => $validated['phone'],
             'branch_id'   => $validated['branch_id'],
             'city_id'     => $branch ? $branch->region_id : null,
             'type'        =>$type,
             'permissions'  =>$premission
         ];
   
         // فقط إذا تم إرسال كلمة مرور جديدة
         if (!empty($validated['password'])) {
             $data['password'] = Hash::make($validated['password']);
         }
     
         $transfer->update($data);
     
         Toastr::addSuccess('تم التعديل بنجاح');
         return redirect()->route('admin.managers.index');
     }
     


    /**
     * Remove the specified transfer from storage.
     */
    public function destroy($id)
    {
        $transfer = Transfer::findOrFail($id);
 
         
        $transfer->delete();
        toastr::success('success', __('messages.deleted_successfully'));
        return redirect()->route('admin.managers.index');
     }
}