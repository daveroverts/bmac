<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OAuthController;
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
        $this->provider = new OAuthController();
    }

    public function login(Request $request)
    {
        if (!$request->has('code') || !$request->has('state')) { // User has clicked "login", redirect to Connect
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
            $request->session()->put('oauthstate', $this->provider->getState());
            return redirect()->away($authorizationUrl);
        } elseif ($request->input('state') !== session()->pull('oauthstate')) { // State mismatch, error
            flashMessage('error', 'Login failed', 'Something went wrong, please try again');
            return to_route('home');
        } else { // Callback (user has just logged in Connect)
            return $this->verifyLogin($request);
        }
    }

    protected function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
        } catch (IdentityProviderException) {
            flashMessage('error', 'Login failed', 'Something went wrong, please try again');
            return to_route('home');
        }
        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        $data = [
            'cid' => OAuthController::getOAuthProperty(config('oauth.mapping_cid'), $resourceOwner),
            'first_name' => OAuthController::getOAuthProperty(config('oauth.mapping_first_name'), $resourceOwner),
            'last_name' => OAuthController::getOAuthProperty(config('oauth.mapping_last_name'), $resourceOwner),
            'email' => OAuthController::getOAuthProperty(config('oauth.mapping_mail'), $resourceOwner),
        ];

        // Check if user has granted us the data we need
        if (
            !$data['cid'] ||
            !$data['first_name'] ||
            !$data['last_name'] ||
            !$data['email']
        ) {
            flashMessage('error', 'Login failed', 'We need you to grant us all marked permissions');
            return to_route('home');
        }

        $this->completeLogin($data, $accessToken);

        if (session('booking')) {
            $booking = Booking::whereUuid(session('booking'))->first();
            session()->forget('booking');
            if (!empty($booking)) {
                if ($booking->status != BookingStatus::BOOKED) {
                    return to_route('bookings.edit', $booking);
                }
                return to_route('bookings.show', $booking);
            }
        } elseif (session('event')) {
            $event = Event::whereSlug(session('event'))->first();
            session()->forget('event');
            if (!empty($event)) {
                return to_route('events.show', $event);
            }
        }
        return to_route('home');
    }

    protected function completeLogin($data, $token): User
    {
        $account = User::updateOrCreate(
            ['id' => $data['cid']],
            [
                'name_first' => $data['first_name'],
                'name_last' => $data['last_name'],
                'email' => $data['email'],
            ]
        );

        if ($token->getToken() !== null) {
            $account->access_token = $token->getToken();
        }
        if ($token->getRefreshToken() !== null) {
            $account->refresh_token = $token->getRefreshToken();
        }
        if ($token->getExpires() !== null) {
            $account->token_expires = $token->getExpires();
        }

        $account->save();
        auth()->loginUsingId($data['cid'], true);
        activity()->log('Login');

        return $account;
    }

    public function logout()
    {
        activity()->log('Logout');
        auth()->logout();
        return to_route('home');
    }
}
