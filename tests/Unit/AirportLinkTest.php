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

        $this->assertDatabaseHas('airport_links', [
            'id' => $airportLink->id,
            'airport_id' => $airportLink->airport_id,
            'airportLinkType_id' => $airportLink->airportLinkType_id,
            'url' => $airportLink->url,
        ]);
    }
}