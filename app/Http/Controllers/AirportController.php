<?php

namespace App\Http\Controllers;

use App\{Http\Requests\StoreAirport, Models\Airport};
use Illuminate\{Http\Request, Support\Facades\Storage};
use Rap2hpoutre\FastExcel\FastExcel;

class AirportController extends Controller
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
        $airports = Airport::paginate(100);
        return view('airport.overview', compact('airports'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('airport.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAirport $request)
    {
        $airport = Airport::create($request->only(['icao', 'iata', 'name']));
        flashMessage('success', 'Done', $airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . '] has been added!');
        return redirect(route('airport.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param Airport $airport
     * @return void
     */
    public function show(Airport $airport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Airport $airport
     * @return void
     */
    public function edit(Airport $airport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Airport $airport
     * @return void
     */
    public function update(Request $request, Airport $airport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Airport $airport
     * @return void
     */
    public function destroy(Airport $airport)
    {
        //
    }

    public function import()
    {
        return response()->stream(function () {
            $file = 'import.csv';
            Storage::disk('local')->put($file, "airportId,name,city,country,iata,icao,latitude,longitude,altitude,timezone,dst,tzDatabaseTimeZone,type,source\n" . file_get_contents('https://raw.githubusercontent.com/jpatokal/openflights/master/data/airports.dat'));
            $collection = (new FastExcel)->configureCsv(',')->import(Storage::path($file), function ($line) {
                // Check if airport already exists
                if (!Airport::where('icao', $line['icao'])->exists()) {
                    // Check if ICAO and IATA are filled in
                    if (strlen($line['icao']) == 4 && strlen($line['iata']) == 3) {
                        Airport::create([
                            'icao' => $line['icao'],
                            'iata' => $line['iata'],
                            'name' => $line['name'],
                        ]);
                    }
                }
            });
            Storage::delete($file);
            flashMessage('succes', 'Done', 'Airports have been added');
        });
    }
}
