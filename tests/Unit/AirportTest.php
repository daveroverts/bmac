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
        $airport = Airport::create([
            'icao' => 'EHGG',
            'iata' => 'GRQ',
            'name' => 'Groningen Airport Eelde',
        ]);

        $this->assertDatabaseHas('airports', [
            'icao' => 'EHGG',
            'iata' => 'GRQ',
            'name' => 'Groningen Airport Eelde',
        ]);
    }
}
