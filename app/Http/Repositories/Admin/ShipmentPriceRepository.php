<?php

namespace App\Http\Repositories\Admin;

use App\Models\ShipmentPrice;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\ShipmentPriceInterface;
use App\Models\ShipmentCompany;

class ShipmentPriceRepository  implements   ShipmentPriceInterface{

 protected $shipmentPriceModel;
 public function __construct(ShipmentPrice $shipmentPrice)
 {
   $this->shipmentPriceModel =$shipmentPrice;
 }

 public function index(){
    $shipmentprices =$this->shipmentPriceModel::all();
    return view('Admin.shipmentprice.index',compact('shipmentprices'));
 }
 public function create(){
    $companies =ShipmentCompany::get();
    return view('Admin.shipmentprice.create',compact('companies'));
}

public function store($request){

$company= $this->shipmentPriceModel::where('shipment_company_id',$request->company)->first();

if($company){
    Alert::success('success','company already exists');
    return redirect()->back();
}else{
$this->shipmentPriceModel::create([
    'shipment_company_id'=>$request->company,
    'weight'=> $request->weight,
    'price'  => $request->price,
    'tax' =>$request->tax,
    'currency'=>$request->currency,
    'increase'=> $request->increase
]);
Alert::success('success','sucessfully added');
return redirect()->route('admin.shipmentprice.index');
}

}
 public function edit($id)
 {
    $price= $this->shipmentPriceModel::find($id);
  return view('Admin.shipmentprice.edit',compact('price'));
 }

 public function update($request)
 {
    $shipmentprice=$this->shipmentPriceModel::find($request->id);
    $shipmentprice->update([
        'weight'=> $request->weight,
        'price'  => $request->price,
        'tax' =>$request->tax,
        'currency'=>$request->currency,
        'increase'=> $request->increase
       ]);
       Alert::success('success','sucessfully updated');
       return redirect()->back();

 }
}
