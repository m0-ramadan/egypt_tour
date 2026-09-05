<?php

namespace App\Http\Repositories\Admin;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\ContactInterface;

Class   ContactRepository  implements   ContactInterface
{


    protected $contactModel;
    public function __construct(Contact $contactmodel)
    {

        $this->contactModel=$contactmodel;
    }

    public function index(){
        $contacts= $this->contactModel::get();
        return view('Admin.contact.index',compact('contacts'));
    }

  public function read($id){
    $contact=$this->contactModel::find($id);
    $contact->update([
        'status'=> 1
    ]);
    Alert::success('success', 'contact deleted successfully');
    return redirect()->route('admin.contact.index');
  }

    public function destroy($id){
        $about= $this->contactModel::find($id);

        if($about){

            $about->update([
                'status'=>1
            ]);

        }
        Alert::success('success', 'contact deleted successfully');
          return redirect()->route('admin.contact.index');

    }

}
