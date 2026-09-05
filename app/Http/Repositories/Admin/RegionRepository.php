<?php

namespace App\Http\Repositories\Admin;


use App\Models\Region;
use App\Models\Country;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\RegionInterface;

class RegionRepository   implements  RegionInterface
{

    protected $regionModel;
      protected $countryModel;
    public function __construct( Region $region,Country $country)
    {
        $this->regionModel=$region;
         $this->countryModel=$country;
    }
    public function  index(){

        $regions= $this->regionModel::get();
        return view('Admin.region.index',compact('regions'));
    }

    public function create(){
         $countries= $this->countryModel::get();
        return view('Admin.region.create',compact('countries'));
    }

    public function store($request){
    $this->regionModel::create([
        'region'=> $request->region,
        'region_ar'=>$request->region_ar,
        'country_id'=>$request->country
        
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.region.index');
    }

    public function edit($id){
         $countries= $this->countryModel::get();
        $region=$this->regionModel::find($id);
         return view('Admin.region.edit',compact('region','countries'));
    }

    public function update($request){

        $region=$this->regionModel::find($request->id);
        $region->update([
             'region'=> $request->region,
             'region_ar'=>$request->region_ar,
             'country_id'=>$request->country
           ]);
           
           
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.region.index');
    }

    public function destroy($id){
        $region=$this->regionModel::find($id);
        if($region){
            $region->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.region.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.region.index');

    }
}
