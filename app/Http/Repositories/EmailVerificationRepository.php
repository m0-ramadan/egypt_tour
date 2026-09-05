<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Social;
use App\Models\Setting;
use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Interfaces\EmailVerificationInterface;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationRepository implements EmailVerificationInterface
{

    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('auth')->only('resend');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }


    public function sendVerificationEmail(Request $request)
    {

        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => trans('api.email-already-verified'),
            ];
        }

        $request->user()->sendEmailVerificationNotification();

        return ['status' => trans('api.verify-link')];
    }

    public function verify(Request $request)
    {

        $user = User::find($request->route('id'));

         Auth::login($user);


        if ($request->route('id') != $request->user()->getKey()) {
            throw new AuthorizationException;
        }

        if ($request->user()->hasVerifiedEmail()) {

        $message=trans('api.email-already-verified');

            // return redirect($this->redirectPath());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
            $message =trans('api.email-verify');
        }
        $setting=Setting::first();
        $socials=Social::get();

        return  view('front.emailverifysuccess',compact('message','setting','socials'));


    }
}
