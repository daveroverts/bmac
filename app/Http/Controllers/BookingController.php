<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Event;
use App\Exports\BookingsExport;
use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Mail;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $event = Event::find(1);
        $bookings = Booking::where('event_id', 1)->orderBy('ctot')->get();
        // Check all reservedBy_id's to see if 10 minutes have exceeded
        foreach ($bookings as $booking) {
            // If a reservation has been marked as reserved
            if (isset($booking->reservedBy_id)) {
                // If a reservation has been reserved for more then 10 minutes, remove reservedBy_id
                if (Carbon::now() > Carbon::createFromFormat('Y-m-d H:i:s',$booking->updated_at)->addMinutes(10)) {
                    $booking->fill([
                        'reservedBy_id' => null,
                        'updated_at' => NOW(),
                    ]);
                    $booking->save();
                }
            }
        }
        return view('booking.overview',compact('event','bookings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (Auth::check() && Auth::user()->isAdmin) {
            $event = Event::whereKey($request->id)->first();
            return view('booking.create',compact('event'));
        }
        else return redirect('/');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $event = Event::whereKey($request->id)->first();
        $request->validate([
            'start' => 'date_format:H:i',
            'end' => 'date_format:H:i',
            'separation' => 'integer|min:2'
        ]);
        $event_start = Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() .' '. $request->start);
        $event_end = Carbon::createFromFormat('Y-m-d H:i', $event->endEvent->toDateString() .' '. $request->end);
        $separation = $request->separation;
        for ($event_start; $event_start <= $event_end; $event_start->addMinutes($separation)) {
            if (!Booking::where([
                'event_id' => $request->id,
                'ctot' => $event_start,
            ])->first()) {
                Booking::create([
                    'event_id' => $request->id,
                    'dep' => 'EHAM',
                    'arr' => 'KBOS',
                    'ctot' => $event_start,
                ])->save();
            }
        }
        Session::flash('type','success');
        Session::flash('title', 'Done');
        Session::flash('message', 'Bookings have been created!');
        return redirect('/booking');
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $booking = Booking::findOrFail($id);
        if (Auth::check() && Auth::id() == $booking->bookedBy_id || Auth::user()->isAdmin) {
            return view('booking.show',compact('booking'));
        }
        else {
            Session::flash('type','danger');
            Session::flash('title', 'Already booked');
            Session::flash('message', 'Whoops that booking belongs to somebody else!');
            return redirect('/booking');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Check if user is logged in
        if (Auth::check()) {
            $booking = Booking::findOrFail($id);
            $user = User::find(Auth::id());
            // Check if the booking has already been booked or reserved
            if (isset($booking->bookedBy_id) ||  isset($booking->reservedBy_id)) {
                // Check if current user has booked/reserved
                if ($booking->bookedBy_id == Auth::id() ||$booking->reservedBy_id == Auth::id()) {
                    Session::flash('type','info');
                    Session::flash('title', 'Slot reserved');
                    Session::flash('message','Will remain reserved until '.$booking->updated_at->addMinutes(10)->format('Hi').'z');
                    return view('booking.edit',compact('booking', 'user'));
                }
                else {
                    if (isset($booking->reservedBy_id)) {
                        Session::flash('type','danger');
                        Session::flash('title', 'Warning');
                        Session::flash('message', 'Whoops! Somebody else reserved that slot just before you! Please choose another one. The slot will become available if it isn\'t confirmed within 10 minutes.');
                        return redirect('/booking');

                    }
                    else return redirect('/booking')
                        ->with('type', 'danger')
                        ->with('title', 'Warning')
                        ->with('message', 'Whoops! Somebody else booked that slot just before you! Please choose another one.');
                }
            }
            else {
                $booking->reservedBy_id = Auth::id();
                $booking->save();
                Session::flash('type','info');
                Session::flash('title', 'Slot reserved');
                Session::flash('message','Will remain reserved until '.$booking->updated_at->addMinutes(10)->format('Hi').'z');
                return view('booking.edit',compact('booking', 'user'));
            }
        }
        else {
            Session::flash('type','danger');
            Session::flash('title', 'Warning');
            Session::flash('message', 'You need to be logged in before you can book a reservation.');
            return redirect('/booking');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'callsign' => 'required:alpha_num|max:7|unique:bookings,callsign,null,null,event_id,'.$booking->event->id,
            'aircraft' => 'required:alpha_num|between:3,4',
            'selcal1' => 'sometimes:alpha|size:2',
            'selcal2' => 'required_if:selcal1,!='.null.':alpha|size:2',
            'checkStudy' => 'accepted',
            'checkCharts' => 'accepted',
        ]);
        $booking = Booking::find($id);
        $this->validateSELCAL($request->selcal1, $request->selcal2, $booking->event_id);
        $selcal = $request->selcal1 .'-'. $request->selcal2;
        $booking->fill([
            'reservedBy_id' => null,
            'bookedBy_id' => Auth::id(),
            'callsign' => $request->callsign,
            'acType' => $request->aircraft,
            'selcal' => $selcal,
        ]);
        $booking->save();
        Mail::to(Auth::user())->send(new BookingConfirmed($booking));
        return redirect('/booking');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        //
    }

    public function validateSELCAL($a, $b, $eventId) {
        $selcal = $a.'-'.$b;
        $bookings = Booking::where('event_id',$eventId)->get();
        foreach ($bookings as $booking) {
            if ($booking->selcal == $selcal) {
                return false;
            }
        }
        return $a .'-'. $b;
    }

    public function cancelBooking($id) {
        $booking = Booking::findOrFail($id);
        $event = Event::findOrFail($booking->event_id);
        $booking->fill([
            'bookedBy_id' => null,
            'callsign' => null,
            'acType' => null,
            'selcal' => null,
        ]);
        $user = Auth::user();
        $booking->save();
        Mail::to(Auth::user())->send(new BookingCancelled($event, $user));
        return redirect('/booking');
    }

    public function export()
    {
        return new BookingsExport(1);
    }
}
