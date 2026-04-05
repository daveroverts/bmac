<?php

use Tabuna\Breadcrumbs\Trail;

// Home
Breadcrumbs::for('home', function (Trail $trail): void {
    $trail->push('Home', route('home'));
});

// Home > Admin
Breadcrumbs::for('admin', function (Trail $trail): void {
    $trail->parent('home');
    $trail->push('Admin');
});

// Home > Admin > Airports
Breadcrumbs::for('admin.airports.index', function (Trail $trail): void {
    $trail->parent('admin');
    $trail->push('Airports', route('admin.airports.index'));
});

// Home > Admin > Airports > New
Breadcrumbs::for('admin.airports.create', function (Trail $trail): void {
    $trail->parent('admin.airports.index');
    $trail->push('New', route('admin.airports.create'));
});

// Home > Admin > Airports > [Airport]
Breadcrumbs::for('admin.airports.show', function (Trail $trail, $airport): void {
    $trail->parent('admin.airports.index');
    $trail->push($airport->name . ' [' . $airport->icao . ' | ' . $airport->iata . ']', route('admin.airports.show', $airport));
});

// Home > Admin > Airports > [Airport] > Edit Airport
Breadcrumbs::for('admin.airports.edit', function (Trail $trail, $airport): void {
    $trail->parent('admin.airports.show', airport: $airport);
    $trail->push('Edit Airport', route('admin.airports.edit', $airport));
});

// Home > Admin > Airport Links
Breadcrumbs::for('admin.airportLinks.index', function (Trail $trail): void {
    $trail->parent('admin');
    $trail->push('Airport Links', route('admin.airportLinks.index'));
});

// Home > Admin > Airport Links > New
Breadcrumbs::for('admin.airportLinks.create', function (Trail $trail): void {
    $trail->parent('admin.airportLinks.index');
    $trail->push('New', route('admin.airportLinks.create'));
});

// Home > Admin > Airports > [Airport] > [Airport Link] >  Edit Airport Link
Breadcrumbs::for('admin.airportLinks.edit', function (Trail $trail, $airportLink): void {
    $trail->parent('admin.airports.show', airport: $airportLink->airport);
    $trail->push('Edit Airport Link', route('admin.airportLinks.edit', $airportLink));
});

// Home > Admin > Event Links
Breadcrumbs::for('admin.eventLinks.index', function (Trail $trail): void {
    $trail->parent('admin');
    $trail->push('Event Links', route('admin.eventLinks.index'));
});

// Home > Admin > Event Links > New
Breadcrumbs::for('admin.eventLinks.create', function (Trail $trail): void {
    $trail->parent('admin.eventLinks.index');
    $trail->push('New', route('admin.eventLinks.create'));
});

// Home > Admin > Events > [Event] > [Event Link] >  Edit Event Link
Breadcrumbs::for('admin.eventLinks.edit', function (Trail $trail, $eventLink): void {
    $trail->parent('admin.events.show', event: $eventLink->event);
    $trail->push('Edit Airport Link', route('admin.eventLinks.edit', $eventLink));
});

// Home > Admin > Events
Breadcrumbs::for('admin.events.index', function (Trail $trail): void {
    $trail->parent('admin');
    $trail->push('Events', route('admin.events.index'));
});

// Home > Admin > Events > [Event] > Edit event
Breadcrumbs::for('admin.events.edit', function (Trail $trail, $event): void {
    $trail->parent('admin.events.show', event: $event);
    $trail->push('Edit Event', route('admin.events.edit', $event));
});

// Home > Admin > Events > New
Breadcrumbs::for('admin.events.create', function (Trail $trail): void {
    $trail->parent('admin.events.index');
    $trail->push('New', route('admin.events.create'));
});

// Home > Admin > Events > [Event]
Breadcrumbs::for('admin.events.show', function (Trail $trail, $event): void {
    $trail->parent('admin.events.index');
    $trail->push($event->name . ' [' . $event->startEvent->toFormattedDateString() . ']', route('admin.events.show', $event));
});

