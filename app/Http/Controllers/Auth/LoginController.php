<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectTo()
    {
        if (Auth::user()->Position === 'CANDIDATE') {
            return '/candidate/dashboard';
        }

        return 'dashboard1';
    }

    /**
     * Redirect to the login page after logout.
     */
    protected function loggedOut(Request $request)
    {
        return redirect('/login');
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        if (Auth::user()->Position === 'CANDIDATE') {
            return redirect('/candidate/dashboard');
        }

        return redirect()->intended($this->redirectPath());
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [

            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    protected function credentials(Request $request)
    {
        $data = $request->only($this->username(), 'password');
        return $data;
    }
    protected function create(array $data)
    {

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        // $user->notify(new EmailVerificationNotification);

        return $user;
    }


    public function username()
    {


        return "username";
    }
}
