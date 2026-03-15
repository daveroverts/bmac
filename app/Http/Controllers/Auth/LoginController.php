<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Services\Auth\AuthenticationService;
use App\Services\OAuth\VatsimProvider;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected VatsimProvider $provider,
        protected AuthenticationService $authService,
    ) {
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $request->has('code') || ! $request->has('state')) {
            // User has clicked "login", redirect to Connect
            if ($request->get('booking')) {
                // Check if the booking exists, just to prevent a 404 later on
                $booking = Booking::whereUuid($request->booking)->first();
                if (! empty($booking)) {
                    session()->put('booking', $booking->uuid);
                }
            } elseif ($request->get('event')) {
                // Check if the event exists, just to prevent a 404 later on
                $event = Event::whereSlug($request->event)->first();
                if (! empty($event)) {
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
        return $this->handleOAuthCallback($request);
    }

    protected function handleOAuthCallback(Request $request): RedirectResponse
    {
        $result = $this->authService->authenticateFromOAuth($request);

        if ($result === null) {
            flashMessage('error', __('Login failed'), __('Something went wrong, please try again'));

            return to_route('home');
        }

        if (session('booking')) {
            $booking = Booking::whereUuid(session('booking'))->first();
            session()->forget('booking');
            if (! empty($booking)) {
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
            if (! empty($event)) {
                return to_route('events.show', $event);
            }
        }

        return to_route('home');
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
            ->reserved()
            ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a reservation! Please cancel or book that flight first.'));

            return to_route('events.bookings.index', $booking->event);
        }

        // Check if event allows multiple bookings
        if (! $booking->event->multiple_bookings_allowed
            && $user->bookings()
                ->where('event_id', $booking->event_id)
                ->booked()
                ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a booking!'));

            return to_route('events.bookings.index', $booking->event);
        }

        // Atomically claim the slot: only update if it is still UNASSIGNED.
        // The user may have been logging in while another request reserved the slot.
        $claimed = Booking::query()
            ->where('id', $booking->id)
            ->unassigned()
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
