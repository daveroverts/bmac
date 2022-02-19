<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

// Home (no event found)
Breadcrumbs::for('bookings.index', function ($trail) {
    $trail->parent('home');
});

// Home > [Event]
Breadcrumbs::for('events.show', function ($trail, $event) {
    $trail->parent('home');
    $trail->push($event->name . ' [' . $event->startEvent->toFormattedDateString() . ']', route('events.show', $event));
});

// Home > [Event] > Bookings
Breadcrumbs::for('bookings.event.index', function ($trail, $event) {
    $trail->parent('events.show', $event);
    $trail->push('Bookings', route('bookings.event.index', $event));
});

// Home > [Event] > Booking
Breadcrumbs::for('bookings.edit', function ($trail, $booking) {
    $trail->parent('bookings.event.index', $booking->event);
    $trail->push('Booking', route('bookings.edit', $booking));
});

// Home > [Event] > My Booking
Breadcrumbs::for('bookings.show', function ($trail, $booking) {
    $trail->parent('bookings.event.index', $booking->event);
    $trail->push('My Booking', route('bookings.show', $booking));
});

// Home > FAQ
Breadcrumbs::for('faq', function ($trail) {
    $trail->parent('home');
    $trail->push('FAQ', route('faq'));
});

// Home > My settings
Breadcrumbs::for('user.settings', function ($trail) {
    $trail->parent('home');
    $trail->push('My settings', route('user.settings', auth()->user()));
});
