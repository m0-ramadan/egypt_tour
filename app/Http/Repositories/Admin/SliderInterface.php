<?php

namespace  App\Http\Repositories\Admin;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Model;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\sliderInterface;
use App\Http\Traits\ImageTrait;

class   SliderRepository  implements  SliderInterface{

    use ImageTrait ;
    protected $sliderModel;
    public function __construct( Slider $slider)
    {
        $this->sliderModel=$slider;
    }
    public function  index(){

        $sliders= $this->sliderModel::get();
        return view('Admin.slider.index',compact('sliders'));
    }

    public function create(){
        return view('Admin.slider.create');
    }

    public function store($request){

        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'slider');
            $image='images/slider/'.$imagename;

        }
    $this->sliderModel::create([
     'name'=> $request->name,
     'price'  => $request->price,
     'description' =>$request->description,
     'details'=>$request->details,
     'image'=> $image,
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.slider.index');
    }

    public function edit($id){
        $slider=$this->sliderModel::find($id);
         return view('Admin.slider.edit',compact('slider'));
    }

    public function update($request){
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'slider',);
            $image='images/slider/'.$imagename;

        }
        $slider=$this->sliderModel::find($request->id);
        $slider->update([
            'name'=> $request->name,
            'price'  => $request->price,
            'description' =>$request->description,
            'details'=>$request->details,
            'image'=> $image ?? $slider->image
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.slider.index');
    }

    public function destroy($id){
        $slider=$this->sliderModel::find($id);
        if($slider){
            $slider->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.slider.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.slider.index');

    }
}
