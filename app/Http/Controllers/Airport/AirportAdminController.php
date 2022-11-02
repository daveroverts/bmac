<?php

namespace App\Http\Controllers\Airport;

use App\Models\Airport;
use Illuminate\View\View;
use App\Policies\AirportPolicy;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\AdminController;
use App\Http\Requests\Airport\Admin\StoreAirport;
use App\Http\Requests\Airport\Admin\UpdateAirport;

class AirportAdminController extends AdminController
{
    public function __construct()
    {
        $this->authorizeResource(AirportPolicy::class, 'airport');
    }

    public function index(): View
    {
        $airports = Airport::with(['flightsDep', 'flightsArr', 'eventDep', 'eventArr'])
            ->paginate(100);
        return view('airport.admin.overview', compact('airports'));
    }

    public function create(): View
    {
        return view('airport.admin.form', ['airport' => new Airport()]);
    }

    public function store(StoreAirport $request): RedirectResponse
    {
        $airport = Airport::create($request->validated());
        flashMessage('success', __('Done'), __(':airport has been added!', ['airport' => "$airport->name [$airport->icao | $airport->iata]"]));
        return to_route('admin.airports.index');
    }

    public function show(Airport $airport): View
    {
        return view('airport.admin.show', compact('airport'));
    }

    public function edit(Airport $airport): View
    {
        return view('airport.admin.form', compact('airport'));
    }

    public function update(UpdateAirport $request, Airport $airport): RedirectResponse
    {
        $airport->update($request->validated());
        flashMessage('success', __('Done'), __(':airport has been updated!', ['airport' => "$airport->name [$airport->icao | $airport->iata]"]));

        return to_route('admin.airports.index');
    }

    public function destroy(Airport $airport): RedirectResponse
    {
        if ($airport->flightsDep->isEmpty() && $airport->flightsArr->isEmpty()) {
            $airport->delete();
            flashMessage('success', __('Done'), __(':airport has been deleted!', ['airport' => "$airport->name [$airport->icao | $airport->iata]"]));

            return redirect()->back();
        } else {
            flashMessage(
                'danger',
                __('Warning'),
                __(':airport could not be deleted! It\'s linked to another event', ['airport' => "$airport->name [$airport->icao | $airport->iata]"])
            );
            return redirect()->back();
        }
    }

    public function destroyUnused()
    {
        $this->authorize('destroy', Airport::class);
        Airport::whereDoesntHave('flightsDep')
            ->whereDoesntHave('flightsArr')
            ->whereDoesntHave('eventDep')
            ->whereDoesntHave('eventArr')
            ->delete();

        flashMessage('success', __('Done'), __('Unused airport have been deleted!'));

        return to_route('admin.airports.index');
    }
}
