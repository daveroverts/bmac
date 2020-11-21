<?php

namespace App\Http\Controllers\Airport;

use App\Http\Controllers\AdminController;
use App\Http\Requests\Airport\Admin\StoreAirport;
use App\Http\Requests\Airport\Admin\UpdateAirport;
use App\Imports\AirportsImport;
use App\Models\Airport;
use App\Policies\AirportPolicy;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use Rap2hpoutre\FastExcel\FastExcel;

class AirportAdminController extends AdminController
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(AirportPolicy::class, 'airport');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $airports = Airport::orderBy('icao')
            ->with(['flightsDep', 'flightsArr', 'eventDep', 'eventArr'])
            ->paginate(100);
        return view('airport.admin.overview', compact('airports'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('airport.admin.form', ['airport' => new Airport()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAirport  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAirport $request)
    {
        $airport = Airport::create($request->validated());
        flashMessage('success', 'Done', $airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . '] has been added!');
        return redirect(route('admin.airports.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Airport  $airport
     * @return \Illuminate\Http\Response
     */
    public function show(Airport $airport)
    {
        return view('airport.admin.show', compact('airport'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Airport  $airport
     * @return \Illuminate\Http\Response
     */
    public function edit(Airport $airport)
    {
        return view('airport.admin.form', compact('airport'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAirport  $request
     * @param  Airport  $airport
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAirport $request, Airport $airport)
    {
        $airport->update($request->validated());
        flashMessage('success', 'Done', $airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . '] has been updated!');
        return redirect(route('admin.airports.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Airport  $airport
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Airport $airport)
    {
        if ($airport->flightsDep->isEmpty() && $airport->flightsArr->isEmpty()) {
            $airport->delete();
            flashMessage(
                'success',
                'Done',
                $airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . '] has been deleted!'
            );
            return redirect()->back();
        } else {
            flashMessage(
                'danger',
                'Warning',
                $airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . '] could not be deleted! It\'s linked to another event'
            );
            return redirect()->back();
        }
    }

    /**
     * Script to import airports from a .dat file
     *
     * @return \Illuminate\Http\Response
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws NoFilePathGivenException
     * @throws BindingResolutionException
     */
    public function import()
    {
        $file = 'import.csv';
        Storage::disk('local')->put(
            $file,
            "airportId,name,city,country,iata,icao,latitude,longitude,altitude,timezone,dst,tzDatabaseTimeZone,type,source\n" . file_get_contents('https://raw.githubusercontent.com/jpatokal/openflights/master/data/airports.dat')
        );
        (new AirportsImport)->import($file);
        Storage::delete($file);
        flashMessage('success', 'Done', 'Airports have been added');
        return redirect(route('admin.airports.index'));
    }
}
