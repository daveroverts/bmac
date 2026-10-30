<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores route assignments from a valid CSV file', function (): void {
    /** @var Tests\TestCase $this */
    $event = Event::factory()->create();

    $airportA = $event->airportDep;
    $airportB = $event->airportArr;

    $booking = Booking::factory()->create(['event_id' => $event->id]);
    $flight = $booking->flights()->create([
        'dep' => $airportA->id,
        'arr' => $airportB->id,
    ]);

    $csvContent = "from,to,route,notes\n{$airportA->icao},{$airportB->icao},DCT HELEN,test note";

    $uploadedFile = UploadedFile::fake()->createWithContent('routes.csv', $csvContent);

    Storage::fake();

    $this->post(route('admin.events.bookings.routeAssign.store', $event), [
        'file' => $uploadedFile,
    ])->assertRedirect(route('events.bookings.index', $event));

    $flight->refresh();

    expect($flight->route)->toBe('DCT HELEN')
        ->and($flight->notes)->toBe('test note');
});

it('only assigns routes to flights of the given event', function (): void {
    /** @var Tests\TestCase $this */
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();

    $airportA = $event->airportDep;
    $airportB = $event->airportArr;

    $flight = Booking::factory()->create(['event_id' => $event->id])
        ->flights()->create(['dep' => $airportA->id, 'arr' => $airportB->id]);
    $otherFlight = Booking::factory()->create(['event_id' => $otherEvent->id])
        ->flights()->create(['dep' => $airportA->id, 'arr' => $airportB->id, 'route' => 'OLD ROUTE', 'notes' => 'old note']);

    $csvContent = "from,to,route,notes\n{$airportA->icao},{$airportB->icao},DCT HELEN,test note";

    Storage::fake();

    $this->post(route('admin.events.bookings.routeAssign.store', $event), [
        'file' => UploadedFile::fake()->createWithContent('routes.csv', $csvContent),
    ])->assertRedirect(route('events.bookings.index', $event));

    expect($flight->refresh()->route)->toBe('DCT HELEN')
        ->and($otherFlight->refresh()->route)->toBe('OLD ROUTE')
        ->and($otherFlight->notes)->toBe('old note');
});

it('redirects non-admin users when attempting to store route assignments', function (): void {
    /** @var Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.events.bookings.routeAssign.store', $event), [])
        ->assertForbidden();
});
