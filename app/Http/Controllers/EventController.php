<?php

namespace App\Http\Controllers;

use App\{Enums\BookingStatus,
    Http\Requests\SendEmail,
    Mail\EventBulkEmail,
    Mail\EventFinalInformation,
    Models\Airport,
    Models\Booking,
    Models\Event};
use Carbon\Carbon;
use Illuminate\{
    Http\Request, Support\Facades\Mail
};

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
        $events = Event::all();
        return view('event.overview', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $airports = Airport::all();
        return view('event.create', compact('airports'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'bail|required:string',
            'dateEvent' => 'required|date',
            'timeBeginEvent' => 'required',
            'timeEndEvent' => 'required',
            'dateBeginBooking' => 'required|date',
            'timeBeginBooking' => 'required',
            'dateEndBooking' => 'required|date|after_or_equal:dateBeginBooking',
            'timeEndBooking' => 'required',
            'description' => 'required:string',
        ]);

        $event = Event::create([
            'name' => $request->name,
            'startEvent' => Carbon::createFromFormat('d-m-Y H:i', $request->dateEvent . ' ' . $request->timeBeginEvent),
            'endEvent' => Carbon::createFromFormat('d-m-Y H:i', $request->dateEvent . ' ' . $request->timeEndEvent),
            'startBooking' => Carbon::createFromFormat('d-m-Y H:i', $request->dateBeginBooking . ' ' . $request->timeBeginBooking),
            'endBooking' => Carbon::createFromFormat('d-m-Y H:i', $request->dateEndBooking . ' ' . $request->timeEndBooking),
            'timeFeedbackForm' => Carbon::createFromFormat('d-m-Y H:i', $request->dateEndBooking . ' ' . $request->timeEndBooking)->addHours($request->timeFeedbackForm),
            'description' => $request->description,
        ]);
        $event->save();
        flashMessage('success', 'Done', 'Event has been created!');
        return redirect('admin/event');
    }

    /**
     * Display the specified resource.
     *
     * @param Event $event
     * @return void
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Event $event
     * @return void
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Event $event
     * @return void
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Event $event
     * @return void
     */
    public function destroy(Event $event)
    {
        //
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
        $bookings = Booking::where('event_id',$event->id)
            ->where('status', BookingStatus::Booked)
            ->get();
        $count = 0;
        foreach ($bookings as $booking) {
            Mail::to($booking->user->email)->send(new EventBulkEmail($event, $booking->user, $request->subject, $request->message));
            $count++;
        }
        flashMessage('success', 'Done', 'Bulk E-mail has been sent to '.$count.' people!');
        return redirect('/admin/event');
    }

    /**
     * Sends E-mail to all users who booked a flight the final information.
     *
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendFinalInformationMail(Event $event){
        $bookings = Booking::where('event_id',$event->id)
            ->where('status', BookingStatus::Booked)
            ->get();
        $count = 0;
        foreach ($bookings as $booking) {
            Mail::to($booking->user->email)->send(new EventFinalInformation($booking));
            $count++;
        }
        flashMessage('success', 'Done', 'Final Information has been sent to '.$count.' people!');
        return redirect('/admin/event');
    }
}
