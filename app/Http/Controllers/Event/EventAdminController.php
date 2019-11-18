<?php

namespace App\Http\Controllers\Event;

use App\Enums\BookingStatus;
use App\Http\Controllers\AdminController;
use App\Http\Requests\Event\Admin\SendEmail;
use App\Http\Requests\Event\Admin\StoreEvent;
use App\Http\Requests\Event\Admin\UpdateEvent;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use App\Notifications\EventBulkEmail;
use App\Notifications\EventFinalInformation;
use App\Policies\EventPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Notification;

class EventAdminController extends AdminController
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(EventPolicy::class, 'event');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::orderBy('startEvent')
            ->with('type')
            ->paginate();
        return view('event.admin.overview', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $event = new Event;
        $airports = Airport::orderBy('icao')->get();
        $eventTypes = EventType::all();
        return view('event.admin.form', compact('event', 'airports', 'eventTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEvent  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEvent $request)
    {
        $event = new Event();
        $event->fill($request->only('is_online', 'show_on_homepage', 'name', 'event_type_id', 'import_only',
            'uses_times',
            'multiple_bookings_allowed', 'is_oceanic_event', 'dep', 'arr', 'image_url', 'description'));
        $event->fill([
            'startEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent.' '.$request->timeBeginEvent),
            'endEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent.' '.$request->timeEndEvent),
            'startBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateBeginBooking.' '.$request->timeBeginBooking),
            'endBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEndBooking.' '.$request->timeEndBooking),
        ])->save();

        flashMessage('success', 'Done', 'Event has been created!');
        return redirect(route('admin.events.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Event  $event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Event $event)
    {
        return view('event.admin.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        $airports = Airport::orderBy('icao')->get();
        $eventTypes = EventType::all();
        return view('event.admin.form', compact('event', 'airports', 'eventTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateEvent  $request
     * @param  Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEvent $request, Event $event)
    {
        $event->fill($request->only('is_online', 'show_on_homepage', 'name', 'event_type_id', 'import_only',
            'uses_times',
            'multiple_bookings_allowed', 'is_oceanic_event', 'dep', 'arr', 'image_url', 'description'));
        $event->fill([
            'startEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent.' '.$request->timeBeginEvent),
            'endEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent.' '.$request->timeEndEvent),
            'startBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateBeginBooking.' '.$request->timeBeginBooking),
            'endBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEndBooking.' '.$request->timeEndBooking),
        ])->save();
        flashMessage('success', 'Done', 'Event has been updated!');
        return redirect(route('admin.events.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Event $event)
    {
        if ($event->startEvent > now()) {
            $event->delete();
            flashMessage('success', 'Done', $event->name.' has been deleted!');
            return redirect()->back();
        } else {
            flashMessage('danger', 'Nope!', 'You cannot remove a event after it has begun!');
            return redirect()->back();
        }
    }

    /**
     * Opens form to either use sendEmail() or sendFinalInformationMail()
     *
     * @param  Event  $event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sendEmailForm(Event $event)
    {
        return view('event.admin.sendEmail', compact('event'));
    }

    /**
     * Sends E-mail to all users who booked a flight as a notification by administrators.
     *
     * @param  SendEmail  $request
     * @param  Event  $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendEmail(SendEmail $request, Event $event)
    {
        if ($request->testmode) {
            Notification::send(Auth::user(), new EventBulkEmail($event, $request->subject, $request->message));
            activity()
                ->by(Auth::user())
                ->on($event)
                ->withProperties(
                    [
                        'subject' => $request->subject,
                        'message' => $request->message,
                    ]
                )
                ->log('Bulk E-mail test performed');
            return response()->json(['success' => 'Email has been sent to yourself']);
        } else {
            $bookings = Booking::where('event_id', $event->id)
                ->where('status', BookingStatus::BOOKED)
                ->get();
            $users = User::find($bookings->pluck('user_id'));
            Notification::send($users, new EventBulkEmail($event, $request->subject, $request->message));
            $count = $users->count();

            flashMessage('success', 'Done', 'Bulk E-mail has been sent to '.$count.' people!');
            activity()
                ->by(Auth::user())
                ->on($event)
                ->withProperties(
                    [
                        'subject' => $request->subject,
                        'message' => $request->message,
                        'count' => $count,
                    ]
                )
                ->log('Bulk E-mail');
        }
        return redirect(route('admin.events.index'));
    }

    /**
     * Sends E-mail to all users who booked a flight the final information.
     *
     * @param  Event  $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendFinalInformationMail(Request $request, Event $event)
    {
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->get();

        if ($request->testmode) {
            $booking = $bookings->random();
            Notification::send(Auth::user(), new EventFinalInformation($booking));

            activity()
                ->by(Auth::user())
                ->on($event)
                ->withProperties(
                    [
                        'booking' => $booking,
                    ]
                )
                ->log('Final Information E-mail test performed');
            return response()->json(['success' => 'Email has been sent to yourself']);
        } else {
            $count = $bookings->count();
            foreach ($bookings as $booking) {
                $booking->user->notify(new EventFinalInformation($booking));
            }
            flashMessage('success', 'Done', 'Final Information has been sent to '.$count.' people!');
            activity()
                ->by(Auth::user())
                ->on($event)
                ->withProperty('count', $count)
                ->log('Final Information E-mail');
        }
        return redirect(route('admin.events.index'));
    }
}
