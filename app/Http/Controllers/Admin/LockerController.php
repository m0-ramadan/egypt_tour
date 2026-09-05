<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Branchs;
use App\Models\Region;
use Flasher\Toastr\Prime\ToastrInterface;
use Flasher\Toastr\Laravel\Facade\Toastr;
use App\Models\Country;
use App\Models\Vault;
 
class LockerController extends Controller
{

 
  

 
    public function index(){
        $lockers=Vault::orderBy('created_at', 'desc')->get();
        return view('Admin.lockers.index',compact('lockers'));
    }


    public function create(){
 
        $branchs= Branchs::get();
        return view('Admin.lockers.create',compact('branchs'));
    }

    public function store(Request $request){
        Vault::create([
            'name'         => $request->name,
            'balance'       => $request->balance,
            'currency'       => $request->currency,
            'branch_id'      => $request->branch_id,  
        ]);

       
        toastr::success('success','sucessfully added');
        return redirect()->route('admin.lockers.index');
    }

    public function edit($id){
        $locker=Vault::find($id);
        $branchs= Branchs::get();
        return view('Admin.lockers.edit',compact( 'locker','branchs'));
    }

    public function update(Request $request)
    {
        $branch = Vault::find($request->id); // تأكد أنك جلبت الفرع الموجود
        
         
        $branch->update([
            'name'         => $request->name,
            'balance'       => $request->balance,
            'currency'       => $request->currency,
            'branch_id'      => $request->branch_id,
            
        ]);
 
        toastr()->success('Success', 'Successfully updated');
    return redirect()->route('admin.lockers.index');
    }
    

    public function destroy($id){
        $branchs=Vault::find($id);
        if($branchs){
            $branchs->delete();
             Toastr::addSuccess('تم التحديث بنجاح');
                return redirect()->route('admin.lockers.index');
        }
            toastr()->success('not found');
            return redirect()->route('admin.lockers.index');
       
    }
 
}
