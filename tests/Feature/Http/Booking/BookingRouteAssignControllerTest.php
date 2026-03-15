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

it('redirects non-admin users when attempting to store route assignments', function (): void {
    /** @var Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.events.bookings.routeAssign.store', $event), [])
        ->assertForbidden();
});
