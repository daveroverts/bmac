<?php

namespace App\Http\Controllers\AirportLink;

use App\Http\Controllers\AdminController;
use App\Http\Requests\AirportLink\Admin\StoreAirportLink;
use App\Http\Requests\AirportLink\Admin\UpdateAirportLink;
use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\AirportLinkType;
use App\Policies\AirportLinkPolicy;

class AirportLinkAdminController extends AdminController
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(AirportLinkPolicy::class, 'airportLink');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $airportLinks = AirportLink::orderBy('airport_id', 'asc')
            ->with(['airport', 'type'])
            ->paginate();
        return view('airportLink.admin.overview', compact('airportLinks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $airportLink = new AirportLink();
        $airportLinkTypes = AirportLinkType::all(['id', 'name'])->pluck('name', 'id');
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(function ($airport) {
                /** @var Airport $airport */
                return "$airport->icao [$airport->name ($airport->iata )]";
            });
        return view('airportLink.admin.form', compact('airportLink', 'airportLinkTypes', 'airports'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAirportLink  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAirportLink $request)
    {
        $airportLink = AirportLink::create($request->validated());
        flashMessage(
            'success',
            __('Done'),
            __(':Type item has been added for :airport', ['Type' => $airportLink->type->name, 'airport' => "{$airportLink->airport->name} [{$airportLink->airport->icao} | {$airportLink->airport->iata}]"])
        );
        return redirect(route('admin.airports.index'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     */
    public function edit(AirportLink $airportLink)
    {
        $airportLinkTypes = AirportLinkType::all(['id', 'name'])->pluck('name', 'id');

        return view('airportLink.admin.form', compact('airportLink', 'airportLinkTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAirportLink  $request
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAirportLink $request, AirportLink $airportLink)
    {
        $airportLink->update($request->validated());
        flashMessage('success', __('Done'), __('Airport Link has been updated'));
        return redirect(route('admin.airportLinks.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AirportLink  $airportLink
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(AirportLink $airportLink)
    {
        $airportLink->delete();
        flashMessage('success', __('Airport link deleted'), __('Airport link has been deleted'));
        return back();
    }
}
