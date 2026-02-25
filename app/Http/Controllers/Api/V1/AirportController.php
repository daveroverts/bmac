<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirportResource;
use App\Http\Resources\AirportsCollection;
use App\Models\Airport;

class AirportController extends Controller
{
    /**
     * Return a paginated list of all airports.
     */
    public function index(): AirportsCollection
    {
        return new AirportsCollection(Airport::query()->paginate());
    }

    /**
     * Return a single airport.
     */
    public function show(Airport $airport): AirportResource
    {
        return new AirportResource($airport);
    }
}
