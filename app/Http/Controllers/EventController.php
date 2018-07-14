<?php

namespace App\Http\Controllers;

use App\{
    Airport, Event
};
use Carbon\Carbon;
use Illuminate\{
    Http\Request, Support\Facades\Session
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
        return view('event.overview',compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $airports = Airport::all();
        return view('event.create',compact('airports'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
            'startEvent' => Carbon::createFromFormat('d-m-Y H:i',$request->dateEvent .' '. $request->timeBeginEvent),
            'endEvent' => Carbon::createFromFormat('d-m-Y H:i',$request->dateEvent .' '. $request->timeEndEvent),
            'startBooking' => Carbon::createFromFormat('d-m-Y H:i',$request->dateBeginBooking .' '. $request->timeBeginBooking),
            'endBooking' => Carbon::createFromFormat('d-m-Y H:i',$request->dateEndBooking .' '. $request->timeEndBooking),
            'timeFeedbackForm' => Carbon::createFromFormat('d-m-Y H:i',$request->dateEndBooking .' '. $request->timeEndBooking)->addHours($request->timeFeedbackForm),
            'description' => $request->description,
        ]);
        $event->save();
        Session::flash('type','success');
        Session::flash('title', 'Done');
        Session::flash('message', 'Event have been created!');
        return redirect('admin/event');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }
}
