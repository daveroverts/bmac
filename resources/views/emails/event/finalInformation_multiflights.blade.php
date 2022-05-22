@component('mail::message')
# Booking confirmed

Dear **{{ $booking->user->full_name }}**,

Thanks for booking a slot for the {{ $booking->event->name }} event. Here you can find your slot information:

Callsign: **{{ $booking->formatted_callsign }}**

@component('mail::table')
| FROM | TO | CTOT | ROUTE |
|----------------------------|:--------------------------:|:--------------------------:|:----:|
@foreach ($booking->flights()->get() as $flight)
| {{ $flight->airportDep->icao }} | {{ $flight->airportArr->icao }} | {{ $flight->formattedCtot }} | {{ $flight->route }}
@endforeach
@endcomponent

Visit the [Website]({{ url('/') }}) for further information.

We look forward to seeing you in the virtual skies, and wish you best of luck with the giveaways.

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent
