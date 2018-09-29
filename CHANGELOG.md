# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Saving AirportLink works again
- ``BookingController@index()`` will now use ``strtolower()`` to make sure the filter always works.
- Fixed ``count()`` error when saving a single slot.

## [v0.6.2](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.1...v0.6.2) - 2018-09-28

### Added
- [beyondcode/laravel-er-diagram-generator](https://github.com/beyondcode/laravel-er-diagram-generator) as a dev dependency.
- This changelog.
- ``Cancel Reservation`` in ``booking.edit`` view
- ``StoreEventRequest``, used by ``EventController@store()``

### Changed
- Issue if multiple events where active, ordering was not correct in this case.
- Makes ``store()`` for the following controllers:
    - AirportController
    - AirportLinkController
    - EventController

## [v0.6.1](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.0b...v0.6.1) - 2018-09-25
### Added
- Check if user has multiple bookings in navbar
    - 1 booking: Still same behaviour
    - Multiple: Each callsign will now be shown
- Check in navbar if event is active (between ``startBooking`` and ``endEvent``). If active, and user has booking, they will show (see above).

### Changed
- Fixed issue where title on booking.show, and booking.edit always shows 'My **reservation**'.
- Fixed some spelling here and there.
- Temporary hiding FAQ till I wrote something where admins can do stuff with it.
- Temporary allows users to have multiple bookings (but not reservations)
- Replaces [Laravel Excel](https://github.com/Maatwebsite/Laravel-Excel) for [rap2hpoutre/fast-excel](https://github.com/rap2hpoutre/fast-excel).
    - [rap2hpoutre/fast-excel](https://github.com/rap2hpoutre/fast-excel) was already used in ``v0.6.0``, but only for imports.
    - [rap2hpoutre/fast-excel](https://github.com/rap2hpoutre/fast-excel) is now also used for exports, and thus everything related to Laravel Excel has been removed.

### Removed
- ``sendFeedbackFrom`` from views, controllers, and DB. It was never really used.
    - Alternative (that was already known) would be to send everybody an E-mail with the button shown in ``event.overview``

## [v0.6.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.5.0...v0.6.0b) - 2018-09-23

### Added
- Gitlab CI:
    - Added 3 stages, ``build``, ``test``, and ``deploy``
    - ``build``: Adds ``composer`` for PHP, and ``yarn`` for JS/CSS
    - Added ``phpcs`` in test stage
    - Added deployment to my stage enviroment.
- Support for different event types.
- Import script for airports.
- Import script for bookings.
- ``ETA`` to a booking.
- Optional filter for departures/arrivals in booking.overview page.

### Changed
- Almost all views, and E-mails have been edited to support the Rotterdam World Port event.
   - At some point, admins can decide what to show in different views / E-mails.
- Homepage for the Rotterdam World Port event

## [v0.5.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.4.0...v0.5.0) - 2018-09-16

### Added
- SELCAL validation (not only duplicate check)
- ``bookings`` table:
   - Adds UUID's, this will be visible by users in the URL's
   - Replaces ``reservedBy_id`` and ``bookedBy_id`` with ``user_id`` and ``status``
- Start of using GitLab CI
   - At the moment, only to run PHPUnit, later also to run Laravel Mix.
   
### Changed
- Some optimalisation on Eloquent models
- Updates FontAwesome to 5.2.0

### Removed
- ``package-lock.json`` as this project is used with yarn

*Sidenote: I didn't really keep track of changes before v0.5.0*