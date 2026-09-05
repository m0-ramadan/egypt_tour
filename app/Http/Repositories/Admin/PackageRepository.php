<?php

namespace App\Http\Repositories\Admin;

use App\Models\Package;
use App\Models\Setting;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\PackageInterface;

Class  PackageRepository   implements PackageInterface{

    protected $packageModel;
    public function __construct(Package $package)
    {
        $this->packageModel=$package;
    }
    public function  index(){
      $packages= $this->packageModel::get();
            $status= Setting::first();
        return view('Admin.package.index',compact('packages','status'));
    }
        public function  status(){

        $status= Setting::first();
          
         if($status->packagestatus==1){
              $statu=Setting::find(1);
        $statu->update([
            'packagestatus'=> 0,
           ]);
    }
    
             if($status->packagestatus==0){
              $statu=Setting::find(1);
        $statu->update([
            'packagestatus'=> 1,
           ]);
    }
         
        Alert::success('success','sucessfully added');
    return redirect()->route('admin.package.index');
    }

    public function create(){
        return view('Admin.package.create');
    }

    public function store($request){
    $this->packageModel::create([
     'name'=> $request->name,
     'price'  => $request->price,
     'number' =>$request->number,
     'weight'=>$request->weight,
     'description'=>$request->description,
      'status'=>1,
     'unit'=> 0,
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.package.index');
    }

    public function edit($id){
        $package=$this->packageModel::find($id);
         return view('Admin.package.edit',compact('package'));
    }

    public function update($request){
    
     
        $package=$this->packageModel::find($request->id);
        $package->update([
            'name'=> $request->name,
            'price'  => $request->price,
            'number' =>$request->number,
            'unit'=> 0,
            'weight'=>$request->weight,
            'description'=>$request->description,
              'status'=> (($request->status) ? 1 : 0)
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.package.index');
    }

    public function destroy($id){
        $package=$this->packageModel::find($id);
        if($package){
            $package->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.package.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.package.index');

    }

}
