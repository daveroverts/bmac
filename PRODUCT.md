# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Primary: VATSIM pilots.** They find an upcoming event, choose a slot, book it, and return to it for the details and briefing material they need to fly. When pilot and staff needs conflict, pilots win.

**Secondary: division event staff.** They create events in `/admin`, import or add slots, assign routes and flights, manage airports, links and FAQs, send event emails, and export bookings. Admin tooling must stay capable and efficient, but it does not set the design priority.

## Product Purpose

Book me a Cookie (BMAC) is a booking system for VATSIM events. It lets a division publish an event's slots and lets pilots claim one. Success means a pilot gets a slot and has the right flight information, and staff can run the event without building their own tooling.

## Positioning

BMAC is VATSIM-native and open source. Vatsim Connect login and flight-simulation concepts such as CTOT, SELCAL, airports, routes and charts are built in, rather than layered on a generic booking tool. Any division can self-host its own copy under the MIT license, with its own name, colours and contact details.

## Operating Context

- Pilots sign in with Vatsim Connect by default; another OAuth2 provider can be configured.
- A booking moves through three statuses: unassigned, reserved, booked.
- Times are displayed in Zulu.
- Staff work with spreadsheet imports, using the templates in `public/`, and export bookings for use elsewhere.
- Pilots also receive booking and event information by email.
- Each division runs its own deployment, so URLs, contact details and pilot-brief and chart links vary.

## Capabilities and Constraints

- Event types: one-way, citypair, fly-in, groupflight, and multiflights (limited to 2 flights per booking).
- Slots can be imported or added manually, and routes can be assigned in bulk.
- Airports and events can carry links for charts, briefing, scenery and similar material, shown to pilots after they book.
- A read-only public JSON API is available at `/api/v1/`. The unversioned routes are deprecated and will be removed on 2026-12-31.
- Stack is an existing Laravel, Livewire, Blade and Bootstrap application.
- Division name, title, links and Bootstrap colours are set through `SITE_*` and `BOOTSTRAP_COLOR_*` environment values, and colours are compiled at build time.
- Event descriptions are staff-authored rich text, and event images are optional. Designs must hold up with variable content.

## Brand Commitments

- Name: Book me a Cookie, abbreviated BMAC.
- White-label stays: any division must be able to apply its own name, logo and colours, so no design may depend on one fixed brand.
- BMAC should still have a recognisable default identity for deployments that do not customise it.
- Assets: `public/images/bmac-logo.png` for BMAC, with `division-horizontal.png` and `division-square.png` as division logo slots in `public/images/`.

## Evidence on Hand

- BMAC originated with one event, The Holland - America Line, as recorded in `README.md`.
- The MIT license is in `LICENSE.md`.
- No testimonials, usage figures, or list of deploying divisions are on hand. Do not fabricate them.

## Product Principles

1. **The pilot's path comes first.** Finding an event, securing a slot and getting flight details take priority over everything else on a shared surface.
2. **Speak VATSIM.** Use the network's and aviation's own terms and conventions rather than generic booking language.
3. **Every division can make it theirs.** Features and designs must survive a change of name, logo, colours and content.
4. **Stay self-hostable.** Each division runs its own copy, so work must not assume a central service or a single deployment.
