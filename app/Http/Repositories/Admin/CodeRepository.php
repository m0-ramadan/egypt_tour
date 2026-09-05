<?php

namespace  App\Http\Repositories\Admin;

use App\Models\Code;
use App\Models\ShipmentCompany;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\CodeInterface;


class CodeRepository  implements   CodeInterface  {
    protected $codeModel;
    protected $companyModel;
    public function __construct( Code $code,ShipmentCompany $company)
    {
        $this->codeModel=$code;
        $this->companyModel=$company;
    }
    public function  index(){

        $codes= $this->codeModel::get();
        return view('Admin.code.index',compact('codes'));
    }

    public function create(){
        $companies=$this->companyModel::get();
        return view('Admin.code.create',compact('companies'));
    }

    public function store($request){



    $this->codeModel::create([
        'shipment_company_id'=>$request->company,
        'code'=> $request->code,
        'discount'  => $request->discount,
        'type' =>$request->type,
        'from'=>$request->from,
        'to'  => $request->to,
        'time' => $request->time,

    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.code.index');
    }

    public function edit($id){
        $code=$this->codeModel::find($id);
        $companies=$this->companyModel::get();
         return view('Admin.code.edit',compact('code','companies'));
    }

    public function update($request){

        $code=$this->codeModel::find($request->id);
        $code->update([
            'shipment_company_id'=>$request->company,
            'code'=> $request->code,
            'discount'  => $request->discount,
            'type' =>$request->type,
            'from'=>$request->from,
            'to'  => $request->to,
            'time' => $request->time,

           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.code.index');
    }

    public function destroy($id){
        $code=$this->codeModel::find($id);
        if($code){
            $code->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.code.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.code.index');

    }
}
