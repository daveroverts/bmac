@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thank you for your recent booking for **{{ $booking->event->name }}** event.
For reference, your booking details are listed below.

@component('mail::table')
|  |  |
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Departs: | **{{ $booking->flights()->first()->airportDep->icao  }}** |
| Arrives: | **{{ $booking->flights()->first()->airportArr->icao }}** |
@if($booking->event->is_oceanic_event)
| Cruising: | **{{ $booking->flights()->first()->oceanicFL }}** |
| SELCAL: | **{{ $booking->selcal }}** |
@endif
| Aircraft: | **{{ $booking->acType }}** |
@if($booking->event->uses_times)
| CTOT: | **{{ $booking->flights()->first()->ctot }}** |
@endif
| Event Date: | **{{ $booking->event->startEvent->toFormattedDateString() }}** |
@endcomponent

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent
