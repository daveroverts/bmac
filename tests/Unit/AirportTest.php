<?php

namespace Tests\Unit;

use App\Models\Airport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if a airport can be added.
     */
    public function testItCreatesNewAirport()
    {
        $airport = Airport::factory()->create();

        $this->assertDatabaseHas('airports', [
            'id' => $airport->id,
            'icao' => $airport->icao,
            'iata' => $airport->iata,
            'name' => $airport->name,
        ]);
    }
}
