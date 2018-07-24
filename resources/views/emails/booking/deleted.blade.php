@component('mail::message')
# Booking deleted

Dear **{{ $user->full_name }}**,

Your booking for **{{ $event->name }}** event has been removed by an administrator. If you would like to know why, please send a E-mail to [events@dutchvacc.nl](mailto:events@dutchvacc.nl)

As long as the bookings remain open ({{ $event->endEvent->format('d-m-Y H:i') }}z), you can still create a new booking.

Regards,

**Dutch VACC**
@endcomponent