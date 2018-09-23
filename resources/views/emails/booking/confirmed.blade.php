@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thank you for your recent booking for **{{ $booking->event->name }}** event.
For reference, your booking details are listed below.

@component('mail::table')
|  |  |
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Departs: | **{{ $booking->dep  }}** |
| Arrives: | **{{ $booking->arr }}** |
| Aircraft: | **{{ $booking->acType }}** |
| Event Date: | **{{ $booking->event->startEvent->toFormattedDateString() }}** |
@endcomponent

Regards,

**Dutch VACC**
@endcomponent