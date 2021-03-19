<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    public function attemptLogin(Request $request)
    {
        //attempt to issue token based on login credentials
        $token = $this->guard()->attempt($this->credentials($request));

        if(! $token){
            return false;
        }

        //get authenticated user
        $user= $this->guard()->user();

        if($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()){
            return false;
        }

        //set user token
        $this->guard()->setToken($token);
        return true;
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        //get token from authentication guard
        $token = (string)$this->guard()->getToken();

        //extract expiry date
        $expiration = $this->guard()->getPayLoad()->get('exp');
        return response()->json(['token'=>$token,"token_type"=>'bearer',"expires_in"=>$expiration]);
    }


    protected function sendFailedLoginResponse()
    {
        $user= $this->guard()->user();

        if($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())
        {
            return response()->json(["errors"=>["verification"=>"Please verify your email account"]]);
        }

        throw ValidationException::withMessages([
            $this->username()=>"Authentication failed"
        ]);
    }
    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message'=>'Logged out Successfully']);
    }
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