// Home > Admin > Events > [Event] > Send E-mail
Breadcrumbs::for('admin.events.emails.bulk.create', function (Trail $trail, $event): void {
    $trail->parent('admin.events.show', event: $event);
    $trail->push('Send E-mail', route('admin.events.emails.bulk.create', $event));
});

// Home (no event found)
Breadcrumbs::for('bookings.index', function (Trail $trail): void {
    $trail->parent('home');
});

// Home > [Event]
Breadcrumbs::for('events.show', function (Trail $trail, $event): void {
    $trail->parent('home');
    $trail->push($event->name . ' [' . $event->startEvent->toFormattedDateString() . ']', route('events.show', $event));
});

// Home > [Event] > Bookings
Breadcrumbs::for('events.bookings.index', function (Trail $trail, $event): void {
    $trail->parent('events.show', event: $event);
    $trail->push('Bookings', route('events.bookings.index', $event));
});

// Home > [Event] > Booking
Breadcrumbs::for('bookings.edit', function (Trail $trail, $booking): void {
    $trail->parent('events.bookings.index', event: $booking->event);
    $trail->push('Booking', route('bookings.edit', $booking));
});

// Home > [Event] > My Booking
Breadcrumbs::for('bookings.show', function (Trail $trail, $booking): void {
    $trail->parent('events.bookings.index', event: $booking->event);
    $trail->push('My Booking', route('bookings.show', $booking));
});

// Home > Admin > [Event] > Add Slot
Breadcrumbs::for('admin.events.bookings.create', function (Trail $trail, $event): void {
    $trail->parent('admin.events.show', event: $event);
    $trail->push('Add Slot(s)', route('admin.events.bookings.create', $event));
});

// Home > Admin > [Event] > Import
Breadcrumbs::for('admin.events.bookings.import.create', function (Trail $trail, $event): void {
    $trail->parent('admin.events.show', event: $event);
    $trail->push('Import', route('admin.events.bookings.import.create', $event));
});

// Home > Admin > [Event] > Booking
Breadcrumbs::for('admin.bookings.edit', function (Trail $trail, $booking): void {
    $trail->parent('admin.events.show', event: $booking->event);
    $trail->push('Edit Booking', route('admin.bookings.edit', $booking));
});

// Home > Admin > [Event] > Auto-Assign FL / Route
Breadcrumbs::for('admin.events.bookings.autoAssign.create', function (Trail $trail, $event): void {
    $trail->parent('admin.events.show', event: $event);
    $trail->push('Auto-Assign FL / Route', route('admin.events.bookings.autoAssign.create', $event));
});

// Home > Admin > [Event] > Route assign
Breadcrumbs::for('admin.events.bookings.routeAssign.create', function (Trail $trail, $event): void {
    $trail->parent('admin.events.show', event: $event);
    $trail->push('Route assign', route('admin.events.bookings.routeAssign.create', $event));
});

// Home > FAQ
Breadcrumbs::for('faq', function (Trail $trail): void {
    $trail->parent('home');
    $trail->push('FAQ', route('faq'));
});

// Home > Admin > FAQ
Breadcrumbs::for('admin.faq.index', function (Trail $trail): void {
    $trail->parent('admin');
    $trail->push('FAQ', route('admin.faq.index'));
});

// Home > Admin > FAQ > New
Breadcrumbs::for('admin.faq.create', function (Trail $trail): void {
    $trail->parent('admin.faq.index');
    $trail->push('New', route('admin.faq.create'));
});

// Home > Admin > FAQ > [FAQ]
Breadcrumbs::for('admin.faq.show', function (Trail $trail, $faq): void {
    $trail->parent('admin.faq.index');
    $trail->push('[' . $faq->id . '] ' . $faq->question);
});

// Home > Admin > FAQ > [FAQ] > Edit FAQ
Breadcrumbs::for('admin.faq.edit', function (Trail $trail, $faq): void {
    $trail->parent('admin.faq.show', faq: $faq);
    $trail->push('Edit FAQ', route('admin.faq.edit', $faq));
});

// Home > My settings
Breadcrumbs::for('user.settings.edit', function (Trail $trail): void {
    $trail->parent('home');
    $trail->push('My settings', route('user.settings.edit'));
});
