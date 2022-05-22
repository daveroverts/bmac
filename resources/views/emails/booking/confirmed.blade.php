@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thank you for your recent booking for **{{ $booking->event->name }}** event.
For reference, your booking details are listed below.

@component('mail::table')
|  |  |
@if($booking->event->event_type_id == \App\Enums\EventType::MULTIFLIGHTS)
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Aircraft: | **{{ $booking->acType }}** |
| Flight #1: | **{{ $booking->airportCtot(1, false)  }}** |
| Flight #2: | **{{ $booking->airportCtot(2, false)  }}** |
| Event Date: | **{{ $booking->event->startEvent->toFormattedDateString() }}** |
@else
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Aircraft: | **{{ $booking->acType }}** |
@if($booking->flights()->first()->dep)
| Departs: | **{{ $booking->flights()->first()->airportDep->icao  }}** |
@endif
@if($booking->flights()->first()->arr)
| Arrives: | **{{ $booking->flights()->first()->airportArr->icao }}** |
@endif
@if($booking->event->is_oceanic_event)
| Cruising: | **{{ $booking->flights()->first()->formatted_oceanicfl }}** |
| SELCAL: | **{{ $booking->formatted_selcal }}** |
@endif
@if(!empty($booking->flights()->first()->route))
| Route: | **{{ $booking->flights()->first()->route }}** |
@endif
@if(!empty($booking->flights()->first()->notes))
| Notes: | **{{ $booking->flights()->first()->formatted_notes }}** |
@endif
@if($booking->event->uses_times)
| CTOT: | **{{ $booking->flights()->first()->formattedCtot }}** |
@endif
| Event Date: | **{{ $booking->event->startEvent->toFormattedDateString() }}** |
@endif
@endcomponent

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
This mailbox is not being monitored. Please do not reply to this email. If you have any queries please direct them to the Cross The Land staff members via Discord.
@endcomponent
