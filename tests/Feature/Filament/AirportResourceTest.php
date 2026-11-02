<?php

use App\Filament\Resources\Airports\Pages\CreateAirport;
use App\Filament\Resources\Airports\Pages\EditAirport;
use App\Filament\Resources\Airports\Pages\ListAirports;
use App\Models\Airport;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\TestCase;

it('forbids non-admin users from the Filament panel', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->create())
        ->get('/filament/airports')
        ->assertForbidden();
});

it('lists airports for admins', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());
    $airports = Airport::factory()->count(3)->create();

    Livewire::test(ListAirports::class)
        ->assertOk()
        ->assertCanSeeTableRecords($airports);
});

it('creates an airport and uppercases the codes', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreateAirport::class)
        ->fillForm([
            'icao' => 'ehle',
            'iata' => 'ley',
            'name' => 'Lelystad Airport',
            'latitude' => 52.4603,
            'longitude' => 5.5272,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('airports', ['icao' => 'EHLE', 'iata' => 'LEY']);
});

it('rejects a duplicate ICAO code', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());
    $existing = Airport::factory()->create();

    Livewire::test(CreateAirport::class)
        ->fillForm([
            'icao' => $existing->icao,
            'iata' => 'ZZZ',
            'name' => 'Duplicate',
            'latitude' => 1,
            'longitude' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['icao' => 'unique']);
});

it('hides the delete action for airports used by an event', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());
    $event = Event::factory()->create();

    Livewire::test(EditAirport::class, ['record' => $event->airportDep->getRouteKey()])
        ->assertActionHidden(DeleteAction::class);
});

it('bulk deletes only unused airports', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());
    $event = Event::factory()->create();
    $unused = Airport::factory()->create();

    Livewire::test(ListAirports::class)
        ->selectTableRecords([$unused->getKey(), $event->dep])
        ->callAction(TestAction::make('deleteUnused')->table()->bulk());

    $this->assertModelMissing($unused);
    $this->assertDatabaseHas('airports', ['id' => $event->dep]);
});
