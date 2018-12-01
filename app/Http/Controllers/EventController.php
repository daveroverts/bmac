<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\SendEmail;
use App\Http\Requests\StoreEvent;
use App\Http\Requests\UpdateEvent;
use App\Mail\EventBulkEmail;
use App\Mail\EventFinalInformation;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.isAdmin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::orderBy('endEvent', 'DESC')->paginate();
        return view('event.overview', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $airports = Airport::orderBy('icao')->get();
        $eventTypes = EventType::all();
        return view('event.create', compact('airports', 'eventTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreEvent $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEvent $request)
    {
        $event = new Event();
        $event->fill($request->only('name', 'event_type_id', 'import_only', 'uses_times',
            'multiple_bookings_allowed', 'is_oceanic_event', 'dep', 'arr', 'image_url', 'description'));
        $event->fill([
            'startEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent . ' ' . $request->timeBeginEvent),
            'endEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent . ' ' . $request->timeEndEvent),
            'startBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateBeginBooking . ' ' . $request->timeBeginBooking),
            'endBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEndBooking . ' ' . $request->timeEndBooking),
        ])->save();

        flashMessage('success', 'Done', 'Event has been created!');
        return redirect(route('events.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param Event $event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Event $event)
    {
        return view('event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        $airports = Airport::orderBy('icao')->get();
        $eventTypes = EventType::all();
        return view('event.edit', compact('event', 'airports', 'eventTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateEvent $request
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEvent $request, Event $event)
    {
        $event->fill($request->only('name', 'event_type_id', 'import_only', 'uses_times',
            'multiple_bookings_allowed', 'is_oceanic_event', 'dep', 'arr', 'image_url', 'description'));
        $event->fill([
            'startEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent . ' ' . $request->timeBeginEvent),
            'endEvent' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEvent . ' ' . $request->timeEndEvent),
            'startBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateBeginBooking . ' ' . $request->timeBeginBooking),
            'endBooking' => Carbon::createFromFormat('d-m-Y H:i',
                $request->dateEndBooking . ' ' . $request->timeEndBooking),
        ])->save();
        flashMessage('success', 'Done', 'Event has been updated!');
        return redirect(route('events.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Event $event)
    {
        if ($event->startEvent > now()) {
            $event->delete();
            flashMessage('success', 'Done', $event->name . ' has been deleted!');
            return redirect()->back();
        } else {
            flashMessage('danger', 'Nope!', 'You cannot remove a event after it has begun!');
            return redirect()->back();
        }
    }

    /**
     * Opens form to either use sendEmail() or sendFinalInformationMail()
     *
     * @param Event $event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sendEmailForm(Event $event)
    {
        return view('event.sendEmail', compact('event'));
    }

    /**
     * Sends E-mail to all users who booked a flight as a notification by administrators.
     *
     * @param SendEmail $request
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendEmail(SendEmail $request, Event $event)
    {
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->get();
        $count = 0;
        foreach ($bookings as $booking) {
            Mail::to($booking->user->email)->send(
                new EventBulkEmail($event, $booking->user, $request->subject, $request->message)
            );
            $count++;
        }
        flashMessage('success', 'Done', 'Bulk E-mail has been sent to ' . $count . ' people!');
        return redirect(route('events.index'));
    }

    /**
     * Sends E-mail to all users who booked a flight the final information.
     *
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendFinalInformationMail(Event $event)
    {
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->get();
        $count = 0;
        foreach ($bookings as $booking) {
            Mail::to($booking->user->email)->send(new EventFinalInformation($booking));
            $count++;
        }
        flashMessage('success', 'Done', 'Final Information has been sent to ' . $count . ' people!');
        return redirect(route('events.index'));
    }
}
