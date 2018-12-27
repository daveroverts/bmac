@component('mail::message')
# Booking change

Dear **{{ $booking->user->full_name }}**,

Your booking for the **{{ $booking->event->name }}** has been amended, please review the changes below:

@component('mail::table')
|  |  |  |
|-----|-----|-----|
@foreach($changes as $change)
@switch($change['name'])
@case('callsign')
| Callsign: | **{{ $change['new'] }}** | (was {{ $change['old'] }}) |
@break
@case('dep')
| ADEP: | **{{ \App\Models\Airport::find($change['new'])->icao }}** | (was {{ \App\Models\Airport::find($change['old'])->first()->icao }}) |
@break
@case('arr')
| ADES: | **{{ \App\Models\Airport::find($change['new'])->icao }}** | (was {{ \App\Models\Airport::find($change['old'])->first()->icao }}) |
@break
@case('ctot')
| CTOT: | **{{ \Carbon\Carbon::parse($change['new'])->format('Hi').'z' }}** | (was {{ \Carbon\Carbon::parse($change['old'])->format('Hi').'z' }}) |
@break
@case('eta')
| ETA: | **{{ \Carbon\Carbon::parse($change['new'])->format('Hi').'z' }}** | (was {{ \Carbon\Carbon::parse($change['old'])->format('Hi').'z' }}) |
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
@case('acType')
| Aircraft code: | **{{ $change['new'] }}** | (was {{ $change['old'] }}) |
@break
@case('message')
| A message has been left: | {{ $change['new'] }} |
@break
@endswitch
@endforeach
@endcomponent

@lang('Regards'),

**{{ config('mail.from.name', config('app.name')) }}**
@endcomponent
