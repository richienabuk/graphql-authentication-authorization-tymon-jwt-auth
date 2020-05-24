<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    protected $username;

    public function __construct()
    {
        $this->username = $this->findUsername();
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function findUsername()
    {
        $login = request()->input('email');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    /**
     * Get username property.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }


    public function attemptLogin(Request $request)
    {
        // attempt to issue a token to the user based on the login credentials
        $token = $this->guard()->attempt($this->credentials($request));

        if(!$token){
            return false;
        }

        // Get the authenticated user
        $user = $this->guard()->user();

        if($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()){
            return false;
        }

        // set the user's token
        $this->guard()->setToken($token);
        return true;
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        // get the token from the authentication guard (JWT)
        $token = (string)$this->guard()->getToken();

        // compute the expiry time of the token
        $expiration = auth()->factory()->getTTL() * 60;

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration
        ]);
    }


    protected function sendFailedLoginResponse()
    {
        $user = $this->guard()->user();

        if($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()){
            return response()->json(["errors" => [
                "message" => "You need to verify your email account"
            ]], 422);
        }

        throw ValidationException::withMessages([
            $this->username() => "Invalid credentials"
        ]);
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Logged out successfully!']);
    }
}
