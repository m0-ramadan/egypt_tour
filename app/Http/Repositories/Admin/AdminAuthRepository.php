<?php

namespace App\Http\Repositories\Admin;



use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Interfaces\Admin\AdminAuthInterface;

class AdminAuthRepository implements AdminAuthInterface{


public function register(){


}

public function loginPage(){
  if (auth('admin')->check()) {
    return redirect('/');
}
    return view('Admin.auth.login');


}
public function login($request){

  $credintials= $request->only('email','password');
  if(Auth::guard('admin')->attempt($credintials)){

    return redirect('/');

  }  else{
    return back()->withErrors('please inter avalid data');
  }



}
public function logout(){

  auth()->guard('admin')->logout();
  Session::flush();
  return redirect()->route('admin.login');
}
}
