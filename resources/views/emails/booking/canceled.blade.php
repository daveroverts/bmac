@component('mail::message')
# Booking cancellation

Dear **{{ $user->full_name }}**,

We’ve processed your cancellation for the **{{ $event->name }}** event and opened the slot you held for other pilots to book. Thanks for letting us know.

We hope to see you at Schiphol in the future.

Regards,

**Dutch VACC**
@endcomponent