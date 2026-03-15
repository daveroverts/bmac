<?php

namespace Tests\Unit;

use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\AirportLinkType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

it('creates new airport link', function (): void {
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

it('has BelongsTo relationship for airport', function (): void {
    $airportLink = AirportLink::factory()->create();

    expect($airportLink->airport())->toBeInstanceOf(BelongsTo::class);
});

it('has BelongsTo relationship for type', function (): void {
    $airportLink = AirportLink::factory()->create();

    expect($airportLink->type())->toBeInstanceOf(BelongsTo::class);
});

it('resolves airport to correct Airport model', function (): void {
    $airport = Airport::factory()->create();
    $airportLink = AirportLink::factory()->create(['airport_id' => $airport->id]);

    expect($airportLink->airport->id)->toBe($airport->id);
});

it('AirportLinkType has HasMany relationship for links', function (): void {
    /** @var AirportLinkType $type */
    $type = AirportLinkType::query()->first();

    expect($type->links())->toBeInstanceOf(HasMany::class);
});

it('resolves AirportLinkType links to correct AirportLink models', function (): void {
    /** @var AirportLinkType $type */
    $type = AirportLinkType::query()->first();

    $airportLink = AirportLink::factory()->create(['airportLinkType_id' => $type->id]);

    expect($type->links->contains($airportLink))->toBeTrue();
});
