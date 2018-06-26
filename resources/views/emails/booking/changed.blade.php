@component('mail::message')
# Booking change

Dear **{{ $booking->bookedBy->full_name }}**,

Your booking for the **{{ $booking->event->name }}** has been amended, please review the changes below:

@component('mail::table')
|  |  |  |
|-----|-----|-----|
@foreach($changes as $change)
@switch($change['name'])
@case('ctot')
| CTOT: | **{{ \Carbon\Carbon::parse($change['new'])->format('Hi').'z' }}** | (was {{ \Carbon\Carbon::parse($change['old'])->format('Hi').'z' }}) |
@break
@case('route')
| Route: | **{{ $change['new'] }}** | (was {{ $change['old'] }}) |
@break
@case('oceanicTrack')
| Track: | **{{ $change['new'] }}** | (was {{ $change['old'] }}) |
@break
@case('oceanicFL')
| Oceanic Entry FL: | **FL{{ $change['new'] }}** | (was FL{{ $change['old'] }}) |
@break
@endswitch
@endforeach
@endcomponent

Regards,

**Dutch VACC**
@endcomponent