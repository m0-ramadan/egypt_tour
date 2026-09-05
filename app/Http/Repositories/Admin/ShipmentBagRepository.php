<?php

namespace App\Http\Repositories\Admin;

use App\Models\ShipmentBag;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\ShipmentBagInterface;

Class  ShipmentBagRepository   implements ShipmentBagInterface{

    protected $shipmentBagModel;
    public function __construct(ShipmentBag $shipmentBag)
    {
        $this->shipmentBagModel=$shipmentBag;
    }
    public function  index(){

        $shipmentbags= $this->shipmentBagModel::with('shipment')->get();
        return view('Admin.shipmentbag.index',compact('shipmentbags'));
    }


    public function destroy($id){
        $shipmentBag=$this->shipmentBagModel::find($id);
        if($shipmentBag){
            $shipmentBag->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.shipmentBag.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.shipmentBag.index');

    }

}
