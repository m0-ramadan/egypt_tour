<?php

namespace  App\Http\Repositories\Admin;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Model;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\SliderInterface;
use App\Http\Traits\ImageTrait;

class   SliderRepository  implements  SliderInterface{

    use ImageTrait ;
    protected $sliderModel;
    public function __construct( Slider $slider)
    {
        $this->sliderModel=$slider;
    }
    public function  index(){

        $sliders= $this->sliderModel::orderBy('item_order')->get();

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
        'type' => $request->type,
        'image'=> $image,
        'item_order' => $request->item_order,
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
            'item_order' => $request->item_order,
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
