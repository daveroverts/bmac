<?php

namespace Tests\Unit;

use App\Models\AirportLink;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AirportLinkTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewAirportLink()
    {
        $airportLink = AirportLink::factory()->create();

        $this->assertDatabaseHas('airport_links', $airportLink->toArray());
    }
}
