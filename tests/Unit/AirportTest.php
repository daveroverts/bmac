<?php

namespace Tests\Unit;

use App\Models\Airport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AirportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if a airport can be added
     *
     */
    public function testItCreatesNewAirport()
    {
        $icao = 'EHGG';
        $iata = 'GRQ';
        $name = 'Groningen Airport Eelde';

        Airport::create([
            'icao' => $icao,
            'iata' => $iata,
            'name' => $name,
        ]);

        $this->assertDatabaseHas('airports', [
            'icao' => $icao,
            'iata' => $iata,
            'name' => $name,
        ]);
    }
}
