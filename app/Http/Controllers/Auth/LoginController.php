<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Faker\Factory as Faker;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
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

    public function login(Request $request)
    {
        if ($request->get('booking')) {
            // Check if the booking exists, just to prevent a 404 later on
            $booking = Booking::whereUuid($request->booking)->first();
            if (!empty($booking)) {
                session()->put('booking', $booking->uuid);
            }
        } elseif ($request->get('event')) {
            // Check if the event exists, just to prevent a 404 later on
            $event = Event::whereSlug($request->event)->first();
            if (!empty($event)) {
                session()->put('event', $event->slug);
            }
        }

        $returnUrl = config('vatsim-sso.return'); // load URL from config
        return VatsimSSO::login(
            $returnUrl,
            function ($key, $secret, $url) {
                session()->put('vatsimauth', compact('key', 'secret'));
                return redirect($url);
            },
            function ($e) {
                Bugsnag::notifyException($e);
                flashMessage('danger', 'Login failed', 'Something went wrong, please try again');
                return redirect(route('home'));
            }
        );
    }

    public function validateLogin(Request $request)
    {
        $session = session('vatsimauth');
        if (!empty($request->get('oauth_verifier')) && !empty($session)) {
            return VatsimSSO::validate(
                $session['key'],
                $session['secret'],
                $request->get('oauth_verifier'),
                function ($user, $request) {
                    // At this point we can remove the session data.
                    session()->forget('vatsimauth');

                    $account = User::firstOrNew(['id' => $user->id]);
                    $account->id = $user->id;
                    $account->name_first = utf8_decode($user->name_first);
                    $account->name_last = utf8_decode($user->name_last);
                    // Check if this is the production environment to determine to use the actual E-mail adress or something random
                    $account->email = app()->environment() == 'production' ? $user->email : Faker::create()->email();
                    $account->country = $user->country->code;
                    $account->region = $user->region->code;
                    $account->division = $user->division->code;
                    $account->subdivision = $user->subdivision->code;
                    $account->save();

                    auth()->loginUsingId($user->id);
                    activity()->log('Login');

                    if (session('booking')) {
                        $booking = Booking::whereUuid(session('booking'))->first();
                        session()->forget('booking');
                        if (!empty($booking)) {
                            if ($booking->status !== BookingStatus::BOOKED) {
                                return redirect(route('bookings.edit', $booking));
                            }
                            return redirect(route('bookings.show', $booking));
                        }
                    } elseif (session('event')) {
                        $event = Event::whereSlug(session('event'))->first();
                        session()->forget('event');
                        if (!empty($event)) {
                            return redirect(route('events.show', $event));
                        }
                    }

                    return redirect('/');
                },
                function ($e) {
                    Bugsnag::notifyException($e);
                }
            );
        } else {
            flashMessage('error', 'Login failed', 'Something went wrong, please try again');
            return redirect(route('home'));
        }
    }

    public function logout()
    {
        activity()->log('Logout');
        auth()->logout();
        return redirect('/');
    }
}
