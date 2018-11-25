# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- [renatomarinho/laravel-page-speed](https://github.com/renatomarinho/laravel-page-speed)
    - This was **not** the cause of JS/CSS not initialising
- ``mix.extract()``
- ``booking.export`` in ``laravel-page-speed`` ``skip``
- ``bookings.event.index`` This will now be used to show the Slot Table (``booking.overview``)
- A 301 redirect from ``/booking`` to ``/bookings`` (``bookings.index``). Plan is to keep this for at least 2 events

### Changed
- Most URL's now include a extra 's'
- Production stage will now use both dependencies again. This was the reason the JS/CSS files where not included in production, but where included in dev
- ``app.css`` and ``app.js`` in ``app.blade.php`` now use ``mix()`` instead of ``asset()`` for better detection for versioning
- ``FAQ`` nows shows up again in the navbar. Text is also changed for the ``Holland - America Line [KBOS-EHAM]``
- If event is a ``ONEWAY`` event, filter buttons are hidden

## [v0.6.8b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.8a...v0.6.8b) - 2018-11-23

### Changed
- ``users``.``division`` is now nullable. In case a member did not join a division yet.

## [v0.6.8a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.8...v0.6.8a) - 2018-11-19

### Removed
- [renatomarinho/laravel-page-speed](https://github.com/renatomarinho/laravel-page-speed)

## [v0.6.8](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7c...v0.6.8) - 2018-11-19

### Added
- [renatomarinho/laravel-page-speed](https://github.com/renatomarinho/laravel-page-speed)

### Changed
- Request ``StoreBooking()`` callsign and aircraft rules are slightly changed
- Moves [fzaninotto/Faker](https://github.com/fzaninotto/Faker) to a normal dependency
- Fixes issue where tinyMCE did not initialise in event.sendEmail

## [v0.6.7c](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7b...v0.6.7c) - 2018-11-13

### Changed
- CTOT and ETA changes in update process

## [v0.6.7b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7a...v0.6.7b) - 2018-11-13

### Added
- ETA to:
    - ``booking.admin.edit`` view
    - ``AdminUpdateBooking`` validator
    - ``BookingController@adminUpdate()``
    - ``booking.changed`` email

## [v0.6.7a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7...v0.6.7a) - 2018-11-02

### Changed
- Some actions in ``event.overview`` and ``booking.edit`` have different or new checks.

## [v0.6.7](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.6...v0.6.7) - 2018-11-02

### Changed
- Laravel Mix versioning will now only run with ``yarn run prod``
- ``mix-manifest.json`` will be included again

### Removed
- [Laravel Dusk](https://laravel.com/docs/5.7/dusk)

## [v0.6.6](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.5...v0.6.6) - 2018-11-02

### Added
- Meta tag to disallow robots for the whole website
- TinyMCE (again, but now via Laravel Mix). Seems that it was removed in v0.6.0 by accident after assuming it was already done via Mix.

### Changed
- Image on homepage now uses ``img-fluid`` and ``rounded``
- ``BookingController@index()`` now also orders by callsign if CTOT/ETA is the same
- ``BookingController@cancel()`` can once again make some variables ``null`` if event allows it

### Removed
- ``public\mix-manifest.json``, CI generates this already

## [v0.6.5](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.4...v0.6.5) - 2018-10-11

### Added
- ``AirportController``:
    - ``edit()``
    - ``update()``
    - ``destroy()``
- ``AirportLinkController``
    - ``index()``
    - ``edit()``
    - ``update()``
    - ``destroy()``
- [lorisleiva/laravel-deployer](https://github.com/lorisleiva/laravel-deployer)

### Changed
- Deployment procedure, due to [lorisleiva/laravel-deployer](https://github.com/lorisleiva/laravel-deployer) being added
    
### Removed
- ``AirportLinkController@show()``
- [svenluijten/artisan-view](https://github.com/svenluijten/artisan-view) as dev dependency
- ``JS`` and ``CSS`` files from the repository. They will now be generated, and uploaded on each deployment

## [v0.6.4](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.3...v0.6.4) - 2018-10-09

### Added
- Pagination for ``events.overview``
- ``Groupflight`` in ``EventType`` Enum and ``event_types`` table
- The following event variables in ``events`` table
    - ``import_only``
    - ``uses_times``
    - ``multiple_bookings_allowed``
    - ``is_oceanic_event``
- Event details on homepage if active
- ``image_url`` in ``events`` table

### Changed
- Homepage now checks if event is active, and fill the title, date, and description

## [v0.6.3](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.2...v0.6.3) - 2018-09-29

### Changed
- Saving AirportLink works again
- ``BookingController@index()`` will now use ``strtolower()`` to make sure the filter always works.
- Fixed ``count()`` error when saving a single slot.

### Removed
- [webpatser/laravel-uuid](https://github.com/webpatser/laravel-uuid)

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
