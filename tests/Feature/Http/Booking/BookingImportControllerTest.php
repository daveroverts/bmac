<?php

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

it('imports bookings from a CSV file', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $depAirport = Airport::factory()->create(['icao' => 'EHAM']);
    $arrAirport = Airport::factory()->create(['icao' => 'EGLL']);

    /** @var Event $event */
    $event = Event::factory()->create([
        'dep' => $depAirport->id,
        'arr' => $arrAirport->id,
    ]);

    $csvContent = "origin,destination,call_sign,aircraft_type,notes\n";
    $csvContent .= "EHAM,EGLL,KLM01,B738,Test note\n";
    $csvContent .= "EHAM,EGLL,,,,\n";

    $file = UploadedFile::fake()->createWithContent('bookings.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    expect(Booking::where('event_id', $event->id)->count())->toBe(2);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => 'KLM01',
        'acType' => 'B738',
        'is_editable' => false,
    ]);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => null,
        'is_editable' => true,
    ]);
});
