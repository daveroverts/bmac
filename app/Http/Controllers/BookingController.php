<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Event;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $bookings = Booking::where('event_id', 1)->get();
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
            Booking::create([
                'event_id' => $request->id,
                'dep' => 'EHAM',
                'arr' => 'KBOS',
                'ctot' => $event_start,
            ])->save();
        }
        return redirect('/booking')->with('message', 'Bookings have been created!');
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
        return view('booking.show',compact('booking'));
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
                    return view('booking.edit',compact('booking', 'user'));
                }
                else {
                    if (isset($booking->reservedBy_id)) {
                        return redirect('/booking')->with('message', 'Whoops! Somebody else reserved that slot just before you! Please choose another one. The slot will become available if it isn\'t confirmed within 10 minutes.');
                    }
                    else return redirect('/booking')->with('message', 'Whoops! Somebody else booked that slot just before you! Please choose another one.');
                }
            }
            else {
                $booking->reservedBy_id = Auth::id();
                $booking->save();
                return view('booking.edit',compact('booking', 'user'))->with('message', 'Slot reserved!');
            }
        }
        else return redirect('/booking')->with('message', 'You need to be logged in before you can book a reservation.');
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
            'callsign' => 'alpha_num|max:7',
            'aircraft' => 'alpha_num|between:3,4',
            'selcal1' => 'alpha|max:2',
            'selcal2' => 'alpha|max:2',
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
}
