<?php

namespace App\Http\Repositories\Admin;

use App\Models\Shipment;
use App\Models\Country;
use App\Models\Region;

use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\ShipmentInterface;
use App\Models\User;
use App\Http\Traits\ImageTrait;
use Illuminate\Support\Facades\Storage;


Class  ShipmentRepository   implements ShipmentInterface{
    use ImageTrait;

    protected $shipmentModel;
    public function __construct(Shipment $shipment)
    {
        $this->shipmentModel=$shipment;
    }

    public function  index($request){
        $status = $request->status;
        $shipments= $this->shipmentModel::when($request->status, function ($query) use($request){
                        return $query->where('status', $request->status);
                    }, function ($query) use($request){
                        return $query->where('status', 1);
                    })->with('company')->get();
        return view('Admin.shipment.index',compact('shipments', 'status'));
    }


    public function show($id){
        $shipment=$this->shipmentModel::where('id',$id)->with('shipmentDetail','company')->first();


        return view('Admin.shipment.show',compact('shipment'));
    }

    public function edit($id)
     {
        $countries=Country::orderBy('item_order')->get();
        $regions=Region::get();
        $shipment= $this->shipmentModel::with('shipmentDetail', 'company')->find($id);
        return view('Admin.shipment.edit',compact('shipment','regions','countries'));
     }

    public function update( $request, Shipment $shipment)
    {
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'shipment', $shipment->image);
            $image='images/shipment/'.$imagename;
        }

        $shipment->update([
            'image'    => $image  ?? $shipment->image,
        ] + $request->validated());
        $shipment->shipmentDetail()->update([
            "size" => $request->size,
            "box_number" => $request->box_number,
            "description" => $request->description,
            "price" => $request->price,
        ]);

        Alert::success('success','sucessfully updated');
        return redirect()->back();
    }

    public function destroy($id){
        $shipment=$this->shipmentModel::find($id);
        if($shipment){
            $shipment->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->back();
        }
            Alert::error('error','not found');
            return redirect()->back();

    }

    public function cancelledShipments(){
        $shipments= $this->shipmentModel::where([['status','=',4],['activity','!=','cost returned']])->with('company')->get();
        return view('Admin.shipment.cancelled',compact('shipments'));
    }

    public function returnCost($id){
        $shipment= $this->shipmentModel::find($id);
        $user=User::find($shipment->user_id);
        if($shipment->shipmentDetail->cash == 1){
            $user->subscribe->update([
                'remain'=>  $user->subscribe->remain +$shipment->shipmentDetail->size
             ]);
             $shipment->update([
                'cost_returend' => 1
             ]);

        }else{

         $user->update([
            'wallet'=> $user->wallet +$shipment->shipmentDetail->price
         ]);
         $shipment->update([
            'cost_returend' => 1
         ]);
        }
        Alert::success('success','sucessfully returned');
        return redirect()->back();


    }

}
