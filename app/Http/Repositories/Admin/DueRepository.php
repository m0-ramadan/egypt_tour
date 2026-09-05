<?php

namespace   App\Http\Repositories\Admin;

use Carbon\Carbon;
use App\Models\UserDue;
use App\Http\Traits\ImageTrait;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Traits\SendNotificationTrait;
use App\Http\Interfaces\Admin\DueInterface;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserNotify;

class DueRepository   implements    DueInterface{
use ImageTrait;
use SendNotificationTrait;
    protected $userDueModel;
    public function __construct(UserDue $userdue)
    {
        $this->userDueModel =$userdue;

    }

    public function index(){
        $dues=$this->userDueModel::get();
        return view('Admin.due.index',compact('dues'));
    }
    public function edit($id){
     $due=$this->userDueModel::find($id);
      return view('Admin.due.edit',compact('due'));
    }
    
    public function reject($id){
        $due=$this->userDueModel::find($id);

        $due->update([
            'status' => 2
        ]);
       $this->walletNotify('تم رفض تحويل المبلغ المراد سحبه  ', $due->amount,$due->user_id);
        
        Alert::success('success','due sucessfully rejected');
        return redirect()->back();
    }

    public function update($request){

        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->extension();
            $imagename =  $this->uploadImage($request->image, $filename, 'due');
            $image='images/due/'.$imagename;

        }
        $due=$this->userDueModel::find($request->id);


        $due->update([
            'reference_number'=> $request->reference_number,
            'image'=> $image ?? $due->image,
            'status'=> 1,
            'transfer_date'       => Carbon::now(),
           ]);
           $due->user->update([
            'wallet' =>($due->user->wallet - $due->amount)
           ]);

           $this->walletNotify('تم تحويل المبلغ المراد سحبة  بنجاح ', $due->amount,$due->user_id);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.due.index');
    }


}
