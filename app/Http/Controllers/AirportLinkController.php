<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAirportLink;
use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\AirportLinkType;
use Illuminate\Http\Request;

class AirportLinkController extends Controller
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $airportLinkTypes = AirportLinkType::all();
        $airports = Airport::all();
        return view('airportLink.create', compact('airportLinkTypes', 'airports'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAirportLink $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAirportLink $request)
    {
        $airportLink = AirportLink::create([
            'icao_airport' => $request->airport,
            'airportLinkType_id' => $request->airportLinkType,
            'name' => $request->name,
            'url' => $request->url,
        ]);
        flashMessage('success', 'Done', $airportLink->type->name . ' item has been added for ' . $airportLink->airport->name . ' [' . $airportLink->airport->icao . ' | ' . $airportLink->airport->iata . ']');
        return redirect(route('airport.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     */
    public function show(AirportLink $airportLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     */
    public function edit(AirportLink $airportLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AirportLink $airportLink)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     */
    public function destroy(AirportLink $airportLink)
    {
        //
    }
}
