<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\ForgetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Mail;
use App\Http\Interfaces\ForgetPasswordInterface;


class ForgetPasswordRepository implements ForgetPasswordInterface{



    public function forgetPassword(){
      return view('forgetPassword');
    }


    public function getEmail($request){


            $user=User::where('email', $request->email)->first();



        if(is_null($user)){
            return response(['message'=>'This account not found !'], 200);
        }
        $token = Str::random(10);

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now()->addHours(6)
        ]);

         // Send Mail
        Mail::to($user->email)->send(new ForgetPasswordMail($token));



        return redirect()->back()->with('success','تم ارسال الي بريدك الالكتروني للتحقق لتغير اكلمة المرور') ;
    }


    public function resetPasswordPage($token){
     return view('resetpasswordpage',compact('token'));

    }
    // reset password
    public function resetPassword($request){

        $token = $request->token;
        $passwordRest = DB::table('password_resets')->where('token', $token)->first();

        // Verify
        if(!$passwordRest){
            return  redirect()->route('front.login')->with('error','التوكن غير موجود');
        }

        // Validate exipire time
        if(!$passwordRest->created_at >= now()){
            return  redirect()->route('front.login')->with('error','وقت ال توكن انتهي ');
        }

            $user = User::where('email', $passwordRest->email)->first();




        if(!$user){
            return  redirect()->route('front.login')->with('error','    الاكونت غير موجود');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('token', $token)->delete();

        return  redirect()->route('front.login')->with('success','تم تغيير كلمة المرور بنجاح');
    }



}
