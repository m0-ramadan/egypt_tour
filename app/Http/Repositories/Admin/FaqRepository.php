<?php

namespace App\Http\Repositories\Admin;

use App\Http\Interfaces\Admin\FaqInterface;
use App\Models\Faq;
use RealRashid\SweetAlert\Facades\Alert;

class FaqRepository implements FaqInterface{

     protected $faqModel;
    public function __construct(Faq $faq)
    {
        $this->faqModel=$faq;
    }
    public function  index(){

        $faqs= $this->faqModel::orderBy('item_order')->get();
        return view('Admin.faq.index',compact('faqs'));
    }

    public function create(){
        return view('Admin.faq.create');
    }

    public function store($request){
    $this->faqModel::create([
     'question'=> $request->question,
     'answer'  => $request->answer,
     'item_order' => $request->item_order,
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.faq.index');
    }

    public function edit($id){
        $faq=$this->faqModel::find($id);
    return view('Admin.faq.edit',compact('faq'));
    }

    public function update($request){
        $faq=$this->faqModel::find($request->id);
        $faq->update([
            'question'=> $request->question,
            'answer'  => $request->answer,
            'item_order' => $request->item_order,
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.faq.index');
    }

    public function destroy($id){
        $faq=$this->faqModel::find($id);
        if($faq){
            $faq->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.faq.index');
        }
        Alert::error('error','not found');
            return redirect()->route('admin.faq.index');

    }

}
