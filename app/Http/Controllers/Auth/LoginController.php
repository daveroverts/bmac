<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Services\OAuth\VatsimProvider;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Create a new controller instance.
     */
    public function __construct(protected VatsimProvider $provider)
    {
    }

    public function login(Request $request): RedirectResponse
    {
        if (!$request->has('code') || !$request->has('state')) {
            // User has clicked "login", redirect to Connect
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

            $authorizationUrl = $this->provider->getAuthorizationUrl();
            // Generates state
            $request->session()->put('oauthstate', $this->provider->getState());
            return redirect()->away($authorizationUrl);
        }

        if ($request->input('state') !== session()->pull('oauthstate')) {
            // State mismatch, error
            flashMessage('error', __('Login failed'), __('Something went wrong, please try again'));
            return to_route('home');
        }

        // Callback (user has just logged in Connect)
        return $this->verifyLogin($request);
    }

    protected function verifyLogin(Request $request): RedirectResponse
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
        } catch (IdentityProviderException) {
            flashMessage('error', __('Login failed'), __('Something went wrong, please try again'));
            return to_route('home');
        }

        /** @var \League\OAuth2\Client\Token\AccessToken $accessToken */
        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        $data = [
            'cid' => VatsimProvider::getOAuthProperty(config('oauth.mapping_cid'), $resourceOwner),
            'first_name' => VatsimProvider::getOAuthProperty(config('oauth.mapping_first_name'), $resourceOwner),
            'last_name' => VatsimProvider::getOAuthProperty(config('oauth.mapping_last_name'), $resourceOwner),
            'email' => VatsimProvider::getOAuthProperty(config('oauth.mapping_mail'), $resourceOwner),
        ];

        // Check if user has granted us the data we need
        if (
            !$data['cid'] ||
            !$data['first_name'] ||
            !$data['last_name'] ||
            !$data['email']
        ) {
            flashMessage('error', __('Login failed'), __('We need you to grant us all marked permissions'));
            return to_route('home');
        }

        $this->completeLogin($data, $accessToken);

        if (session('booking')) {
            $booking = Booking::whereUuid(session('booking'))->first();
            session()->forget('booking');
            if (!empty($booking)) {
                if ($booking->status === BookingStatus::UNASSIGNED) {
                    return $this->reserveAfterLogin($booking);
                }

                if ($booking->status === BookingStatus::BOOKED) {
                    return to_route('bookings.show', $booking);
                }

                return to_route('bookings.edit', $booking);
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

    protected function completeLogin(array $data, $token): User
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

    protected function reserveAfterLogin(Booking $booking): RedirectResponse
    {
        // Booking window may have closed while the user was logging in
        if ($booking->event->endBooking < now()) {
            flashMessage('danger', __('Danger'), __('Bookings have been closed at :time', ['time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z']));
            return to_route('events.bookings.index', $booking->event);
        }

        $user = auth()->user();

        // User may already have a reservation for this event
        if ($user->bookings()
            ->where('event_id', $booking->event_id)
            ->where('status', BookingStatus::RESERVED)
            ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a reservation! Please cancel or book that flight first.'));
            return to_route('events.bookings.index', $booking->event);
        }

        // Check if event allows multiple bookings
        if (!$booking->event->multiple_bookings_allowed
            && $user->bookings()
                ->where('event_id', $booking->event_id)
                ->where('status', BookingStatus::BOOKED)
                ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a booking!'));
            return to_route('events.bookings.index', $booking->event);
        }

        // Atomically claim the slot: only update if it is still UNASSIGNED.
        // The user may have been logging in while another request reserved the slot.
        $claimed = Booking::query()
            ->where('id', $booking->id)
            ->where('status', BookingStatus::UNASSIGNED)
            ->update([
                'status' => BookingStatus::RESERVED,
                'user_id' => $user->id,
            ]);

        if ($claimed === 0) {
            flashMessage('danger', __('Warning'), __('Whoops! Somebody else reserved that slot just before you! Please choose another one.'));
            return to_route('events.bookings.index', $booking->event);
        }

        $booking->refresh();

        activity()
            ->by($user)
            ->on($booking)
            ->log('Flight reserved');

        flashMessage(
            'info',
            __('Slot reserved'),
            __('Slot remains reserved until :time', [
                'time' => $booking->updated_at->addMinutes(10)->format('Hi') . 'z',
            ])
        );

        return to_route('bookings.edit', $booking);
    }

    public function logout(): RedirectResponse
    {
        activity()->log('Logout');
        auth()->logout();
        return to_route('home');
    }
}
