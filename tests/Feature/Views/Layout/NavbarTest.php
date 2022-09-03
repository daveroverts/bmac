<?php

use App\Models\Booking;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;

it('can render navbar', function () {
    /** @var TestCase $this */

    $this->view('layouts.app')
        ->assertSee('Events')
        ->assertSee('FAQ')
        ->assertSee('Admin')
        ->assertSee('Contact Us')
        ->assertSee(auth()->user()->full_name);

    $this->actingAs(User::factory()->create());

    $this->view('layouts.app')
        ->assertSee('Events')
        ->assertSee('FAQ')
        ->assertDontSee('Admin')
        ->assertSee('Contact Us')
        ->assertSee(auth()->user()->full_name);

    auth()->logout();

    $this->view('layouts.app')
        ->assertSee('Events')
        ->assertSee('FAQ')
        ->assertDontSee('Admin')
        ->assertSee('Contact Us')
        ->assertSee('Login');
});


it('can render events', function () {
    /** @var TestCase $this */

    /** @var Event $event1 */
    $event1 = Event::factory()->expired()->create();

    /** @var Event $event2 */
    $event2 = Event::factory()->create();

    /** @var Event $event3 */
    $event3 = Event::factory()->notOnHomePage()->create();

    $this->view('layouts.app')
        ->assertDontSee($event1->name)
        ->assertSee($event2->name)
        ->assertDontSee($event3->name);
});

it('can render single booking', function () {
    /** @var TestCase $this */

    $booking = Booking::factory()->booked()->create(['user_id' => auth()->id()]);

    $this->view('layouts.app')
        ->assertSee($booking->event->name)
        ->assertSee('My booking');
});

it('can render multiple bookings', function () {
    /** @var TestCase $this */

    $event = Event::factory()->create();

    $booking1 = Booking::factory()->booked()->create([
        'event_id' => $event->getKey(),
        'user_id' => auth()->id(),
        'callsign' => 'PJDPX',
        'acType' => 'B737'
    ]);

    $booking2 = Booking::factory()->booked()->create([
        'event_id' => $event->getKey(),
        'user_id' => auth()->id(),
        'callsign' => 'CPA252',
        'acType' => 'A35K'
    ]);

    $this->view('layouts.app')
        ->assertSee($event->name)
        ->assertSee($booking1->callsign)
        ->assertSee($booking2->callsign);
});
