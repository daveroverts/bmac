@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thanks for booking a slot for the {{ $booking->event->name }} event. Here you can find your slot information:

Callsign: **{{ $booking->callsign }}**

@component('mail::table')
| FROM | TO | CTOT | ROUTE |
|----------------------------|:--------------------------:|:--------------------------:|:----:|
| {{ $flight1->airportDep->icao }} | {{ $flight1->airportArr->icao }} | {{ $flight1->ctot }} | {{ $flight1->route }}
| {{ $flight2->airportDep->icao }} | {{ $flight2->airportArr->icao }} | {{ $flight2->ctot }} | {{ $flight2->route }}
@endcomponent

Visit the [Website]({{ url('/') }}) for further information.

We look forward to seeing you in the virtual skies.

Good luck with the product giveaways!

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent
