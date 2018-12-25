@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thank you for your recent booking for **{{ $booking->event->name }}** event.
For reference, your booking details are listed below.

@component('mail::table')
|  |  |
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Departs: | **{{ $booking->airportDep->icao  }}** |
| Arrives: | **{{ $booking->airportArr->icao }}** |
@if($booking->event->is_oceanic_event)
| Cruising: | **{{ $booking->oceanicFL }}** |
| SELCAL: | **{{ $booking->selcal }}** |
@endif
| Aircraft: | **{{ $booking->acType }}** |
@if($booking->event->uses_times)
| CTOT: | **{{ $booking->ctot }}** |
@endif
| Event Date: | **{{ $booking->event->startEvent->toFormattedDateString() }}** |
@endcomponent

Regards,

**Dutch VACC**
@endcomponent