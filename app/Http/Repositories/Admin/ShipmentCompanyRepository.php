<?php

namespace  App\Http\Repositories\Admin;

use App\Models\ShipmentCompany;
use Illuminate\Database\Eloquent\Model;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\ShipmentCompanyInterface;
use App\Http\Traits\ImageTrait;

class   ShipmentCompanyRepository  implements  ShipmentCompanyInterface{

    use ImageTrait ;
    protected $shipmentCompanyModel;
    public function __construct( ShipmentCompany $shipmentcompany)
    {
        $this->shipmentCompanyModel=$shipmentcompany;
    }
    public function  index(){

        $shipmentcompanies= $this->shipmentCompanyModel::get();
        return view('Admin.shipmentcompany.index',compact('shipmentcompanies'));
    }

    public function create(){
        return view('Admin.shipmentcompany.create');
    }

    public function store($request){


        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'shipmentcompany',);
            $image='images/shipmentcompany/'.$imagename;

        }
    $this->shipmentCompanyModel::create([
     'name'=> $request->name,
     'image'=> $image
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.shipmentcompany.index');
    }

    public function edit($id){
        $shipmentcompany=$this->shipmentCompanyModel::find($id);
         return view('Admin.shipmentcompany.edit',compact('shipmentcompany'));
    }

    public function update($request){
      
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'shipmentcompany',);
            $image='images/shipmentcompany/'.$imagename;

        }
        $shipmentcompany=$this->shipmentCompanyModel::find($request->id);
        $shipmentcompany->update([
            'name'=> $request->name,
            'image'=> $image ?? $shipmentcompany->image,
            'status'=> (($request->status) ? 1 : 0)
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.shipmentcompany.index');
    }

    public function destroy($id){
        $shipmentcompany=$this->shipmentCompanyModel::find($id);
        if($shipmentcompany){
            $shipmentcompany->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.shipmentcompany.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.shipmentcompany.index');

    }
}
