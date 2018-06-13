<p>Hi {{ $booking->bookedBy->name }},</p>
<p>Thank you for booking a slot for the {{ $booking->event->name }}!</p>

<p>Here is some information regarding the event</p>
<p>{{ $booking->callsign }}</p>

<p>If you are not flying for the event anymore, please drop the booking, so somebody else can use it</p>
<p>Kind regards,</p>
<p>Dutch VACC Booking System</p>