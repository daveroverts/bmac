# [2.8.0](https://github.com/daveroverts/bmac/compare/v2.7.2...v2.8.0) (2022-06-02)


### Features

* drop php 8.0 support ([ac77241](https://github.com/daveroverts/bmac/commit/ac772415f81f035508b6403118d9f6c01d7d3b4e))

## [2.7.2](https://github.com/daveroverts/bmac/compare/v2.7.1...v2.7.2) (2022-02-27)


### Bug Fixes

* fix multi-flight route assign template ([dc0a639](https://github.com/daveroverts/bmac/commit/dc0a6398a6738962b7804084554960afc7424d0b))

## [2.7.1](https://github.com/daveroverts/bmac/compare/v2.7.0...v2.7.1) (2022-02-23)


### Bug Fixes

* **AirportsImport:** fix validation errors ([2b82f6f](https://github.com/daveroverts/bmac/commit/2b82f6fd75411b7b33c644abe75473f301274710))

# [2.7.0](https://github.com/daveroverts/bmac/compare/v2.6.5...v2.7.0) (2022-02-22)


### Features

* add sentry as extra logging service ([1e793ab](https://github.com/daveroverts/bmac/commit/1e793ab8e7b464c78d4ab1179fccaccc03b8eb03))

## [2.6.5](https://github.com/daveroverts/bmac/compare/v2.6.4...v2.6.5) (2022-02-13)


### Bug Fixes

* fix first login not logging in ([d0eda66](https://github.com/daveroverts/bmac/commit/d0eda6606e635664a535e01bf47f42724db64561)), closes [#408](https://github.com/daveroverts/bmac/issues/408)
* **Helper.php:** fix for redeclare messages ([85711c8](https://github.com/daveroverts/bmac/commit/85711c810d58f081b363bb0a3c1db5f093aabeb9))

## [2.6.4](https://github.com/daveroverts/bmac/compare/v2.6.3...v2.6.4) (2022-02-12)


### Bug Fixes

* upgrade to laravel 9 ([f582f33](https://github.com/daveroverts/bmac/commit/f582f337eeb903a0827a467f93bf29924c6962e0))

## [2.6.3](https://github.com/daveroverts/bmac/compare/v2.6.2...v2.6.3) (2022-01-12)


### Bug Fixes

* Remove active class from logout ([9e11108](https://github.com/daveroverts/bmac/commit/9e11108fea912af15cc036b73df5ad886e58434d)), closes [#409](https://github.com/daveroverts/bmac/issues/409)
* Remove events divider if there's no event ([7f7f0db](https://github.com/daveroverts/bmac/commit/7f7f0db64fd75193d60d8a56ae771473e820be78)), closes [#410](https://github.com/daveroverts/bmac/issues/410)

## [2.6.2](https://github.com/daveroverts/bmac/compare/v2.6.1...v2.6.2) (2022-01-11)


### Bug Fixes

* re-add event / airport links to booking views ([96c639a](https://github.com/daveroverts/bmac/commit/96c639aefd56f916ef481ee7c4da1ba8704837c9))

## [2.6.1](https://github.com/daveroverts/bmac/compare/v2.6.0...v2.6.1) (2022-01-06)


### Bug Fixes

* SendEventFinalInformationNotification sending email to booked user ([5bf1831](https://github.com/daveroverts/bmac/commit/5bf1831d7471d2ebba76fcc0160896e4be855384)), closes [#405](https://github.com/daveroverts/bmac/issues/405)

# [2.6.0](https://github.com/daveroverts/bmac/compare/v2.5.2...v2.6.0) (2022-01-02)


### Bug Fixes

* **AirportImport:** fix majority of airports not importing ([492cfec](https://github.com/daveroverts/bmac/commit/492cfec0041bfa2f6246e7123270e29594bc871a))


### Features

* add coordinates to airport model ([fb88e9f](https://github.com/daveroverts/bmac/commit/fb88e9f0689190a55f9433e8bf9405e749f44903)), closes [#194](https://github.com/daveroverts/bmac/issues/194)
* **AirportImport:** replace route with command ([6e2f000](https://github.com/daveroverts/bmac/commit/6e2f00015ee9617ce5302a053f2599aa33b6ed85))
* **AirportsImport:** add better validation to airport import ([6b1ae83](https://github.com/daveroverts/bmac/commit/6b1ae83959c82d22eabd7cfd60ed8263e2c7330d))
* **AirportsImport:** change airport import file source ([bb0859f](https://github.com/daveroverts/bmac/commit/bb0859f042e5be75343cea31851b49497f0c5c45))


### Performance Improvements

* **AirportImport:** AirportImport now runs on queue ([f7170fe](https://github.com/daveroverts/bmac/commit/f7170fe33b408a9eb4ab9f65938a576015dbbeee))

## [2.5.2](https://github.com/daveroverts/bmac/compare/v2.5.1...v2.5.2) (2022-01-01)


### Bug Fixes

* fix missing time in flatpickr ([036bf0b](https://github.com/daveroverts/bmac/commit/036bf0b38571cc6e2fd51c06667987dc6950e23a))

## [2.5.1](https://github.com/daveroverts/bmac/compare/v2.5.0...v2.5.1) (2022-01-01)


### Bug Fixes

* fix missing styling for flatpickr ([5a2e01b](https://github.com/daveroverts/bmac/commit/5a2e01b1cb8beb2aadc4b0223ba2231626e4b066))

# [2.5.0](https://github.com/daveroverts/bmac/compare/v2.4.0...v2.5.0) (2022-01-01)


### Features

* Add alpine.js ([ac036e2](https://github.com/daveroverts/bmac/commit/ac036e2fc64fd067f62bcc363fa0e0946e66919f))
* add blade-ui-kit package ([d52915c](https://github.com/daveroverts/bmac/commit/d52915ca1dff9744dc1f659c2039632b890ad366))
* **admin:** replace jquery-ui datepicker with flatpickr ([9d91ff1](https://github.com/daveroverts/bmac/commit/9d91ff1fe49e465dc2e3775a83d8c9a3ee94bb25)), closes [#122](https://github.com/daveroverts/bmac/issues/122)
* move tinymce to separate js file ([ac732d8](https://github.com/daveroverts/bmac/commit/ac732d8ddd38af46ec75241d4abc58321916ee72))

# [2.4.0](https://github.com/daveroverts/bmac/compare/v2.3.0...v2.4.0) (2021-12-30)


### Bug Fixes

* airports.import route could not be called by name ([90b265b](https://github.com/daveroverts/bmac/commit/90b265b653ffaa06c44af253d5c076895ff7d184))


### Features

* Add ability to delete unused airports ([46b3d2f](https://github.com/daveroverts/bmac/commit/46b3d2f31f93463ad2321eb689aeda4a9dfde562)), closes [#399](https://github.com/daveroverts/bmac/issues/399)

# [2.3.0](https://github.com/daveroverts/bmac/compare/v2.2.7...v2.3.0) (2021-12-28)


### Features

* Add commitizen with cz-commitlint adapter ([476f56a](https://github.com/daveroverts/bmac/commit/476f56ad4e24285adaf1c9531d7e8a64eecbb216))
* Add semantic-release ([856d3b0](https://github.com/daveroverts/bmac/commit/856d3b05e2e6f64dcf20e80c848933fb34f8ba68))

## [v2.2.7](https://github.com/daveroverts/bmac/compare/v2.2.6...v2.2.7) - 2021-12-25

### Added
- Added PHP 8.1 support
- All e-mails now include the event name as part of subject.

## [v2.2.6](https://github.com/daveroverts/bmac/compare/v2.2.5...v2.2.6) - 2021-12-13

### Added
- Added [protonemedia/laravel-form-components](https://github.com/protonemedia/laravel-form-components) for all forms. Bootstrap 4 stuff have been published and changed to support custom forms (for Bootswatch Flatly) as much as possible. The only one I didn't change was input file.
- All strings (the ones I looked at) while adding laravel-form-components are now translatable.
- Added `EventCleanupReservations` job + command
- Added ability to delete bookings, in case you screwed up import, and want to do that again.
- Added following to `EventResource`
  - `url`: URL of the event for easier access
  - `total_bookings_count`: Total bookings that are in the system for the event.
  - `available_bookings_count`: Bookings that are still available to be booked.

### Changed
- Changed some flash messages.
- Lots of old migrations that I screwed up back when I started this project in 2018

### Removed
- Removed (renatomarinho/laravel-page-speed)[https://github.com/renatomarinho/laravel-page-speed] as it gave to too many headaches, and I always just disabled it because of that.
- Removed `BookingController removeOverdueReservations()`, replaced by `EventCleanupReservationsJob`
- Dropped support for PHP 7.4
- Removed route `/bookings` | `bookings.index`.

## [v2.2.5](https://github.com/daveroverts/bmac/compare/v2.2.4a...v2.2.5) - 2021-11-14

### Changed
- `master` branch renamed to `main`
- Laravel Horizon and Laravel Telescope use dark theme by default.
- Fix for Laravel Horizon and Laravel Telescope gates failing in non-local environments
- Fix for Bootstrap pagination + jQuery UI (for datepicker) missing CSS.
- The very first `users` table migration now does not include unique index. #357

## [v2.2.4a](https://github.com/daveroverts/bmac/compare/v2.2.4...v2.2.4a) - 2021-11-08

### Changed
- Updated config files in `config` to match default Laravel 8 as much as possible.
- Updated `.env.example` to match default Laravel 8 as much as possible.

## [v2.2.4](https://github.com/daveroverts/bmac/compare/v2.2.3...v2.2.4) - 2021-11-07

### Added

- Added [laravel-mix-purgecss](https://github.com/spatie/laravel-mix-purgecss) to minify css.
- Added [Laravel Horizon](https://laravel.com/docs/master/horizon).

## [v2.2.3](https://github.com/daveroverts/bmac/compare/v2.2.2b...v2.2.3) - 2021-11-06

### Added

- Added [AWS SDK for PHP](https://github.com/aws/aws-sdk-php) to support services like S3, SES and SQS

## [v2.2.2b](https://github.com/daveroverts/bmac/compare/v2.2.2a...v2.2.2b) - 2021-10-24

### Added

- Added [Laravel Sail](https://laravel.com/docs/master/sail) with `.devcontainer`

### Changed

- Fix for send test email not working (again)

## [v2.2.2a](https://github.com/daveroverts/bmac/compare/v2.2.2...v2.2.2a) - 2021-10-24

### Changed

- README.md rewritten
- Fix for send test email not working

## [v2.2.2](https://github.com/daveroverts/bmac/compare/v2.2.1...v2.2.2) - 2021-10-09

### Changed

- Deployment package changed from ``lorisleiva/laravel-deployer`` to ``deployphp/deployer``
  - This is temporary for PHP 8 support, I'll move the deployment script somewhere else later on.
- PHP 8 support
- `import_template.xlsx` now uses correct header values.

## [v2.2.1](https://github.com/daveroverts/bmac/compare/v2.2.0...v2.2.1) - 2021-04-03

### Changed

- `BookingResource` times return `null` instead of '-' if not set.

## [v2.2.0](https://github.com/daveroverts/bmac/compare/v2.1.0.e...v2.2.0) - 2021-01-29

### Added

- Added `EventLink`, a copy of `AirportLink`

### Removed

- Removed old static oceanic briefing, use the new `EventLink` for that.

## [v2.1.0.e](https://github.com/daveroverts/bmac/compare/v2.1.0.d...v2.1.0.e) - 2021-01-05

### Changed

- Fix for times not showing in 'views.booking.edit' view.

## [v2.1.0.d](https://github.com/daveroverts/bmac/compare/v2.1.0.c...v2.1.0.d) - 2021-01-05

### Added

- Added '/ddev' to .gitignore.
- Validation for aircraft_type in import.

### Changed

- Fix for is_editable always being ignored in import.
- Fix for destination not showing in 'views.booking.edit' view.

## [v2.1.0.c](https://github.com/daveroverts/bmac/compare/v2.1.0.b...v2.1.0.c) - 2021-01-03

### Added

- Re-Added 'route' as extra optional import field.

## [v2.1.0.b](https://github.com/daveroverts/bmac/compare/v2.1.0.a...v2.1.0.b) - 2021-01-03

### Added

- Added 'oceanicTrack' as extra optional import field.
- Added 'fl' as extra optional import field.

### Changed

- Fixed issue where empty notes in import return HTTP 500.
- PHP 8 support.

## [v2.1.0.a](https://github.com/daveroverts/bmac/compare/v2.1.0...v2.1.0.a) - 2020-12-22

### Changed

- Fixed issue where empty callsigns in import return HTTP 500.
- Renamed 'EOBT' to 'CTOT' in example label.

## [v2.1.0](https://github.com/daveroverts/bmac/compare/v2.0.0...v2.1.0) - 2020-12-14

### Added

- Added Events and Listeners for the Notifications we already have.
- Added option to use different OAuth2 Provider if you don't want to use Vatsim-Connect #90, thanks to @blt950
- `notes` now work for all event types

### Changed

- Deployment is now done using Github Actions instead of Gitlab CI.
- ``FinalInformationEmail`` is now generic.
- `flights.route` changed from `varchar` to `text` to allow longer routes.
- Upgrade to Laravel 8
- Flash messages no longer show that a e-mail was sent.
- Booking airports are now optional, and won't show in views / emails if one hasn't been set.
- Replaced ``rap2hpoutre/fast-excel`` with ``maatwebsite/excel``
- ``booking.overview`` now uses polling depending if it's needed
- User e-mail no longer unique

### Removed

## [v2.0.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v2.0.0...v1.5.3) - 2020-06-14

### Added

- Vatsim SSO replaced with Vatsim Connect
- `BookingAdminController@import()` now supports Route
- [davejamesmiller/laravel-breadcrumbs](https://github.com/davejamesmiller/laravel-breadcrumbs) replaced with [tabuna/breadcrumbs](https://github.com/tabuna/breadcrumbs)
- Possiblity to change the default Bootswatch Flatly colors #23 + various site variables #16, thanks to @blt950
- Laravel Livewire, replaces current ``bookings.overview``

### Changed

- Removed unique index for `icao` in `2018_06_06_172719_create_airports_table.php` to fix a problem in the first migrations, Thanks to @blt950
- Upgraded to Laravel 7
- `BookingAdminController@import()` now supports even more empty fields
- Homepage / Upcoming Events page layout #23, thanks to @blt950

### Removed

- Laravel Passport, as it was not really used

## [v1.5.3](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.3...v1.5.2) - 2020-03-04

### Changed

- BookingController@export() for normal events now includes ETA, and now never falls back to event times

## [v1.5.2](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.2...v1.5.1.e) - 2020-02-16

### Added

- `final_information_email_sent_at` to `Booking` model to keep track if the 'final_information_email' was already sent.
- It's now possible to not notify users when a booking is changed, should that be needed.
- We can redirect to the `events.show` page if one was opened before logging in.

### Changed

- Always empty `SELCAL` from `Booking` when cancelling one. We never need that for unbooked flights
- Fixed issue where mutators would not set something to null when actually needed.
- Removed some mutators and accessors from `Booking` model that got moved to the `Flight` model
- Fixed issue where `BookingAdminController@update()` did not take the `Flight` model changes
- Don't show `Send e-mail` button if there's no user coupled to a booking
- The order thingy for multi city
- `login` route no longer accepts a optional Booking, it now accepts either `booking` or `event` in the GET request.

## [v1.5.1.e](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.1.e...v1.5.1.d) - 2020-01-24

### Added

- Route is now included in booking.confirmed email if one is actually set

### Changed

- Fix for `BookingAdminController@autoAssign()` giving 500's. Will have to optimise the check later on.

## [v1.5.1.d](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.1.d...v1.5.1.c) - 2020-01-22

### Added

- Bookingsexport now contains aircraft

### Changed

- `BookingController@edit()` 'Already reserved' message to be more understanding

## [v1.5.1.c](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.1.c...v1.5.1.b) - 2020-01-17

### Changed

- Email stuff

## [v1.5.1.b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.1.b...v1.5.1.a) - 2020-01-17

### Changed

- Some more email stuff and added notes to view

## [v1.5.1.a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.1.a...v1.5.1) - 2020-01-17

### Changed

- Email styling

## [v1.5.1](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.1...v1.5.0.c) - 2020-01-15

### Added

- Export for Multi-city event type (with e-mails and with CTOT's)
- Added `auth.isLoggedIn` middleware to `BookingController@edit()` that you are logged in
- E-mail template for Multi city event type
- Route assign for multi-city event type

### Changed

- In the navbar, the Dutch VACC logo's are now routing to the Dutch VACC website
- Route for `bookings.autoAssignForm` + ``bookings.autoAssign` is now URL friendly

## [v1.5.0c](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.0.c...v1.5.0.b) - 2020-01-07

### Changed

- Fix for `pic` undefined in some views

### Changed

- `event.admin.import` view formats now include `<abbr>`'s to better explain what's needed

## [v1.5.0b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.0.b...v1.5.0.a) - 2020-01-04

### Changed

- `HomeController@index()` `$events` temporary puts `Multi-City` events below the rest. This is temporary, and will be replaced with a `order_by` soon

## [v1.5.0a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.0.a...v1.5.0) - 2019-12-30

### Changed

- in `booking.overview` views, the 'bookings will be available at x' is now a h3 with a br

## [v1.5.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.5.0...v1.4.2) - 2019-12-29

### Added

- Event type 'multi city'
- `Flight` model, which takes over most fields from `Booking` model
- Some separate views for multi city event type (too lazy to combine for now)

## [v1.4.2](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.2...v1.4.1) - 2019-10-11

### Added

- Added `AdminController`, that is used by all AdminControllers. It uses the `IsAdmin` Middleware by default.
- Added `nextEventsForFaq()` helper function, that makes use of the new parameter found in `nextEvents()` (see below).
- [Laravel Passport](https://laravel.com/docs/5.8), at the time of writing, only to prepare to consume own API.
- AutoAssign now has the possibility to auto-assign all flights, regardless of being booked, if needed.
- Added `facade/ignition`

### Changed

- Controllers have been split into normal and AdminControllers
- Views have been split into normal and admin views.
- Requests have been split into normal and admin requests.
- `routes/web.php` now only includes calls to controllers.
- Pretty much all routes have been renamed (admin routes now has `admin.` as prefix)
- Fixed issue where it was never possible to update a FAQ item.
- `nextEvents()` now accepts parameter to use `with()` to prevent N+1 problem
- Upgraded to Laravel 6.0
- Fixed issue where a user could book a flight, after 10 minutes has passed, and crash the whole `booking.overview` view

### Removed

- Removed Default Auth scaffolding, as it was never used (except some parts of `LoginController`.
- Removed `pragmarx/version`, as it was never used

## [v1.4.2](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.2...v1.4.1) - 2019-10-11

### Added

- Added `AdminController`, that is used by all AdminControllers. It uses the `IsAdmin` Middleware by default.
- Added `nextEventsForFaq()` helper function, that makes use of the new parameter found in `nextEvents()` (see below).
- [Laravel Passport](https://laravel.com/docs/5.8), at the time of writing, only to prepare to consume own API.
- AutoAssign now has the possibility to auto-assign all flights, regardless of being booked, if needed.
- Added `facade/ignition`

### Changed

- Controllers have been split into normal and AdminControllers
- Views have been split into normal and admin views.
- Requests have been split into normal and admin requests.
- `routes/web.php` now only includes calls to controllers.
- Pretty much all routes have been renamed (admin routes now has `admin.` as prefix)
- Fixed issue where it was never possible to update a FAQ item.
- `nextEvents()` now accepts parameter to use `with()` to prevent N+1 problem
- Upgraded to Laravel 6.0
- Fixed issue where a user could book a flight, after 10 minutes has passed, and crash the whole `booking.overview` view

### Removed

- Removed Default Auth scaffolding, as it was never used (except some parts of `LoginController`.
- Removed `pragmarx/version`, as it was never used

## [v1.4.1](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.1...v1.4.0c) - 2019-08-13

### Changed

- Navbar now has a extra link to goto it's bookings
- Homepage now shows all online events that have 'show on homepage' enabled
- 'Open Booking Table' button is now moved to the bottom and middle

## [v1.4.0c](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.0c...v1.4.0b) - 2019-08-13

### Changed

- `EventPolicy@view` now always returns true

## [v1.4.0b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.0b...v1.4.0a) - 2019-08-13

### Changed

- `EventController@show` no longer uses `EventPolicy@view`

## [v1.4.0a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.0a...v1.4.0) - 2019-08-13

### Changed

- `events.show` now no longer uses the `IsAdmin` middleware

## [v1.4.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.4.0...v1.3.0) - 2019-08-12

### Added

- You can now use `DB_LOWER_STRING_LENGTH=true` in the `.env` file to use shorter string lengths (especially for indexes)
- `events.admin.show`, which is a copy of `events.show`
- In `home` and `events.show`, the word 'the' has been added before the event name + date
- A `Event` can now be shown on the homepage or not

### Changed

- CI images now use 7.3 alpine images <https://github.com/edbizarro/gitlab-ci-pipeline-php>
- `booking.overview` now only shows Vatsim ID instead of name + Vatsim ID
- `events.show` is now something that's reachable for normal users
- Fixed issue where it was no longer possible to link events and FAQ items
- `nextEvents()` now has a 3th parameter to show events that needs to be shown on the homepage
- `nextEvent()` now has a optional parameter to only show the first event that needs to be shown on the homepage.
- The navbar got a major overhaul
  - `bookings.index` (Bookings) button has been removed
  - Added a dropdown that shows online events, with a route to `events.show` for each of them
  - Within each event, if a user has booking(s) for a event, they are also shown. This replaces the separate 'My booking' / callsigns items.
  - For the admin items, the detection of active routes is improved to include more then just index

## [v1.3.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.3.0...v1.2.0a) - 2019-07-27

### Added

- SonarQube Scans, thanks to @Johnnei
- Bugsnag Browser integration
- Login + Logout are now logged
- FAQ is now a CRUD thingy
- E-mail tester for event E-mails
- A event can now be marked as online/offline
- `nextEvents()` now also accepts a second parameter `showAll`, default false. This can be used to show offline events
- A `Booking` now has `is_editable` to determine if a user can edit some details (`callsign` and `acType`)

### Changed

- `oceanicFL` is now always nullable
- `Airport`, `AirportLink`, and `Event` now use 1 form, instead of 2 different for `create` and `edit`
- `BookingController@store()` Bulk save now allows a float as separator (examples: 1.5 and 1.25)
- Changed `table-primary` to `table-active`
- [Laravel Telescope](https://laravel.com/docs/5.7/telescope) is now also enabled outside of local. Admin rights are needed to access.
- `Airport` `getFullNameAttribute()` now uses name by default in case you are not logged in (same behaviour as when you create account for the first time).
- Updated to Laravel 5.8
- A `Event` is now only reachable via the slug to prevent PostgreSQL issues
- `booking.show` will only show a edit button if a booking is editable
- When editing a booking you already own, you won't see the 'Slot reserved' message
- `booking.create` and `booking.admin.edit` now only fills in `CTOT` and `ETA` with it's value, and won't return the current time

### Security

- [CVE-2019-10905](https://github.com/erusev/parsedown/issues/699)

## [v1.2.0a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.2.0a...v1.2.0) - 2019-04-17

### Changed

- Fix for Booking import not working, due to `airports` table change (id [int])

## [v1.2.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.2.0...v1.1.0) - 2019-02-11

### Added

- [Laravel Telescope](https://laravel.com/docs/5.7/telescope) as dev dependency, only used local
- Bugsnag recipe for Deployer
- Gates/Policies
- `mix-manifest.json` once again excluded (not sure what past me was thinking)

## [v1.1.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v1.1.0...v1.0.0) - 2018-12-27

### Added

- [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog)
  - `.env`: `ACTIVITY_LOGGER_ENABLED`, default `true`
- Factories:
  - `AirportFactory`
  - `AirportLinkFactory`
  - `BookingFactory`
  - `EventFactory`
- Tests:
  - `AirportLinkTest`
  - `BookingsTest`
- `fullName` attribute for `Airport` model
- A warning if you try to do something with `yarn`
- `laravel-query-detector` as dev dependency
- Eager loading to several controllers / view to make them load faster, thanks to `laravel-query-detector`:
  - `BookingController@index()` / `booking.overview`
  - `AirportController@index()` / `airport.overview`
  - `AirportLinkController@index()` / `airportLink.overview`
  - `EventController@index()` / `event.overview`
- Check in `BookingController@destroy()` and `BookingController@adminEdit` to prevent data being edited after a event has ended
- [sweetalert2](https://github.com/sweetalert2/sweetalert2)
- `scripts` stack, placed below the default scripts
  - This is used to show SweetAlert, but can be used for others things if needed.
- Confirm messages to most 'destructive' actions (deletes and emails).
- Duplicate check for `ICAO` and `IATA` in `StoreAirport` request
- `id` (auto-increment) to Airport Model.
- API Resource:
  - `/events/upcoming/{limit?}`, which is the same as `/events`, but with a optional limit (default 3)
  - `/api/events` now has pagination
  - `/events/{event}/bookings`, which shows all **booked** bookings related to the event
  - `BookingResource` + `EventResource`:
    - Links have been added to both `dep` and `arr`, routing to `AirportResource`
  - `BookingResource`:
    - Link to the user has been added.
    - `full_name` and `event_name` have been added
- Route binding for `event` now accepts `id` and `slug`
- `holdOnWeGotABadAss()` that replaces all RickRolls

### Changed

- `AirportTest` and `EventTest`
- `.env.testing` + `MYSQL_DATABASE`
  - `DB_DATABASE` added `_test` in the name
- Optimizes `$fillable` vs `$guarded` in Models.
- Some models use `Request->all()` now
- `yarn` to `npm` in GitLab CI.
- `npm install` changed to `npm ci`
- `Laravel Mix` has been updated from v2 to v4
- The old alerts have been replaced by SweetAlert
- `events.email.final` is now a `PATCH` route
- `bookings.cancel` is now a `PATCH` route
- `id` is now the primary key for the Airport Model.
- Lots of views, emails, requests, and controllers have been changed to work with the new changes.
- API Resource:
  - Resources that showed `AirportResource`, now only show the ICAO
  - `BookingResource`:
    - `user` now only shows the id or `null`
    - `event` (name) replaced with `event_id`
- All `Mailables` have been converted to `Notifications`. For now, E-mails, but it could be expanded to notifications on the site
- CI script

### Removed

- `yarn.lock` (replaced by `package-lock.json`)
- API Resource:
  - `meta` removed from all collections
  - `AirportLinkResource`:
    - `airport` removed. Was a bit strange to include that, as you should only get `AirportLink` via `Airport`

## [v1.0.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.7.0c...v1.0.0) - 2018-12-15

### Added

- Redirect to a booking if logging in (and available)
- `nextEvent()`, which is now placed in `routes/web.php` for route `/` + `BookingController@index()`
- [laravel-breadcrumbs](https://github.com/davejamesmiller/laravel-breadcrumbs)
- Extra `/` in `public/robots.txt`
- The following API resources / routes:
  - AirportLinkResource
  - AirportLinksCollection
  - AirportResource / `/airports/{airport}`
  - AirportsCollection / `/airports`
  - BookingResource / `/bookings/{booking}`
  - BookingsCollection / `/bookings`
  - EventResource / `/events/{event}`
  - EventsCollection / `/events`
  - UserResource / `/users/{user}`
  - UsersCollection / `/users`
- PHP CodeSniffer xml file, source: [https://medium.com/@nandosalles/the-ruleset-phpcs-for-my-laravel-projects-a54cb3c95b31](https://medium.com/@nandosalles/the-ruleset-phpcs-for-my-laravel-projects-a54cb3c95b31)
- `EventController@destroy()`
- Relation `Airport`->`Event` `eventDep` + `eventArr`
- `EventController@update()`
- `UpdateEvent` Request
- `events.show` now actually does something
- `BookingController@store()` now allows same CTOT's, as long as the Departure Airports are different.
- Enum `AirportView`:
  - 0 = `NAME`: Amsterdam Airport Schiphol - EHAM | [AMS]
  - 1 = `ICAO`: EHAM - Amsterdam Airport Schiphol | [AMS]
  - 2 = `IATA`: AMS - Amsterdam Airport Schiphol | [EHAM]
  - This is now added in the `users` Model. Users can now choose what to see by default in views (except e-mails, that might come in later).
- `use_monospace_font` Allows users to see `callsign` and `aircraft` with a monospace font.
- `users` prefix:
  - `settings`:
    - Makes it possible to edit user settings (see previous 2 points)

### Changed

- Upcoming event order is now correct
- `BookingController@edit()` had one `flashMessage()` that did not show correctly
- Airports now use a `orderBy('ICAO')`
- Relation `Event`->`dep` renamed to `Event`->`airportDep`
- Relation `Event`->`arr` renamed to `Event`->`airportArr`
- Both of these relations where not in use before, but if called, it would not really work due to same names.
- When creating an event, `dep` and `arr` are now separated, and now actually save in the database
- `event.create` now picks up old values correctly
- Some if statements in `BookingController` and `booking.overview` now check if the dateTime are on the current minute (example: Bookings should be available at 0000z, not a minute later)

## [v0.7.0c](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.7.0b...v0.7.0c) - 2018-12-14

### Changed

- Final information E-mail for the Holland - America line event [KBOS-EHAM]

## [v0.7.0b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.7.0a...v0.7.0b) - 2018-11-25

### Changed

- SELCAL validation was wrong at one point. This is now solved

## [v0.7.0a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.7.0...v0.7.0a) - 2018-11-25

### Changed

- Oceanic PDF is no longer available. Changed to VNAS information instead

## [v0.7.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.8b...v0.7.0) - 2018-11-25

### Added

- [renatomarinho/laravel-page-speed](https://github.com/renatomarinho/laravel-page-speed)
  - This was **not** the cause of JS/CSS not initialising
- `mix.extract()`
- `booking.export` in `laravel-page-speed` `skip`
- `bookings.event.index` This will now be used to show the Slot Table (`booking.overview`)
- A 301 redirect from `/booking` to `/bookings` (`bookings.index`). Plan is to keep this for at least 2 events

### Changed

- Most URL's now include a extra 's'
- Production stage will now use both dependencies again. This was the reason the JS/CSS files where not included in production, but where included in dev
- `app.css` and `app.js` in `app.blade.php` now use `mix()` instead of `asset()` for better detection for versioning
- `FAQ` nows shows up again in the navbar. Text is also changed for the `Holland - America Line [KBOS-EHAM]`
- If event is a `ONEWAY` event, filter buttons are hidden

## [v0.6.8b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.8a...v0.6.8b) - 2018-11-23

### Changed

- `users`.`division` is now nullable. In case a member did not join a division yet.

## [v0.6.8a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.8...v0.6.8a) - 2018-11-19

### Removed

- [renatomarinho/laravel-page-speed](https://github.com/renatomarinho/laravel-page-speed)

## [v0.6.8](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7c...v0.6.8) - 2018-11-19

### Added

- [renatomarinho/laravel-page-speed](https://github.com/renatomarinho/laravel-page-speed)

### Changed

- Request `StoreBooking()` callsign and aircraft rules are slightly changed
- Moves [fzaninotto/Faker](https://github.com/fzaninotto/Faker) to a normal dependency
- Fixes issue where tinyMCE did not initialise in event.sendEmail

## [v0.6.7c](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7b...v0.6.7c) - 2018-11-13

### Changed

- CTOT and ETA changes in update process

## [v0.6.7b](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7a...v0.6.7b) - 2018-11-13

### Added

- ETA to:
  - `booking.admin.edit` view
  - `AdminUpdateBooking` validator
  - `BookingController@adminUpdate()`
  - `booking.changed` email

## [v0.6.7a](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.7...v0.6.7a) - 2018-11-02

### Changed

- Some actions in `event.overview` and `booking.edit` have different or new checks.

## [v0.6.7](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.6...v0.6.7) - 2018-11-02

### Changed

- Laravel Mix versioning will now only run with `yarn run prod`
- `mix-manifest.json` will be included again

### Removed

- [Laravel Dusk](https://laravel.com/docs/5.7/dusk)

## [v0.6.6](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.5...v0.6.6) - 2018-11-02

### Added

- Meta tag to disallow robots for the whole website
- TinyMCE (again, but now via Laravel Mix). Seems that it was removed in v0.6.0 by accident after assuming it was already done via Mix.

### Changed

- Image on homepage now uses `img-fluid` and `rounded`
- `BookingController@index()` now also orders by callsign if CTOT/ETA is the same
- `BookingController@cancel()` can once again make some variables `null` if event allows it

### Removed

- `public\mix-manifest.json`, CI generates this already

## [v0.6.5](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.4...v0.6.5) - 2018-10-11

### Added

- `AirportController`:
  - `edit()`
  - `update()`
  - `destroy()`
- `AirportLinkController`
  - `index()`
  - `edit()`
  - `update()`
  - `destroy()`
- [lorisleiva/laravel-deployer](https://github.com/lorisleiva/laravel-deployer)

### Changed

- Deployment procedure, due to [lorisleiva/laravel-deployer](https://github.com/lorisleiva/laravel-deployer) being added

### Removed

- `AirportLinkController@show()`
- [svenluijten/artisan-view](https://github.com/svenluijten/artisan-view) as dev dependency
- `JS` and `CSS` files from the repository. They will now be generated, and uploaded on each deployment

## [v0.6.4](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.3...v0.6.4) - 2018-10-09

### Added

- Pagination for `events.overview`
- `Groupflight` in `EventType` Enum and `event_types` table
- The following event variables in `events` table
  - `import_only`
  - `uses_times`
  - `multiple_bookings_allowed`
  - `is_oceanic_event`
- Event details on homepage if active
- `image_url` in `events` table

### Changed

- Homepage now checks if event is active, and fill the title, date, and description

## [v0.6.3](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.2...v0.6.3) - 2018-09-29

### Changed

- Saving AirportLink works again
- `BookingController@index()` will now use `strtolower()` to make sure the filter always works.
- Fixed `count()` error when saving a single slot.

### Removed

- [webpatser/laravel-uuid](https://github.com/webpatser/laravel-uuid)

## [v0.6.2](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.1...v0.6.2) - 2018-09-28

### Added

- [beyondcode/laravel-er-diagram-generator](https://github.com/beyondcode/laravel-er-diagram-generator) as a dev dependency.
- This changelog.
- `Cancel Reservation` in `booking.edit` view
- `StoreEventRequest`, used by `EventController@store()`

### Changed

- Issue if multiple events where active, ordering was not correct in this case.
- Makes `store()` for the following controllers:
  - AirportController
  - AirportLinkController
  - EventController

## [v0.6.1](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.6.0b...v0.6.1) - 2018-09-25

### Added

- Check if user has multiple bookings in navbar
  - 1 booking: Still same behaviour
  - Multiple: Each callsign will now be shown
- Check in navbar if event is active (between `startBooking` and `endEvent`). If active, and user has booking, they will show (see above).

### Changed

- Fixed issue where title on booking.show, and booking.edit always shows 'My **reservation**'.
- Fixed some spelling here and there.
- Temporary hiding FAQ till I wrote something where admins can do stuff with it.
- Temporary allows users to have multiple bookings (but not reservations)
- Replaces [Laravel Excel](https://github.com/Maatwebsite/Laravel-Excel) for [rap2hpoutre/fast-excel](https://github.com/rap2hpoutre/fast-excel).
  - [rap2hpoutre/fast-excel](https://github.com/rap2hpoutre/fast-excel) was already used in `v0.6.0`, but only for imports.
  - [rap2hpoutre/fast-excel](https://github.com/rap2hpoutre/fast-excel) is now also used for exports, and thus everything related to Laravel Excel has been removed.

### Removed

- `sendFeedbackFrom` from views, controllers, and DB. It was never really used.
  - Alternative (that was already known) would be to send everybody an E-mail with the button shown in `event.overview`

## [v0.6.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.5.0...v0.6.0b) - 2018-09-23

### Added

- Gitlab CI:
  - Added 3 stages, `build`, `test`, and `deploy`
  - `build`: Adds `composer` for PHP, and `yarn` for JS/CSS
  - Added `phpcs` in test stage
  - Added deployment to my stage enviroment.
- Support for different event types.
- Import script for airports.
- Import script for bookings.
- `ETA` to a booking.
- Optional filter for departures/arrivals in booking.overview page.

### Changed

- Almost all views, and E-mails have been edited to support the Rotterdam World Port event.
  - At some point, admins can decide what to show in different views / E-mails.
- Homepage for the Rotterdam World Port event

## [v0.5.0](https://gitlab.com/daveroverts/Book-me-a-cookie/compare/v0.4.0...v0.5.0) - 2018-09-16

### Added

- SELCAL validation (not only duplicate check)
- `bookings` table:
  - Adds UUID's, this will be visible by users in the URL's
  - Replaces `reservedBy_id` and `bookedBy_id` with `user_id` and `status`
- Start of using GitLab CI
  - At the moment, only to run PHPUnit, later also to run Laravel Mix.

### Changed

- Some optimalisation on Eloquent models
- Updates FontAwesome to 5.2.0

### Removed

- `package-lock.json` as this project is used with yarn

_Sidenote: I didn't really keep track of changes before v0.5.0_
