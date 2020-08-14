@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thanks for booking a slot for the {{ $booking->event->name }} event. Here you can find your slot information:

@component('mail::table')
|  |  |
|-----------|---------------------------|
| Callsign: | **{{ $booking->callsign }}** |
| Aircraft: | **{{ $booking->acType }}** |
@if($booking->getRawOriginal('selcal') != null)
| SELCAL: | **{{ $booking->selcal }}** |
@endif
| From: | **{{ $flight->airportDep->icao  }}** |
| To: | **{{ $flight->airportArr->icao }}** |
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
| Oceanic Entry Level: | **FL{{ $flight->getOriginal('oceanicFL') }}** |
@endif
@if($flight->getRawOriginal('oceanicTrack') != null)
| NAT Track: | **{{ $flight->oceanicTrack }}** |
@endif
| NAT TMI: | **{{ $booking->event->startEvent->dayOfYear }}** |
@else
@if($flight->getRawOriginal('oceanicFL') != null)
| Cruise FL: | **FL{{ $flight->getOriginal('oceanicFL') }}** |
@endif
@endif
@endcomponent

Visit the [Website]({{ url('/') }}) for further information.

We look forward to seeing you in the virtual skies.

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent
