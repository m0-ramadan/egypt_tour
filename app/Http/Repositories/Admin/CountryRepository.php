<?php

namespace App\Http\Repositories\Admin;

use App\Events\ChangeOrder;
use App\Models\Country;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\CountryInterface;
use App\Models\Region;

class CountryRepository   implements  CountryInterface
{

    protected $countryModel;
    protected $regionModel;
    public function __construct( Country $country,Region $region)
    {
        $this->countryModel=$country;
        $this->regionModel=$region;
    }
    public function  index(){
        $countries= $this->countryModel::orderBy('item_order')->get();
        return view('Admin.country.index',compact('countries'));
    }

    public function create(){
        $regions= $this->regionModel::get();
        return view('Admin.country.create',compact('regions'));
    }

    public function store($request){
        
        event(new ChangeOrder($this->countryModel, $request->item_order));
        
        $this->countryModel::create([
             'country'=> $request->country,
              'country_ar'=>$request->country_ar,
             'item_order'=>$request->item_order,
        ]);
        Alert::success('success','sucessfully added');
        return redirect()->route('admin.country.index');
    }

    public function edit($id){
        $country=$this->countryModel::find($id);
        $regions= $this->regionModel::get();
         return view('Admin.country.edit',compact('country','regions'));
    }

    public function update($request){

        $country=$this->countryModel::find($request->id);
        if($request->item_order != $country->item_order)
        {
            event(new ChangeOrder($this->countryModel, $request->item_order));
        }
        
        $country->update([
            'country'=> $request->country,
            'country_ar'=>$request->country_ar,
             'item_order'=>$request->item_order,

           ]);
       Alert::success('success','sucessfully updated');
       return redirect()->route('admin.country.index');
    }

    public function destroy($id){
        $country=$this->countryModel::find($id);
        if($country){
            $country->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.country.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.country.index');

    }
}
