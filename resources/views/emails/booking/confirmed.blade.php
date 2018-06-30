@component('mail::message')
# Booking confirmed

Dear **{{ $booking->bookedBy->full_name }}**,

Thank you for your recent booking for **{{ $booking->event->name }}** event.
For reference, your booking details are listed below. On the day of the event, you will receive an email containing your route, oceanic flight level and slot time based on the appropriate NAT tracks.
You will also be able to view this route upon login to the Dutch VACC booking page.

@component('mail::table')
|  |  |
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Departs: | **{{ $booking->dep  }}** |
| Arrives: | **{{ $booking->arr }}** |
| Cruising: | **{{ $booking->oceanicFL }}** |
| SELCAL: | **{{ $booking->selcal }}** |
| Aircraft: | **{{ $booking->acType }}** |
| CTOT: | **{{ $booking->ctot }}** |
| Event Date: | **{{ $booking->event->startEvent->toFormattedDateString() }}** |
@endcomponent

Regards,

**Dutch VACC**
@endcomponent