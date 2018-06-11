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

                $account = User::firstOrNew(['vatsim_id' => $user->id]);
                $account->name = utf8_decode($user->name_first) . ' ' . utf8_decode($user->name_last);
                $account->vatsim_id = $user->id;
                $account->email = $user->email;
                $account->country = $user->country->name;
                $account->division = $user->division->name;
                $account->subdivision = $user->subdivision->name;
                $account->save();

                Auth::loginUsingId($account->id);
                return Redirect('/booking');
            },
            function($e) {
                throw $e; // Do something with the exception
            }
        );
    }

}
