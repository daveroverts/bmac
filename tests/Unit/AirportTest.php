<?php

namespace Tests\Unit;

use App\Models\Airport;
use Tests\TestCase;

it('creates new airport', function () {
    /** @var TestCase $this */

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $this->assertDatabaseHas('airports', [
        'id' => $airport->id,
        'icao' => $airport->icao,
        'iata' => $airport->iata,
        'name' => $airport->name,
    ]);
});
