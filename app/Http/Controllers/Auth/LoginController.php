<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Config;
use Illuminate\Support\Facades\Input;
use Redirect;
use Session;
use VatsimSSO;

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
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/booking';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login() {

        $returnUrl = Config::get('vatsim-sso.return'); // load URL from config
        return VatsimSSO::login(
            $returnUrl,
            function($key, $secret, $url) {
                Session::put('vatsimauth', compact('key', 'secret'));
                return Redirect::to($url);
            },
            function($e) {
                throw $e; // Do something with the exception
            }
        );
    }

    public function validateLogin()
    {
        $session = Session::get('vatsimauth');
        return VatsimSSO::validate(
            $session['key'],
            $session['secret'],
            Input::get('oauth_verifier'),
            function($user, $request) {
                // At this point we can remove the session data.
                Session::forget('vatsimauth');

                $account = User::firstOrNew(['id' => $user->id]);
                $account->id = $user->id;
                $account->name_first = utf8_decode($user->name_first);
                $account->name_last = utf8_decode($user->name_last);
                $account->email = $user->email;
                $account->country = $user->country->code;
                $account->region = $user->region->code;
                $account->division = $user->division->code;
                $account->subdivision = $user->subdivision->code;
                $account->save();

                Auth::loginUsingId($user->id);
                return Redirect('/booking');
            },
            function($e) {
                throw $e; // Do something with the exception
            }
        );
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

}
