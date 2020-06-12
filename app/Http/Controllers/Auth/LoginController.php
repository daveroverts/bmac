<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\VatsimOAuthController;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

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

    protected $provider;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->provider = new VatsimOAuthController;
    }

    public function login(Request $request)
    {
        if (! $request->has('code') || ! $request->has('state')) { // User has clicked "login", redirect to Connect
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
            $authorizationUrl = $this->provider->getAuthorizationUrl(); // Generates state
            $request->session()->put('vatsimauthstate', $this->provider->getState());
	    	return redirect()->away($authorizationUrl);
        }
        else if ($request->input('state') !== session()->pull('vatsimauthstate')) { // State mismatch, error
            flashMessage('error', 'Login failed', 'Something went wrong, please try again');
            return redirect('/')->withError("Something went wrong, please try again.");
        }
		else { // Callback (user has just logged in Connect)
            return $this->verifyLogin($request);
        }
    }

    protected function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
        } catch (IdentityProviderException $e) {
            flashMessage('error', 'Login failed', 'Something went wrong, please try again');
            return redirect('/')->withError("Something went wrong, please try again later.");
        }
        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

		// Check if user has granted us the data we need
        if (
            ! isset($resourceOwner->data) ||
            ! isset($resourceOwner->data->cid) ||
            ! isset($resourceOwner->data->personal->name_first) ||
            ! isset($resourceOwner->data->personal->name_last) ||
            ! isset($resourceOwner->data->personal->email)
        ) {
            flashMessage('error', 'Login failed', 'We need you to grant us all marked permissions');
            return redirect('/')->withError("We need you to grant us all marked permissions");
        }

        $this->completeLogin($resourceOwner, $accessToken);
        if (session('booking')) {
            $booking = Booking::whereUuid(session('booking'))->first();
            session()->forget('booking');
            if (!empty($booking)) {
                if ($booking->status !== BookingStatus::BOOKED) {
                    return redirect()->intended(route('bookings.edit', $booking))->withSuccess('Login Successful');
                }
                return redirect()->intended(route('bookings.show', $booking))->withSuccess('Login Successful');
            }
        } elseif (session('event')) {
            $event = Event::whereSlug(session('event'))->first();
            session()->forget('event');
            if (!empty($event)) {
                return redirect()->intended(route('events.show', $event))->withSuccess('Login Successful');
            }
        }
        return redirect()->intended('/')->withSuccess('Login Successful');
    }

    protected function completeLogin($resourceOwner, $token)
    {
        $account = User::firstOrNew(['id' => $resourceOwner->data->cid]);
        $account->id = $resourceOwner->data->cid;
        $account->name_first = $resourceOwner->data->personal->name_first;
        $account->name_last = $resourceOwner->data->personal->name_last;
        $account->email = $resourceOwner->data->personal->email;
        if ($resourceOwner->data->oauth->token_valid === "true") { // User has given us permanent access to data
            $account->access_token = $token->getToken();
            $account->refresh_token = $token->getRefreshToken();
            $account->token_expires = $token->getExpires();
        }

        $account->save();
        auth()->login($account, true);
		activity()->log('Login');
        return $account;
    }

    public function logout()
    {
        activity()->log('Logout');
        auth()->logout();
        return redirect('/');
    }
}
