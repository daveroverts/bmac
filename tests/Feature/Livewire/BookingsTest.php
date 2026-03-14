<?php

use App\Enums\EventType;
use App\Livewire\Bookings;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

it('renders for admin even when event is offline', function (): void {
    $event = Event::factory()->create(['is_online' => false]);

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertSee('Slot Table');
});

it('renders for non-admin user when event is online', function (): void {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create(['is_online' => true]);

    Livewire::actingAs($user)
        ->test(Bookings::class, ['event' => $event])
        ->assertSee('Slot Table');
});

it('returns 404 for non-admin user when event is offline', function (): void {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create(['is_online' => false]);

    Livewire::actingAs($user)
        ->test(Bookings::class, ['event' => $event])
        ->assertStatus(404);
});

it('shows filter buttons for FLYIN events', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::FLYIN->value]);

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertSee('Departures')
        ->assertSee('Arrivals');
});

it('hides filter buttons for non-FLYIN/GROUPFLIGHT event types', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::ONEWAY->value]);

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertDontSee('Departures')
        ->assertDontSee('Arrivals');
});

it('updates the filter property when setFilter is called', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::FLYIN->value]);

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertSet('filter', null)
        ->call('setFilter', 'departures')
        ->assertSet('filter', 'departures')
        ->call('setFilter', 'arrivals')
        ->assertSet('filter', 'arrivals');
});

it('enables polling when event is currently active', function (): void {
    $event = Event::factory()->create([
        'is_online' => true,
        'startEvent' => now()->subHour(),
        'endEvent' => now()->addHours(2),
        'startBooking' => now()->subHour(),
        'endBooking' => now()->addHour(),
    ]);

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertSet('refreshInSeconds', 15);
});

it('disables polling when event is not yet active', function (): void {
    // Default factory creates events with startBooking in the future
    $event = Event::factory()->create();

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertSet('refreshInSeconds', 0);
});

it('counts total and booked bookings correctly', function (): void {
    $event = Event::factory()->create(['is_online' => true]);

    $booked = Booking::factory()->booked()->create(['event_id' => $event->id]);
    $unassigned = Booking::factory()->unassigned()->create(['event_id' => $event->id]);

    // withWhereHas requires flights to exist for a booking to be counted
    $booked->flights()->create(['dep' => $event->dep, 'arr' => $event->arr]);
    $unassigned->flights()->create(['dep' => $event->dep, 'arr' => $event->arr]);

    Livewire::test(Bookings::class, ['event' => $event])
        ->assertSet('total', 2)
        ->assertSet('booked', 1);
});
