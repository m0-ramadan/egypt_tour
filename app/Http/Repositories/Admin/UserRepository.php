<?php

namespace App\Http\Repositories\Admin;

use App\Models\User;
use Carbon\Carbon;
use App\Notifications\UserNotify;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\UserInterface;
use App\Models\Package;
use App\Models\Subscribe;
use Illuminate\Support\Facades\Notification;


Class  UserRepository   implements UserInterface{

    protected $userModel;
    public function __construct(User $user)
    {
        $this->userModel=$user;
    }
    public function  index(){

        $users= $this->userModel::get();
        return view('Admin.user.index',compact('users'));
    }

    public function show($id){
        $user= $this->userModel::with('userDue','subscribes', 'shipments', 'WalletDetails', 'adminWallets')->find($id);
        $packages = Package::get();
        return view('Admin.user.show',compact('user', 'packages'));
    }

    public function create(){
        return view('Admin.user.create');
    }

    public function store($request){
    $this->userModel::create([
        'name'=> $request->name,
        'email'=>$request->email,
        'phone' =>$request->phone,
        'phone2'=>$request->phone2,
        'address'=>$request->address,
    ]);
    Alert::success('success','sucessfully added');
    return redirect()->route('admin.user.index');
    }

    public function edit($id){
        $user=$this->userModel::find($id);
         return view('Admin.user.edit',compact('user'));
    }

    public function update($request){
        $user=$this->userModel::find($request->id);
        $user->update([
            'name'=> $request->name,
            'email'=>$request->email,
            'phone' =>$request->phone,
            'phone2'=>$request->phone2,
            'address'=>$request->address,
           ]);
           Alert::success('success','sucessfully updated');
           return redirect()->route('admin.user.index');
    }

    public function destroy($id){
        $user=$this->userModel::find($id);
        if($user){
            $user->delete();
            Alert::success('success','sucessfully updated');
            return redirect()->route('admin.user.index');
        }
            Alert::error('error','not found');
            return redirect()->route('admin.user.index');

    }

    public function sendNotify($request){
        $user=User::find($request->user_id);

         Notification::send($user,new UserNotify($request->message));

         Alert::success('success','sucessfully sent');
         return redirect()->route('admin.user.index');
    }

    public function verifyEmail($id){

        $user=$this->userModel::find($id);
        // dd($user);
        $user->update([
            'email_verified_at'=> Carbon::now()
        ]);
        return redirect()->back()->with('success','Email successfully verified');
    }


    public function verify($id){


     $user=$this->userModel::find($id);
     $user->update([

       'verified'=> 1

     ]);

         return redirect()->back()->with('success','successfully verified');

    }


    public function reject($request){
        $user=$this->userModel::find($request->id);

        $user->update([
        'verified'=> 2,
        'rejected_message' => $request->rejected_message
        ]);

        return redirect()->back()->with('success','data successfully rejected and message has been send');
    }


    public function archive(){

       $users= $this->userModel::onlyTrashed()->get();
     return view('Admin.user.archive',compact('users'));
    }

    public function restore($id){
    $user=$this->userModel::withTrashed()->where('id', $id)->restore();
    Alert::success('success','sucessfully restored');
    return redirect()->route('admin.user.index');
    }

    public function forceDelete($id){
        $user=$this->userModel::withTrashed()->where('id', $id)->forceDelete();
        Alert::success('success','sucessfully force deleted');
        return redirect()->route('admin.user.index');
    }


    public function walletControl($request){

        $user=$this->userModel::find($request->user_id);
$amount=$request->amount;

  $newNumbers = range(0, 9);
    $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $string =  str_replace($arabic, $newNumbers, $amount);
    // return str_replace($persian, $newNumbers, $string);
    // die($string);

        if($request->type == 'withdraw'){
            $user->update([
                "wallet" => ($user->wallet - $string)
            ]);
            $user->adminWallets()->create([
                'type' => 'withdraw',
                'amount' => $string,
            ]);

            $message = 'تم سحب مبلغ ' .   $string. ' من محفظتك';
        }elseif($request->type == 'deposit'){
            $user->update([
                "wallet" => ($user->wallet + $string)
            ]);
            $user->adminWallets()->create([
                'type' => 'deposit',
                'amount' => $string,
            ]);
            $message = 'تم اضافة مبلغ ' . $string  . ' علي محفظتك';
        }
        Notification::send($user,new UserNotify($message));
        Alert::success('success','sucess');
        return redirect()->back();

    }

    public function packageControl($request)
    {
        $package = Package::findOrFail($request->package_id);
        $user = User::findOrFail($request->user_id);

        Subscribe::create([
            'user_id'      => $user->id,
            'package_id'   => $package->id,
            'remain'       => $package->number,
            'status'       => 1
        ]);
        Alert::success('success','success');
        return redirect()->back();
    }



}
