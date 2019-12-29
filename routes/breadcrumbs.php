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
Breadcrumbs::for('admin.airports.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Airports', route('admin.airports.index'));
});

// Home > Admin > Airports > New
Breadcrumbs::for('admin.airports.create', function ($trail) {
    $trail->parent('admin.airports.index');
    $trail->push('New', route('admin.airports.create'));
});

// Home > Admin > Airports > [Airport]
Breadcrumbs::for('admin.airports.show', function ($trail, $airport) {
    $trail->parent('admin.airports.index');
    $trail->push($airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . ']', route('admin.airports.show', $airport));
});

// Home > Admin > Airports > [Airport] > Edit Airport
Breadcrumbs::for('admin.airports.edit', function ($trail, $airport) {
    $trail->parent('admin.airports.show', $airport);
    $trail->push('Edit Airport', route('admin.airports.edit', $airport));
});

// Home > Admin > Airport Links
Breadcrumbs::for('admin.airportLinks.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Airport Links', route('admin.airportLinks.index'));
});

// Home > Admin > Airport Links > New
Breadcrumbs::for('admin.airportLinks.create', function ($trail) {
    $trail->parent('admin.airportLinks.index');
    $trail->push('New', route('admin.airportLinks.create'));
});

// Home > Admin > Airports > [Airport] > [Airport Link] >  Edit Airport Link
Breadcrumbs::for('admin.airportLinks.edit', function ($trail, $airportLink) {
    $trail->parent('admin.airports.show', $airportLink->airport);
    $trail->push('Edit Airport Link', route('admin.airportLinks.edit', $airportLink));
});

// Home > Admin > Events
Breadcrumbs::for('admin.events.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Events', route('admin.events.index'));
});

// Home > Admin > Events > [Event] > Edit event
Breadcrumbs::for('admin.events.edit', function ($trail, $event) {
    $trail->parent('admin.events.show', $event);
    $trail->push('Edit Event', route('admin.events.edit', $event));
});

// Home > Admin > Events > New
Breadcrumbs::for('admin.events.create', function ($trail) {
    $trail->parent('admin.events.index');
    $trail->push('New', route('admin.events.create'));
});

// Home > Admin > Events > [Event]
Breadcrumbs::for('admin.events.show', function ($trail, $event) {
    $trail->parent('admin.events.index');
    $trail->push($event->name . ' [' . $event->startEvent->toFormattedDateString() . ']', route('admin.events.show', $event));
});

// Home > Admin > Events > [Event] > Send E-mail
Breadcrumbs::for('admin.events.email.form', function ($trail, $event) {
    $trail->parent('admin.events.show', $event);
    $trail->push('Send E-mail', route('admin.events.email.form', $event));
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

// Home > Admin > [Event] > Add Slot
Breadcrumbs::for('admin.bookings.create', function ($trail, $event) {
    $trail->parent('admin.events.show', $event);
    $trail->push('Add Slot(s)', route('admin.bookings.create', $event));
});

// Home > Admin > [Event] > Import
Breadcrumbs::for('admin.bookings.importForm', function ($trail, $event) {
    $trail->parent('admin.events.show', $event);
    $trail->push('Import', route('admin.bookings.importForm', $event));
});

// Home > Admin > [Event] > Booking
Breadcrumbs::for('admin.bookings.edit', function ($trail, $booking) {
    $trail->parent('admin.events.show', $booking->event);
    $trail->push('Edit Booking', route('admin.bookings.edit', $booking));
});

// Home > Admin > [Event] > Auto-Assign FL / Route
Breadcrumbs::for('admin.bookings.autoAssignForm', function ($trail, $event) {
    $trail->parent('admin.events.show', $event);
    $trail->push('Auto-Assign FL / Route', route('admin.bookings.autoAssignForm', $event));
});

// Home > FAQ
Breadcrumbs::for('faq', function ($trail) {
    $trail->parent('home');
    $trail->push('FAQ', route('faq'));
});

// Home > Admin > FAQ
Breadcrumbs::for('admin.faq.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('FAQ', route('admin.faq.index'));
});

// Home > Admin > FAQ > New
Breadcrumbs::for('admin.faq.create', function ($trail) {
    $trail->parent('admin.faq.index');
    $trail->push('New', route('admin.faq.create'));
});

// Home > Admin > FAQ > [FAQ]
Breadcrumbs::for('admin.faq.show', function ($trail, $faq) {
    $trail->parent('admin.faq.index');
    $trail->push('[' . $faq->id . '] ' . $faq->question);
});

// Home > Admin > FAQ > [FAQ] > Edit FAQ
Breadcrumbs::for('admin.faq.edit', function ($trail, $faq) {
    $trail->parent('admin.faq.show', $faq);
    $trail->push('Edit FAQ', route('admin.faq.edit', $faq));
});

// Home > My settings
Breadcrumbs::for('user.settings', function ($trail) {
    $trail->parent('home');
    $trail->push('My settings', route('user.settings', Auth::user()));
});
