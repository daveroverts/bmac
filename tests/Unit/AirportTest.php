<?php

namespace Tests\Unit;

use App\Models\Airport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class AirportTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Test if a airport can be added
     *
     */
    public function testItCreatesNewAirport()
    {
        $airport = factory(\App\Models\Airport::class)->make();

        Airport::create($airport->toArray());

        $this->assertDatabaseHas('airports', $airport->toArray());
    }
}
