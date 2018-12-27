<?php

namespace Tests\Unit;

use App\Models\AirportLink;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AirportLinkTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewAirportLink()
    {
        $airportLink = factory(\App\Models\AirportLink::class)->make();

        AirportLink::create($airportLink->toArray());

        $this->assertDatabaseHas('airport_links', $airportLink->toArray());
    }
}
