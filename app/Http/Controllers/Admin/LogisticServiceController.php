<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Flasher\Toastr\Laravel\Facade\Toastr;
use App\Traits\ImageTrait;

class LogisticServiceController extends Controller
{

        use ImageTrait ;

  
    public function index(){
        $logisticservices= Service::whereNotNull('parent_id')->get();
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

 
        $this->Service::create([
     'title'=> $request->title,
     'description' =>$request->description,
      'image'=> $image,
     ]);
    toastr::success('success','sucessfully added');
    return redirect()->route('admin.logisticservice.index');
    }

    public function edit($id){
        $logisticservice=Service::find($id);
        return view('Admin.logisticservice.edit',compact('logisticservice'));
    }

    public function update(Request $request){

     

        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $image = $this->uploadImg($request->image, $filename, 'services');
        //    $image = 'images/slider/' . $image;
        }
  
        $logisticservice=Service::find($request->id);
        $logisticservice->update([
            'name'=> $request->title,
            'description' =>$request->description,
             'image'=> $image ?? $logisticservice->image,
            ]);
           toastr::success('success','sucessfully updated');
           return redirect()->route('admin.logisticservice.index');
         
    }

    public function destroy($id){
        return  $this->logisticServiceInterface->destroy($id);
    }
}

