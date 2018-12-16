<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
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

    public function login(Booking $booking)
    {
        if (!empty($booking)) {
            Session::put('booking', $booking->uuid);
        }

        $returnUrl = Config::get('vatsim-sso.return'); // load URL from config
        return VatsimSSO::login(
            $returnUrl,
            function ($key, $secret, $url) {
                Session::put('vatsimauth', compact('key', 'secret'));
                return Redirect::to($url);
            },
            function ($e) {
                throw $e; // Do something with the exception
            }
        );
    }

    public function validateLogin()
    {
        $session = Session::get('vatsimauth');
        if (!empty(Input::get('oauth_verifier'))) {
            return VatsimSSO::validate(
                $session['key'],
                $session['secret'],
                Input::get('oauth_verifier'),
                function ($user, $request) {
                    // At this point we can remove the session data.
                    Session::forget('vatsimauth');

                    $account = User::firstOrNew(['id' => $user->id]);
                    $account->id = $user->id;
                    $account->name_first = utf8_decode($user->name_first);
                    $account->name_last = utf8_decode($user->name_last);
                    // Check if this is the production environment to determine to use the actual E-mail adress or something random
                    $account->email = App::environment('production') ? $user->email : Faker::create()->email();
                    $account->country = $user->country->code;
                    $account->region = $user->region->code;
                    $account->division = $user->division->code;
                    $account->subdivision = $user->subdivision->code;
                    $account->save();

                    Auth::loginUsingId($user->id);

                    if (Session::get('booking')) {
                        $booking = Booking::where('uuid', Session::get('booking'))->first();
                        Session::forget('booking');
                        if (!empty($booking)) {
                            if ($booking->status !== BookingStatus::BOOKED) {
                                return Redirect::route('bookings.edit', $booking);
                            }
                            return Redirect::route('bookings.show', $booking);
                        }
                    }

                    return Redirect('/');
                },
                function ($e) {
                    throw $e; // Do something with the exception
                }
            );
        } else {
            flashMessage('danger', 'Login failed', 'Something went wrong, please try again');
            return redirect(route('home'));
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
