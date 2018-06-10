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
        return view('booking.overview',compact('event','bookings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        //
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
                $booking->ctot = Carbon::createFromFormat('H:i:s',$booking->ctot)->format('Hi');
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
            'callsign' => 'string|max:7',
            'aircraft' => 'string|between:3,4',
            'selcal1' => 'string|max:2',
            'selcal2' => 'string|max:2',
            'checkStudy' => 'accepted',
            'checkCharts' => 'accepted',
        ]);

        $booking = Booking::find($id);
        $selcal = $request->selcal1 . '-'.$request->selcal2;
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
}
