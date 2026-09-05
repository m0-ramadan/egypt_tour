<?php

namespace App\Http\Interfaces;


interface  ForgetPasswordInterface{

    public function forgetPassword();

    public function getEmail( $request);

    public function resetPasswordPage($token);

    public function resetPassword($request);

}
