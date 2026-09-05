<?php

namespace App\Http\Repositories\Admin;

use App\Http\Traits\ImageTrait;
use App\Models\LogisticService;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\LogisticServiceInterface;

class LogisticServiceRepository   implements  LogisticServiceInterface
{
    use ImageTrait ;
    protected $logisticServiceModel;
    public function __construct( LogisticService $logisticservice)
    {
        $this->logisticServiceModel=$logisticservice;
    }
    public function  index(){

        $logisticservices= $this->logisticServiceModel::get();
        return view('Admin.logisticservice.index',compact('logisticservices'));
    }

    public function create(){
        return view('Admin.logisticservice.create');
    }

    public function store($request){


        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'logisticservice',);
            $image='images/logisticservice/'.$imagename;

        }

        if ($request->hasFile('detail_image')) {
            $filename = time() . '.' . $request->detail_image->extension();
            $detail_imagename =  $this->uploadImage($request->detail_image, $filename, 'logisticservice',);
            $detail_image='images/logisticservice/'.$detail_imagename;

        }
    $this->logisticServiceModel::create([
     'title'=> $request->title,
     'description' =>$request->description,
     'details'=>$request->details,
     'image'=> $image,
     'detail_image'=> $detail_image
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.logisticservice.index');
    }

    public function edit($id){
        $logisticservice=$this->logisticServiceModel::find($id);
         return view('Admin.logisticservice.edit',compact('logisticservice'));
    }

    public function update($request){
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'logisticservice',);
            $image='images/logisticservice/'.$imagename;

        }
        if ($request->hasFile('detail_image')) {
            $filename = time() . '.' . $request->detail_image->extension();
            $detail_imagename =  $this->uploadImage($request->detail_image, $filename, 'logisticservice',);
            $detail_image='images/logisticservice/'.$detail_imagename;

        }
        $logisticservice=$this->logisticServiceModel::find($request->id);
        $logisticservice->update([
            'title'=> $request->title,
            'description' =>$request->description,
            'details'=>$request->details,
            'image'=> $image ?? $logisticservice->image,
            'detail_image'=> $detail_image ?? $logisticservice->detail_image
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.logisticservice.index');
    }

    public function destroy($id){
        $logisticservice=$this->logisticServiceModel::find($id);
        if($logisticservice){
            $logisticservice->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.logisticservice.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.logisticservice.index');

    }
}
