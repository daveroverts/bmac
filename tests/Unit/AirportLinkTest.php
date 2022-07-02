<?php

namespace Tests\Unit;

use App\Models\AirportLink;
use Tests\TestCase;

it('creates new airport link', function () {
    /** @var TestCase $this */

    /** @var AirportLink $airportLink */
    $airportLink = AirportLink::factory()->create();

    $this->assertDatabaseHas('airport_links', [
        'id' => $airportLink->id,
        'airport_id' => $airportLink->airport_id,
        'airportLinkType_id' => $airportLink->airportLinkType_id,
        'url' => $airportLink->url,
    ]);
});
