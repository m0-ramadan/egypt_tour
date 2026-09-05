<?php

namespace  App\Http\Repositories\Admin;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\ProductInterface;
use App\Http\Traits\ImageTrait;

class   ProductRepository  implements  ProductInterface{

    use ImageTrait ;
    protected $productModel;
    public function __construct( Product $product)
    {
        $this->productModel=$product;
    }
    public function  index(){

        $products= $this->productModel::get();
        return view('Admin.product.index',compact('products'));
    }

    public function create(){
        return view('Admin.product.create');
    }

    public function store($request){


        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'product',);
            $image='images/product/'.$imagename;

        }
    $this->productModel::create([
     'name'=> $request->name,
     'price'  => $request->price,
     'description' =>$request->description,
     'details'=>$request->details,
     'image'=> $image
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.product.index');
    }

    public function edit($id){
        $product=$this->productModel::find($id);
         return view('Admin.product.edit',compact('product'));
    }

    public function update($request){
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'product',);
            $image='images/product/'.$imagename;

        }
        $product=$this->productModel::find($request->id);
        $product->update([
            'name'=> $request->name,
            'price'  => $request->price,
            'description' =>$request->description,
            'details'=>$request->details,
            'image'=> $image ?? $product->image
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.product.index');
    }

    public function destroy($id){
        $product=$this->productModel::find($id);
        if($product){
            $product->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.product.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.product.index');

    }
}
