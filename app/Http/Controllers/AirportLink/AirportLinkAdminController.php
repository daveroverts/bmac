<?php

namespace App\Http\Controllers\AirportLink;

use App\Http\Controllers\AdminController;
use App\Http\Requests\AirportLink\Admin\StoreAirportLink;
use App\Http\Requests\AirportLink\Admin\UpdateAirportLink;
use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\AirportLinkType;
use App\Policies\AirportLinkPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AirportLinkAdminController extends AdminController
{
    public function __construct()
    {
        $this->authorizeResource(AirportLinkPolicy::class, 'airportLink');
    }

    public function index(): View
    {
        $airportLinks = AirportLink::orderBy('airport_id', 'asc')
            ->with(['airport', 'type'])
            ->paginate();

        return view('airportLink.admin.overview', compact('airportLinks'));
    }

    public function create(): View
    {
        $airportLink = new AirportLink();
        $airportLinkTypes = AirportLinkType::all(['id', 'name'])->pluck('name', 'id');
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(function ($airport) {
                /** @var Airport $airport */
                return "$airport->icao | $airport->name | $airport->iata";
            });

        return view('airportLink.admin.form', compact('airportLink', 'airportLinkTypes', 'airports'));
    }

    public function store(StoreAirportLink $request): RedirectResponse
    {
        $airportLink = AirportLink::create($request->validated());
        flashMessage(
            'success',
            __('Done'),
            __(':Type item has been added for :airport', ['Type' => $airportLink->type->name, 'airport' => "{$airportLink->airport->name} [{$airportLink->airport->icao} | {$airportLink->airport->iata}]"])
        );

        return redirect(route('admin.airports.index'));
    }

    public function edit(AirportLink $airportLink): View
    {
        $airportLinkTypes = AirportLinkType::all(['id', 'name'])->pluck('name', 'id');

        return view('airportLink.admin.form', compact('airportLink', 'airportLinkTypes'));
    }

    public function update(UpdateAirportLink $request, AirportLink $airportLink): RedirectResponse
    {
        $airportLink->update($request->validated());
        flashMessage('success', __('Done'), __('Airport Link has been updated'));

        return redirect(route('admin.airportLinks.index'));
    }

    public function destroy(AirportLink $airportLink): RedirectResponse
    {
        $airportLink->delete();
        flashMessage('success', __('Airport link deleted'), __('Airport link has been deleted'));

        return back();
    }
}
