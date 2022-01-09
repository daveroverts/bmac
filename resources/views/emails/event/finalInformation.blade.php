@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thanks for booking a slot for the {{ $booking->event->name }} event. Here you can find your slot information:

@component('mail::table')
|  |  |
|-----------|---------------------------|
| Callsign: | **{{ $booking->formatted_callsign }}** |
| Aircraft: | **{{ $booking->formatted_actype }}** |
@if($booking->getRawOriginal('selcal') != null)
| SELCAL: | **{{ $booking->formatted_selcal }}** |
@endif
@if($flight->dep)
| From: | **{{ $flight->airportDep->icao  }}** |
@endif
@if($flight->arr)
| To: | **{{ $flight->airportArr->icao }}** |
@endif
@isset($flight->ctot)
| CTOT: | **{{ $flight->formattedCtot }}** |
@endisset
@isset($flight->eta)
| ETA: | **{{ $flight->formattedEta }}** |
@endisset
@isset($flight->route)
| Full Route: | **{{ $flight->route }}** |
@endisset
@if($booking->event->is_oceanic_event)
@if($flight->getRawOriginal('oceanicFL') != null)
| Oceanic Entry Level: | **{{ $flight->formatted_oceanicfl }}** |
@endif
@if($flight->getRawOriginal('oceanicTrack') != null)
| NAT Track: | **{{ $flight->oceanicTrack }}** |
@endif
| NAT TMI: | **{{ $booking->event->startEvent->dayOfYear }}** |
@else
@if($flight->getRawOriginal('oceanicFL') != null)
| Cruise FL: | **{{ $flight->formatted_oceanicfl }}** |
@endif
@endif
@endcomponent

Visit the [Website]({{ url('/') }}) for further information.

We look forward to seeing you in the virtual skies.

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
This mailbox is not being monitored. Please do not reply to this email. If you have any queries please direct them to the Cross The Land staff members via Discord.
@endcomponent
