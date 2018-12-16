<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

// Home > Admin
Breadcrumbs::for('admin', function ($trail) {
    $trail->parent('home');
    $trail->push('Admin');
});

// Home > Admin > Airports
Breadcrumbs::for('airports.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Airports', route('airports.index'));
});

// Home > Admin > Airports > New
Breadcrumbs::for('airports.create', function ($trail) {
    $trail->parent('airports.index');
    $trail->push('New', route('airports.create'));
});

// Home > Admin > Airports > [Airport]
Breadcrumbs::for('airports.show', function ($trail, $airport) {
    $trail->parent('airports.index');
    $trail->push($airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . ']', route('airports.show', $airport));
});

// Home > Admin > Airports > [Airport] > Edit Airport
Breadcrumbs::for('airports.edit', function ($trail, $airport) {
    $trail->parent('airports.show', $airport);
    $trail->push('Edit Airport', route('airports.edit', $airport));
});

// Home > Admin > Airport Links
Breadcrumbs::for('airportLinks.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Airport Links', route('airportLinks.index'));
});

// Home > Admin > Airport Links > New
Breadcrumbs::for('airportLinks.create', function ($trail) {
    $trail->parent('airportLinks.index');
    $trail->push('New', route('airportLinks.create'));
});

// Home > Admin > Airports > [Airport] > [Airport Link] >  Edit Airport Link
Breadcrumbs::for('airportLinks.edit', function ($trail, $airportLink) {
    $trail->parent('airports.show', $airportLink->airport);
    $trail->push('Edit Airport Link', route('airportLinks.edit', $airportLink));
});

// Home > Admin > Events
Breadcrumbs::for('events.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Events', route('events.index'));
});

// Home > Admin > Events > [Event] > Edit event
Breadcrumbs::for('events.edit', function ($trail, $event) {
    $trail->parent('events.show', $event);
    $trail->push('Edit Event', route('events.edit', $event));
});

// Home > Admin > Events > New
Breadcrumbs::for('events.create', function ($trail) {
    $trail->parent('events.index');
    $trail->push('New', route('events.create'));
});

// Home > Admin > Events > [Event]
Breadcrumbs::for('events.show', function ($trail, $event) {
    $trail->parent('events.index');
    $trail->push($event->name . ' [' . $event->startEvent->toFormattedDateString() . ']', route('events.show', $event));
});

// Home > Admin > Events > [Event] > Send E-mail
Breadcrumbs::for('events.email.form', function ($trail, $event) {
    $trail->parent('events.show', $event);
    $trail->push('Send E-mail', route('events.email.form', $event));
});

// Home (no event found)
Breadcrumbs::for('bookings.index', function ($trail) {
    $trail->parent('home');
});

// Home > [Event]
Breadcrumbs::for('bookings.event.index', function ($trail, $event) {
    $trail->parent('home');
    $trail->push($event->name . ' [' . $event->startEvent->toFormattedDateString() . ']', route('bookings.event.index', $event));
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

// Home > Admin > [Event] > Add Slot
Breadcrumbs::for('bookings.create', function ($trail, $event) {
    $trail->parent('events.show', $event);
    $trail->push('Add Slot(s)', route('bookings.create', $event));
});

// Home > Admin > [Event] > Import
Breadcrumbs::for('bookings.admin.importForm', function ($trail, $event) {
    $trail->parent('events.show', $event);
    $trail->push('Import', route('bookings.admin.importForm', $event));
});

// Home > Admin > [Event] > Booking
Breadcrumbs::for('bookings.admin.edit', function ($trail, $booking) {
    $trail->parent('events.show', $booking->event);
    $trail->push('Edit Booking', route('bookings.admin.edit', $booking));
});

// Home > Admin > [Event] > Auto-Assign FL / Route
Breadcrumbs::for('bookings.admin.autoAssignForm', function ($trail, $event) {
    $trail->parent('events.show', $event);
    $trail->push('Auto-Assign FL / Route', route('bookings.admin.autoAssignForm', $event));
});

// Home > FAQ
Breadcrumbs::for('faq', function ($trail) {
    $trail->parent('home');
    $trail->push('FAQ', route('faq'));
});

// Home > My settings
Breadcrumbs::for('user.settings', function ($trail) {
    $trail->parent('home');
    $trail->push('My settings', route('user.settings', Auth::user()));
});
