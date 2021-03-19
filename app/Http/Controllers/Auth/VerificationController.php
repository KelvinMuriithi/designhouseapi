<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;

//use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{




    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        /* $this->middleware('signed')->only('verify'); */
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
    public function verify(Request $request, User $user)
    {
        //check if the url is valid
        if(! URL::hasValidSignature($request)){
            return response()->json(["errors"=>["message"=>"Invalid Verification link"]],422);
        }

        //check if the user has already verified account
        if($user->hasVerifiedEmail()){
            return response()->json(["errors"=>["message"=>"Email address already verified"]],422);
        }


     $user->markEmailAsVerified();
     event(new Verified($user));

     return response()->json(['message'=>'Email successfully Verified'],200);
    }
    public function resend(Request $request)
    {
       $this->validate($request,['email'=>['email','required']]);
       $user = User::where('email',$request->email)->first();
       if(! $user){
           response()->json(["errors"=>["email"=>"No user found with this email address"]],422);
       }
       if($user->hasVerifiedEmail()){
        return response()->json(["errors"=>["message"=>"Email address already verified"]],422);
         }
         $user->sendEMailVerificationNotification();
         return response()->json(['status'=>'Verification link resent']);

    }
}
