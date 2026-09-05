<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
 
use App\Models\Note;
use Flasher\Toastr\Laravel\Facade\Toastr;
use App\Models\ContactUs;

class NoteController extends Controller
{
   
    public function index(){
        $listcontactinfo= Note::get();
       
        return view('Admin.notes.index',compact('listcontactinfo'));
       
    }


    public function create(){
         return view('Admin.notes.create');
    }

    public function store(Request $request){
       
        Note::create([
            'text'=> $request->text,
       ]);
       
         toastr()->success('success','sucessfully added');
       return redirect()->route('admin.notes.index');
    }

    public function edit($id){
         $note=Note::find($id);
         return view('Admin.notes.edit',compact('note'));
    }

    public function update(Request $request){
        $country= Note::find($request->id);
        $country->update([
            'txt'=> $request->txt,
           ]);
           toastr()->success('success','sucessfully updated');
       return redirect()->route('admin.notes.index');


    }

    public function destroy($id){

        $country=Note::find($id);
        if($country){
            $country->delete();
          Toastr::addSuccess('Deleted successfully');
            return redirect()->route('admin.notes.index');
        }
            toastr()->success('not found');
            return redirect()->route('admin.notes.index');
         
    }

}
